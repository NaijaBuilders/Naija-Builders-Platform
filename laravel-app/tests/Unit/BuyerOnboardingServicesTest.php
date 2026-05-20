<?php

namespace Tests\Unit;

use App\Services\BuyerOnboarding\GatewayRiskService;
use App\Services\BuyerOnboarding\PaymentRoutingService;
use App\Services\BuyerOnboarding\VerificationTierService;
use Tests\TestCase;

class BuyerOnboardingServicesTest extends TestCase
{
    public function test_verification_tier_calculation_uses_ngn_thresholds(): void
    {
        config([
            'buyer_onboarding.tiers.tier_1_upper_ngn' => 1000000,
            'buyer_onboarding.tiers.tier_2_upper_ngn' => 5000000,
        ]);

        $tiers = app(VerificationTierService::class);

        $this->assertSame(1, $tiers->tierForAmount(1000000));
        $this->assertSame(2, $tiers->tierForAmount(1000001));
        $this->assertSame(2, $tiers->tierForAmount(5000000));
        $this->assertSame(3, $tiers->tierForAmount(5000001));
    }

    public function test_payment_routing_picks_paystack_for_nigerian_methods_and_stripe_for_diaspora_cards(): void
    {
        config([
            'buyer_onboarding.exchange_rates.gbp_to_ngn' => 2000,
            'currency.ngn_per_unit.USD' => 1600,
            'currency.ngn_per_unit.EUR' => 1700,
        ]);

        $router = app(PaymentRoutingService::class);

        $this->assertSame('paystack', $router->route(100000, ['payment_method_type' => 'bank_transfer'])['provider']);
        $this->assertSame('paystack', $router->route(100000, ['payment_method_type' => 'card', 'card_country' => 'NG'])['provider']);

        $ukRoute = $router->route(200000, ['payment_method_type' => 'card', 'card_country' => 'GB']);
        $this->assertSame('stripe', $ukRoute['provider']);
        $this->assertSame('GBP', $ukRoute['currency']);
        $this->assertSame(100.0, $ukRoute['amount']);

        $usRoute = $router->route(160000, ['payment_method_type' => 'card', 'card_country' => 'US']);
        $this->assertSame('USD', $usRoute['currency']);
        $this->assertSame(100.0, $usRoute['amount']);
    }

    public function test_gateway_risk_logic_handles_diaspora_medium_risk_without_blocking_tier_one_or_two(): void
    {
        $risk = app(GatewayRiskService::class);

        $tierOne = $risk->evaluate(1, [
            'provider' => 'stripe',
            'gateway_risk_level' => 'medium',
            'card_country' => 'GB',
            'delivery_country' => 'NG',
        ]);
        $this->assertSame('proceed', $tierOne['action']);
        $this->assertSame('passive', $tierOne['trigger_level']);
        $this->assertTrue($tierOne['expected_cross_border']);

        $tierTwo = $risk->evaluate(2, [
            'provider' => 'stripe',
            'gateway_risk_level' => 'medium',
            'card_country' => 'US',
            'delivery_country' => 'NG',
        ]);
        $this->assertSame('proceed', $tierTwo['action']);
        $this->assertSame('medium', $tierTwo['trigger_level']);

        $tierThree = $risk->evaluate(3, [
            'provider' => 'stripe',
            'gateway_risk_level' => 'medium',
            'card_country' => 'FR',
            'delivery_country' => 'NG',
        ]);
        $this->assertSame('hold', $tierThree['action']);

        $high = $risk->evaluate(1, ['gateway_risk_level' => 'high']);
        $this->assertSame('block', $high['action']);
        $this->assertSame('hard', $high['trigger_level']);
    }
}
