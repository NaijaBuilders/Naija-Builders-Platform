@php
    $pageTitle = 'Browse Materials';
    $legacyUserId = (int) session('legacy_user_id', 0);
@endphp
@extends('layouts.app')

@section('meta_description', 'Browse construction materials from Nigerian suppliers, compare prices, stock, ratings, and save products for procurement planning.')

@section('content')
<div class="container materials-page page-shell">
    <style>
        .materials-page {
            padding-top: var(--spacing-lg);
            padding-bottom: var(--spacing-xl);
        }

        .materials-page .section-hero-banner {
            position: relative;
            overflow: hidden;
            border-radius: var(--rounded-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-lg);
            box-shadow: 0 18px 38px rgba(11, 42, 91, 0.2);
        }

        .materials-page .section-hero-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 85% 15%, rgba(255, 255, 255, 0.22), transparent 42%),
                linear-gradient(140deg, var(--primary-color), var(--secondary-color));
            pointer-events: none;
        }

        .materials-page .section-hero-banner > * {
            position: relative;
            z-index: 1;
        }

        .materials-page .section-hero-banner h1 {
            margin: 0 0 0.65rem;
            color: var(--white);
            font-size: clamp(1.55rem, 4vw, 2.2rem);
            line-height: 1.2;
        }

        .materials-page .section-hero-banner p {
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            max-width: 70ch;
        }

        .materials-page details.filter-group > summary {
            list-style: none;
        }

        .materials-page details.filter-group > summary::-webkit-details-marker {
            display: none;
        }

        .materials-page-layout {
            display: grid;
            grid-template-columns: minmax(250px, 290px) minmax(0, 1fr);
            gap: var(--spacing-lg);
            align-items: start;
        }

        .materials-topbar {
            display: flex;
            gap: var(--spacing-md);
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .materials-topbar__controls {
            display: flex;
            gap: var(--spacing-md);
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .materials-topbar__control {
            min-width: 170px;
        }

        .materials-topbar__control label {
            display: block;
            font-size: var(--font-size-sm);
            color: var(--neutral-600);
            margin-bottom: var(--spacing-xs);
            font-weight: var(--font-weight-semibold);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .materials-page-indicator {
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
            color: var(--neutral-600);
            white-space: nowrap;
        }

        .materials-grid {
            margin-top: var(--spacing-lg);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: var(--spacing-lg);
        }

        .material-card {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .material-card__media {
            position: relative;
            aspect-ratio: 16 / 10;
            border-bottom: 1px solid var(--neutral-200);
            background: var(--neutral-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--neutral-500);
            font-size: 0.88rem;
            overflow: hidden;
        }

        .material-card__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .material-badges {
            position: absolute;
            top: 0.55rem;
            left: 0.55rem;
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .material-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.5rem;
            border-radius: var(--rounded-full);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-bold);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--white);
        }

        .material-badge--new {
            background: var(--accent-success);
        }

        .material-badge--discount {
            background: var(--accent-danger);
        }

        .material-badge--negotiable {
            background: var(--secondary-color);
        }

        .material-card__content {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            flex: 1;
        }

        .material-card__rating {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--neutral-600);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
        }

        .material-stars {
            display: inline-flex;
            gap: 0.08rem;
            color: var(--accent-warning);
            font-size: 0.92rem;
            line-height: 1;
        }

        .material-stars span {
            color: var(--neutral-300);
        }

        html[data-theme="dark"] .material-stars span {
            color: var(--neutral-400);
        }

        .material-feature-list {
            margin: 0;
            padding-left: 1rem;
            color: var(--neutral-600);
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-size: var(--font-size-sm);
        }

        .material-price-wrap {
            margin-top: 0.1rem;
            display: flex;
            align-items: baseline;
            gap: 0.45rem;
            flex-wrap: wrap;
        }

        .material-price-current {
            color: var(--accent-danger);
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
        }

        .material-price-original {
            color: var(--neutral-500);
            font-size: var(--font-size-sm);
            text-decoration: line-through;
        }

        .material-actions {
            margin-top: auto;
            display: grid;
            gap: var(--spacing-sm);
        }

        .material-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .material-secondary-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-sm);
        }

        .materials-pagination {
            margin-top: var(--spacing-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
        }

        .materials-page-chip {
            min-width: 42px;
        }

        @media (max-width: 1023px) {
            .materials-page-layout {
                grid-template-columns: 1fr;
            }

            .materials-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            }
        }

        @media (max-width: 640px) {
            .materials-topbar {
                flex-direction: column;
                align-items: stretch;
            }

            .materials-topbar__controls {
                width: 100%;
            }

            .materials-page-indicator {
                text-align: left;
            }

            .material-secondary-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="section-hero-banner">
        <h1>Construction Materials Marketplace</h1>
        <p>Compare trusted suppliers, evaluate specs quickly, and add materials straight to your basket.</p>
    </section>

    @if (session('save_success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('save_success') }}</div>
    @endif
    @if (session('save_error'))
        <div class="alert alert-danger" style="margin-bottom: 1rem;">{{ session('save_error') }}</div>
    @endif

    <div class="materials-page-layout">
        <aside class="materials-filter-column card">
            <details class="card-body materials-filter-panel" open data-collapse-on-mobile>
                <summary>Filters</summary>
                <div class="materials-filter-panel__body">
                <h2>Filters</h2>
                <form method="GET" action="/materials.php">
                    <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                    <input type="hidden" name="per_page" value="{{ (int) $perPage }}">

                    <details class="filter-group" open>
                        <summary>Search</summary>
                        <div class="filter-group__content">
                            <div class="form-group" style="margin-bottom: 0;">
                                <input id="materials-search" type="search" name="search" value="{{ $search }}" placeholder="Search materials">
                            </div>
                        </div>
                    </details>

                    <details class="filter-group" open>
                        <summary>Material Type</summary>
                        <div class="filter-group__content">
                            <div class="form-group" style="margin-bottom: 0;">
                                <select id="materials-category" name="category">
                                    <option value="">All Materials</option>
                                    @foreach ($categories as $categoryOption)
                                        <option value="{{ $categoryOption->category }}" {{ $category === (string) $categoryOption->category ? 'selected' : '' }}>{{ $categoryOption->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </details>

                    <details class="filter-group" open>
                        <summary>Location</summary>
                        <div class="filter-group__content">
                            <div class="form-group" style="margin-bottom: 0;">
                                <select id="materials-location" name="location">
                                    <option value="">All Locations</option>
                                    @foreach ($locations as $locationOption)
                                        <option value="{{ $locationOption->location }}" {{ $location === (string) $locationOption->location ? 'selected' : '' }}>{{ $locationOption->location }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </details>

                    @if ($priceUnits->count() > 0)
                        <details class="filter-group" open>
                            <summary>Price Unit</summary>
                            <div class="filter-group__content">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <select id="materials-price-unit" name="price_unit">
                                        <option value="">All Units</option>
                                        @foreach ($priceUnits as $unitOption)
                                            <option value="{{ $unitOption->price_unit }}" {{ $priceUnit === (string) $unitOption->price_unit ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', (string) $unitOption->price_unit)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </details>
                    @endif

                    @if ($savedCategories->count() > 0)
                        <details class="filter-group" open>
                            <summary>Saved Products</summary>
                            <div class="filter-group__content">
                                <div class="form-group" style="margin-bottom: 0.65rem;">
                                    <select id="materials-saved-category" name="saved_collection">
                                        <option value="0">Any Saved Category</option>
                                        @foreach ($savedCategories as $savedCategory)
                                            <option value="{{ (int) $savedCategory->id }}" {{ (int) $savedCollectionId === (int) $savedCategory->id ? 'selected' : '' }}>{{ $savedCategory->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label style="display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.9rem; color: var(--neutral-700);">
                                    <input type="checkbox" name="show_saved" value="1" {{ $showSavedOnly ? 'checked' : '' }}>
                                    Show only saved products
                                </label>
                            </div>
                        </details>
                    @endif

                    <div style="display: grid; gap: 0.5rem; margin-top: 0.8rem;">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="/materials.php" class="btn btn-outline" style="text-decoration: none;">Reset</a>
                    </div>
                </form>
                </div>
            </details>
        </aside>

        <section class="materials-results-column">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="/materials.php" class="materials-topbar">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="category" value="{{ $category }}">
                        <input type="hidden" name="location" value="{{ $location }}">
                        <input type="hidden" name="price_unit" value="{{ $priceUnit }}">
                        <input type="hidden" name="saved_collection" value="{{ (int) $savedCollectionId }}">
                        @if ($showSavedOnly)
                            <input type="hidden" name="show_saved" value="1">
                        @endif

                        <div class="materials-topbar__controls">
                            <div class="materials-topbar__control">
                                <label for="materials-sort">Sort By</label>
                                <select id="materials-sort" name="sort_by">
                                    <option value="top_sellers" {{ $sortBy === 'top_sellers' ? 'selected' : '' }}>Top Sellers</option>
                                    <option value="newest" {{ $sortBy === 'newest' ? 'selected' : '' }}>Newest Arrivals</option>
                                    <option value="price_low" {{ $sortBy === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="price_high" {{ $sortBy === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                </select>
                            </div>

                            <div class="materials-topbar__control">
                                <label for="materials-per-page">Results per page</label>
                                <select id="materials-per-page" name="per_page">
                                    @foreach ($perPageOptions as $perPageOption)
                                        <option value="{{ (int) $perPageOption }}" {{ (int) $perPage === (int) $perPageOption ? 'selected' : '' }}>{{ (int) $perPageOption }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <p class="materials-page-indicator">Page {{ $materials->currentPage() }} of {{ max(1, $materials->lastPage()) }}</p>
                    </form>

                    <p class="materials-count">
                        Showing {{ number_format((int) ($materials->firstItem() ?? 0)) }} - {{ number_format((int) ($materials->lastItem() ?? 0)) }} of {{ number_format($materials->total()) }} products
                    </p>
                </div>
            </div>

            <div class="materials-grid">
                @forelse ($materials as $material)
                    @php
                        $supplierName = trim((string) ($material->company ?? '')) !== ''
                            ? (string) $material->company
                            : \App\Support\NameFormatter::title((string) ($material->full_name ?? 'Supplier'));
                        $unit = ucfirst(str_replace('_', ' ', (string) ($material->price_unit ?? 'item')));
                        $isSaved = in_array((int) $material->id, $savedMaterialIds, true);
                        $cardImagePath = trim((string) ($material->image_path ?? ''));
                        $cardImageUrl = $cardImagePath !== '' ? '/' . ltrim($cardImagePath, '/') : '';
                        $productRatingAvgRaw = $material->product_rating_avg !== null ? (float) $material->product_rating_avg : 0.0;
                        $productRatingCount = (int) ($material->product_rating_count ?? 0);
                        $displayRating = $productRatingCount > 0 ? number_format($productRatingAvgRaw, 1) : '0.0';
                        $fullStars = (int) floor($productRatingAvgRaw);
                        $currentUserRating = $currentUserProductRatings[(int) $material->id] ?? null;

                        $createdTimestamp = strtotime((string) ($material->created_at ?? ''));
                        $isNew = $createdTimestamp !== false && $createdTimestamp >= strtotime('-14 days');

                        $isNegotiable = (int) ($material->is_negotiable ?? 0) === 1;
                        $stockQty = (int) ($material->stock_qty ?? 0);
                    @endphp

                    <article class="material-card card">
                        <div class="material-card__media">
                            <div class="material-badges">
                                @if ($isNew)
                                    <span class="material-badge material-badge--new">New</span>
                                @endif
                                @if ($isNegotiable)
                                    <span class="material-badge material-badge--negotiable">Negotiable</span>
                                @endif
                            </div>

                            @if ($cardImageUrl !== '')
                                <img src="{{ $cardImageUrl }}" alt="{{ $material->name }}">
                            @else
                                <span>Image placeholder</span>
                            @endif
                        </div>

                        <div class="material-card__content card-body">
                            <h3 class="material-card__title">{{ $material->name }}</h3>

                            <div class="material-card__rating">
                                <span class="material-stars" aria-hidden="true">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <span class="{{ $star <= $fullStars ? 'is-filled' : '' }}">★</span>
                                    @endfor
                                </span>
                                <span>{{ $displayRating }}/5 ({{ number_format($productRatingCount) }})</span>
                            </div>

                            <ul class="material-feature-list">
                                <li>
                                    <span>{{ $supplierName }}</span>
                                    @if ((int) ($material->is_verified_badge ?? 0) === 1)
                                        <span class="sr-only">Verified</span>
                                        <span aria-hidden="true" title="Verified supplier" style="display: inline-flex; align-items: center; justify-content: center; width: 14px; height: 14px; margin-left: 0.35rem; border-radius: 999px; background: #1d4ed8; color: #fff; font-size: 0.62rem; font-weight: 700; line-height: 1;">✓</span>
                                    @endif
                                </li>
                                <li>{{ $material->location ?: 'Nigeria' }}</li>
                                <li>{{ $stockQty > 0 ? number_format($stockQty) . ' in stock' : 'Out of stock' }}</li>
                            </ul>

                            <div class="material-price-wrap">
                                <span class="material-price-current">{{ $formatMoney((float) $material->price) }}</span>
                                <span style="font-size: 0.8rem; color: var(--neutral-600);">/ {{ $unit }}</span>
                            </div>

                            @if ($currentUserRating !== null)
                                <p style="margin: 0; font-size: 0.8rem; color: var(--neutral-600);">Your rating: {{ (int) $currentUserRating }}/5</p>
                            @endif

                            <div class="material-actions">
                                @if ($legacyUserId > 0 && $stockQty > 0)
                                    <form method="POST" action="/cart/add.php">
                                        @csrf
                                        <input type="hidden" name="material_id" value="{{ (int) $material->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-success">Add to basket</button>
                                    </form>
                                @elseif ($stockQty <= 0)
                                    <button type="button" class="btn btn-outline" disabled>Out of stock</button>
                                @else
                                    <a href="/login.php" class="btn btn-success" style="text-decoration: none;">Login to add to basket</a>
                                @endif

                                <div class="material-secondary-actions">
                                    <a href="/material-detail.php?id={{ (int) $material->id }}" class="btn btn-outline" style="text-decoration: none;">View</a>

                                    @if ($legacyUserId > 0)
                                        @if ($isSaved)
                                            <form method="POST" action="/saved-products/remove">
                                                @csrf
                                                <input type="hidden" name="material_id" value="{{ (int) $material->id }}">
                                                <button type="submit" class="btn btn-outline">Saved</button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-secondary open-save-modal" data-material-id="{{ (int) $material->id }}" data-material-name="{{ $material->name }}">Save</button>
                                        @endif
                                    @else
                                        <a href="/login.php" class="btn btn-outline" style="text-decoration: none;">Save</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="card" style="grid-column: 1 / -1;">
                        <div class="card-body">
                            <h3 style="margin: 0 0 0.45rem;">No materials found</h3>
                            <p style="margin: 0; color: var(--neutral-600);">Try changing your filters to broaden results.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($materials->lastPage() > 1)
                @php
                    $startPage = max(1, $materials->currentPage() - 2);
                    $endPage = min($materials->lastPage(), $materials->currentPage() + 2);
                @endphp

                <div class="card" style="margin-top: 1rem;">
                    <div class="card-body">
                        <nav class="materials-pagination" aria-label="Material listing pagination">
                            <a href="{{ $materials->onFirstPage() ? '#' : $materials->previousPageUrl() }}" class="btn {{ $materials->onFirstPage() ? 'btn-outline' : 'btn-secondary' }}" @if ($materials->onFirstPage()) aria-disabled="true" @endif>Previous</a>

                            @for ($page = $startPage; $page <= $endPage; $page++)
                                <a href="{{ $materials->url($page) }}" class="btn materials-page-chip {{ $page === $materials->currentPage() ? 'btn-primary' : 'btn-outline' }}">{{ $page }}</a>
                            @endfor

                            <a href="{{ $materials->hasMorePages() ? $materials->nextPageUrl() : '#' }}" class="btn {{ $materials->hasMorePages() ? 'btn-secondary' : 'btn-outline' }}" @if (!$materials->hasMorePages()) aria-disabled="true" @endif>Next</a>
                        </nav>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>

<div id="saveProductModal" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); display: none; align-items: center; justify-content: center; z-index: 1200; padding: 1rem;">
    <div style="width: 100%; max-width: 460px; background: white; border-radius: var(--rounded-lg); box-shadow: var(--shadow-xl); padding: 1.2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 0.8rem;">
            <h3 style="margin: 0; font-size: 1.2rem;">Save Product</h3>
            <button id="closeSaveProductModal" type="button" aria-label="Close save product dialog" style="border: 0; background: transparent; font-size: 1.3rem; cursor: pointer;">×</button>
        </div>
        <p id="saveProductLabel" style="margin: 0 0 1rem; color: var(--neutral-600);">Choose where to save this product.</p>
        <form method="POST" action="/saved-products">
            @csrf
            <input type="hidden" id="saveProductMaterialId" name="material_id" value="0">
            <div class="form-group">
                <label for="saveCategoryId">Save to Existing Category</label>
                <select id="saveCategoryId" name="category_id">
                    <option value="0">General Saves</option>
                    @foreach ($savedCategories as $savedCategory)
                        <option value="{{ (int) $savedCategory->id }}">{{ $savedCategory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="newCategoryName">Or Create New Category</label>
                <input type="text" id="newCategoryName" name="new_category_name" maxlength="120" placeholder="Example: Monthly Procurement Plan">
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
    const topbarForm = document.querySelector('.materials-topbar');

    if (topbarForm) {
        const autoSubmitSelectors = ['#materials-sort', '#materials-per-page'];
        autoSubmitSelectors.forEach(function (selector) {
            const field = topbarForm.querySelector(selector);
            if (field) {
                field.addEventListener('change', function () {
                    topbarForm.submit();
                });
            }
        });
    }

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
