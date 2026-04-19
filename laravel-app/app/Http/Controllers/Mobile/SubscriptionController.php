<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
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
                    'price' => 'Free',
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
                    'price' => 'N9,500 / month',
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
                    'price' => 'N25,000 / month',
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
                'price' => 'Free',
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
                'price' => 'N6,500 / month',
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
                'price' => 'N18,000 / month',
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
        $currentUserId = (int) $request->user()->id;
        $role = (string) DB::table('users')->where('id', $currentUserId)->value('role');

        $activePlan = 'standard';
        if (Schema::hasColumn('users', 'subscription_plan')) {
            $activePlan = (string) (DB::table('users')->where('id', $currentUserId)->value('subscription_plan') ?: 'standard');
        }

        $plans = $this->plansForRole($role);

        return response()->json([
            'plans' => $plans,
            'active_plan' => $activePlan,
            'role' => $role !== '' ? $role : 'builder',
        ]);
    }

    public function store(Request $request)
    {
        $currentUserId = (int) $request->user()->id;
        $role = (string) DB::table('users')->where('id', $currentUserId)->value('role');

        $plan = (string) $request->input('subscription_plan', 'standard');
        $allowedPlanCodes = collect($this->plansForRole($role))->pluck('code')->all();
        if (!in_array($plan, $allowedPlanCodes, true)) {
            return response()->json(['message' => 'Invalid plan.'], 422);
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

        return response()->json([
            'message' => 'Subscription updated.',
            'active_plan' => $plan,
            'role' => $role !== '' ? $role : 'builder',
        ]);
    }
}
