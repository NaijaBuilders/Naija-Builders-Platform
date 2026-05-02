<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubscriptionController extends Controller
{
    /**
     * @return array<int, array<string, mixed>>
     */
    private function plansForRole(string $role): array
    {
        if ($role === 'supplier') {
            return [
                [
                    'code' => 'standard',
                    'name' => 'Standard Marketplace',
                    'price_ngn' => 0,
                    'description' => 'For new suppliers that want to start listing materials quickly.',
                    'benefits' => [
                        'List materials on marketplace',
                        'Receive and send buyer messages',
                        'Basic search visibility',
                    ],
                ],
                [
                    'code' => 'pro',
                    'name' => 'Pro Marketplace',
                    'price_ngn' => 9500,
                    'description' => 'For suppliers that want stronger visibility and trust.',
                    'benefits' => [
                        'Everything in Standard',
                        'Priority listing placement',
                        'Verification badge on profile and listings',
                        'Advanced listing analytics',
                    ],
                ],
                [
                    'code' => 'enterprise',
                    'name' => 'Enterprise Marketplace',
                    'price_ngn' => 25000,
                    'description' => 'For high-volume suppliers and teams.',
                    'benefits' => [
                        'Everything in Pro',
                        'Dedicated account support',
                        'Team-ready account management',
                        'Campaign promotion opportunities',
                    ],
                ],
            ];
        }

        return [
            [
                'code' => 'standard',
                'name' => 'Buyer Standard',
                'price_ngn' => 0,
                'description' => 'For buyers that want to browse and connect with suppliers.',
                'benefits' => [
                    'Browse all materials',
                    'Save up to 25 products in collections',
                    'Send direct supplier messages',
                    'Single buyer account access',
                ],
            ],
            [
                'code' => 'pro',
                'name' => 'Buyer Pro',
                'price_ngn' => 6500,
                'description' => 'For active buyers comparing many products each month.',
                'benefits' => [
                    'Everything in Buyer Standard',
                    'Verification badge on buyer profile',
                    'Priority message visibility to suppliers',
                    'Save up to 200 products in collections',
                    'Price watchlist for shortlisted items',
                ],
            ],
            [
                'code' => 'enterprise',
                'name' => 'Buyer Enterprise',
                'price_ngn' => 18000,
                'description' => 'For procurement teams and larger buying operations.',
                'benefits' => [
                    'Everything in Buyer Pro',
                    'Multi-user procurement access (up to 5 users)',
                    'Priority account support',
                    'Procurement-focused approval controls',
                    'Unlimited saved-product collections',
                ],
            ],
        ];
    }

    public function show(Request $request)
    {
        $currentUser = (array) $request->session()->get('legacy_user', []);
        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);
        $role = (string) ($currentUser['role'] ?? 'builder');

        if ($currentUserId <= 0) {
            return redirect('/login.php');
        }

        $activePlan = 'standard';
        if (Schema::hasColumn('users', 'subscription_plan')) {
            $activePlan = (string) (DB::table('users')->where('id', $currentUserId)->value('subscription_plan') ?: 'standard');
        }

        $plans = $this->plansForRole($role);
        $plans = array_map(function (array $plan): array {
            $amount = (float) ($plan['price_ngn'] ?? 0);
            $plan['price'] = $amount > 0 ? 'NGN ' . number_format($amount, 2) . ' / month' : 'Free';
            return $plan;
        }, $plans);

        return view('subscription.index', compact('currentUser', 'plans', 'activePlan', 'role'));
    }

    public function store(Request $request)
    {
        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);
        $currentUser = (array) $request->session()->get('legacy_user', []);
        $role = (string) ($currentUser['role'] ?? 'builder');

        if ($currentUserId <= 0) {
            return redirect('/login.php');
        }

        $plan = (string) $request->input('subscription_plan', 'standard');
        $allowedPlanCodes = collect($this->plansForRole($role))->pluck('code')->all();
        if (!in_array($plan, $allowedPlanCodes, true)) {
            return redirect('/subscription.php?error=invalid_plan');
        }

        $hasVerification = in_array($plan, ['pro', 'enterprise'], true);

        if (Schema::hasColumn('users', 'subscription_plan')) {
            DB::table('users')
                ->where('id', $currentUserId)
                ->update([
                    'subscription_plan' => $plan,
                    'subscription_started_at' => now(),
                    'is_verified_badge' => $hasVerification ? 1 : 0,
                    'updated_at' => now(),
                ]);
        }

        $currentUser['subscription_plan'] = $plan;
        $currentUser['is_verified_badge'] = $hasVerification;
        $request->session()->put('legacy_user', $currentUser);

        return redirect($role === 'supplier' ? '/dashboard.php' : '/buyer-dashboard.php');
    }
}
