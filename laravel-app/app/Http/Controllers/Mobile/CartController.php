<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $currentUserId = (int) $request->user()->id;
        $cart = $this->getCart($currentUserId);
        $materialIds = array_map('intval', array_keys($cart));

        $materials = collect();
        if (count($materialIds) > 0) {
            $materials = DB::table('materials as m')
                ->leftJoin('users as u', 'u.id', '=', 'm.supplier_id')
                ->select([
                    'm.id',
                    'm.name',
                    'm.price',
                    'm.stock_qty',
                    'm.category',
                    'u.company',
                    DB::raw('(SELECT mi.image_path FROM material_images mi WHERE mi.material_id = m.id ORDER BY mi.display_order ASC, mi.id ASC LIMIT 1) as image_path'),
                ])
                ->whereIn('m.id', $materialIds)
                ->get()
                ->keyBy('id');
        }

        [$items, $total] = $this->buildCartItems($cart, $materials);

        return response()->json([
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function add(Request $request)
    {
        $currentUserId = (int) $request->user()->id;
        $materialId = (int) $request->input('material_id', 0);
        $quantity = max(1, (int) $request->input('quantity', 1));

        $material = DB::table('materials')
            ->select(['id', 'stock_qty', 'status'])
            ->where('id', $materialId)
            ->first();

        if (!$material || $material->status !== 'active') {
            return response()->json(['message' => 'Product is not available.'], 422);
        }

        $cart = $this->getCart($currentUserId);
        $existingQty = (int) ($cart[$materialId]['quantity'] ?? 0);
        $newQty = $existingQty + $quantity;
        $maxQty = max(1, (int) $material->stock_qty);

        $cart[$materialId] = [
            'quantity' => min($newQty, $maxQty),
        ];

        $this->saveCart($currentUserId, $cart);

        return $this->index($request);
    }

    public function update(Request $request, int $materialId)
    {
        $currentUserId = (int) $request->user()->id;
        $quantity = max(1, (int) $request->input('quantity', 1));

        $cart = $this->getCart($currentUserId);
        if (!isset($cart[$materialId])) {
            return response()->json(['message' => 'Item not found in cart.'], 404);
        }

        $stockQty = (int) DB::table('materials')->where('id', $materialId)->value('stock_qty');
        $cart[$materialId]['quantity'] = min($quantity, max(1, $stockQty));

        $this->saveCart($currentUserId, $cart);

        return $this->index($request);
    }

    public function remove(Request $request, int $materialId)
    {
        $currentUserId = (int) $request->user()->id;
        $cart = $this->getCart($currentUserId);
        unset($cart[$materialId]);

        $this->saveCart($currentUserId, $cart);

        return $this->index($request);
    }

    private function buildCartItems(array $cart, $materials): array
    {
        $items = [];
        $total = 0.0;

        foreach ($cart as $materialId => $entry) {
            $materialId = (int) $materialId;
            $quantity = max(1, (int) ($entry['quantity'] ?? 1));
            $material = $materials->get($materialId);
            if (!$material) {
                continue;
            }

            $lineTotal = (float) $material->price * $quantity;
            $total += $lineTotal;

            $items[] = [
                'id' => $materialId,
                'name' => (string) $material->name,
                'category' => (string) ($material->category ?? 'General'),
                'company' => (string) ($material->company ?? ''),
                'price' => (float) $material->price,
                'stock_qty' => (int) $material->stock_qty,
                'quantity' => $quantity,
                'image_path' => (string) ($material->image_path ?? ''),
                'line_total' => $lineTotal,
            ];
        }

        return [$items, $total];
    }

    private function getCart(int $userId): array
    {
        return (array) Cache::get($this->cartCacheKey($userId), []);
    }

    private function saveCart(int $userId, array $cart): void
    {
        Cache::put($this->cartCacheKey($userId), $cart, now()->addDays(7));
    }

    private function cartCacheKey(int $userId): string
    {
        return 'mobile_cart_' . $userId;
    }
}
