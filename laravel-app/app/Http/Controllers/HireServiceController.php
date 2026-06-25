<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\NameFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HireServiceController extends Controller
{
    private const PROVIDERS_PER_PAGE = 9;
    private const REVIEW_LIMIT = 6;

    public function show(Request $request)
    {
        $user = $this->currentLegacyUser($request);
        $serviceTypes = config('service_marketplace.service_types', []);
        $budgetRanges = config('service_marketplace.budget_ranges', []);
        $selectedServiceType = trim((string) $request->query('service_type', ''));
        if ($selectedServiceType !== '' && ! array_key_exists($selectedServiceType, $serviceTypes)) {
            $selectedServiceType = '';
        }

        $search = trim((string) $request->query('search', ''));
        $location = trim((string) $request->query('location', ''));
        $sortBy = trim((string) $request->query('sort_by', 'top_rated'));
        if (! in_array($sortBy, ['top_rated', 'most_projects', 'newest', 'name'], true)) {
            $sortBy = 'top_rated';
        }

        return view('services.hire', [
            'budgetRanges' => $budgetRanges,
            'locations' => $this->serviceProviderLocations(),
            'location' => $location,
            'providers' => $this->serviceProviderQuery($search, $selectedServiceType, $location, $sortBy)
                ->paginate(self::PROVIDERS_PER_PAGE)
                ->withQueryString(),
            'search' => $search,
            'selectedServiceType' => $selectedServiceType,
            'serviceTypes' => $serviceTypes,
            'sortBy' => $sortBy,
            'successCode' => (string) $request->query('success', ''),
            'user' => $user,
        ]);
    }

    public function provider(Request $request)
    {
        $providerId = (int) $request->query('id', 0);
        if ($providerId <= 0) {
            return redirect('/hire-service.php');
        }

        $provider = $this->serviceProviderQuery('', '', '', 'top_rated')
            ->where('u.id', $providerId)
            ->first();

        if (! $provider) {
            return redirect('/hire-service.php');
        }

        $serviceTypes = config('service_marketplace.service_types', []);
        $budgetRanges = config('service_marketplace.budget_ranges', []);
        $portfolioProjects = $this->portfolioProjects($providerId);
        $reviews = $this->providerReviews($providerId);
        $user = $this->currentLegacyUser($request);

        return view('services.provider', [
            'budgetRanges' => $budgetRanges,
            'portfolioProjects' => $portfolioProjects,
            'provider' => $provider,
            'providerName' => $this->providerName($provider),
            'reviews' => $reviews,
            'serviceTypes' => $serviceTypes,
            'serviceTypeLabel' => $serviceTypes[(string) ($provider->service_category ?? '')] ?? 'Construction service',
            'successCode' => (string) $request->query('success', ''),
            'user' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $serviceTypes = array_keys(config('service_marketplace.service_types', []));
        $budgetRanges = array_keys(config('service_marketplace.budget_ranges', []));

        $validated = $request->validate([
            'service_provider_id' => ['nullable', 'integer', 'min:1'],
            'service_type' => ['required', 'string', 'in:'.implode(',', $serviceTypes)],
            'project_title' => ['required', 'string', 'min:3', 'max:191'],
            'project_location' => ['required', 'string', 'min:2', 'max:191'],
            'project_description' => ['required', 'string', 'min:15', 'max:5000'],
            'budget_range' => ['nullable', 'string', 'in:'.implode(',', $budgetRanges)],
            'preferred_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'contact_name' => ['required', 'string', 'min:2', 'max:191'],
            'contact_phone' => ['required', 'string', 'min:7', 'max:40'],
            'contact_email' => ['required', 'email', 'max:191'],
        ]);

        $providerId = (int) ($validated['service_provider_id'] ?? 0);
        if ($providerId > 0 && ! $this->serviceProviderExists($providerId)) {
            return back()
                ->withErrors(['service_provider_id' => 'The selected service provider is no longer available.'])
                ->withInput();
        }

        $userId = $request->session()->has('legacy_user_id')
            ? (int) $request->session()->get('legacy_user_id')
            : null;

        $payload = [
            ...Arr::only($validated, [
                'service_type',
                'project_title',
                'project_location',
                'project_description',
                'budget_range',
                'preferred_start_date',
                'contact_name',
                'contact_phone',
                'contact_email',
            ]),
            'user_id' => $userId ?: null,
            'status' => 'new',
        ];

        if ($providerId > 0 && Schema::hasColumn('service_requests', 'service_provider_id')) {
            $payload['service_provider_id'] = $providerId;
        }

        ServiceRequest::query()->create($payload);

        if ($providerId > 0) {
            return redirect('/service-provider.php?id='.$providerId.'&success=requested');
        }

        return redirect('/hire-service.php?success=requested');
    }

    private function currentLegacyUser(Request $request): ?User
    {
        if (! $request->session()->has('legacy_user_id')) {
            return null;
        }

        return User::query()->find((int) $request->session()->get('legacy_user_id'));
    }

    private function serviceProviderExists(int $providerId): bool
    {
        if (! Schema::hasColumn('users', 'offers_services')) {
            return false;
        }

        return DB::table('users')
            ->where('id', $providerId)
            ->where('offers_services', true)
            ->exists();
    }

    private function serviceProviderQuery(string $search, string $serviceType, string $location, string $sortBy)
    {
        $hasOffersServices = Schema::hasColumn('users', 'offers_services');
        $hasServiceCategory = Schema::hasColumn('users', 'service_category');
        $hasServiceAreas = Schema::hasColumn('users', 'service_areas');
        $hasProfileImage = Schema::hasColumn('users', 'profile_image_path');
        $hasSupplierBadge = Schema::hasColumn('users', 'is_verified_badge');
        $hasSupplierReviews = Schema::hasTable('supplier_reviews');
        $hasPortfolioProjects = Schema::hasTable('service_provider_projects');

        $query = DB::table('users as u')
            ->select(array_merge([
                'u.id',
                'u.full_name',
                'u.company',
                'u.location',
                'u.business_description',
                'u.created_at',
                $hasServiceCategory ? 'u.service_category' : DB::raw('NULL as service_category'),
                $hasServiceAreas ? 'u.service_areas' : DB::raw('NULL as service_areas'),
                $hasProfileImage ? 'u.profile_image_path' : DB::raw('NULL as profile_image_path'),
                $hasSupplierBadge ? 'u.is_verified_badge' : DB::raw('0 as is_verified_badge'),
                $hasSupplierReviews ? DB::raw('(SELECT ROUND(AVG(sr.rating), 1) FROM supplier_reviews sr WHERE sr.supplier_id = u.id) as provider_rating_avg') : DB::raw('NULL as provider_rating_avg'),
                $hasSupplierReviews ? DB::raw('(SELECT COUNT(*) FROM supplier_reviews sr WHERE sr.supplier_id = u.id) as provider_rating_count') : DB::raw('0 as provider_rating_count'),
                $hasPortfolioProjects ? DB::raw('(SELECT COUNT(*) FROM service_provider_projects spp WHERE spp.provider_id = u.id) as portfolio_count') : DB::raw('0 as portfolio_count'),
                $hasPortfolioProjects ? DB::raw("(SELECT COUNT(*) FROM service_provider_projects spp WHERE spp.provider_id = u.id AND spp.status = 'ongoing') as ongoing_project_count") : DB::raw('0 as ongoing_project_count'),
                $hasPortfolioProjects ? DB::raw("(SELECT spp.image_path FROM service_provider_projects spp WHERE spp.provider_id = u.id AND spp.image_path IS NOT NULL AND spp.image_path <> '' ORDER BY spp.display_order ASC, spp.id ASC LIMIT 1) as portfolio_image_path") : DB::raw('NULL as portfolio_image_path'),
            ]));

        if ($hasOffersServices) {
            $query->where('u.offers_services', true);
        } else {
            $query->whereRaw('1 = 0');
        }

        if ($hasServiceCategory && $serviceType !== '') {
            $query->where('u.service_category', $serviceType);
        }

        if ($location !== '') {
            $query->where(function ($subQuery) use ($location, $hasServiceAreas): void {
                $subQuery->where('u.location', $location);
                if ($hasServiceAreas) {
                    $subQuery->orWhere('u.service_areas', 'like', '%'.$location.'%');
                }
            });
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search, $hasServiceCategory, $hasServiceAreas): void {
                $subQuery->where('u.full_name', 'like', '%'.$search.'%')
                    ->orWhere('u.company', 'like', '%'.$search.'%')
                    ->orWhere('u.business_description', 'like', '%'.$search.'%');

                if ($hasServiceCategory) {
                    $subQuery->orWhere('u.service_category', 'like', '%'.$search.'%');
                }

                if ($hasServiceAreas) {
                    $subQuery->orWhere('u.service_areas', 'like', '%'.$search.'%');
                }
            });
        }

        if ($sortBy === 'most_projects') {
            $query->orderByDesc('portfolio_count')
                ->orderByDesc('provider_rating_avg')
                ->orderBy('u.full_name');
        } elseif ($sortBy === 'newest') {
            $query->orderByDesc('u.created_at');
        } elseif ($sortBy === 'name') {
            $query->orderByRaw("COALESCE(NULLIF(u.company, ''), u.full_name) ASC");
        } else {
            $query->orderByDesc('provider_rating_avg')
                ->orderByDesc('provider_rating_count')
                ->orderByDesc('portfolio_count')
                ->orderBy('u.full_name');
        }

        return $query;
    }

    private function serviceProviderLocations()
    {
        if (! Schema::hasColumn('users', 'offers_services')) {
            return collect();
        }

        return DB::table('users')
            ->select('location')
            ->where('offers_services', true)
            ->whereNotNull('location')
            ->where('location', '<>', '')
            ->distinct()
            ->orderBy('location')
            ->get();
    }

    private function portfolioProjects(int $providerId)
    {
        if (! Schema::hasTable('service_provider_projects')) {
            return collect();
        }

        return DB::table('service_provider_projects')
            ->select([
                'id',
                'service_type',
                'title',
                'location',
                'status',
                'description',
                'image_path',
                'display_order',
                'created_at',
            ])
            ->where('provider_id', $providerId)
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->get();
    }

    private function providerReviews(int $providerId)
    {
        if (! Schema::hasTable('supplier_reviews')) {
            return collect();
        }

        return DB::table('supplier_reviews as sr')
            ->leftJoin('users as ru', 'ru.id', '=', 'sr.user_id')
            ->select([
                'sr.rating',
                'sr.review_text',
                'sr.created_at',
                'ru.full_name as reviewer_full_name',
                'ru.company as reviewer_company',
            ])
            ->where('sr.supplier_id', $providerId)
            ->orderByDesc('sr.created_at')
            ->limit(self::REVIEW_LIMIT)
            ->get()
            ->map(function ($review) {
                $review->reviewer_full_name = NameFormatter::title((string) ($review->reviewer_full_name ?? 'Verified Buyer'), 'Verified Buyer');

                return $review;
            });
    }

    private function providerName(object $provider): string
    {
        return trim((string) ($provider->company ?? '')) !== ''
            ? (string) $provider->company
            : NameFormatter::title((string) ($provider->full_name ?? 'Service Provider'), 'Service Provider');
    }
}
