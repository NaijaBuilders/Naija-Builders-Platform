<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\SupplierOnboarding\AdminReviewActionRequest;
use App\Models\SupplierApplication;
use App\Services\SupplierOnboarding\SupplierOnboardingService;

class AdminSupplierOnboardingController extends Controller
{
    public function __construct(private readonly SupplierOnboardingService $onboarding) {}

    public function index()
    {
        $applications = SupplierApplication::query()
            ->with('user')
            ->where('status', SupplierApplication::STATUS_MANUAL_REVIEW)
            ->latest()
            ->get()
            ->map(fn (SupplierApplication $application): array => [
                'id' => (int) $application->id,
                'supplier_id' => (int) $application->user_id,
                'supplier_name' => (string) ($application->user->full_name ?? ''),
                'business_name' => (string) ($application->business_name ?? ''),
                'status' => (string) $application->status,
                'current_stage' => (string) $application->current_stage,
                'submitted_at' => optional($application->submitted_at)->toISOString(),
                'updated_at' => optional($application->updated_at)->toISOString(),
            ])
            ->values();

        return response()->json(['data' => $applications]);
    }

    public function show(SupplierApplication $application)
    {
        return response()->json([
            'application' => $this->onboarding->adminPayload($application),
        ]);
    }

    public function approve(AdminReviewActionRequest $request, SupplierApplication $application)
    {
        $application = $this->onboarding->approveManually($application, $request->user(), $request->validated('notes'));

        return response()->json([
            'message' => 'Supplier approved.',
            'application' => $this->onboarding->adminPayload($application),
        ]);
    }

    public function reject(AdminReviewActionRequest $request, SupplierApplication $application)
    {
        $application = $this->onboarding->rejectManually($application, $request->user(), $request->validated('notes'));

        return response()->json([
            'message' => 'Supplier rejected.',
            'application' => $this->onboarding->adminPayload($application),
        ]);
    }

    public function moreInfo(AdminReviewActionRequest $request, SupplierApplication $application)
    {
        $application = $this->onboarding->requestMoreInfo(
            $application,
            $request->user(),
            $request->validated('message'),
            $request->validated('notes')
        );

        return response()->json([
            'message' => 'More information requested.',
            'application' => $this->onboarding->adminPayload($application),
        ]);
    }
}
