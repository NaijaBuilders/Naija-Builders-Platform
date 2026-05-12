<?php

namespace Tests\Feature;

use App\Models\SupplierApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupplierOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_supplier_is_auto_approved(): void
    {
        $supplier = User::factory()->supplier()->create();

        $response = $this->submitApplication($supplier);

        $response->assertOk()->assertJsonPath('application.status', 'APPROVED');
        $this->assertDatabaseHas('verification_decisions', ['decision' => 'AUTO_APPROVED']);
        $this->assertDatabaseHas('supplier_audit_logs', ['decision' => 'AUTO_APPROVED', 'new_status' => 'APPROVED']);
        $this->assertSame('approved', DB::table('users')->where('id', $supplier->id)->value('kyc_status'));
    }

    public function test_invalid_cac_auto_rejects_with_generic_supplier_message(): void
    {
        $supplier = User::factory()->supplier()->create();

        $response = $this->submitApplication($supplier, business: ['cac_number' => 'INVALID12345']);

        $response->assertOk()
            ->assertJsonPath('application.status', 'REJECTED')
            ->assertJsonPath('application.supplier_message', SupplierApplication::GENERIC_REJECTION_MESSAGE)
            ->assertJsonMissing(['CAC_INVALID_OR_UNREGISTERED']);
    }

    public function test_missing_both_bvn_and_nin_auto_rejects(): void
    {
        $supplier = User::factory()->supplier()->create();

        $this->submitBusiness($supplier);
        $this->submitBank($supplier);
        Sanctum::actingAs($supplier);
        $response = $this->postJson('/api/mobile/supplier/onboarding/submit');

        $response->assertOk()->assertJsonPath('application.status', 'REJECTED');
        $this->assertDecisionReason('IDENTITY_NUMBER_MISSING_OR_INVALID');
    }

    public function test_failed_liveness_auto_rejects(): void
    {
        $supplier = User::factory()->supplier()->create();

        $response = $this->submitApplication($supplier, identity: ['liveness_reference' => 'FAIL_LIVENESS']);

        $response->assertOk()->assertJsonPath('application.status', 'REJECTED');
        $this->assertDecisionReason('LIVENESS_FAILED');
    }

    public function test_low_face_match_auto_rejects(): void
    {
        $supplier = User::factory()->supplier()->create();

        $response = $this->submitApplication($supplier, identity: ['face_match_reference' => 'LOW_FACE_MATCH']);

        $response->assertOk()->assertJsonPath('application.status', 'REJECTED');
        $this->assertDecisionReason('FACE_MATCH_BELOW_THRESHOLD');
    }

    public function test_tampered_id_document_auto_rejects(): void
    {
        $supplier = User::factory()->supplier()->create();

        $response = $this->submitApplication($supplier, identity: ['id_document_reference' => 'TAMPERED']);

        $response->assertOk()->assertJsonPath('application.status', 'REJECTED');
        $this->assertDecisionReason('ID_DOCUMENT_TAMPERED');
    }

    public function test_unverifiable_bank_account_auto_rejects(): void
    {
        $supplier = User::factory()->supplier()->create();

        $response = $this->submitApplication($supplier, bank: ['account_number' => '0001234567']);

        $response->assertOk()->assertJsonPath('application.status', 'REJECTED');
        $this->assertDecisionReason('BANK_ACCOUNT_UNVERIFIABLE');
    }

    public function test_significant_bank_name_mismatch_auto_rejects(): void
    {
        $supplier = User::factory()->supplier()->create();

        $response = $this->submitApplication($supplier, bank: ['account_number' => '9991234567']);

        $response->assertOk()->assertJsonPath('application.status', 'REJECTED');
        $this->assertDecisionReason('BANK_NAME_MISMATCH_SIGNIFICANT');
    }

    public function test_aml_or_pep_flag_auto_rejects(): void
    {
        $supplier = User::factory()->supplier()->create();

        $response = $this->submitApplication($supplier, business: ['business_name' => 'PEP Flag Materials Ltd']);

        $response->assertOk()->assertJsonPath('application.status', 'REJECTED');
        $this->assertDecisionReason('AML_PEP_FLAG');
    }

    public function test_duplicate_rejected_bvn_auto_rejects(): void
    {
        $first = User::factory()->supplier()->create();
        $this->submitApplication($first, business: ['cac_number' => 'INVALID12345']);

        $second = User::factory()->supplier()->create();
        $response = $this->submitApplication($second);

        $response->assertOk()->assertJsonPath('application.status', 'REJECTED');
        $this->assertDecisionReason('DUPLICATE_REJECTED_BVN');
    }

    public function test_minor_bank_name_mismatch_goes_to_manual_review(): void
    {
        $supplier = User::factory()->supplier()->create();

        $response = $this->submitApplication($supplier, bank: ['account_number' => '8881234567']);

        $response->assertOk()->assertJsonPath('application.status', 'MANUAL_REVIEW');
        $this->assertDecisionReason('BANK_NAME_MISMATCH_MINOR');
    }

    public function test_new_cac_registration_goes_to_manual_review(): void
    {
        $supplier = User::factory()->supplier()->create();

        $response = $this->submitApplication($supplier, business: ['cac_number' => 'NEW123456']);

        $response->assertOk()->assertJsonPath('application.status', 'MANUAL_REVIEW');
        $this->assertDecisionReason('CAC_RECENT_REGISTRATION');
    }

    public function test_duplicate_device_goes_to_manual_review(): void
    {
        $first = User::factory()->supplier()->create();
        $this->submitApplication($first, headers: ['X-Device-Fingerprint' => 'device-one']);

        $second = User::factory()->supplier()->create();
        $response = $this->submitApplication($second, headers: ['X-Device-Fingerprint' => 'device-one']);

        $response->assertOk()->assertJsonPath('application.status', 'MANUAL_REVIEW');
        $this->assertDecisionReason('DUPLICATE_DEVICE');
    }

    public function test_duplicate_bank_account_goes_to_manual_review(): void
    {
        $first = User::factory()->supplier()->create();
        $this->submitApplication($first, bank: ['account_number' => '1234500000']);

        $second = User::factory()->supplier()->create();
        $response = $this->submitApplication($second, bank: ['account_number' => '1234500000']);

        $response->assertOk()->assertJsonPath('application.status', 'MANUAL_REVIEW');
        $this->assertDecisionReason('DUPLICATE_BANK_ACCOUNT');
    }

    public function test_admin_can_see_internal_rejection_reason(): void
    {
        $supplier = User::factory()->supplier()->create();
        $this->submitApplication($supplier, business: ['cac_number' => 'INVALID12345']);
        $admin = User::factory()->admin()->create();
        $application = SupplierApplication::query()->where('user_id', $supplier->id)->firstOrFail();

        Sanctum::actingAs($admin);
        $response = $this->getJson('/api/mobile/admin/supplier-onboarding/'.$application->id);

        $response->assertOk()->assertJsonFragment(['CAC_INVALID_OR_UNREGISTERED']);
    }

    public function test_sensitive_identity_and_bank_values_are_not_stored_raw(): void
    {
        $supplier = User::factory()->supplier()->create();

        $this->submitApplication($supplier);

        $application = SupplierApplication::query()->where('user_id', $supplier->id)->firstOrFail();
        $storedApplication = json_encode($application->getAttributes(), JSON_THROW_ON_ERROR);
        $storedChecks = DB::table('verification_checks')->pluck('normalized_result')->implode(' ');
        $storedSignals = DB::table('risk_signals')->get()->map(fn ($signal) => json_encode((array) $signal, JSON_THROW_ON_ERROR))->implode(' ');

        $this->assertStringNotContainsString('12345678901', $storedApplication);
        $this->assertStringNotContainsString('1234567890', $storedApplication);
        $this->assertStringNotContainsString('08012345678', $storedSignals);
        $this->assertStringNotContainsString('12345678901', $storedChecks);
        $this->assertStringNotContainsString('1234567890', $storedChecks);
        $this->assertSame('*******8901', $application->bvn_mask);
        $this->assertSame('******7890', $application->account_number_mask);
    }

    public function test_unauthorized_users_cannot_access_admin_review_routes(): void
    {
        $supplier = User::factory()->supplier()->create();

        Sanctum::actingAs($supplier);

        $this->getJson('/api/mobile/admin/supplier-onboarding/manual-review')
            ->assertForbidden();
    }

    public function test_supplier_cannot_access_another_supplier_application(): void
    {
        $owner = User::factory()->supplier()->create();
        $other = User::factory()->supplier()->create();
        $this->submitApplication($owner);
        $application = SupplierApplication::query()->where('user_id', $owner->id)->firstOrFail();

        Sanctum::actingAs($other);

        $this->getJson('/api/mobile/supplier/onboarding/applications/'.$application->id)
            ->assertNotFound();
    }

    public function test_audit_trail_is_created_for_manual_review_approve_reject_and_more_info(): void
    {
        $supplier = User::factory()->supplier()->create();
        $this->submitApplication($supplier, bank: ['account_number' => '8881234567']);
        $application = SupplierApplication::query()->where('user_id', $supplier->id)->firstOrFail();
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);
        $this->postJson('/api/mobile/admin/supplier-onboarding/'.$application->id.'/approve', ['notes' => 'Reviewed docs.'])->assertOk();
        $this->postJson('/api/mobile/admin/supplier-onboarding/'.$application->id.'/more-info', ['message' => 'Please update your address.', 'notes' => 'Address unclear.'])->assertOk();
        $this->postJson('/api/mobile/admin/supplier-onboarding/'.$application->id.'/reject', ['notes' => 'Unable to clear review.'])->assertOk();

        $this->assertDatabaseHas('supplier_audit_logs', ['new_status' => 'MANUAL_REVIEW', 'decision' => 'MANUAL_REVIEW']);
        $this->assertDatabaseHas('supplier_audit_logs', ['new_status' => 'APPROVED', 'decision' => 'AUTO_APPROVED']);
        $this->assertDatabaseHas('supplier_audit_logs', ['new_status' => 'MORE_INFO_REQUIRED', 'decision' => 'MANUAL_REVIEW']);
        $this->assertDatabaseHas('supplier_audit_logs', ['new_status' => 'REJECTED', 'decision' => 'AUTO_REJECTED']);
    }

    private function submitApplication(User $supplier, array $business = [], array $identity = [], array $bank = [], array $headers = [])
    {
        $this->submitBusiness($supplier, $business, $headers);
        $this->submitIdentity($supplier, $identity, $headers);
        $this->submitBank($supplier, $bank, $headers);

        Sanctum::actingAs($supplier);

        return $this->withHeaders($headers)->postJson('/api/mobile/supplier/onboarding/submit');
    }

    private function submitBusiness(User $supplier, array $overrides = [], array $headers = []): void
    {
        Sanctum::actingAs($supplier);

        $this->withHeaders($headers)->postJson('/api/mobile/supplier/onboarding/business-details', array_merge([
            'cac_number' => 'RC123456',
            'business_name' => 'Solid Build Materials Ltd',
            'business_type' => 'Distributor',
            'business_address' => '12 Market Road, Ikeja',
            'state' => 'Lagos',
            'contact_name' => 'Ada Supplier',
            'contact_email' => 'supplier@example.com',
            'contact_phone' => '08012345678',
        ], $overrides))->assertOk();
    }

    private function submitIdentity(User $supplier, array $overrides = [], array $headers = []): void
    {
        Sanctum::actingAs($supplier);

        $this->withHeaders($headers)->postJson('/api/mobile/supplier/onboarding/identity-verification', array_merge([
            'bvn' => '12345678901',
            'nin' => null,
            'id_document_type' => 'national_id',
        ], $overrides))->assertOk();
    }

    private function submitBank(User $supplier, array $overrides = [], array $headers = []): void
    {
        Sanctum::actingAs($supplier);

        $this->withHeaders($headers)->postJson('/api/mobile/supplier/onboarding/bank-details', array_merge([
            'bank_name' => 'Access Bank',
            'bank_code' => '044',
            'account_number' => '1234567890',
            'account_name' => 'Solid Build Materials Ltd',
        ], $overrides))->assertOk();
    }

    private function assertDecisionReason(string $reasonCode): void
    {
        $this->assertTrue(
            DB::table('verification_decisions')
                ->where('internal_reason_codes', 'like', '%'.$reasonCode.'%')
                ->exists(),
            'Expected decision reason '.$reasonCode
        );
    }
}
