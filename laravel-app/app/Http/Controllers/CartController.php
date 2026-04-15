<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = (array) $request->session()->get('cart', []);
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

        $items = [];
        $total = 0;
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

        return view('cart.index', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function add(Request $request)
    {
        $materialId = (int) $request->input('material_id', 0);
        $quantity = max(1, (int) $request->input('quantity', 1));

        $material = DB::table('materials')
            ->select(['id', 'stock_qty', 'status'])
            ->where('id', $materialId)
            ->first();

        if (!$material || $material->status !== 'active') {
            return back()->with('error', 'Product is not available.');
        }

        $cart = (array) $request->session()->get('cart', []);
        $existingQty = (int) ($cart[$materialId]['quantity'] ?? 0);
        $newQty = $existingQty + $quantity;
        $maxQty = max(1, (int) $material->stock_qty);

        $cart[$materialId] = [
            'quantity' => min($newQty, $maxQty),
        ];

        $request->session()->put('cart', $cart);

        return back()->with('success', 'Product added to cart.');
    }

    public function update(Request $request)
    {
        $materialId = (int) $request->input('material_id', 0);
        $quantity = max(1, (int) $request->input('quantity', 1));

        $cart = (array) $request->session()->get('cart', []);
        if (!isset($cart[$materialId])) {
            return redirect('/cart.php');
        }

        $stockQty = (int) DB::table('materials')->where('id', $materialId)->value('stock_qty');
        $cart[$materialId]['quantity'] = min($quantity, max(1, $stockQty));

        $request->session()->put('cart', $cart);

        return redirect('/cart.php')->with('success', 'Cart updated.');
    }

    public function remove(Request $request)
    {
        $materialId = (int) $request->input('material_id', 0);

        $cart = (array) $request->session()->get('cart', []);
        unset($cart[$materialId]);

        $request->session()->put('cart', $cart);

        return redirect('/cart.php')->with('success', 'Item removed from cart.');
    }
}
