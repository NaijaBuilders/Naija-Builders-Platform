<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('orders')) {
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
        if (!Schema::hasTable('orders')) {
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

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json([
            'order' => $this->orderPayload($order, $this->orderItems($orderId)),
        ]);
    }

    private function baseOrderQuery()
    {
        $itemCountSelect = Schema::hasTable('order_items')
            ? DB::raw('(SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count')
            : DB::raw('0 as item_count');

        return DB::table('orders as o')
            ->leftJoin('users as buyer', 'buyer.id', '=', 'o.buyer_id')
            ->leftJoin('users as supplier', 'supplier.id', '=', 'o.supplier_id')
            ->select([
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
            ]);
    }

    private function orderItems(int $orderId): array
    {
        if (!Schema::hasTable('order_items')) {
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

    private function orderPayload(object $order, array $items): array
    {
        $supplierName = trim((string) ($order->supplier_company ?? '')) ?:
            trim((string) ($order->supplier_name ?? 'Supplier'));
        $buyerName = trim((string) ($order->buyer_company ?? '')) ?:
            trim((string) ($order->buyer_name ?? 'Buyer'));

        return [
            'id' => (string) $order->id,
            'reference' => 'NB-' . $order->id,
            'title' => 'Order NB-' . $order->id,
            'buyer_id' => (string) $order->buyer_id,
            'supplier_id' => (string) $order->supplier_id,
            'buyer_name' => $buyerName,
            'supplier_name' => $supplierName,
            'total_amount' => (float) ($order->total_amount ?? 0),
            'item_count' => count($items) > 0 ? count($items) : (int) ($order->item_count ?? 0),
            'order_status' => (string) ($order->order_status ?? 'pending'),
            'delivery_address' => '',
            'items' => $items,
            'created_at' => (string) ($order->created_at ?? ''),
            'updated_at' => (string) ($order->updated_at ?? ''),
        ];
    }
}
