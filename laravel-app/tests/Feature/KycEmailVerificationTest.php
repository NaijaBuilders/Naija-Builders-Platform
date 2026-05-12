<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KycEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_verify_email_with_fake_provider(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/mobile/kyc/email-verification', [
            'email' => 'supplier@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('verification.status', 'VERIFIED')
            ->assertJsonPath('verification.provider', 'fake')
            ->assertJsonMissing(['EMAIL_INVALID', 'EMAIL_UNVERIFIABLE']);
    }

    public function test_email_verification_response_hides_internal_reason_codes(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/mobile/kyc/email-verification', [
            'email' => 'unverified@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('verification.status', 'NOT_VERIFIED')
            ->assertJsonPath('message', 'We could not verify this email at this time.')
            ->assertJsonMissing(['EMAIL_UNVERIFIABLE']);
    }

    public function test_email_verification_requires_authentication(): void
    {
        $this->postJson('/api/mobile/kyc/email-verification', [
            'email' => 'supplier@example.com',
        ])->assertUnauthorized();
    }
}
