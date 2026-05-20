@php($pageTitle = 'Buyer Dashboard')
@extends('layouts.app')

@section('content')
<div class="container dashboard-page dashboard-page--buyer" style="padding-top: 2rem; padding-bottom: 2rem;">
    <div style="background: linear-gradient(135deg, var(--secondary-color), var(--primary-color)); color: white; padding: 2rem; border-radius: var(--rounded-lg); margin-bottom: 2rem;">
        <h1 style="color: white; margin: 0 0 0.5rem;">Welcome, {{ \App\Support\NameFormatter::title((string) ($currentUser['name'] ?? 'Buyer')) }}</h1>
        <p style="margin: 0; color: rgba(255, 255, 255, 0.9);">Track your orders, discover materials, and connect with suppliers.</p>
    </div>

    @if (session('save_success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('save_success') }}</div>
    @endif
    @if (session('save_error'))
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">{{ session('save_error') }}</div>
    @endif

    <div class="grid-4 gap-lg" style="margin-bottom: 2rem;">
        <div class="card"><div class="card-body"><p style="color: var(--neutral-600); margin: 0 0 0.5rem;">Items in Cart</p><h2 style="color: var(--primary-color); margin: 0 0 0.5rem;">{{ number_format($cartItemCount) }}</h2></div></div>
        <div class="card"><div class="card-body"><p style="color: var(--neutral-600); margin: 0 0 0.5rem;">Total Orders</p><h2 style="color: var(--secondary-color); margin: 0 0 0.5rem;">{{ number_format($orderCount) }}</h2></div></div>
        <div class="card"><div class="card-body"><p style="color: var(--neutral-600); margin: 0 0 0.5rem;">Pending Orders</p><h2 style="color: var(--accent-warning); margin: 0 0 0.5rem;">{{ number_format($pendingOrders) }}</h2></div></div>
        <div class="card"><div class="card-body"><p style="color: var(--neutral-600); margin: 0 0 0.5rem;">Unread Messages</p><h2 style="color: var(--accent-info); margin: 0 0 0.5rem;">{{ number_format($unreadMessages) }}</h2></div></div>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div>
                <h3 style="margin: 0 0 0.35rem;">Total Spend</h3>
                <p style="margin: 0; color: var(--neutral-600);">Across completed and active orders</p>
            </div>
            <h2 style="margin: 0; color: var(--accent-success);">{{ $formatMoney($totalSpent) }}</h2>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem; border: 1px solid rgba(14, 165, 233, 0.22);">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div style="max-width: 680px;">
                <h3 style="margin: 0 0 0.35rem;">Buyer verification</h3>
                <p style="margin: 0; color: var(--neutral-600); line-height: 1.55;">
                    You can browse without KYC. Larger orders may ask for ID, but you can complete verification once now so future eligible orders move faster.
                </p>
            </div>
            <a href="/buyer-kyc.php" class="btn btn-primary" style="text-decoration: none;">Open KYC Page</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
            <h3 style="margin: 0;">Recent Orders</h3>
            <a href="/messages.php" class="btn btn-outline" style="text-decoration: none;">Open Messages</a>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--neutral-50);"><tr><th style="padding: 1rem; text-align: left;">Order #</th><th style="padding: 1rem; text-align: left;">Supplier</th><th style="padding: 1rem; text-align: left;">Status</th><th style="padding: 1rem; text-align: left;">Amount</th><th style="padding: 1rem; text-align: left;">Date</th></tr></thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        @php($supplierName = trim((string) ($order->company ?? '')) !== '' ? (string) $order->company : \App\Support\NameFormatter::title((string) ($order->full_name ?? 'Supplier')))
                        <tr style="border-bottom: 1px solid var(--neutral-200);"><td style="padding: 1rem;">#{{ $order->id }}</td><td style="padding: 1rem;">{{ $supplierName }}</td><td style="padding: 1rem;">{{ ucfirst((string) $order->order_status) }}</td><td style="padding: 1rem;">{{ $formatMoney((float) $order->total_amount) }}</td><td style="padding: 1rem;">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td></tr>
                    @empty
                        <tr><td colspan="5" style="padding: 1rem; color: var(--neutral-600);">No orders yet. Start by browsing available materials.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
            <h3 style="margin: 0;">Recommended Materials</h3>
            <a href="/materials.php" class="btn btn-primary" style="text-decoration: none;">Browse Materials</a>
        </div>
        <div class="materials-grid" style="padding: 1.5rem;">
            @forelse ($recommendedMaterials as $material)
                @php($supplierName = trim((string) ($material->company ?? '')) !== '' ? (string) $material->company : \App\Support\NameFormatter::title((string) ($material->full_name ?? 'Supplier')))
                <article class="card" style="border: 1px solid var(--neutral-200); box-shadow: none;">
                    <div class="card-body">
                        <h4 style="margin-top: 0; margin-bottom: 0.5rem;">{{ $material->name }}</h4>
                        <p style="margin: 0 0 0.5rem; color: var(--neutral-600);">
                            Supplier: {{ $supplierName }}
                            @if ((int) ($material->is_verified_badge ?? 0) === 1)
                                <span style="display: inline-block; margin-left: 0.35rem; background: var(--secondary-color); color: white; border-radius: 999px; font-size: 0.72rem; padding: 0.15rem 0.45rem;">Verified</span>
                            @endif
                        </p>
                        <p style="margin: 0 0 1rem; font-weight: 600; color: var(--primary-color);">{{ $formatMoney((float) $material->price) }} / {{ ucfirst(str_replace('_', ' ', (string) ($material->price_unit ?: 'item'))) }}</p>
                        <a href="/material-detail.php?id={{ $material->id }}" class="btn btn-outline" style="text-decoration: none; margin-bottom: 0.45rem;">View Material</a>
                        <button
                            type="button"
                            class="btn btn-secondary btn-block open-save-modal"
                            data-material-id="{{ (int) $material->id }}"
                            data-material-name="{{ $material->name }}"
                            style="margin-top: 0.45rem;"
                        >
                            Save Product
                        </button>
                    </div>
                </article>
            @empty
                <div class="card" style="grid-column: 1 / -1;"><div class="card-body"><p style="margin: 0; color: var(--neutral-600);">No active materials available yet.</p></div></div>
            @endforelse
        </div>
    </div>

    <div class="card" style="margin-top: 2rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <h3 style="margin: 0;">Saved Materials</h3>
            <form method="GET" action="/buyer-dashboard.php" style="display: flex; align-items: center; gap: 0.5rem;">
                <label for="saved-category-filter" style="margin: 0; font-size: 0.9rem; color: var(--neutral-600);">Category</label>
                <select id="saved-category-filter" name="saved_category" onchange="this.form.submit()" style="min-width: 170px;">
                    <option value="0">All Saved</option>
                    @foreach ($savedCategories as $collection)
                        <option value="{{ (int) $collection->id }}" {{ (int) $selectedSavedCategoryId === (int) $collection->id ? 'selected' : '' }}>{{ $collection->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="card-body" style="padding-top: 0.5rem;">
            @forelse ($savedMaterials as $savedItem)
                @php($savedSupplierName = trim((string) ($savedItem->company ?? '')) !== '' ? (string) $savedItem->company : \App\Support\NameFormatter::title((string) ($savedItem->full_name ?? 'Supplier')))
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; border-bottom: 1px solid var(--neutral-200); padding: 0.85rem 0; flex-wrap: wrap;">
                    <div>
                        <h4 style="margin: 0 0 0.35rem; font-size: 1rem;">{{ $savedItem->name }}</h4>
                        <p style="margin: 0; color: var(--neutral-600); font-size: 0.9rem;">
                            {{ $savedSupplierName }}
                            @if ((int) ($savedItem->is_verified_badge ?? 0) === 1)
                                <span style="display: inline-block; margin-left: 0.35rem; background: var(--secondary-color); color: white; border-radius: 999px; font-size: 0.72rem; padding: 0.15rem 0.45rem;">Verified</span>
                            @endif
                            · {{ $formatMoney((float) $savedItem->price) }} / {{ ucfirst(str_replace('_', ' ', (string) ($savedItem->price_unit ?: 'item'))) }}
                        </p>
                        <p style="margin: 0.2rem 0 0; color: var(--neutral-500); font-size: 0.85rem;">
                            Saved to: {{ $savedItem->saved_category_name ?: 'General Saves' }}
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <a href="/material-detail.php?id={{ (int) $savedItem->material_id }}" class="btn btn-outline" style="text-decoration: none;">Open</a>
                        <form method="POST" action="/saved-products/remove.php">
                            @csrf
                            <input type="hidden" name="material_id" value="{{ (int) $savedItem->material_id }}">
                            <button class="btn btn-danger" type="submit">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <p style="margin: 0.5rem 0 0; color: var(--neutral-600);">No saved products yet. Use "Save Product" on materials you want to revisit later.</p>
            @endforelse
        </div>
    </div>
</div>

<div id="saveProductModal" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); display: none; align-items: center; justify-content: center; z-index: 1200; padding: 1rem;">
    <div style="width: 100%; max-width: 460px; background: white; border-radius: var(--rounded-lg); box-shadow: var(--shadow-xl); padding: 1.2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 0.8rem;">
            <h3 style="margin: 0; font-size: 1.2rem;">Save Product</h3>
            <button id="closeSaveProductModal" type="button" aria-label="Close save product dialog" style="border: 0; background: transparent; font-size: 1.3rem; cursor: pointer;">×</button>
        </div>
        <p id="saveProductLabel" style="margin: 0 0 1rem; color: var(--neutral-600);">Choose where to save this product.</p>
        <form method="POST" action="/saved-products.php">
            @csrf
            <input type="hidden" id="saveProductMaterialId" name="material_id" value="0">
            <div class="form-group">
                <label for="saveCategoryId">Save to Existing Category</label>
                <select id="saveCategoryId" name="category_id">
                    <option value="0">General Saves</option>
                    @foreach ($savedCategories as $collection)
                        <option value="{{ (int) $collection->id }}">{{ $collection->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="newCategoryName">Or Create New Category</label>
                <input type="text" id="newCategoryName" name="new_category_name" maxlength="120" placeholder="Example: Foundation Materials">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Product</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const modal = document.getElementById('saveProductModal');
    const closeButton = document.getElementById('closeSaveProductModal');
    const materialIdInput = document.getElementById('saveProductMaterialId');
    const label = document.getElementById('saveProductLabel');
    const openButtons = Array.from(document.querySelectorAll('.open-save-modal'));

    if (!modal || !closeButton || !materialIdInput || !label || openButtons.length === 0) {
        return;
    }

    const closeModal = function () {
        modal.style.display = 'none';
    };

    openButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const materialId = button.getAttribute('data-material-id') || '0';
            const materialName = button.getAttribute('data-material-name') || 'this product';
            materialIdInput.value = materialId;
            label.textContent = 'Choose where to save "' + materialName + '".';
            modal.style.display = 'flex';
        });
    });

    closeButton.addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });
})();
</script>
@endpush
