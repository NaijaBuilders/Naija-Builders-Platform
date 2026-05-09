@php($pageTitle = 'Cart')
@extends('layouts.app')

@section('content')
<div class="container page-shell cart-page">
    <div class="flex-between cart-header-row">
        <h1 class="title-reset">Your Cart</h1>
        <a href="/materials.php" class="btn btn-outline">Continue Shopping</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="grid-4 gap-lg cart-page-layout" style="align-items: flex-start;">
        <div class="cart-items-column" style="grid-column: 1 / span 3;">
            <div class="card">
                <div class="card-body" style="padding: 0;">
                    @if (count($items) === 0)
                        <div class="empty-state">
                            <h3>Your cart is empty</h3>
                            <p>Browse materials and add the products you want to compare or request from suppliers.</p>
                            <a href="/materials.php" class="btn btn-primary">Browse Materials</a>
                        </div>
                    @else
                        @foreach ($items as $item)
                            <div class="cart-item-row" style="display: grid; grid-template-columns: 96px 1fr auto; gap: 1rem; align-items: center; padding: 1rem 1.2rem; border-bottom: 1px solid var(--neutral-200);">
                                <div class="material-thumb">
                                    @if ($item['image_path'] !== '')
                                        <img src="/{{ $item['image_path'] }}" alt="{{ $item['name'] }}" class="material-thumb-image">
                                    @else
                                        <div class="material-thumb-placeholder">NO IMAGE</div>
                                    @endif
                                </div>
                                <div>
                                    <h4 style="margin: 0 0 0.35rem; font-size: 1rem;">{{ $item['name'] }}</h4>
                                    <p style="margin: 0 0 0.35rem; color: var(--neutral-600); font-size: 0.9rem;">{{ $item['category'] }} · {{ $item['company'] !== '' ? $item['company'] : 'Supplier' }}</p>
                                    <p style="margin: 0; color: var(--primary-color); font-weight: 700;">{{ $formatMoney($item['price']) }}</p>
                                    <a href="/messages.php?receiver_id={{ (int) $item['supplier_id'] }}&material_id={{ (int) $item['id'] }}" class="text-note" style="display: inline-block; margin-top: 0.35rem;">Message supplier</a>
                                </div>
                                <div class="cart-item-actions" style="display: flex; flex-direction: column; gap: 0.6rem; align-items: flex-end;">
                                    <form method="POST" action="/cart/update.php" style="display: flex; gap: 0.5rem; align-items: center;">
                                        @csrf
                                        <input type="hidden" name="material_id" value="{{ $item['id'] }}">
                                        <input type="number" name="quantity" min="1" max="{{ max(1, $item['stock_qty']) }}" value="{{ $item['quantity'] }}" style="width: 72px; padding: 0.4rem;">
                                        <button class="btn btn-outline btn-sm" type="submit">Update</button>
                                    </form>
                                    <p style="margin: 0; font-weight: 700;">{{ $formatMoney($item['line_total']) }}</p>
                                    <form method="POST" action="/cart/remove.php">
                                        @csrf
                                        <input type="hidden" name="material_id" value="{{ $item['id'] }}">
                                        <button type="submit" style="border: 0; background: transparent; color: var(--accent-danger); cursor: pointer;">Remove</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="cart-summary-column">
            <div class="card">
                <div class="card-body">
                    <h3 class="cart-summary-title">Order Summary</h3>
                    <div class="summary-row"><span>Subtotal</span><strong>{{ $formatMoney($total) }}</strong></div>
                    <div class="summary-row"><span>Delivery</span><span>Supplier quote</span></div>
                    <div class="summary-row"><span>Taxes / fees</span><span>Confirmed by supplier</span></div>
                    <div class="summary-row summary-row--total"><span>Estimated total</span><strong>{{ $formatMoney($total) }}</strong></div>
                    <p class="summary-note">Cart totals use listed product prices only. Delivery, taxes, and final order terms are confirmed with suppliers.</p>
                    @if (count($items) > 0)
                        <a href="/messages.php" class="btn btn-primary btn-block" style="text-decoration: none;">Contact Suppliers to Order</a>
                    @else
                        <a href="/materials.php" class="btn btn-primary btn-block" style="text-decoration: none;">Browse Materials</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
