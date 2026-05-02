@php
    $pageTitle = 'Choose Subscription';
@endphp
@extends('layouts.app')

@section('content')
<div class="container subscription-page" style="padding-top: 2rem; padding-bottom: 3rem;">
    @php($isSupplierRole = ($role ?? 'builder') === 'supplier')
    @php($currentPlan = $isSupplierRole
        ? ($activePlan === 'enterprise' ? 'Enterprise Marketplace' : ($activePlan === 'pro' ? 'Pro Marketplace' : 'Standard Marketplace'))
        : ($activePlan === 'enterprise' ? 'Buyer Enterprise' : ($activePlan === 'pro' ? 'Buyer Pro' : 'Buyer Standard')))
    @php($currentHasBadge = in_array($activePlan, ['pro', 'enterprise'], true))

    <section class="subscription-page__hero" style="position: relative; overflow: hidden; border-radius: var(--rounded-lg); margin-bottom: 1.5rem; background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.28), transparent 40%), linear-gradient(135deg, var(--secondary-color), var(--primary-color)); color: white; padding: 2.1rem; box-shadow: 0 20px 45px rgba(0, 0, 0, 0.14);">
        <div style="max-width: 760px; position: relative; z-index: 1;">
            <p style="margin: 0 0 0.65rem; font-size: 0.82rem; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255, 255, 255, 0.85);">Subscription Center</p>
            <h1 style="margin: 0 0 0.65rem; color: white; font-size: clamp(1.55rem, 4vw, 2.25rem); line-height: 1.2;">{{ $isSupplierRole ? 'Scale your supplier growth with the right plan' : 'Optimize your buying workflow with the right plan' }}</h1>
            <p style="margin: 0; color: rgba(255, 255, 255, 0.9); max-width: 56ch; line-height: 1.65;">{{ $isSupplierRole ? 'Pick a subscription with the visibility, trust, and support level your business needs. Upgrade or switch plans at any time.' : 'Choose the subscription that matches your purchasing volume, team structure, and supplier communication needs.' }}</p>
        </div>
    </section>

    <section class="card subscription-page__summary-card" style="margin-bottom: 1rem; border: 1px solid var(--neutral-200); box-shadow: 0 8px 24px rgba(14, 28, 51, 0.06);">
        <div class="card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; align-items: center;">
            <div>
                <p style="margin: 0 0 0.35rem; color: var(--neutral-600); font-size: 0.86rem; text-transform: uppercase; letter-spacing: 0.07em;">Current Plan</p>
                <p style="margin: 0; color: var(--neutral-900); font-size: 1.02rem; font-weight: 700;">
                    {{ $currentPlan }}
                    @if ($currentHasBadge)
                        <span style="display: inline-flex; align-items: center; margin-left: 0.45rem; font-size: 0.72rem; background: var(--secondary-color); color: white; border-radius: 999px; padding: 0.14rem 0.45rem;">Verified</span>
                    @endif
                </p>
            </div>
            <div>
                <p style="margin: 0 0 0.35rem; color: var(--neutral-600); font-size: 0.86rem; text-transform: uppercase; letter-spacing: 0.07em;">Plan Policy</p>
                <p style="margin: 0; color: var(--neutral-700);">Change anytime. Verified badge is active on Pro and Enterprise.</p>
            </div>
            <div style="display: flex; justify-content: flex-start;">
                <a href="/edit-profile.php" class="btn btn-outline" style="text-decoration: none; min-width: 170px;">Open Profile</a>
            </div>
        </div>
    </section>

    @if (request()->query('error') === 'invalid_plan')
        <div class="alert alert-danger" style="margin-bottom: 1rem;">Please choose a valid subscription plan.</div>
    @endif

    <section class="subscription-page__plans-head" style="margin: 1.35rem 0 0.7rem; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 0.75rem; align-items: end;">
        <div>
            <h2 style="margin: 0; font-size: clamp(1.15rem, 2.8vw, 1.45rem);">Available Plans</h2>
            <p style="margin: 0.35rem 0 0; color: var(--neutral-600);">Compare features and activate the plan that best matches your workflow.</p>
        </div>
        <p style="margin: 0; color: var(--neutral-500); font-size: 0.9rem;">Prices shown in {{ $displayCurrency }}. Platform billing thresholds remain calculated in NGN.</p>
    </section>

    <div class="subscription-page__plans-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; align-items: stretch;">
        @foreach ($plans as $plan)
            @php($isActive = $activePlan === $plan['code'])
            @php($isProPlan = $plan['code'] === 'pro')
            @php($isEnterprisePlan = $plan['code'] === 'enterprise')
            @php($hasBadge = in_array($plan['code'], ['pro', 'enterprise'], true))

            <article
                class="card"
                style="position: relative; border: 1px solid {{ $isActive ? 'var(--primary-color)' : ($isProPlan ? 'rgba(8, 179, 120, 0.45)' : 'var(--neutral-200)') }}; box-shadow: {{ $isActive ? '0 16px 36px rgba(31, 75, 157, 0.19)' : '0 8px 24px rgba(14, 28, 51, 0.08)' }}; transform: {{ $isActive ? 'translateY(-4px)' : 'none' }};">
                @if ($isActive)
                    <span style="position: absolute; top: 0.75rem; right: 0.75rem; background: var(--primary-color); color: white; border-radius: 999px; font-size: 0.72rem; padding: 0.2rem 0.55rem; z-index: 2;">Current</span>
                @elseif ($isProPlan)
                    <span style="position: absolute; top: 0.75rem; right: 0.75rem; background: var(--secondary-color); color: white; border-radius: 999px; font-size: 0.72rem; padding: 0.2rem 0.55rem; z-index: 2;">Popular</span>
                @endif

                @if ($isEnterprisePlan)
                    <div style="position: absolute; inset: 0; border-radius: inherit; pointer-events: none; border: 1px solid rgba(31, 75, 157, 0.35);"></div>
                @endif

                <div class="card-body subscription-page__plan-body" style="display: flex; flex-direction: column; height: 100%; gap: 0.85rem; padding-top: 1.1rem;">
                    <div>
                        <h3 style="margin: 0; color: var(--neutral-900);">{{ $plan['name'] }}</h3>
                        <p style="margin: 0.35rem 0 0; color: var(--neutral-600); line-height: 1.55;">{{ $plan['description'] }}</p>
                    </div>

                    <div style="padding: 0.85rem 0.95rem; border-radius: var(--rounded-md); background: var(--neutral-50); border: 1px solid var(--neutral-200);">
                        <p style="margin: 0; color: var(--neutral-500); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.07em;">Price</p>
                        <p style="margin: 0.3rem 0 0; font-size: 1.3rem; line-height: 1; color: var(--secondary-color); font-weight: 800;">
                            {{ ((float) ($plan['price_ngn'] ?? 0)) > 0 ? $formatMoney((float) $plan['price_ngn']) . ' / month' : 'Free' }}
                        </p>
                    </div>

                    <ul style="margin: 0; padding: 0; list-style: none; display: grid; gap: 0.52rem; color: var(--neutral-700); flex: 1;">
                        @foreach ($plan['benefits'] as $benefit)
                            <li style="display: grid; grid-template-columns: 18px 1fr; gap: 0.45rem; align-items: start;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 999px; background: rgba(8, 179, 120, 0.16); color: var(--secondary-color); font-size: 0.72rem; font-weight: 700; line-height: 1;">✓</span>
                                <span>{{ $benefit }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @if ($hasBadge)
                        <p style="margin: 0; color: var(--secondary-color); font-size: 0.88rem; font-weight: 600;">Includes verified badge on listings/profile.</p>
                    @endif

                    <form method="POST" action="/subscription.php" style="margin-top: 0.1rem;">
                        @csrf
                        <input type="hidden" name="subscription_plan" value="{{ $plan['code'] }}">
                        <button type="submit" class="btn {{ $isActive ? 'btn-secondary' : 'btn-primary' }} btn-block" style="font-weight: 700;">
                            {{ $isActive ? 'Keep This Plan' : 'Choose Plan' }}
                        </button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>

    <section class="card" style="margin-top: 1.1rem; border: 1px solid var(--neutral-200);">
        <div class="card-body" style="display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: space-between; align-items: center;">
            <p style="margin: 0; color: var(--neutral-700);">Need a custom plan for larger operations? Reach out to support for tailored onboarding.</p>
            <a href="/support.php" class="btn btn-outline" style="text-decoration: none;">Contact Support</a>
        </div>
    </section>
</div>
@endsection
