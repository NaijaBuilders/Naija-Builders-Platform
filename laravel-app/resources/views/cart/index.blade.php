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
                        <div style="padding: 1.2rem; color: var(--neutral-600);">Your cart is empty.</div>
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
                                    <p style="margin: 0; color: var(--primary-color); font-weight: 700;">₦{{ number_format($item['price'], 2) }}</p>
                                </div>
                                <div class="cart-item-actions" style="display: flex; flex-direction: column; gap: 0.6rem; align-items: flex-end;">
                                    <form method="POST" action="/cart/update.php" style="display: flex; gap: 0.5rem; align-items: center;">
                                        @csrf
                                        <input type="hidden" name="material_id" value="{{ $item['id'] }}">
                                        <input type="number" name="quantity" min="1" max="{{ max(1, $item['stock_qty']) }}" value="{{ $item['quantity'] }}" style="width: 72px; padding: 0.4rem;">
                                        <button class="btn btn-outline btn-sm" type="submit">Update</button>
                                    </form>
                                    <p style="margin: 0; font-weight: 700;">₦{{ number_format($item['line_total'], 2) }}</p>
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
                    <div class="flex-between" style="margin-bottom: 0.7rem;"><span>Total</span><strong>₦{{ number_format($total, 2) }}</strong></div>
                    <button class="btn btn-primary btn-block" type="button">Checkout</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
