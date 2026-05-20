<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\BuyerOnboarding\AdminBuyerReviewService;
use App\Support\NameFormatter;
use App\Support\Security\SensitiveData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBuyerReviewController extends Controller
{
    public function __construct(private readonly AdminBuyerReviewService $reviews) {}

    public function index(Request $request)
    {
        $query = DB::table('orders as o')
            ->leftJoin('users as buyer', 'buyer.id', '=', 'o.buyer_id')
            ->where(function ($query): void {
                $query->where('o.verification_status', 'manual_review')
                    ->orWhereIn('o.fraud_trigger_level', ['hard', 'medium'])
                    ->orWhere('o.review_status', 'flagged');
            });

        if ($request->filled('trigger_level')) {
            $query->where('o.fraud_trigger_level', $request->query('trigger_level'));
        }
        if ($request->filled('gateway_risk_level')) {
            $query->where('o.gateway_risk_level', $request->query('gateway_risk_level'));
        }
        if ($request->filled('min_fraud_score')) {
            $query->where('o.fraud_score', '>=', (int) $request->query('min_fraud_score'));
        }
        if ($request->filled('max_fraud_score')) {
            $query->where('o.fraud_score', '<=', (int) $request->query('max_fraud_score'));
        }
        if ($request->filled('min_order_value')) {
            $query->where('o.total_amount', '>=', (float) $request->query('min_order_value'));
        }
        if ($request->filled('max_order_value')) {
            $query->where('o.total_amount', '<=', (float) $request->query('max_order_value'));
        }
        if ($request->filled('max_account_age_hours')) {
            $query->where('buyer.created_at', '>=', now()->subHours((int) $request->query('max_account_age_hours')));
        }

        $orders = $query
            ->select([
                'o.id',
                'o.reference',
                'o.total_amount',
                'o.verification_tier',
                'o.verification_status',
                'o.review_status',
                'o.fraud_score',
                'o.fraud_trigger_level',
                'o.gateway_risk_level',
                'o.created_at',
                'buyer.full_name as buyer_name',
                'buyer.created_at as buyer_created_at',
            ])
            ->orderByDesc('o.created_at')
            ->limit(100)
            ->get()
            ->map(fn ($order): array => [
                'id' => (int) $order->id,
                'reference' => (string) ($order->reference ?? 'NB-'.$order->id),
                'buyer_name' => NameFormatter::title((string) ($order->buyer_name ?? 'Buyer')),
                'account_age_hours' => $order->buyer_created_at ? now()->diffInHours($order->buyer_created_at) : null,
                'order_value' => (float) $order->total_amount,
                'verification_tier' => (int) $order->verification_tier,
                'verification_status' => (string) $order->verification_status,
                'review_status' => $order->review_status,
                'fraud_score' => (int) $order->fraud_score,
                'fraud_trigger_level' => $order->fraud_trigger_level,
                'gateway_risk_level' => $order->gateway_risk_level,
                'created_at' => (string) $order->created_at,
            ])
            ->values();

        return response()->json(['data' => $orders]);
    }

    public function show(int $orderId)
    {
        $order = DB::table('orders as o')
            ->leftJoin('users as buyer', 'buyer.id', '=', 'o.buyer_id')
            ->leftJoin('buyer_identity_verifications as idv', function ($join): void {
                $join->on('idv.user_id', '=', 'o.buyer_id')
                    ->where('idv.status', '=', 'verified');
            })
            ->where('o.id', $orderId)
            ->select([
                'o.*',
                'buyer.full_name as buyer_name',
                'buyer.email as buyer_email',
                'buyer.phone as buyer_phone',
                'buyer.created_at as buyer_created_at',
                'buyer.registration_ip_display',
                'buyer.device_fingerprint_display',
                'idv.status as id_status',
                'idv.provider_reference as id_provider_reference',
                'idv.verified_id_name',
                'idv.document_type',
            ])
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json([
            'case' => [
                'order' => [
                    'id' => (int) $order->id,
                    'reference' => (string) ($order->reference ?? 'NB-'.$order->id),
                    'total_amount' => (float) $order->total_amount,
                    'verification_tier' => (int) $order->verification_tier,
                    'verification_status' => (string) $order->verification_status,
                    'fraud_score' => (int) $order->fraud_score,
                    'fraud_trigger_level' => $order->fraud_trigger_level,
                    'gateway_risk_level' => $order->gateway_risk_level,
                    'billing_name' => $order->billing_name,
                    'created_at' => (string) $order->created_at,
                ],
                'buyer' => [
                    'id' => (int) $order->buyer_id,
                    'name' => NameFormatter::title((string) $order->buyer_name),
                    'email' => (string) $order->buyer_email,
                    'phone' => SensitiveData::maskDigits((string) $order->buyer_phone),
                    'account_age_hours' => $order->buyer_created_at ? now()->diffInHours($order->buyer_created_at) : null,
                ],
                'device_ip' => [
                    'registration_ip' => $order->registration_ip_display,
                    'device' => $order->device_fingerprint_display,
                ],
                'identity' => [
                    'status' => $order->id_status,
                    'provider_reference' => $order->id_provider_reference,
                    'verified_id_name' => $order->verified_id_name,
                    'document_type' => $order->document_type,
                    'billing_name_match' => $this->billingNameMatch($order->billing_name, $order->verified_id_name),
                ],
                'fraud_events' => DB::table('buyer_fraud_events')->where('order_id', $order->id)->orderBy('id')->get(),
                'transaction_history' => DB::table('orders')
                    ->where('buyer_id', $order->buyer_id)
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get(['id', 'reference', 'total_amount', 'order_status', 'verification_status', 'created_at']),
                'audit_logs' => DB::table('buyer_review_audits')->where('order_id', $order->id)->orderByDesc('id')->get(),
            ],
        ]);
    }

    public function approve(Request $request, int $orderId)
    {
        $validated = $request->validate(['notes' => ['nullable', 'string', 'max:2000']]);

        return response()->json([
            'message' => 'Order approved.',
            'order' => $this->reviews->approve($orderId, $request->user(), $validated['notes'] ?? null),
        ]);
    }

    public function reject(Request $request, int $orderId)
    {
        $validated = $request->validate(['notes' => ['nullable', 'string', 'max:2000']]);

        return response()->json([
            'message' => 'Order rejected.',
            'order' => $this->reviews->reject($orderId, $request->user(), $validated['notes'] ?? null),
        ]);
    }

    public function moreInfo(Request $request, int $orderId)
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json([
            'message' => 'More information requested.',
            'order' => $this->reviews->requestMoreInfo(
                $orderId,
                $request->user(),
                $validated['message'] ?? null,
                $validated['notes'] ?? null
            ),
        ]);
    }

    private function billingNameMatch(?string $billingName, ?string $verifiedName): ?bool
    {
        if (! $billingName || ! $verifiedName) {
            return null;
        }

        $left = preg_replace('/[^A-Z]/', '', strtoupper($billingName)) ?: '';
        $right = preg_replace('/[^A-Z]/', '', strtoupper($verifiedName)) ?: '';

        return $left !== '' && $left === $right;
    }
}
