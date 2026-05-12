<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PremblyWebhookEvent;
use App\Models\VerificationCheck;
use App\Services\Kyc\Data\ProviderResponseNormalizer;
use App\Services\SupplierOnboarding\SupplierOnboardingService;
use Illuminate\Http\Request;

class PremblyWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        ProviderResponseNormalizer $normalizer,
        SupplierOnboardingService $onboarding,
    ) {
        $secret = trim((string) config('services.kyc.prembly.webhook_secret', ''));
        if ($secret === '') {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $payload = $request->getContent();
        $signature = (string) $request->header('x-prembly-signature', '');
        $token = (string) $request->header('token', '');

        if ($payload === '' || $signature === '' || $token === '') {
            return response()->json(['message' => 'Invalid webhook.'], 401);
        }

        $expected = base64_encode(hash_hmac('sha256', $payload, $secret, true));
        if (! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid webhook.'], 401);
        }

        $event = PremblyWebhookEvent::query()->where('token', $token)->first();
        if ($event && $event->processed_at) {
            return response()->json(['status' => 'already_processed']);
        }

        $data = json_decode($payload, true);
        if (! is_array($data)) {
            return response()->json(['message' => 'Invalid webhook.'], 400);
        }

        $reference = $normalizer->premblyReference($data);
        $event ??= PremblyWebhookEvent::query()->create([
            'token' => $token,
            'event_hash' => hash('sha256', $payload),
            'provider_reference' => $reference,
            'status' => 'received',
            'received_at' => now(),
        ]);

        if ($reference === null) {
            $event->forceFill([
                'status' => 'ignored',
                'processed_at' => now(),
            ])->save();

            return response()->json(['status' => 'received']);
        }

        $check = VerificationCheck::query()
            ->where('provider', 'prembly')
            ->where('provider_reference', $reference)
            ->latest('id')
            ->first();

        if (! $check) {
            $event->forceFill([
                'status' => 'unmatched',
                'processed_at' => now(),
            ])->save();

            return response()->json(['status' => 'received']);
        }

        $result = $normalizer->fromPrembly((string) $check->check_type, $data);
        $application = $onboarding->applyProviderWebhookResult($check, $result);

        $event->forceFill([
            'supplier_application_id' => $application->id,
            'provider_reference' => $reference,
            'status' => 'processed',
            'processed_at' => now(),
        ])->save();

        return response()->json(['status' => 'received']);
    }
}
