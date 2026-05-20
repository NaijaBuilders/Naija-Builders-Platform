<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\BuyerOnboarding\BuyerOtpService;
use Illuminate\Http\Request;

class BuyerOtpController extends Controller
{
    public function __construct(private readonly BuyerOtpService $otp) {}

    public function send(Request $request)
    {
        $validated = $request->validate([
            'channel' => ['required', 'in:email,phone'],
        ]);

        $result = $this->otp->issue($request->user(), $validated['channel']);

        return response()->json([
            'message' => 'Confirmation code sent.',
            'otp' => $result,
        ]);
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'channel' => ['required', 'in:email,phone'],
            'otp' => ['required', 'digits:6'],
        ]);

        $this->otp->confirm($request->user(), $validated['channel'], $validated['otp']);

        return response()->json([
            'message' => $validated['channel'] === 'email'
                ? 'Email confirmed.'
                : 'Phone confirmed.',
            'channel' => $validated['channel'],
        ]);
    }
}
