<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ServiceRequestController extends Controller
{
    public function options()
    {
        return response()->json([
            'service_types' => $this->optionList(config('service_marketplace.service_types', [])),
            'budget_ranges' => $this->optionList(config('service_marketplace.budget_ranges', [])),
        ]);
    }

    public function store(Request $request)
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

        $serviceRequest = ServiceRequest::query()->create([
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
            'user_id' => (int) $request->user()->id,
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'Service request received.',
            'service_request' => [
                'id' => (string) $serviceRequest->id,
                'service_type' => $serviceRequest->service_type,
                'status' => $serviceRequest->status,
                'created_at' => optional($serviceRequest->created_at)->toISOString(),
            ],
        ], 201);
    }

    /**
     * @param  array<string, string>  $options
     * @return array<int, array{value: string, label: string}>
     */
    private function optionList(array $options): array
    {
        return collect($options)
            ->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->all();
    }
}
