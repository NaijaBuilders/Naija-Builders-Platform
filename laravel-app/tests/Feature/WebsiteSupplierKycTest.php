<?php

namespace Tests\Feature;

use App\Models\SupplierApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebsiteSupplierKycTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_supplier_kyc_page_shows_standard_form(): void
    {
        $supplier = User::factory()->supplier()->create();

        $this->withSession($this->legacySession($supplier))
            ->get('/supplier-kyc.php')
            ->assertOk()
            ->assertSee('Business Registration')
            ->assertSee('CAC number')
            ->assertSee('Identity Verification')
            ->assertSee('BVN')
            ->assertSee('Bank Verification');
    }

    public function test_website_supplier_kyc_submission_runs_onboarding_checks(): void
    {
        $supplier = User::factory()->supplier()->create();

        $this->withSession($this->legacySession($supplier))
            ->post('/supplier-kyc.php', $this->validPayload())
            ->assertRedirect('/supplier-kyc.php?success=processed');

        $application = SupplierApplication::query()->where('user_id', $supplier->id)->firstOrFail();

        $this->assertSame(SupplierApplication::STATUS_APPROVED, $application->status);
        $this->assertSame('fake', $application->provider);
        $this->assertNotSame('12345678901', $application->bvn_mask);
        $this->assertNotSame('1234567890', $application->account_number_mask);
        $this->assertSame('approved', DB::table('users')->where('id', $supplier->id)->value('kyc_status'));
        $this->assertDatabaseHas('verification_checks', [
            'supplier_application_id' => $application->id,
            'check_type' => 'cac_lookup',
            'status' => 'PASSED',
        ]);
    }

    public function test_website_supplier_kyc_validation_does_not_flash_sensitive_inputs(): void
    {
        $supplier = User::factory()->supplier()->create();
        $payload = array_merge($this->validPayload(), [
            'account_number' => 'not-digits',
        ]);

        $this->withSession($this->legacySession($supplier))
            ->post('/supplier-kyc.php', $payload)
            ->assertRedirect('/supplier-kyc.php?error=invalid_fields')
            ->assertSessionHasErrors('account_number')
            ->assertSessionMissing('_old_input.bvn')
            ->assertSessionMissing('_old_input.nin')
            ->assertSessionMissing('_old_input.account_number');
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'cac_number' => 'RC123456',
            'business_name' => 'Solid Build Materials Ltd',
            'business_type' => 'Distributor',
            'business_address' => '12 Market Road, Ikeja',
            'state' => 'Lagos',
            'contact_name' => 'Ada Supplier',
            'contact_email' => 'supplier@example.com',
            'contact_phone' => '08012345678',
            'bvn' => '12345678901',
            'nin' => '',
            'id_document_type' => 'national_id',
            'bank_name' => 'Access Bank',
            'bank_code' => '044',
            'account_number' => '1234567890',
            'account_name' => 'Solid Build Materials Ltd',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function legacySession(User $supplier): array
    {
        return [
            'legacy_user_id' => $supplier->id,
            'legacy_user' => [
                'id' => $supplier->id,
                'email' => $supplier->email,
                'name' => $supplier->full_name,
                'role' => 'supplier',
                'location' => $supplier->location,
                'kyc_status' => 'pending',
            ],
        ];
    }
}
