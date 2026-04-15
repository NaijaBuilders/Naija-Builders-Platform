@php($pageTitle = 'Saved Products')
@extends('layouts.app')

@section('content')
<div class="container saved-products-page" style="padding-top: 2rem; padding-bottom: 3rem;">
    <div style="background: linear-gradient(135deg, var(--secondary-color), var(--primary-color)); color: white; padding: 1.8rem; border-radius: var(--rounded-lg); margin-bottom: 1.5rem;">
        <h1 style="margin: 0 0 0.45rem; color: white;">Saved Products</h1>
        <p style="margin: 0; color: rgba(255, 255, 255, 0.9);">Organize your saved materials by category and revisit them faster.</p>
    </div>

    @if (session('save_success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('save_success') }}</div>
    @endif
    @if (session('save_error'))
        <div class="alert alert-danger" style="margin-bottom: 1rem;">{{ session('save_error') }}</div>
    @endif

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <form method="GET" action="/saved-products" class="grid-3 gap-md saved-products-page__filters" style="align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="saved-search">Search</label>
                    <input id="saved-search" type="search" name="search" value="{{ $search }}" placeholder="Search by product, category, or supplier">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="saved-category">Saved Category</label>
                    <select id="saved-category" name="saved_category">
                        <option value="0">All Categories</option>
                        @foreach ($savedCategories as $category)
                            <option value="{{ (int) $category->id }}" {{ (int) $savedCategoryId === (int) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0; display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <a href="/saved-products" class="btn btn-outline" style="text-decoration: none;">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body saved-products-page__summary" style="display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <p style="margin: 0; color: var(--neutral-600);">{{ number_format($totalRecords) }} saved product{{ $totalRecords === 1 ? '' : 's' }} found</p>
            <a href="/materials.php" class="btn btn-secondary" style="text-decoration: none;">Browse More Materials</a>
        </div>
    </div>

    <div class="grid-3 gap-lg saved-products-page__grid">
        @forelse ($savedMaterials as $savedItem)
            @php($supplierName = trim((string) ($savedItem->company ?? '')) !== '' ? (string) $savedItem->company : (string) ($savedItem->full_name ?? 'Supplier'))
            <article class="card">
                <div class="card-body">
                    <p style="margin: 0 0 0.35rem; font-size: 0.78rem; color: var(--neutral-500); text-transform: uppercase; letter-spacing: 0.04em;">{{ $savedItem->saved_category_name ?: 'General Saves' }}</p>
                    <h3 style="margin: 0 0 0.45rem; font-size: 1.15rem;">{{ $savedItem->name }}</h3>
                    <p style="margin: 0 0 0.35rem; color: var(--primary-color); font-weight: 700;">₦{{ number_format((float) $savedItem->price, 2) }} / {{ ucfirst(str_replace('_', ' ', (string) ($savedItem->price_unit ?: 'item'))) }}</p>
                    <p style="margin: 0 0 0.45rem; color: var(--neutral-600); font-size: 0.9rem;">
                        {{ $supplierName }}
                        @if ((int) ($savedItem->is_verified_badge ?? 0) === 1)
                            <span style="display: inline-block; margin-left: 0.35rem; background: var(--secondary-color); color: white; border-radius: 999px; font-size: 0.72rem; padding: 0.12rem 0.42rem;">Verified</span>
                        @endif
                    </p>
                    <p style="margin: 0 0 1rem; color: var(--neutral-500); font-size: 0.85rem;">Saved {{ \Carbon\Carbon::parse($savedItem->saved_at)->diffForHumans() }}</p>

                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="/material-detail.php?id={{ (int) $savedItem->material_id }}" class="btn btn-outline" style="text-decoration: none;">View</a>
                        <button
                            type="button"
                            class="btn btn-secondary open-save-modal"
                            data-material-id="{{ (int) $savedItem->material_id }}"
                            data-material-name="{{ $savedItem->name }}"
                        >
                            Move
                        </button>
                        <form method="POST" action="/saved-products/remove">
                            @csrf
                            <input type="hidden" name="material_id" value="{{ (int) $savedItem->material_id }}">
                            <button type="submit" class="btn btn-danger">Remove</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="card" style="grid-column: 1 / -1;">
                <div class="card-body">
                    <h3 style="margin: 0 0 0.45rem;">No saved products found</h3>
                    <p style="margin: 0; color: var(--neutral-600);">Try a different filter or save products from the materials page.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($totalPages > 1)
        <div class="card" style="margin-top: 1rem;">
            <div class="card-body" style="display: flex; justify-content: center; gap: 0.4rem; flex-wrap: wrap;">
                @php($previousPage = max(1, $currentPage - 1))
                @php($nextPage = min($totalPages, $currentPage + 1))
                <a
                    href="/saved-products?search={{ urlencode($search) }}&saved_category={{ (int) $savedCategoryId }}&page={{ $previousPage }}"
                    class="btn {{ $currentPage === 1 ? 'btn-outline' : 'btn-secondary' }}"
                    style="text-decoration: none;"
                    @if ($currentPage === 1) aria-disabled="true" @endif
                >
                    Previous
                </a>
                @for ($page = 1; $page <= $totalPages; $page++)
                    <a
                        href="/saved-products?search={{ urlencode($search) }}&saved_category={{ (int) $savedCategoryId }}&page={{ $page }}"
                        class="btn {{ $page === $currentPage ? 'btn-primary' : 'btn-outline' }}"
                        style="text-decoration: none; min-width: 44px;"
                    >
                        {{ $page }}
                    </a>
                @endfor
                <a
                    href="/saved-products?search={{ urlencode($search) }}&saved_category={{ (int) $savedCategoryId }}&page={{ $nextPage }}"
                    class="btn {{ $currentPage === $totalPages ? 'btn-outline' : 'btn-secondary' }}"
                    style="text-decoration: none;"
                    @if ($currentPage === $totalPages) aria-disabled="true" @endif
                >
                    Next
                </a>
            </div>
        </div>
    @endif
</div>

<div id="saveProductModal" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); display: none; align-items: center; justify-content: center; z-index: 1200; padding: 1rem;">
    <div style="width: 100%; max-width: 460px; background: white; border-radius: var(--rounded-lg); box-shadow: var(--shadow-xl); padding: 1.2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 0.8rem;">
            <h3 style="margin: 0; font-size: 1.2rem;">Move Saved Product</h3>
            <button id="closeSaveProductModal" type="button" style="border: 0; background: transparent; font-size: 1.3rem; cursor: pointer;">×</button>
        </div>
        <p id="saveProductLabel" style="margin: 0 0 1rem; color: var(--neutral-600);">Choose where to save this product.</p>
        <form method="POST" action="/saved-products">
            @csrf
            <input type="hidden" id="saveProductMaterialId" name="material_id" value="0">
            <div class="form-group">
                <label for="saveCategoryId">Move to Existing Category</label>
                <select id="saveCategoryId" name="category_id">
                    <option value="0">General Saves</option>
                    @foreach ($savedCategories as $category)
                        <option value="{{ (int) $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="newCategoryName">Or Create New Category</label>
                <input type="text" id="newCategoryName" name="new_category_name" maxlength="120" placeholder="Example: February Vendor Picks">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
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
            label.textContent = 'Choose where to move "' + materialName + '".';
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
