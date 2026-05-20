<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BuyerOnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_transaction_cap_blocks_large_first_order(): void
    {
        $buyer = User::factory()->create();
        $supplier = User::factory()->supplier()->create();

        Sanctum::actingAs($buyer);

        $this->postJson('/api/mobile/orders', $this->orderPayload($supplier, ['amount_ngn' => 500001]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('amount_ngn');
    }

    public function test_first_transaction_velocity_rule_blocks_third_order_in_first_window(): void
    {
        $buyer = User::factory()->create();
        $supplier = User::factory()->supplier()->create();
        DB::table('orders')->insert([
            $this->rawOrder($buyer, $supplier, 100000),
            $this->rawOrder($buyer, $supplier, 100000),
        ]);

        Sanctum::actingAs($buyer);

        $this->postJson('/api/mobile/orders', $this->orderPayload($supplier, ['amount_ngn' => 100000]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('orders');
    }

    public function test_tier_three_hard_trigger_holds_order_for_manual_review(): void
    {
        [$buyer, $supplier] = $this->verifiedReturningBuyer();

        Sanctum::actingAs($buyer);

        $this->postJson('/api/mobile/orders', $this->orderPayload($supplier, [
            'amount_ngn' => 6000000,
            'billing_name' => 'Ada Buyer',
            'payment' => ['fraud_score' => 70],
        ]))
            ->assertCreated()
            ->assertJsonPath('verification_tier', 3)
            ->assertJsonPath('verification_status', 'manual_review')
            ->assertJsonPath('fraud_trigger_level', 'hard');
    }

    public function test_tier_three_medium_trigger_holds_order_for_manual_review(): void
    {
        [$buyer, $supplier] = $this->verifiedReturningBuyer('Ada Verified');

        Sanctum::actingAs($buyer);

        $this->postJson('/api/mobile/orders', $this->orderPayload($supplier, [
            'amount_ngn' => 6000000,
            'billing_name' => 'Different Billing',
        ]))
            ->assertCreated()
            ->assertJsonPath('verification_status', 'manual_review')
            ->assertJsonPath('fraud_trigger_level', 'medium');
    }

    public function test_clean_tier_three_order_auto_approves(): void
    {
        [$buyer, $supplier] = $this->verifiedReturningBuyer();

        Sanctum::actingAs($buyer);

        $this->postJson('/api/mobile/orders', $this->orderPayload($supplier, [
            'amount_ngn' => 6000000,
            'billing_name' => 'Ada Buyer',
        ]))
            ->assertCreated()
            ->assertJsonPath('verification_tier', 3)
            ->assertJsonPath('verification_status', 'approved')
            ->assertJsonPath('review_status', null);
    }

    public function test_delivery_otp_is_locked_until_minimum_photos_are_uploaded(): void
    {
        $buyer = User::factory()->create();
        $supplier = User::factory()->supplier()->create();
        $orderId = $this->createPlacedOrder($buyer, $supplier);

        Sanctum::actingAs($supplier);

        $this->postJson("/api/mobile/orders/{$orderId}/delivery/otp")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('photos');
    }

    public function test_delivery_photos_unlock_otp_and_dispute_window_is_calculated(): void
    {
        Storage::fake('local');
        $buyer = User::factory()->create();
        $supplier = User::factory()->supplier()->create();
        $orderId = $this->createPlacedOrder($buyer, $supplier);

        Sanctum::actingAs($supplier);
        $this->postJson("/api/mobile/orders/{$orderId}/delivery/photos", [
            'photo' => UploadedFile::fake()->image('delivery-one.jpg'),
        ])->assertCreated();
        $this->postJson("/api/mobile/orders/{$orderId}/delivery/photos", [
            'photo' => UploadedFile::fake()->image('delivery-two.jpg'),
        ])->assertCreated();

        $otp = $this->postJson("/api/mobile/orders/{$orderId}/delivery/otp")
            ->assertOk()
            ->json('debug_code');

        Sanctum::actingAs($buyer);
        $this->postJson("/api/mobile/orders/{$orderId}/delivery/otp/confirm", ['otp' => $otp])
            ->assertOk()
            ->assertJsonPath('message', 'Delivery confirmed.');

        $window = DB::table('orders')->where('id', $orderId)->value('dispute_window_ends_at');
        $this->assertNotNull($window);
        $this->assertEqualsWithDelta(now()->addHours(72)->timestamp, CarbonImmutable::parse($window)->timestamp, 10);

        $this->postJson("/api/mobile/orders/{$orderId}/disputes", ['reason' => 'The delivered items are damaged.'])
            ->assertCreated()
            ->assertJsonPath('dispute.status', 'open');
        $this->assertDatabaseHas('orders', ['id' => $orderId, 'dispute_status' => 'open']);
    }

    public function test_admin_review_decisions_are_logged_with_operator_name(): void
    {
        [$buyer, $supplier] = $this->verifiedReturningBuyer('Ada Verified');
        $admin = User::factory()->admin()->create(['full_name' => 'Admin Reviewer']);

        Sanctum::actingAs($buyer);
        $orderId = $this->postJson('/api/mobile/orders', $this->orderPayload($supplier, [
            'amount_ngn' => 6000000,
            'billing_name' => 'Different Billing',
        ]))->assertCreated()->json('order_id');

        Sanctum::actingAs($admin);
        $this->postJson("/api/mobile/admin/buyer-reviews/{$orderId}/approve", ['notes' => 'Looks fine.'])
            ->assertOk();
        $this->postJson("/api/mobile/admin/buyer-reviews/{$orderId}/more-info", [
            'message' => 'Please upload a clearer invoice.',
            'notes' => 'Invoice blurry.',
        ])->assertOk();
        $this->postJson("/api/mobile/admin/buyer-reviews/{$orderId}/reject", ['notes' => 'No response.'])
            ->assertOk();

        $this->assertDatabaseHas('buyer_review_audits', [
            'order_id' => $orderId,
            'operator_name' => 'Admin Reviewer',
            'action' => 'ADMIN_APPROVED',
        ]);
        $this->assertDatabaseHas('buyer_review_audits', [
            'order_id' => $orderId,
            'operator_name' => 'Admin Reviewer',
            'action' => 'MORE_INFO_REQUESTED',
        ]);
        $this->assertDatabaseHas('buyer_review_audits', [
            'order_id' => $orderId,
            'operator_name' => 'Admin Reviewer',
            'action' => 'ADMIN_REJECTED',
        ]);
    }

    public function test_otp_confirmation_marks_email_and_phone_confirmed(): void
    {
        $buyer = User::factory()->create();
        Sanctum::actingAs($buyer);

        $emailCode = $this->postJson('/api/mobile/otp/send', ['channel' => 'email'])
            ->assertOk()
            ->json('otp.debug_code');
        $phoneCode = $this->postJson('/api/mobile/otp/send', ['channel' => 'phone'])
            ->assertOk()
            ->json('otp.debug_code');

        $this->postJson('/api/mobile/otp/confirm', ['channel' => 'email', 'otp' => $emailCode])->assertOk();
        $this->postJson('/api/mobile/otp/confirm', ['channel' => 'phone', 'otp' => $phoneCode])->assertOk();

        $this->assertNotNull(DB::table('users')->where('id', $buyer->id)->value('email_verified_at'));
        $this->assertNotNull(DB::table('users')->where('id', $buyer->id)->value('phone_verified_at'));
    }

    private function orderPayload(User $supplier, array $overrides = []): array
    {
        return array_replace_recursive([
            'supplier_id' => $supplier->id,
            'amount_ngn' => 100000,
            'payment_method_type' => 'card',
            'billing_name' => 'Ada Buyer',
            'billing_country' => 'NG',
            'card_country' => 'NG',
            'gateway_risk_level' => 'low',
            'delivery_address' => '12 Market Road, Lagos',
            'delivery_country' => 'NG',
            'recipient_name' => 'Tunde Recipient',
            'recipient_phone' => '08012345678',
            'recipient_relationship' => 'Site manager',
        ], $overrides);
    }

    private function rawOrder(User $buyer, User $supplier, float $amount): array
    {
        return [
            'buyer_id' => $buyer->id,
            'supplier_id' => $supplier->id,
            'total_amount' => $amount,
            'order_status' => 'processing',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * @return array{0: User, 1: User}
     */
    private function verifiedReturningBuyer(string $verifiedName = 'Ada Buyer'): array
    {
        $buyer = User::factory()->create([
            'full_name' => 'Ada Buyer',
            'first_transaction_monitoring' => false,
            'first_transaction_completed_at' => now()->subDay(),
        ]);
        $supplier = User::factory()->supplier()->create();

        DB::table('buyer_identity_verifications')->insert([
            'user_id' => $buyer->id,
            'provider' => 'fake',
            'provider_reference' => 'fake-ref-'.$buyer->id,
            'verified_id_name' => $verifiedName,
            'document_type' => 'nin_slip',
            'status' => 'verified',
            'normalized_result' => json_encode(['status' => 'PASSED'], JSON_THROW_ON_ERROR),
            'submitted_at' => now(),
            'verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$buyer, $supplier];
    }

    private function createPlacedOrder(User $buyer, User $supplier): int
    {
        Sanctum::actingAs($buyer);

        return (int) $this->postJson('/api/mobile/orders', $this->orderPayload($supplier))
            ->assertCreated()
            ->json('order_id');
    }
}
