<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\BuyerOnboarding\BuyerOrderService;
use App\Support\NameFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    public function __construct(private readonly BuyerOrderService $buyerOrders) {}

    public function index(Request $request)
    {
        if (! Schema::hasTable('orders')) {
            return response()->json(['data' => []]);
        }

        $currentUserId = (int) $request->user()->id;
        $orders = $this->baseOrderQuery()
            ->where(function ($query) use ($currentUserId): void {
                $query->where('o.buyer_id', $currentUserId)
                    ->orWhere('o.supplier_id', $currentUserId);
            })
            ->orderByDesc('o.created_at')
            ->limit(50)
            ->get()
            ->map(fn ($order) => $this->orderPayload($order, []))
            ->values();

        return response()->json(['data' => $orders]);
    }

    public function show(Request $request, int $orderId)
    {
        if (! Schema::hasTable('orders')) {
            return response()->json(['message' => 'Orders table does not exist.'], 404);
        }

        $currentUserId = (int) $request->user()->id;
        $order = $this->baseOrderQuery()
            ->where('o.id', $orderId)
            ->where(function ($query) use ($currentUserId): void {
                $query->where('o.buyer_id', $currentUserId)
                    ->orWhere('o.supplier_id', $currentUserId);
            })
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json([
            'order' => $this->orderPayload($order, $this->orderItems($orderId), $this->deliveryPhotos($orderId)),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['nullable', 'integer', 'exists:users,id'],
            'amount_ngn' => ['nullable', 'numeric', 'min:1'],
            'total_amount' => ['nullable', 'numeric', 'min:1'],
            'items' => ['nullable', 'array'],
            'items.*.material_id' => ['required_with:items', 'integer', 'exists:materials,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'payment_method_type' => ['nullable', 'string', 'max:40'],
            'payment' => ['nullable', 'array'],
            'billing_name' => ['nullable', 'string', 'max:150'],
            'billing_country' => ['nullable', 'string', 'size:2'],
            'card_country' => ['nullable', 'string', 'size:2'],
            'gateway_risk_level' => ['nullable', 'in:low,medium,high'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'delivery_country' => ['nullable', 'string', 'size:2'],
            'recipient_name' => ['required', 'string', 'min:2', 'max:150'],
            'recipient_phone' => ['required', 'regex:/^(?:\+234|234|0)[789][01]\d{8}$/'],
            'recipient_relationship' => ['nullable', 'string', 'max:80'],
            'device_fingerprint' => ['nullable', 'string', 'max:500'],
            'risk_reference' => ['nullable', 'string', 'max:120'],
        ]);

        return response()->json($this->buyerOrders->place($request->user(), $validated, $request), 201);
    }

    private function baseOrderQuery()
    {
        $itemCountSelect = Schema::hasTable('order_items')
            ? DB::raw('(SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count')
            : DB::raw('0 as item_count');

        return DB::table('orders as o')
            ->leftJoin('users as buyer', 'buyer.id', '=', 'o.buyer_id')
            ->leftJoin('users as supplier', 'supplier.id', '=', 'o.supplier_id')
            ->select(array_merge([
                'o.id',
                'o.buyer_id',
                'o.supplier_id',
                'o.total_amount',
                'o.order_status',
                'o.created_at',
                'o.updated_at',
                $itemCountSelect,
                'buyer.full_name as buyer_name',
                'buyer.company as buyer_company',
                'supplier.full_name as supplier_name',
                'supplier.company as supplier_company',
            ], $this->optionalOrderSelects()));
    }

    private function orderItems(int $orderId): array
    {
        if (! Schema::hasTable('order_items')) {
            return [];
        }

        return DB::table('order_items as oi')
            ->leftJoin('materials as m', 'm.id', '=', 'oi.material_id')
            ->select([
                'oi.id',
                'oi.material_id',
                'oi.quantity',
                'oi.unit_price',
                'oi.created_at',
                'oi.updated_at',
                'm.name',
            ])
            ->where('oi.order_id', $orderId)
            ->orderBy('oi.id')
            ->get()
            ->map(function ($item): array {
                return [
                    'id' => (string) $item->id,
                    'material_id' => (string) $item->material_id,
                    'name' => (string) ($item->name ?? 'Material'),
                    'quantity' => (int) ($item->quantity ?? 1),
                    'unit_price' => (float) ($item->unit_price ?? 0),
                    'created_at' => (string) ($item->created_at ?? ''),
                    'updated_at' => (string) ($item->updated_at ?? ''),
                ];
            })
            ->all();
    }

    private function deliveryPhotos(int $orderId): array
    {
        if (! Schema::hasTable('order_delivery_photos')) {
            return [];
        }

        return DB::table('order_delivery_photos')
            ->where('order_id', $orderId)
            ->orderBy('id')
            ->get()
            ->map(fn ($photo): array => [
                'id' => (string) $photo->id,
                'path' => (string) $photo->path,
                'captured_at' => (string) ($photo->captured_at ?? ''),
                'gps_lat' => $photo->gps_lat === null ? null : (float) $photo->gps_lat,
                'gps_lng' => $photo->gps_lng === null ? null : (float) $photo->gps_lng,
            ])
            ->all();
    }

    private function orderPayload(object $order, array $items, array $deliveryPhotos = []): array
    {
        $supplierName = trim((string) ($order->supplier_company ?? '')) ?:
            NameFormatter::title((string) ($order->supplier_name ?? 'Supplier'));
        $buyerName = trim((string) ($order->buyer_company ?? '')) ?:
            NameFormatter::title((string) ($order->buyer_name ?? 'Buyer'));

        return [
            'id' => (string) $order->id,
            'reference' => (string) ($order->reference ?? 'NB-'.$order->id),
            'title' => 'Order '.($order->reference ?? 'NB-'.$order->id),
            'buyer_id' => (string) $order->buyer_id,
            'supplier_id' => (string) $order->supplier_id,
            'buyer_name' => $buyerName,
            'supplier_name' => $supplierName,
            'total_amount' => (float) ($order->total_amount ?? 0),
            'item_count' => count($items) > 0 ? count($items) : (int) ($order->item_count ?? 0),
            'order_status' => (string) ($order->order_status ?? 'pending'),
            'verification_tier' => (int) ($order->verification_tier ?? 1),
            'verification_status' => (string) ($order->verification_status ?? 'approved'),
            'review_status' => $order->review_status ?? null,
            'fraud_score' => (int) ($order->fraud_score ?? 0),
            'fraud_trigger_level' => $order->fraud_trigger_level ?? null,
            'payment_provider' => $order->payment_provider ?? null,
            'payment_method_type' => $order->payment_method_type ?? null,
            'payment_currency' => $order->payment_currency ?? 'NGN',
            'payment_amount' => $order->payment_amount === null ? null : (float) $order->payment_amount,
            'gateway_risk_level' => $order->gateway_risk_level ?? null,
            'delivery_address' => (string) ($order->delivery_address ?? ''),
            'recipient' => [
                'name' => (string) ($order->recipient_name ?? ''),
                'phone' => (string) ($order->recipient_phone ?? ''),
                'relationship' => $order->recipient_relationship ?? null,
            ],
            'delivery' => [
                'status' => (string) ($order->delivery_status ?? 'pending'),
                'photos' => $deliveryPhotos,
                'otp_generated_at' => $order->delivery_otp_generated_at ?? null,
                'otp_confirmed_at' => $order->delivery_otp_confirmed_at ?? null,
                'dispute_window_ends_at' => $order->dispute_window_ends_at ?? null,
                'dispute_status' => (string) ($order->dispute_status ?? 'none'),
                'dispute_outcome' => $order->dispute_outcome ?? null,
                'escrow_release_at' => $order->escrow_release_at ?? null,
            ],
            'items' => $items,
            'created_at' => (string) ($order->created_at ?? ''),
            'updated_at' => (string) ($order->updated_at ?? ''),
        ];
    }

    private function optionalOrderSelects(): array
    {
        $selects = [];

        foreach ([
            'reference',
            'verification_tier',
            'verification_status',
            'review_status',
            'fraud_score',
            'fraud_trigger_level',
            'payment_provider',
            'payment_method_type',
            'payment_currency',
            'payment_amount',
            'gateway_risk_level',
            'delivery_address',
            'recipient_name',
            'recipient_phone',
            'recipient_relationship',
            'delivery_status',
            'delivery_otp_generated_at',
            'delivery_otp_confirmed_at',
            'dispute_window_ends_at',
            'dispute_status',
            'dispute_outcome',
            'escrow_release_at',
        ] as $column) {
            $selects[] = Schema::hasColumn('orders', $column)
                ? 'o.'.$column
                : DB::raw('null as '.$column);
        }

        return $selects;
    }
}
