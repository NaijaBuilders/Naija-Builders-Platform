<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\SupplierOnboarding\BankDetailsRequest;
use App\Http\Requests\Mobile\SupplierOnboarding\BusinessDetailsRequest;
use App\Http\Requests\Mobile\SupplierOnboarding\IdentityVerificationRequest;
use App\Models\SupplierApplication;
use App\Services\SupplierOnboarding\SupplierOnboardingService;
use Illuminate\Http\Request;

class SupplierOnboardingController extends Controller
{
    public function __construct(private readonly SupplierOnboardingService $onboarding) {}

    public function status(Request $request)
    {
        if ((string) ($request->user()?->role ?? '') !== 'supplier') {
            return response()->json(['message' => 'Supplier onboarding only.'], 403);
        }

        $application = $this->onboarding->applicationForUser($request->user());

        return response()->json([
            'application' => $this->onboarding->supplierPayload($application),
        ]);
    }

    public function show(Request $request, SupplierApplication $application)
    {
        if ((int) $application->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json([
            'application' => $this->onboarding->supplierPayload($application),
        ]);
    }

    public function businessDetails(BusinessDetailsRequest $request)
    {
        $application = $this->onboarding->submitBusinessDetails($request->user(), $request->validated(), $request);

        return response()->json([
            'message' => 'Business details saved.',
            'application' => $this->onboarding->supplierPayload($application),
        ]);
    }

    public function identity(IdentityVerificationRequest $request)
    {
        $application = $this->onboarding->submitIdentity($request->user(), $request->validated(), $request);

        return response()->json([
            'message' => 'Identity details saved.',
            'application' => $this->onboarding->supplierPayload($application),
        ]);
    }

    public function bankDetails(BankDetailsRequest $request)
    {
        $application = $this->onboarding->submitBankDetails($request->user(), $request->validated(), $request);

        return response()->json([
            'message' => 'Bank details saved.',
            'application' => $this->onboarding->supplierPayload($application),
        ]);
    }

    public function submit(Request $request)
    {
        if ((string) ($request->user()?->role ?? '') !== 'supplier') {
            return response()->json(['message' => 'Supplier onboarding only.'], 403);
        }

        $application = $this->onboarding->submitForDecision($request->user(), $request);

        return response()->json([
            'message' => 'Supplier application submitted.',
            'application' => $this->onboarding->supplierPayload($application),
        ]);
    }
}
