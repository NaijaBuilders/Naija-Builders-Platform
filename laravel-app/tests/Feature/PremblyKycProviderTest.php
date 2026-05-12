<?php

namespace Tests\Feature;

use App\Models\SupplierApplication;
use App\Models\User;
use App\Services\Kyc\Contracts\KycProvider;
use App\Services\Kyc\Data\VerificationResult;
use App\Services\Kyc\KycConfigurationValidator;
use App\Services\Kyc\Providers\PremblyKycProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class PremblyKycProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_fake_provider_still_resolves_by_default_in_tests(): void
    {
        $this->assertSame('fake', app(KycProvider::class)->name());
    }

    public function test_missing_prembly_key_fails_safely(): void
    {
        config([
            'services.kyc.provider' => 'prembly',
            'services.kyc.prembly.api_key' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Prembly KYC provider requires PREMBLY_API_KEY.');

        app(KycConfigurationValidator::class)->validate();
    }

    public function test_production_mode_cannot_use_fake_provider(): void
    {
        config([
            'services.kyc.provider' => 'fake',
            'services.kyc.mode' => 'production',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Production cannot run with the fake KYC provider.');

        app(KycConfigurationValidator::class)->validate();
    }

    public function test_prembly_business_success_maps_to_normalized_result(): void
    {
        $this->configurePrembly();
        Http::fake([
            'https://api.prembly.com/verification/cac' => Http::response([
                'status' => true,
                'response_code' => '00',
                'verification' => [
                    'reference' => 'prembly-cac-ref',
                    'status' => 'VERIFIED',
                ],
                'data' => [
                    'registration_date' => '2022-01-11',
                ],
            ]),
        ]);

        $result = app(PremblyKycProvider::class)->verifyBusiness($this->application());

        $this->assertSame(VerificationResult::STATUS_PASSED, $result->status);
        $this->assertSame('prembly', $result->provider);
        $this->assertSame('prembly-cac-ref', $result->data['reference']);
        $this->assertSame('2022-01-11', $result->data['registered_at']);

        Http::assertSent(fn ($request): bool => $request->hasHeader('x-api-key', 'not-a-real-key')
            && ! $request->hasHeader('Authorization'));
    }

    public function test_prembly_bank_name_mismatch_maps_to_manual_review(): void
    {
        $this->configurePrembly();
        Http::fake([
            'https://api.prembly.com/verification/bank_account/comparism' => Http::response([
                'status' => true,
                'response_code' => '00',
                'verification' => [
                    'reference' => 'prembly-bank-ref',
                    'status' => 'VERIFIED',
                ],
                'comparism_data' => [
                    'status' => false,
                    'confidence' => 0.74,
                ],
            ]),
        ]);

        $result = app(PremblyKycProvider::class)->verifyBank($this->application(), [
            'account_number' => '1234567890',
        ]);

        $this->assertSame(VerificationResult::STATUS_MANUAL_REVIEW, $result->status);
        $this->assertSame(['BANK_NAME_MISMATCH_MINOR'], $result->reasonCodes);
        $this->assertSame(74, $result->data['name_match_score']);
    }

    public function test_prembly_email_company_search_maps_to_normalized_result(): void
    {
        $this->configurePrembly();
        Http::fake([
            'https://api.prembly.com/identitypass/verification/global/company/search_with_email' => Http::response([
                'status' => true,
                'response_code' => '00',
                'message' => 'Companies Retrieved Successfully',
                'data' => [
                    [
                        'name' => 'Solid Build Materials Ltd',
                        'internationalNumber' => 'RC-123456',
                        'countryCode' => 'ng',
                    ],
                ],
                'verification' => [
                    'reference' => 'prembly-email-ref',
                    'status' => 'VERIFIED',
                ],
            ]),
        ]);

        $result = app(PremblyKycProvider::class)->verifyEmail([
            'email' => 'supplier@example.com',
        ]);

        $this->assertSame(VerificationResult::STATUS_PASSED, $result->status);
        $this->assertSame('prembly-email-ref', $result->data['reference']);
        $this->assertSame(1, $result->data['company_match_count']);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.prembly.com/identitypass/verification/global/company/search_with_email'
            && $request['email'] === 'supplier@example.com'
            && $request->hasHeader('x-api-key', 'not-a-real-key'));
    }

    public function test_prembly_email_company_search_without_matches_fails_safely(): void
    {
        $this->configurePrembly();
        Http::fake([
            'https://api.prembly.com/identitypass/verification/global/company/search_with_email' => Http::response([
                'status' => true,
                'response_code' => '00',
                'data' => [],
            ]),
        ]);

        $result = app(PremblyKycProvider::class)->verifyEmail([
            'email' => 'supplier@example.com',
        ]);

        $this->assertSame(VerificationResult::STATUS_FAILED, $result->status);
        $this->assertSame(['EMAIL_UNVERIFIABLE'], $result->reasonCodes);
        $this->assertSame(0, $result->data['company_match_count']);
    }

    public function test_prembly_timeout_returns_safe_manual_review_without_secret(): void
    {
        $this->configurePrembly();
        Http::fake(fn () => throw new ConnectionException('Connection timeout'));

        $result = app(PremblyKycProvider::class)->verifyBusiness($this->application());

        $this->assertSame(VerificationResult::STATUS_MANUAL_REVIEW, $result->status);
        $this->assertSame(['PROVIDER_TEMPORARY_FAILURE'], $result->reasonCodes);
        $this->assertStringNotContainsString('not-a-real-key', json_encode($result->toArray(), JSON_THROW_ON_ERROR));
    }

    private function configurePrembly(): void
    {
        config([
            'services.kyc.provider' => 'prembly',
            'services.kyc.prembly.api_key' => 'not-a-real-key',
            'services.kyc.prembly.base_url' => 'https://api.prembly.com',
            'services.kyc.prembly.timeout' => 30,
            'services.kyc.prembly.bank_customer_name_field' => 'customer_name',
            'services.kyc.prembly.endpoints.cac' => '/verification/cac',
            'services.kyc.prembly.endpoints.bank_comparison' => '/verification/bank_account/comparism',
            'services.kyc.prembly.endpoints.email_company_search' => '/identitypass/verification/global/company/search_with_email',
        ]);
    }

    private function application(): SupplierApplication
    {
        return SupplierApplication::query()->create([
            'user_id' => User::factory()->supplier()->create()->id,
            'status' => SupplierApplication::STATUS_DRAFT,
            'current_stage' => 'BUSINESS_DETAILS',
            'provider' => 'prembly',
            'cac_number' => 'RC123456',
            'business_name' => 'Solid Build Materials Ltd',
            'business_type' => 'Distributor',
            'business_address' => '12 Market Road, Ikeja',
            'state' => 'Lagos',
            'bank_name' => 'Access Bank',
            'bank_code' => '044',
            'account_name' => 'Solid Build Materials Ltd',
        ]);
    }
}
