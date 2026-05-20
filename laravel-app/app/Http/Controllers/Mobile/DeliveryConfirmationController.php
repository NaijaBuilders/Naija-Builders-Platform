<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\BuyerOnboarding\DeliveryConfirmationService;
use Illuminate\Http\Request;

class DeliveryConfirmationController extends Controller
{
    public function __construct(private readonly DeliveryConfirmationService $delivery) {}

    public function uploadPhoto(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $photo = $this->delivery->storePhoto($request->user(), $orderId, $validated, $request);

        return response()->json([
            'message' => 'Delivery photo uploaded.',
            'photo' => [
                'id' => (int) $photo->id,
                'path' => (string) $photo->path,
                'captured_at' => optional($photo->captured_at)->toISOString(),
                'gps_lat' => $photo->gps_lat === null ? null : (float) $photo->gps_lat,
                'gps_lng' => $photo->gps_lng === null ? null : (float) $photo->gps_lng,
            ],
        ], 201);
    }

    public function generateOtp(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        return response()->json($this->delivery->generateOtp($request->user(), $orderId, $validated));
    }

    public function confirmOtp(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        return response()->json($this->delivery->confirmOtp($request->user(), $orderId, $validated['otp']));
    }

    public function dispute(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $dispute = $this->delivery->raiseDispute($request->user(), $orderId, $validated['reason']);

        return response()->json([
            'message' => 'Dispute raised. Escrow release is on hold.',
            'dispute' => [
                'id' => (int) $dispute->id,
                'status' => (string) $dispute->status,
                'created_at' => optional($dispute->created_at)->toISOString(),
            ],
        ], 201);
    }
}
