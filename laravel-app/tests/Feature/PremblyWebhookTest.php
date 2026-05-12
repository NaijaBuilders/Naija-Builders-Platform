<?php

namespace Tests\Feature;

use App\Models\SupplierApplication;
use App\Models\User;
use App\Models\VerificationCheck;
use App\Services\Kyc\Data\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PremblyWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_prembly_webhook_updates_check_idempotently(): void
    {
        config(['services.kyc.prembly.webhook_secret' => 'not-a-real-webhook-secret']);

        $application = SupplierApplication::query()->create([
            'user_id' => User::factory()->supplier()->create()->id,
            'status' => SupplierApplication::STATUS_VERIFYING,
            'current_stage' => 'AUTOMATED_CHECKS',
            'provider' => 'prembly',
        ]);

        VerificationCheck::query()->create([
            'supplier_application_id' => $application->id,
            'provider' => 'prembly',
            'provider_reference' => 'prembly-ref-123',
            'check_type' => 'bank',
            'status' => VerificationResult::STATUS_MANUAL_REVIEW,
            'reason_codes' => ['PROVIDER_MANUAL_REVIEW'],
            'normalized_result' => ['data' => ['reference' => 'prembly-ref-123']],
            'checked_at' => now(),
        ]);

        $payload = json_encode([
            'status' => true,
            'response_code' => '00',
            'verification' => [
                'reference' => 'prembly-ref-123',
                'status' => 'VERIFIED',
            ],
            'account_data' => [
                'account_name' => 'Solid Build Materials Ltd',
            ],
        ], JSON_THROW_ON_ERROR);

        $signature = base64_encode(hash_hmac('sha256', $payload, 'not-a-real-webhook-secret', true));
        $headers = [
            'HTTP_X_PREMBLY_SIGNATURE' => $signature,
            'HTTP_TOKEN' => 'prembly-token-123',
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];

        $this->call('POST', '/api/kyc/prembly/webhook', [], [], [], $headers, $payload)
            ->assertOk()
            ->assertJsonPath('status', 'received');

        $this->call('POST', '/api/kyc/prembly/webhook', [], [], [], $headers, $payload)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed');

        $this->assertDatabaseHas('verification_checks', [
            'supplier_application_id' => $application->id,
            'provider_reference' => 'prembly-ref-123',
            'status' => VerificationResult::STATUS_PASSED,
        ]);
        $this->assertDatabaseCount('prembly_webhook_events', 1);
        $this->assertDatabaseCount('supplier_audit_logs', 1);
    }
}
