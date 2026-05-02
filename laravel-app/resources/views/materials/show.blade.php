@php($pageTitle = 'Product Details')
@extends('layouts.app')

@section('content')
<div class="container" style="margin: 2rem auto;">
    <style>
        .product-page-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(300px, 1fr);
            gap: 1.5rem;
            align-items: start;
        }

        .product-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: sticky;
            top: 1rem;
        }

        .detail-card {
            border: 1px solid var(--neutral-200);
            border-radius: var(--rounded-lg);
            background: var(--white);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .detail-card-body {
            padding: 1rem 1rem 1.05rem;
        }

        .product-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .product-meta-item {
            background: var(--neutral-50);
            border: 1px solid var(--neutral-200);
            border-radius: var(--rounded-md);
            padding: 0.65rem 0.7rem;
        }

        .product-meta-item p {
            margin: 0;
        }

        .section-title {
            margin: 0 0 0.75rem;
            font-size: 1.02rem;
        }

        .details-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }

        .details-list li {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.65rem;
            border-bottom: 1px dashed var(--neutral-200);
            padding-bottom: 0.45rem;
        }

        .details-list li:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .details-label {
            color: var(--neutral-600);
            font-weight: 600;
            min-width: 120px;
        }

        .details-value {
            color: var(--neutral-800);
            text-align: right;
        }

        .details-value.description {
            text-align: left;
            width: 100%;
        }

        .reviews-shell {
            margin-top: 2rem;
            border-top: 1px solid var(--neutral-200);
            padding-top: 1.35rem;
        }

        .reviews-headline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.8rem;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .review-item {
            border: 1px solid var(--neutral-200);
            border-radius: var(--rounded-md);
            padding: 0.8rem;
            background: var(--white);
        }

        .review-item h5 {
            margin: 0;
            font-size: 0.95rem;
        }

        .review-item p {
            margin: 0.4rem 0 0;
            color: var(--neutral-700);
            line-height: 1.55;
        }

        .review-empty {
            border: 1px dashed var(--neutral-300);
            border-radius: var(--rounded-md);
            padding: 1rem;
            color: var(--neutral-600);
            background: var(--neutral-50);
        }

        .rating-summary {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.86rem;
            color: var(--neutral-700);
            font-weight: 600;
        }

        .stars {
            display: inline-flex;
            gap: 0.1rem;
            color: #f59e0b;
            line-height: 1;
        }

        .star-empty {
            color: var(--neutral-300);
        }

        .review-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.1rem;
        }

        .review-time {
            margin: 0.3rem 0 0;
            color: var(--neutral-500);
            font-size: 0.79rem;
        }

        .review-form {
            margin-top: 0.8rem;
            border-top: 1px solid var(--neutral-200);
            padding-top: 0.75rem;
        }

        .review-form p {
            margin: 0 0 0.45rem;
            color: var(--neutral-600);
            font-size: 0.84rem;
            font-weight: 600;
        }

        .review-form textarea {
            width: 100%;
            min-height: 84px;
            border: 1px solid var(--neutral-300);
            border-radius: var(--rounded-md);
            padding: 0.55rem 0.6rem;
            resize: vertical;
            font: inherit;
        }

        .star-picker {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 0.16rem;
            margin-bottom: 0.55rem;
        }

        .star-picker input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .star-picker label {
            color: var(--neutral-300);
            font-size: 1.2rem;
            line-height: 1;
            cursor: pointer;
        }

        .star-picker label:hover,
        .star-picker label:hover ~ label,
        .star-picker input:checked ~ label {
            color: #f59e0b;
        }

        html[data-theme="dark"] .stars,
        html[data-theme="dark"] .star-picker label:hover,
        html[data-theme="dark"] .star-picker label:hover ~ label,
        html[data-theme="dark"] .star-picker input:checked ~ label {
            color: #fbbf24;
        }

        .review-submit {
            margin-top: 0.55rem;
        }

        .tabs-shell {
            margin-top: 2rem;
            border-top: 1px solid var(--neutral-200);
            padding-top: 1.2rem;
        }

        .tabs-nav {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            border-bottom: 1px solid var(--neutral-200);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            overflow-x: auto;
        }

        .tab-button {
            border: 1px solid var(--neutral-200);
            border-radius: 999px;
            background: var(--neutral-50);
            color: var(--neutral-700);
            font-size: 0.88rem;
            font-weight: 700;
            padding: 0.45rem 0.85rem;
            cursor: pointer;
            white-space: nowrap;
        }

        .tab-button.is-active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white);
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.is-active {
            display: block;
            animation: tabFade 180ms ease-out;
        }

        .tab-copy {
            margin-bottom: 0.75rem;
            color: var(--neutral-600);
            font-size: 0.92rem;
        }

        .qa-list {
            display: grid;
            gap: 0.75rem;
        }

        .qa-item {
            border: 1px solid var(--neutral-200);
            border-radius: var(--rounded-md);
            padding: 0.85rem;
            background: var(--white);
        }

        .qa-item h5 {
            margin: 0 0 0.4rem;
            font-size: 0.95rem;
        }

        .qa-item p {
            margin: 0;
            color: var(--neutral-700);
        }

        .qa-meta {
            margin-top: 0.45rem;
            color: var(--neutral-500);
            font-size: 0.8rem;
        }

        .detail-placeholder-shell {
            border: 1px solid var(--neutral-200);
            border-radius: var(--rounded-lg);
            background: var(--white);
            box-shadow: var(--shadow-sm);
            padding: 1.35rem;
        }

        .detail-placeholder-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
            gap: 1.5rem;
            align-items: start;
        }

        .placeholder-gallery {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .placeholder-thumbs {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.55rem;
        }

        .placeholder-content {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(250px, 0.95fr);
            gap: 1.25rem;
            align-items: start;
            width: 100%;
        }

        .placeholder-content-main {
            display: flex;
            flex-direction: column;
            gap: 0.95rem;
            min-width: 0;
        }

        .placeholder-content-side {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            min-width: 0;
        }

        .placeholder-stars {
            display: inline-flex;
            gap: 0.3rem;
            margin-top: 0.1rem;
        }

        .placeholder-price {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.7rem;
            margin-top: 0;
        }

        .placeholder-qty {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-top: 0;
        }

        .placeholder-btn-sm {
            width: 2.15rem;
            height: 2.15rem;
            border: 1px solid var(--neutral-300);
            border-radius: var(--rounded-sm);
            background: var(--neutral-100);
            color: var(--neutral-700);
            font-weight: 700;
            cursor: pointer;
            transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
        }

        .placeholder-btn-sm:hover {
            border-color: var(--neutral-400);
            background: var(--neutral-200);
            transform: translateY(-1px);
        }

        .placeholder-btn-sm:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        .placeholder-delivery {
            display: grid;
            gap: 0.55rem;
            margin-top: 0.15rem;
            width: 100%;
        }

        .placeholder-delivery-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            width: 100%;
            padding: 0.5rem 0.55rem;
            border-radius: var(--rounded-sm);
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .placeholder-delivery-item:hover {
            background: var(--neutral-100);
        }

        .placeholder-delivery-item input {
            margin: 0;
        }

        .placeholder-delivery-item input[type="radio"] {
            width: 0.74rem;
            height: 0.74rem;
            min-width: 0.74rem;
            border-width: 1.3px;
        }

        .placeholder-delivery-item input[type="radio"]::before {
            width: 0.34rem;
            height: 0.34rem;
        }

        .placeholder-actions {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.7rem;
            margin-top: 0.35rem;
            width: 100%;
        }

        .placeholder-btn-lg {
            height: 2.8rem;
            border: 1px solid var(--neutral-300);
            border-radius: var(--rounded-md);
            background: var(--neutral-100);
            color: var(--neutral-800);
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .placeholder-btn-lg:hover {
            transform: translateY(-1px);
            border-color: var(--neutral-400);
            box-shadow: var(--shadow-sm);
        }

        .placeholder-btn-lg:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        .placeholder-btn-lg.is-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white);
        }

        .placeholder-btn-lg.is-primary:hover {
            filter: brightness(1.05);
        }

        .material-brand-tag {
            display: inline-flex;
            align-self: flex-start;
            padding: 0.24rem 0.62rem;
            border-radius: 999px;
            background: var(--neutral-100);
            border: 1px solid var(--neutral-200);
            color: var(--neutral-700);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .material-title {
            margin: 0;
            font-size: clamp(1.45rem, 2.2vw, 2.15rem);
            line-height: 1.2;
            color: var(--neutral-900);
        }

        .material-rating-row {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: var(--neutral-700);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .material-price-main {
            margin: 0;
            color: var(--primary-color);
            font-size: clamp(1.55rem, 2.8vw, 2.2rem);
            font-weight: 800;
            line-height: 1.15;
        }

        .material-old-price {
            color: var(--neutral-500);
            text-decoration: line-through;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .material-qty-input {
            width: 3.6rem;
            height: 2.15rem;
            border: 1px solid var(--neutral-300);
            border-radius: var(--rounded-sm);
            text-align: center;
            font-weight: 700;
            color: var(--neutral-800);
            background: var(--white);
        }

        .delivery-option-label {
            color: var(--neutral-700);
            font-size: 0.9rem;
        }

        .material-description-wrap {
            width: 100%;
            margin-top: 0;
        }

        .material-description {
            margin: 0;
            color: var(--neutral-700);
            line-height: 1.65;
        }

        .material-description-more {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.45rem;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .material-description-more:hover {
            text-decoration: underline;
        }

        .material-feature-list.material-feature-list--inline {
            margin-top: 0.55rem;
        }

        .material-feature-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 0.45rem;
            width: 100%;
        }

        .material-feature-list li {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            color: var(--neutral-700);
            font-size: 0.92rem;
        }

        .ph {
            border-radius: 10px;
            background: var(--neutral-200);
        }

        .ph-main-image {
            height: 18rem;
            border-radius: 14px;
        }

        .ph-thumb {
            height: 3.1rem;
            border-radius: 8px;
        }

        .ph-brand {
            width: 6rem;
            height: 0.68rem;
        }

        .ph-title-lg {
            width: 84%;
            height: 1.4rem;
        }

        .ph-title-md {
            width: 66%;
            height: 1rem;
        }

        .ph-star {
            width: 0.8rem;
            height: 0.8rem;
            border-radius: 999px;
        }

        .ph-discount {
            width: 3.4rem;
            height: 1.22rem;
            border-radius: 999px;
        }

        .ph-price {
            width: 9.5rem;
            height: 1.58rem;
        }

        .ph-old-price {
            width: 4.5rem;
            height: 0.85rem;
            position: relative;
        }

        .ph-old-price::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: var(--neutral-500);
        }

        .ph-qty-input {
            width: 3.4rem;
            height: 2.15rem;
            border-radius: var(--rounded-sm);
        }

        .ph-radio {
            width: 0.9rem;
            height: 0.9rem;
            border-radius: 999px;
        }

        .ph-delivery {
            width: 8.8rem;
            height: 0.7rem;
        }

        @keyframes tabFade {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1024px) {
            .product-page-grid {
                grid-template-columns: 1fr;
            }

            .product-sidebar {
                position: static;
            }
        }

        @media (max-width: 680px) {
            .product-meta-grid {
                grid-template-columns: 1fr;
            }

            .reviews-grid {
                grid-template-columns: 1fr;
            }

            .details-list li {
                flex-direction: column;
                align-items: flex-start;
            }

            .details-value {
                text-align: left;
            }

            .detail-placeholder-grid {
                grid-template-columns: 1fr;
            }

            .placeholder-content {
                grid-template-columns: 1fr;
            }

            .placeholder-thumbs {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .placeholder-price {
                flex-wrap: wrap;
            }

            .ph-main-image {
                height: 14rem;
            }
        }
    </style>
    @if (session('save_success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('save_success') }}</div>
    @endif
    @if (session('save_error'))
        <div class="alert alert-danger" style="margin-bottom: 1rem;">{{ session('save_error') }}</div>
    @endif

    <div style="margin-bottom: 2rem; color: var(--neutral-600);">
        <a href="/index.php" style="color: var(--primary-color);">Home</a> /
        <a href="/materials.php" style="color: var(--primary-color);">Materials</a> /
        <span>{{ $material->name }}</span>
    </div>

    <section class="detail-placeholder-shell" aria-label="Material detail">
        <div class="detail-placeholder-grid">
            <div class="placeholder-gallery">
                <div id="gallery-stage" style="position: relative; background: linear-gradient(135deg, rgba(31, 79, 163, 0.12), rgba(11, 42, 91, 0.08)); border-radius: 14px; height: 18rem; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 0.7rem;">
                    @if ($mainImagePath !== '')
                        @if ($images->count() > 1)
                            <button type="button" id="gallery-prev" aria-label="Previous image" style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; border: 0; border-radius: 999px; background: rgba(11, 42, 91, 0.76); color: #fff; font-size: 1rem; line-height: 1; cursor: pointer; z-index: 2;">&#10094;</button>
                        @endif
                        <img id="gallery-main-image" src="/{{ $mainImagePath }}" alt="{{ $material->name }}" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto;">
                        @if ($images->count() > 1)
                            <button type="button" id="gallery-next" aria-label="Next image" style="position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; border: 0; border-radius: 999px; background: rgba(11, 42, 91, 0.76); color: #fff; font-size: 1rem; line-height: 1; cursor: pointer; z-index: 2;">&#10095;</button>
                        @endif
                    @else
                        <div style="color: var(--neutral-600); font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em;">No Product Image</div>
                    @endif
                </div>

                @if ($images->count() > 0)
                    <div id="gallery-thumbnails" class="placeholder-thumbs">
                        @foreach ($images as $index => $image)
                            <button type="button" data-gallery-thumb data-index="{{ $index }}" data-src="/{{ $image->image_path }}" data-alt="{{ $material->name }} image {{ $index + 1 }}" aria-label="View image {{ $index + 1 }}" style="padding: 0; border-radius: 8px; border: 2px solid {{ $index === 0 ? 'var(--primary-color)' : 'transparent' }}; overflow: hidden; width: 100%; height: 3.1rem; background: transparent; cursor: pointer;">
                                <img src="/{{ $image->image_path }}" alt="{{ $material->name }} thumbnail {{ $index + 1 }}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="placeholder-thumbs">
                        <span class="ph ph-thumb"></span>
                        <span class="ph ph-thumb"></span>
                        <span class="ph ph-thumb"></span>
                        <span class="ph ph-thumb"></span>
                    </div>
                @endif
            </div>

            <div class="placeholder-content">
                <div class="placeholder-content-main">
                    <span class="material-brand-tag">{{ $material->category ?: 'General' }}</span>
                    <h1 class="material-title">{{ $material->name }}</h1>
                    @php($productRoundedTop = (int) round((float) ($productRatingAvg ?? 0)))
                    <div class="material-rating-row">
                        <span class="stars" aria-label="Product rating">
                            @for ($star = 1; $star <= 5; $star++)
                                <span class="{{ $star <= $productRoundedTop ? '' : 'star-empty' }}">&#9733;</span>
                            @endfor
                        </span>
                        <span>{{ $productRatingAvg !== null ? number_format((float) $productRatingAvg, 1) : '0.0' }} ({{ number_format((int) ($productRatingCount ?? 0)) }})</span>
                    </div>

                    <?php
                        $descriptionText = (string) ($material->description ?: 'No additional product description provided.');
                        $descriptionLimit = 220;
                        $descriptionIsLong = mb_strlen($descriptionText) > $descriptionLimit;
                        $descriptionPreview = $descriptionIsLong
                            ? rtrim(mb_substr($descriptionText, 0, $descriptionLimit)) . '...'
                            : $descriptionText;
                    ?>
                    <div class="material-description-wrap">
                        <p class="material-description">{!! nl2br(e($descriptionPreview)) !!}</p>
                        @if ($descriptionIsLong)
                            <a class="material-description-more" href="#tab-panel-specifications" data-tab-jump="specifications" aria-label="Show full description in specifications">Show more</a>
                        @endif
                    </div>
                    <ul class="material-feature-list material-feature-list--inline">
                        <li><span class="ph ph-radio"></span><span>Price Unit: {{ ucfirst(str_replace('_', ' ', (string) ($material->price_unit ?: 'item'))) }}</span></li>
                        <li><span class="ph ph-radio"></span><span>Stock Quantity: {{ number_format($stockQuantity) }}</span></li>
                        <li><span class="ph ph-radio"></span><span>Location: {{ $material->location ?: 'Nigeria' }}</span></li>
                    </ul>
                </div>

                <div class="placeholder-content-side">
                    <div class="placeholder-price">
                        <span class="material-brand-tag" style="background: var(--accent-success); color: var(--white); border-color: transparent;">-10%</span>
                        <p class="material-price-main">{{ $formatMoney((float) $material->price) }}</p>
                        <span class="material-old-price">{{ $formatMoney((float) $material->price * 1.1) }}</span>
                    </div>

                    <form method="POST" action="/cart/add.php" style="margin: 0; width: 100%;">
                        @csrf
                        <input type="hidden" name="material_id" value="{{ (int) $material->id }}">
                        <div class="placeholder-qty">
                            <button type="button" class="placeholder-btn-sm" aria-label="Decrease quantity" data-qty-action="decrease">-</button>
                            <input type="number" min="1" value="1" name="quantity" class="material-qty-input" data-qty-input>
                            <button type="button" class="placeholder-btn-sm" aria-label="Increase quantity" data-qty-action="increase">+</button>
                        </div>

                        <div class="placeholder-delivery" style="margin-top: 0.6rem;">
                            <label class="placeholder-delivery-item" for="delivery-standard">
                                <input id="delivery-standard" type="radio" name="delivery_option" value="standard" checked>
                                <span class="delivery-option-label">Standard Delivery</span>
                            </label>
                            <label class="placeholder-delivery-item" for="delivery-express">
                                <input id="delivery-express" type="radio" name="delivery_option" value="express">
                                <span class="delivery-option-label">Express Delivery</span>
                            </label>
                            <label class="placeholder-delivery-item" for="delivery-pickup">
                                <input id="delivery-pickup" type="radio" name="delivery_option" value="pickup">
                                <span class="delivery-option-label">Store Pickup</span>
                            </label>
                        </div>

                        <div class="placeholder-actions">
                            <button type="submit" class="placeholder-btn-lg is-primary" aria-label="Add to Cart">Add to Cart</button>
                            @if (session()->has('legacy_user_id'))
                                <button type="button" class="placeholder-btn-lg" id="openSaveProductModal" aria-label="Save Product">{{ $isSaved ? 'Update Saved Category' : 'Save Product' }}</button>
                            @else
                                <a href="/login.php" class="placeholder-btn-lg" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">Save Product</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="tabs-shell" aria-label="Product tabs">
        <div class="tabs-nav" role="tablist" aria-label="Product information tabs">
            <button type="button" class="tab-button is-active" role="tab" id="tab-btn-reviews" aria-controls="tab-panel-reviews" aria-selected="true" data-tab-target="reviews">Reviews</button>
            <button type="button" class="tab-button" role="tab" id="tab-btn-qas" aria-controls="tab-panel-qas" aria-selected="false" data-tab-target="qas">Q&As</button>
            <button type="button" class="tab-button" role="tab" id="tab-btn-specifications" aria-controls="tab-panel-specifications" aria-selected="false" data-tab-target="specifications">Specifications</button>
        </div>

        <div class="tab-panel is-active" role="tabpanel" id="tab-panel-reviews" aria-labelledby="tab-btn-reviews" data-tab-panel="reviews">
            <p class="tab-copy">See what buyers are saying about this product and supplier.</p>
            <div class="reviews-grid">
                <div class="detail-card">
                    <div class="detail-card-body">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.6rem; margin-bottom: 0.75rem;">
                            <h4 class="section-title" style="margin: 0;">Product Reviews</h4>
                            @php($productRounded = (int) round((float) ($productRatingAvg ?? 0)))
                            <span class="rating-summary">
                                <span class="stars" aria-label="Product rating average">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <span class="{{ $star <= $productRounded ? '' : 'star-empty' }}">&#9733;</span>
                                    @endfor
                                </span>
                                <span>{{ $productRatingAvg !== null ? number_format((float) $productRatingAvg, 1) : '0.0' }} ({{ number_format((int) ($productRatingCount ?? 0)) }})</span>
                            </span>
                        </div>

                        @if ($productReviews->count() === 0)
                            <div class="review-empty">No product reviews yet for {{ $material->name }}. Be the first buyer to share your experience.</div>
                        @else
                            @foreach ($productReviews as $review)
                                <article class="review-item" style="margin-bottom: 0.6rem;">
                                    <div class="review-head">
                                        <h5>{{ trim((string) ($review->reviewer_company ?? '')) !== '' ? $review->reviewer_company : ($review->reviewer_full_name ?: 'Verified Buyer') }}</h5>
                                        <span class="stars" aria-label="Product review stars">
                                            @for ($star = 1; $star <= 5; $star++)
                                                <span class="{{ $star <= (int) $review->rating ? '' : 'star-empty' }}">&#9733;</span>
                                            @endfor
                                        </span>
                                    </div>
                                    @if (trim((string) ($review->review_text ?? '')) !== '')
                                        <p>{{ $review->review_text }}</p>
                                    @endif
                                    <p class="review-time">{{ \Illuminate\Support\Carbon::parse($review->created_at)->format('M d, Y') }}</p>
                                </article>
                            @endforeach
                        @endif

                        @if (session()->has('legacy_user_id'))
                            <form method="POST" action="/materials/review-product.php" class="review-form">
                                @csrf
                                <input type="hidden" name="material_id" value="{{ (int) $material->id }}">
                                <p>Rate this product</p>
                                <div class="star-picker" aria-label="Rate product">
                                    @for ($rate = 5; $rate >= 1; $rate--)
                                        @php($productInputId = 'product-rate-' . $rate)
                                        <input id="{{ $productInputId }}" type="radio" name="rating" value="{{ $rate }}" {{ (int) ($currentUserProductRating ?? 0) === $rate ? 'checked' : '' }} required>
                                        <label for="{{ $productInputId }}" title="{{ $rate }} star{{ $rate > 1 ? 's' : '' }}">&#9733;</label>
                                    @endfor
                                </div>
                                <textarea name="review_text" maxlength="1200" placeholder="Optional: describe your experience with this product"></textarea>
                                <button type="submit" class="btn btn-primary btn-block review-submit">Submit Product Review</button>
                            </form>
                        @else
                            <p style="margin: 0.7rem 0 0; color: var(--neutral-600);">Please <a href="/login.php" style="color: var(--primary-color);">log in</a> to rate this product.</p>
                        @endif
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-body">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.6rem; margin-bottom: 0.75rem;">
                            <h4 class="section-title" style="margin: 0;">Supplier Reviews</h4>
                            @php($supplierRoundedReview = (int) round((float) ($supplierRatingAvg ?? 0)))
                            <span class="rating-summary">
                                <span class="stars" aria-label="Supplier rating average">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <span class="{{ $star <= $supplierRoundedReview ? '' : 'star-empty' }}">&#9733;</span>
                                    @endfor
                                </span>
                                <span>{{ $supplierRatingAvg !== null ? number_format((float) $supplierRatingAvg, 1) : '0.0' }} ({{ number_format((int) ($supplierRatingCount ?? 0)) }})</span>
                            </span>
                        </div>

                        @if ($supplierReviews->count() === 0)
                            <div class="review-empty">No supplier reviews yet for {{ $supplierName }}. Verified buyer feedback will appear here.</div>
                        @else
                            @foreach ($supplierReviews as $review)
                                <article class="review-item" style="margin-bottom: 0.6rem;">
                                    <div class="review-head">
                                        <h5>{{ trim((string) ($review->reviewer_company ?? '')) !== '' ? $review->reviewer_company : ($review->reviewer_full_name ?: 'Verified Buyer') }}</h5>
                                        <span class="stars" aria-label="Supplier review stars">
                                            @for ($star = 1; $star <= 5; $star++)
                                                <span class="{{ $star <= (int) $review->rating ? '' : 'star-empty' }}">&#9733;</span>
                                            @endfor
                                        </span>
                                    </div>
                                    @if (trim((string) ($review->review_text ?? '')) !== '')
                                        <p>{{ $review->review_text }}</p>
                                    @endif
                                    <p class="review-time">{{ \Illuminate\Support\Carbon::parse($review->created_at)->format('M d, Y') }}</p>
                                </article>
                            @endforeach
                        @endif

                        @if (session()->has('legacy_user_id'))
                            <form method="POST" action="/materials/review-supplier.php" class="review-form">
                                @csrf
                                <input type="hidden" name="supplier_id" value="{{ (int) $material->supplier_id }}">
                                <p>Rate this supplier</p>
                                <div class="star-picker" aria-label="Rate supplier">
                                    @for ($rate = 5; $rate >= 1; $rate--)
                                        @php($supplierInputId = 'supplier-rate-' . $rate)
                                        <input id="{{ $supplierInputId }}" type="radio" name="rating" value="{{ $rate }}" {{ (int) ($currentUserSupplierRating ?? 0) === $rate ? 'checked' : '' }} required>
                                        <label for="{{ $supplierInputId }}" title="{{ $rate }} star{{ $rate > 1 ? 's' : '' }}">&#9733;</label>
                                    @endfor
                                </div>
                                <textarea name="review_text" maxlength="1200" placeholder="Optional: describe your experience with this supplier"></textarea>
                                <button type="submit" class="btn btn-primary btn-block review-submit">Submit Supplier Review</button>
                            </form>
                        @else
                            <p style="margin: 0.7rem 0 0; color: var(--neutral-600);">Please <a href="/login.php" style="color: var(--primary-color);">log in</a> to rate this supplier.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-panel" role="tabpanel" id="tab-panel-qas" aria-labelledby="tab-btn-qas" data-tab-panel="qas">
            <p class="tab-copy">Questions and answers help buyers make faster decisions before contacting the seller.</p>
            <div class="qa-list">
                <article class="qa-item">
                    <h5>Are there delivery options for this product?</h5>
                    <p>Please use the Contact Seller button above to ask delivery-related questions for your location.</p>
                    <p class="qa-meta">General guidance</p>
                </article>
                <article class="qa-item">
                    <h5>Can I negotiate the price?</h5>
                    <p>{{ (int) ($material->is_negotiable ?? 0) === 1 ? 'Yes. This listing is marked negotiable.' : 'This listing is currently marked as fixed price.' }}</p>
                    <p class="qa-meta">Listing details</p>
                </article>
                <article class="qa-item">
                    <h5>How do I ask a specific question?</h5>
                    <p>Open a direct chat with the seller from this page to ask product-specific questions and get confirmation quickly.</p>
                    <p class="qa-meta">NaijaBuilders support</p>
                </article>
            </div>
        </div>

        <div class="tab-panel" role="tabpanel" id="tab-panel-specifications" aria-labelledby="tab-btn-specifications" data-tab-panel="specifications">
            <p class="tab-copy">Technical and listing specifications for this material.</p>
            <div class="detail-card">
                <div class="detail-card-body">
                    <ul class="details-list">
                        <li><span class="details-label">Product Name</span><span class="details-value">{{ $material->name }}</span></li>
                        <li><span class="details-label">Category</span><span class="details-value">{{ $material->category ?: 'General' }}</span></li>
                        <li><span class="details-label">Price</span><span class="details-value">{{ $formatMoney((float) $material->price) }}</span></li>
                        <li><span class="details-label">Price Unit</span><span class="details-value">{{ ucfirst(str_replace('_', ' ', (string) ($material->price_unit ?: 'item'))) }}</span></li>
                        <li><span class="details-label">Pricing Type</span><span class="details-value">{{ (int) ($material->is_negotiable ?? 0) === 1 ? 'Negotiable' : 'Fixed Price' }}</span></li>
                        <li><span class="details-label">Stock Quantity</span><span class="details-value">{{ number_format($stockQuantity) }}</span></li>
                        <li><span class="details-label">Supplier</span><span class="details-value">{{ $supplierName }}</span></li>
                        <li><span class="details-label">Location</span><span class="details-value">{{ $material->location ?: 'Nigeria' }}</span></li>
                        <li>
                            <span class="details-label">Description</span>
                            <span class="details-value description">{!! nl2br(e($material->description ?: 'No additional product description provided.')) !!}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>

@if (session()->has('legacy_user_id'))
    <div id="saveProductModal" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); display: none; align-items: center; justify-content: center; z-index: 1200; padding: 1rem;">
        <div style="width: 100%; max-width: 460px; background: white; border-radius: var(--rounded-lg); box-shadow: var(--shadow-xl); padding: 1.2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 0.8rem;">
                <h3 style="margin: 0; font-size: 1.2rem;">Save Product</h3>
                <button id="closeSaveProductModal" type="button" style="border: 0; background: transparent; font-size: 1.3rem; cursor: pointer;">×</button>
            </div>
            <p style="margin: 0 0 1rem; color: var(--neutral-600);">Choose where to save "{{ $material->name }}".</p>
            <form method="POST" action="/saved-products.php">
                @csrf
                <input type="hidden" name="material_id" value="{{ (int) $material->id }}">
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
                    <input type="text" id="newCategoryName" name="new_category_name" maxlength="120" placeholder="Example: Tiles Comparison">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Save Product</button>
            </form>
        </div>
    </div>
@endif
@endsection

@push('scripts')
@if ($images->count() > 0)
<script>
(function () {
    const stage = document.getElementById('gallery-stage');
    const mainImage = document.getElementById('gallery-main-image');
    const thumbnails = Array.from(document.querySelectorAll('[data-gallery-thumb]'));
    const prevButton = document.getElementById('gallery-prev');
    const nextButton = document.getElementById('gallery-next');
    if (!stage || !mainImage || thumbnails.length === 0) return;
    let currentIndex = 0;
    const setActive = function (targetIndex) {
        const total = thumbnails.length;
        currentIndex = (targetIndex + total) % total;
        const activeThumb = thumbnails[currentIndex];
        mainImage.src = activeThumb.getAttribute('data-src') || mainImage.src;
        mainImage.alt = activeThumb.getAttribute('data-alt') || mainImage.alt;
        thumbnails.forEach((thumb, index) => { thumb.style.borderColor = index === currentIndex ? 'var(--primary-color)' : 'transparent'; });
    };
    thumbnails.forEach((thumb, index) => thumb.addEventListener('click', () => setActive(index)));
    if (prevButton) prevButton.addEventListener('click', () => setActive(currentIndex - 1));
    if (nextButton) nextButton.addEventListener('click', () => setActive(currentIndex + 1));
})();
</script>
@endif
<script>
(function () {
    const qtyInput = document.querySelector('[data-qty-input]');
    const qtyButtons = Array.from(document.querySelectorAll('[data-qty-action]'));

    if (qtyInput && qtyButtons.length > 0) {
        qtyButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const action = button.getAttribute('data-qty-action');
                const currentValue = parseInt(qtyInput.value, 10) || 1;
                const nextValue = action === 'decrease' ? Math.max(1, currentValue - 1) : currentValue + 1;
                qtyInput.value = String(nextValue);
            });
        });
    }

    const tabButtons = Array.from(document.querySelectorAll('[data-tab-target]'));
    const tabPanels = Array.from(document.querySelectorAll('[data-tab-panel]'));

    if (tabButtons.length === 0 || tabPanels.length === 0) {
        return;
    }

    const activateTab = function (target) {
        tabButtons.forEach(function (button) {
            const isActive = button.getAttribute('data-tab-target') === target;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        tabPanels.forEach(function (panel) {
            const isActive = panel.getAttribute('data-tab-panel') === target;
            panel.classList.toggle('is-active', isActive);
        });
    };

    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activateTab(button.getAttribute('data-tab-target'));
        });
    });

    const tabJumpLinks = Array.from(document.querySelectorAll('[data-tab-jump]'));
    tabJumpLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const target = link.getAttribute('data-tab-jump');
            if (!target) {
                return;
            }
            activateTab(target);
            const panel = tabPanels.find(function (item) {
                return item.getAttribute('data-tab-panel') === target;
            });
            if (panel) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
})();
</script>
@if (session()->has('legacy_user_id'))
<script>
(function () {
    const modal = document.getElementById('saveProductModal');
    const openButton = document.getElementById('openSaveProductModal');
    const closeButton = document.getElementById('closeSaveProductModal');

    if (!modal || !openButton || !closeButton) {
        return;
    }

    openButton.addEventListener('click', function () {
        modal.style.display = 'flex';
    });

    closeButton.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
})();
</script>
@endif
@endpush
