<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class HireServiceController extends Controller
{
    public function show(Request $request)
    {
        $user = $this->currentLegacyUser($request);

        return view('services.hire', [
            'budgetRanges' => config('service_marketplace.budget_ranges', []),
            'serviceTypes' => config('service_marketplace.service_types', []),
            'successCode' => (string) $request->query('success', ''),
            'user' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $serviceTypes = array_keys(config('service_marketplace.service_types', []));
        $budgetRanges = array_keys(config('service_marketplace.budget_ranges', []));

        $validated = $request->validate([
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

        $userId = $request->session()->has('legacy_user_id')
            ? (int) $request->session()->get('legacy_user_id')
            : null;

        ServiceRequest::query()->create([
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
        ]);

        return redirect('/hire-service.php?success=requested');
    }

    private function currentLegacyUser(Request $request): ?User
    {
        if (! $request->session()->has('legacy_user_id')) {
            return null;
        }

        return User::query()->find((int) $request->session()->get('legacy_user_id'));
    }
}
