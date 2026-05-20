<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteBuyerKycTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_dashboard_and_settings_link_to_optional_kyc_page(): void
    {
        $buyer = User::factory()->create();

        $this->actingAsLegacyBuyer($buyer)
            ->get('/buyer-dashboard.php')
            ->assertOk()
            ->assertSee('Buyer verification')
            ->assertSee('/buyer-kyc.php', false);

        $this->actingAsLegacyBuyer($buyer)
            ->get('/settings.php')
            ->assertOk()
            ->assertSee('Complete KYC Once')
            ->assertSee('/buyer-kyc.php', false);
    }

    public function test_buyer_can_open_and_submit_optional_kyc_once(): void
    {
        Storage::fake('local');
        $buyer = User::factory()->create(['full_name' => 'Ada Buyer']);

        $this->actingAsLegacyBuyer($buyer)
            ->get('/buyer-kyc.php')
            ->assertOk()
            ->assertSee('Complete KYC once')
            ->assertSee('How buyer KYC works');

        $this->actingAsLegacyBuyer($buyer)
            ->post('/buyer-kyc.php', [
                'document_type' => 'nin_slip',
                'verified_id_name' => 'Ada Buyer',
                'id_document' => UploadedFile::fake()->image('id.jpg'),
                'selfie' => UploadedFile::fake()->image('selfie.jpg'),
            ])
            ->assertRedirect('/buyer-kyc.php?success=verified');

        $this->assertDatabaseHas('buyer_identity_verifications', [
            'user_id' => $buyer->id,
            'document_type' => 'nin_slip',
            'status' => 'verified',
            'verified_id_name' => 'Ada Buyer',
        ]);

        $verification = DB::table('buyer_identity_verifications')->where('user_id', $buyer->id)->first();
        Storage::disk('local')->assertExists((string) $verification->document_path);
        Storage::disk('local')->assertExists((string) $verification->selfie_path);
    }

    private function actingAsLegacyBuyer(User $buyer): self
    {
        return $this->withSession([
            'legacy_user_id' => $buyer->id,
            'legacy_user' => [
                'id' => $buyer->id,
                'name' => $buyer->full_name,
                'email' => $buyer->email,
                'role' => 'builder',
                'location' => $buyer->location,
            ],
        ]);
    }
}
