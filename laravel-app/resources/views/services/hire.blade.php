@php
    $pageTitle = 'Hire a Service';
    $prefillName = old('contact_name', $user?->full_name ?? '');
    $prefillEmail = old('contact_email', $user?->email ?? '');
    $prefillPhone = old('contact_phone', $user?->phone ?? '');
@endphp
@extends('layouts.app')

@section('meta_description', 'Search and hire trusted construction professionals such as architects, site engineers, surveyors, plumbers, electricians, tilers, and project managers on NaijaBuilders.')

@section('content')
<div class="container services-page page-shell">
    <style>
        .services-page {
            padding-top: var(--spacing-lg);
            padding-bottom: var(--spacing-xl);
        }

        .services-hero {
            position: relative;
            overflow: hidden;
            border-radius: var(--rounded-lg);
            margin-bottom: var(--spacing-lg);
            padding: var(--spacing-xl);
            color: var(--white);
            background:
                linear-gradient(90deg, rgba(9, 31, 67, 0.88), rgba(31, 79, 163, 0.7)),
                url("{{ asset('assets/images/home-suppliers.jpg') }}") center/cover;
            box-shadow: 0 18px 38px rgba(11, 42, 91, 0.2);
        }

        .services-hero h1 {
            margin: 0 0 0.55rem;
            color: var(--white);
            font-size: clamp(1.55rem, 4vw, 2.2rem);
            line-height: 1.2;
        }

        .services-hero p {
            max-width: 72ch;
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
        }

        .services-page-layout {
            display: grid;
            grid-template-columns: minmax(250px, 290px) minmax(0, 1fr);
            gap: var(--spacing-lg);
            align-items: start;
        }

        .services-topbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: var(--spacing-md);
            flex-wrap: wrap;
        }

        .services-topbar__controls {
            display: flex;
            align-items: flex-end;
            gap: var(--spacing-md);
            flex-wrap: wrap;
        }

        .services-topbar__control {
            min-width: 170px;
        }

        .services-topbar__control label,
        .service-filter-label {
            display: block;
            margin-bottom: var(--spacing-xs);
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .services-count {
            margin: 0.55rem 0 0;
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: var(--spacing-lg);
            margin-top: var(--spacing-lg);
        }

        .service-provider-card {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .service-provider-card__media {
            position: relative;
            aspect-ratio: 16 / 10;
            border-bottom: 1px solid var(--neutral-200);
            background: linear-gradient(135deg, var(--secondary-light), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .service-provider-card__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .service-provider-card__initials {
            width: 72px;
            height: 72px;
            border-radius: var(--rounded-full);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            color: var(--primary-color);
            font-weight: var(--font-weight-bold);
            font-size: 1.35rem;
            box-shadow: var(--shadow-sm);
        }

        .service-provider-card__badge {
            position: absolute;
            left: 0.65rem;
            top: 0.65rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.22rem 0.55rem;
            border-radius: var(--rounded-full);
            background: rgba(9, 31, 67, 0.86);
            color: var(--white);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-bold);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .service-provider-card__body {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            flex: 1;
        }

        .service-provider-card__title {
            margin: 0;
            font-size: 1.08rem;
        }

        .service-provider-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
        }

        .service-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: var(--rounded-full);
            background: var(--primary-light);
            color: var(--primary-dark);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-bold);
        }

        .service-rating {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
        }

        .service-stars {
            display: inline-flex;
            gap: 0.08rem;
            color: var(--accent-warning);
            line-height: 1;
        }

        .service-stars .is-empty {
            color: var(--neutral-300);
        }

        .service-provider-card__stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.55rem;
        }

        .service-stat {
            border: 1px solid var(--neutral-200);
            border-radius: var(--rounded-md);
            padding: 0.55rem 0.65rem;
            background: var(--neutral-50);
        }

        .service-stat strong {
            display: block;
            color: var(--neutral-900);
            font-size: 1rem;
            line-height: 1.1;
        }

        .service-stat span {
            color: var(--neutral-600);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-semibold);
            text-transform: uppercase;
        }

        .service-provider-card__actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-sm);
            margin-top: auto;
        }

        .services-pagination {
            margin-top: var(--spacing-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
        }

        .service-request-panel {
            margin-top: var(--spacing-xl);
        }

        .service-request-panel__intro {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-md);
            flex-wrap: wrap;
        }

        .service-request-panel__intro h2 {
            margin: 0 0 0.4rem;
        }

        .service-request-panel__intro p {
            margin: 0;
            max-width: 70ch;
            color: var(--neutral-600);
        }

        @media (max-width: 1023px) {
            .services-page-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .services-hero {
                padding: 1.2rem;
            }

            .services-topbar {
                flex-direction: column;
                align-items: stretch;
            }

            .services-topbar__controls,
            .service-provider-card__actions,
            .service-provider-card__stats {
                grid-template-columns: 1fr;
                width: 100%;
            }
        }
    </style>

    <section class="services-hero">
        <h1>Hire a Service</h1>
        <p>Search by the service your project needs, compare people who offer that service, and open a profile to review their details and previous or ongoing work photos.</p>
    </section>

    @if ($successCode === 'requested')
        <div class="alert alert-success" style="margin-bottom: 1rem;">
            Your service request has been received. A NaijaBuilders team member will follow up with the next step.
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 1rem;">
            Please check the form and complete the highlighted details.
        </div>
    @endif

    <div class="services-page-layout">
        <aside class="card">
            <div class="card-body">
                <h2>Find Providers</h2>
                <form method="GET" action="/hire-service.php">
                    <input type="hidden" name="sort_by" value="{{ $sortBy }}">

                    <div class="form-group">
                        <label class="service-filter-label" for="services-search">Search</label>
                        <input id="services-search" type="search" name="search" value="{{ $search }}" placeholder="Architect, plumber, surveyor">
                    </div>

                    <div class="form-group">
                        <label class="service-filter-label" for="services-service-type">Service Type</label>
                        <select id="services-service-type" name="service_type">
                            <option value="">All Services</option>
                            @foreach ($serviceTypes as $value => $label)
                                <option value="{{ $value }}" {{ $selectedServiceType === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="service-filter-label" for="services-location">Location</label>
                        <select id="services-location" name="location">
                            <option value="">All Locations</option>
                            @foreach ($locations as $locationOption)
                                <option value="{{ $locationOption->location }}" {{ $location === (string) $locationOption->location ? 'selected' : '' }}>{{ $locationOption->location }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary">Search Services</button>
                        <a href="/hire-service.php" class="btn btn-outline" style="text-decoration: none;">Reset</a>
                    </div>
                </form>

                <div class="signup-supplier-note" style="margin-top: 1rem;">
                    <h4 style="margin: 0 0 0.45rem;">Offer services?</h4>
                    <p style="margin: 0 0 0.75rem;">Create a service provider account so buyers can discover your work.</p>
                    <a href="/signup.php?type=service_provider" class="btn btn-outline">Offer Services</a>
                </div>
            </div>
        </aside>

        <section>
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="/hire-service.php" class="services-topbar">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="service_type" value="{{ $selectedServiceType }}">
                        <input type="hidden" name="location" value="{{ $location }}">

                        <div>
                            <h2 style="margin: 0 0 0.35rem;">Service Providers</h2>
                            <p class="services-count">
                                Showing {{ number_format((int) ($providers->firstItem() ?? 0)) }} - {{ number_format((int) ($providers->lastItem() ?? 0)) }} of {{ number_format($providers->total()) }} providers
                            </p>
                        </div>

                        <div class="services-topbar__controls">
                            <div class="services-topbar__control">
                                <label for="services-sort">Sort By</label>
                                <select id="services-sort" name="sort_by">
                                    <option value="top_rated" {{ $sortBy === 'top_rated' ? 'selected' : '' }}>Top Rated</option>
                                    <option value="most_projects" {{ $sortBy === 'most_projects' ? 'selected' : '' }}>Most Work Photos</option>
                                    <option value="newest" {{ $sortBy === 'newest' ? 'selected' : '' }}>Newest Providers</option>
                                    <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="services-grid">
                @forelse ($providers as $provider)
                    @php
                        $providerName = trim((string) ($provider->company ?? '')) !== ''
                            ? (string) $provider->company
                            : \App\Support\NameFormatter::title((string) ($provider->full_name ?? 'Service Provider'), 'Service Provider');
                        $serviceLabel = $serviceTypes[(string) ($provider->service_category ?? '')] ?? 'Construction service';
                        $portfolioImagePath = trim((string) ($provider->portfolio_image_path ?? ''));
                        $profileImagePath = trim((string) ($provider->profile_image_path ?? ''));
                        $cardImagePath = $portfolioImagePath !== '' ? $portfolioImagePath : $profileImagePath;
                        $cardImageUrl = $cardImagePath !== '' ? '/' . ltrim($cardImagePath, '/') : '';
                        $ratingAvg = $provider->provider_rating_avg !== null ? (float) $provider->provider_rating_avg : 0.0;
                        $ratingCount = (int) ($provider->provider_rating_count ?? 0);
                        $displayRating = $ratingCount > 0 ? number_format($ratingAvg, 1) : '0.0';
                        $fullStars = (int) round($ratingAvg);
                        $portfolioCount = (int) ($provider->portfolio_count ?? 0);
                        $ongoingProjectCount = (int) ($provider->ongoing_project_count ?? 0);
                        $nameParts = preg_split('/\s+/', trim($providerName)) ?: [];
                        $initials = strtoupper(substr($nameParts[0] ?? 'N', 0, 1) . substr($nameParts[1] ?? 'B', 0, 1));
                    @endphp

                    <article class="service-provider-card card">
                        <div class="service-provider-card__media">
                            <span class="service-provider-card__badge">{{ $serviceLabel }}</span>
                            @if ($cardImageUrl !== '')
                                <img src="{{ $cardImageUrl }}" alt="{{ $providerName }}">
                            @else
                                <span class="service-provider-card__initials">{{ $initials }}</span>
                            @endif
                        </div>

                        <div class="service-provider-card__body card-body">
                            <div>
                                <h3 class="service-provider-card__title">
                                    {{ $providerName }}
                                    @if ((int) ($provider->is_verified_badge ?? 0) === 1)
                                        <span class="sr-only">Verified provider</span>
                                        <span aria-hidden="true" title="Verified provider" style="display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; margin-left: 0.25rem; border-radius: 999px; background: #1d4ed8; color: #fff; font-size: 0.68rem; font-weight: 700;">&#10003;</span>
                                    @endif
                                </h3>
                                <div class="service-provider-card__meta">
                                    <span>{{ $provider->location ?: 'Nigeria' }}</span>
                                    @if (trim((string) ($provider->service_areas ?? '')) !== '')
                                        <span>Serves {{ $provider->service_areas }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="service-rating">
                                <span class="service-stars" aria-hidden="true">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <span class="{{ $star <= $fullStars ? '' : 'is-empty' }}">&#9733;</span>
                                    @endfor
                                </span>
                                <span>{{ $displayRating }}/5 ({{ number_format($ratingCount) }})</span>
                            </div>

                            <p style="margin: 0; color: var(--neutral-700);">
                                {{ \Illuminate\Support\Str::limit((string) ($provider->business_description ?: 'Profile details will appear here once this provider adds their service background.'), 132) }}
                            </p>

                            <div class="service-provider-card__stats">
                                <div class="service-stat">
                                    <strong>{{ number_format($portfolioCount) }}</strong>
                                    <span>Work photos</span>
                                </div>
                                <div class="service-stat">
                                    <strong>{{ number_format($ongoingProjectCount) }}</strong>
                                    <span>Ongoing</span>
                                </div>
                            </div>

                            <div class="service-provider-card__actions">
                                <a href="/service-provider.php?id={{ (int) $provider->id }}" class="btn btn-outline" style="text-decoration: none;">View Work</a>
                                <a href="/service-provider.php?id={{ (int) $provider->id }}#request" class="btn btn-primary" style="text-decoration: none;">Request</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="card" style="grid-column: 1 / -1;">
                        <div class="card-body">
                            <h3 style="margin: 0 0 0.45rem;">No service providers found</h3>
                            <p style="margin: 0; color: var(--neutral-600);">Try a broader search, choose a different service type, or send a general request below so NaijaBuilders can help match you.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($providers->lastPage() > 1)
                @php
                    $startPage = max(1, $providers->currentPage() - 2);
                    $endPage = min($providers->lastPage(), $providers->currentPage() + 2);
                @endphp

                <div class="card" style="margin-top: 1rem;">
                    <div class="card-body">
                        <nav class="services-pagination" aria-label="Service provider pagination">
                            <a href="{{ $providers->onFirstPage() ? '#' : $providers->previousPageUrl() }}" class="btn {{ $providers->onFirstPage() ? 'btn-outline' : 'btn-secondary' }}" @if ($providers->onFirstPage()) aria-disabled="true" @endif>Previous</a>

                            @for ($page = $startPage; $page <= $endPage; $page++)
                                <a href="{{ $providers->url($page) }}" class="btn {{ $page === $providers->currentPage() ? 'btn-primary' : 'btn-outline' }}">{{ $page }}</a>
                            @endfor

                            <a href="{{ $providers->hasMorePages() ? $providers->nextPageUrl() : '#' }}" class="btn {{ $providers->hasMorePages() ? 'btn-secondary' : 'btn-outline' }}" @if (! $providers->hasMorePages()) aria-disabled="true" @endif>Next</a>
                        </nav>
                    </div>
                </div>
            @endif
        </section>
    </div>

    <section class="service-request-panel" id="request">
        <div class="service-request-panel__intro">
            <div>
                <p class="value-eyebrow">Manual matching</p>
                <h2>Send a General Service Request</h2>
                <p>Use this when you are still deciding or cannot find the exact provider you need. NaijaBuilders can review the request and route it to a suitable professional.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="/hire-service.php">
                    @csrf

                    <div class="grid-2 gap-md">
                        <div class="form-group">
                            <label for="service_type">Service needed</label>
                            <select id="service_type" name="service_type" required>
                                <option value="">Select service</option>
                                @foreach ($serviceTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('service_type', $selectedServiceType) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('service_type')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="project_title">Project title</label>
                            <input id="project_title" name="project_title" type="text" required value="{{ old('project_title') }}" placeholder="Duplex design and site supervision">
                            @error('project_title')<p class="text-note">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="project_location">Project location</label>
                        <input id="project_location" name="project_location" type="text" required value="{{ old('project_location', $user?->location ?? '') }}" placeholder="Lekki, Lagos">
                        @error('project_location')<p class="text-note">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="project_description">What do you need done?</label>
                        <textarea id="project_description" name="project_description" required rows="5" placeholder="Describe the project, site stage, and what support you need.">{{ old('project_description') }}</textarea>
                        @error('project_description')<p class="text-note">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid-2 gap-md">
                        <div class="form-group">
                            <label for="budget_range">Budget range</label>
                            <select id="budget_range" name="budget_range">
                                <option value="">Select budget</option>
                                @foreach ($budgetRanges as $value => $label)
                                    <option value="{{ $value }}" {{ old('budget_range') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('budget_range')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="preferred_start_date">Preferred start date</label>
                            <input id="preferred_start_date" name="preferred_start_date" type="date" value="{{ old('preferred_start_date') }}">
                            @error('preferred_start_date')<p class="text-note">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid-3 gap-md">
                        <div class="form-group">
                            <label for="contact_name">Contact name</label>
                            <input id="contact_name" name="contact_name" type="text" required value="{{ $prefillName }}">
                            @error('contact_name')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_phone">Contact phone</label>
                            <input id="contact_phone" name="contact_phone" type="tel" required value="{{ $prefillPhone }}" placeholder="+234 801 234 5678">
                            @error('contact_phone')<p class="text-note">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_email">Contact email</label>
                            <input id="contact_email" name="contact_email" type="email" required value="{{ $prefillEmail }}" placeholder="you@example.com">
                            @error('contact_email')<p class="text-note">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">Send Request</button>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const sortSelect = document.getElementById('services-sort');
    if (!sortSelect || !sortSelect.form) {
        return;
    }

    sortSelect.addEventListener('change', function () {
        sortSelect.form.submit();
    });
})();
</script>
@endpush
