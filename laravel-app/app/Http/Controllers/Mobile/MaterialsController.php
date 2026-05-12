<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Support\NameFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaterialsController extends Controller
{
    private const REVIEW_PAGE_LIMIT = 8;

    public function index(Request $request)
    {
        $currentUserId = (int) ($request->user()?->id ?? 0);
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $location = trim((string) $request->query('location', ''));
        $priceUnit = trim((string) $request->query('price_unit', ''));
        $sortBy = trim((string) $request->query('sort_by', 'newest'));
        if (! in_array($sortBy, ['newest', 'top_sellers', 'price_low', 'price_high'], true)) {
            $sortBy = 'newest';
        }

        $perPage = (int) $request->query('per_page', 12);
        $perPageOptions = [6, 12, 24, 48];
        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = 12;
        }

        $savedCollectionId = (int) $request->query('saved_collection', 0);
        $showSavedOnly = (int) $request->query('show_saved', 0) === 1;
        $hasPriceUnit = Schema::hasColumn('materials', 'price_unit');
        $hasIsNegotiable = Schema::hasColumn('materials', 'is_negotiable');
        $hasSupplierBadge = Schema::hasColumn('users', 'is_verified_badge');
        $hasSavedTables = Schema::hasTable('saved_product_categories') && Schema::hasTable('saved_materials');

        $hasProductReviewsTable = Schema::hasTable('product_reviews');
        $hasSupplierReviewsTable = Schema::hasTable('supplier_reviews');

        $query = DB::table('materials as m')
            ->join('users as u', 'u.id', '=', 'm.supplier_id')
            ->select(array_merge([
                'm.id',
                'm.supplier_id',
                'm.name',
                'm.category',
                'm.price',
                'm.stock_qty',
                'm.description',
                'm.created_at',
                'u.company',
                'u.full_name',
                'u.location',
                'u.profile_image_path as supplier_profile_image_path',
                DB::raw('(SELECT mi.image_path FROM material_images mi WHERE mi.material_id = m.id ORDER BY mi.display_order ASC, mi.id ASC LIMIT 1) as image_path'),
                $hasProductReviewsTable ? DB::raw('(SELECT ROUND(AVG(pr.rating), 1) FROM product_reviews pr WHERE pr.material_id = m.id) as product_rating_avg') : DB::raw('NULL as product_rating_avg'),
                $hasProductReviewsTable ? DB::raw('(SELECT COUNT(*) FROM product_reviews pr WHERE pr.material_id = m.id) as product_rating_count') : DB::raw('0 as product_rating_count'),
                $hasSupplierReviewsTable ? DB::raw('(SELECT ROUND(AVG(sr.rating), 1) FROM supplier_reviews sr WHERE sr.supplier_id = m.supplier_id) as supplier_rating_avg') : DB::raw('NULL as supplier_rating_avg'),
                $hasSupplierReviewsTable ? DB::raw('(SELECT COUNT(*) FROM supplier_reviews sr WHERE sr.supplier_id = m.supplier_id) as supplier_rating_count') : DB::raw('0 as supplier_rating_count'),
            ], [
                $hasPriceUnit ? 'm.price_unit' : DB::raw("'item' as price_unit"),
                $hasIsNegotiable ? 'm.is_negotiable' : DB::raw('0 as is_negotiable'),
                $hasSupplierBadge ? 'u.is_verified_badge' : DB::raw('0 as is_verified_badge'),
            ]))
            ->where('m.status', 'active');

        if ($hasSavedTables && $currentUserId > 0 && ($showSavedOnly || $savedCollectionId > 0)) {
            $query->join('saved_materials as sm', function ($join) use ($currentUserId) {
                $join->on('sm.material_id', '=', 'm.id')
                    ->where('sm.user_id', '=', $currentUserId);
            });

            if ($savedCollectionId > 0) {
                $query->where('sm.category_id', $savedCollectionId);
            }
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('m.name', 'like', '%'.$search.'%')
                    ->orWhere('m.description', 'like', '%'.$search.'%');
            });
        }

        if ($category !== '') {
            $query->where('m.category', $category);
        }

        if ($location !== '') {
            $query->where('u.location', $location);
        }

        if ($hasPriceUnit && $priceUnit !== '') {
            $query->where('m.price_unit', $priceUnit);
        }

        if ($sortBy === 'top_sellers') {
            $query
                ->orderByDesc('product_rating_count')
                ->orderByDesc('product_rating_avg')
                ->orderByDesc('m.created_at');
        } elseif ($sortBy === 'price_low') {
            $query
                ->orderBy('m.price')
                ->orderByDesc('m.created_at');
        } elseif ($sortBy === 'price_high') {
            $query
                ->orderByDesc('m.price')
                ->orderByDesc('m.created_at');
        } else {
            $query->orderByDesc('m.created_at');
        }

        $materials = $query->paginate($perPage)->withQueryString();

        $categories = DB::table('materials')
            ->select('category')
            ->where('status', 'active')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->get();

        $locations = DB::table('users')
            ->select('location')
            ->whereNotNull('location')
            ->where('location', '<>', '')
            ->distinct()
            ->orderBy('location')
            ->get();

        $priceUnits = $hasPriceUnit
            ? DB::table('materials')
                ->select('price_unit')
                ->where('status', 'active')
                ->whereNotNull('price_unit')
                ->where('price_unit', '<>', '')
                ->distinct()
                ->orderBy('price_unit')
                ->get()
            : collect();

        $savedCategories = collect();
        $savedMaterialIds = [];

        if ($hasSavedTables && $currentUserId > 0) {
            $savedCategories = DB::table('saved_product_categories')
                ->select(['id', 'name'])
                ->where('user_id', $currentUserId)
                ->orderBy('name')
                ->get();

            $savedMaterialIds = DB::table('saved_materials')
                ->where('user_id', $currentUserId)
                ->pluck('material_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $currentUserProductRatings = [];
        if ($hasProductReviewsTable && $currentUserId > 0) {
            $materialIdsOnPage = $materials->getCollection()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $currentUserProductRatings = DB::table('product_reviews')
                ->where('user_id', $currentUserId)
                ->whereIn('material_id', $materialIdsOnPage)
                ->pluck('rating', 'material_id')
                ->mapWithKeys(fn ($rating, $materialId) => [(int) $materialId => (int) $rating])
                ->all();
        }

        return response()->json([
            'data' => $materials->items(),
            'meta' => [
                'current_page' => $materials->currentPage(),
                'last_page' => $materials->lastPage(),
                'per_page' => $materials->perPage(),
                'total' => $materials->total(),
            ],
            'filters' => [
                'search' => $search,
                'category' => $category,
                'location' => $location,
                'price_unit' => $priceUnit,
                'sort_by' => $sortBy,
                'per_page' => $perPage,
                'per_page_options' => $perPageOptions,
                'saved_collection_id' => $savedCollectionId,
                'show_saved_only' => $showSavedOnly,
            ],
            'lookups' => [
                'categories' => $categories,
                'locations' => $locations,
                'price_units' => $priceUnits,
                'saved_categories' => $savedCategories,
            ],
            'saved_material_ids' => $savedMaterialIds,
            'current_user_product_ratings' => $currentUserProductRatings,
        ]);
    }

    public function show(Request $request, int $materialId)
    {
        $hasPriceUnit = Schema::hasColumn('materials', 'price_unit');
        $hasIsNegotiable = Schema::hasColumn('materials', 'is_negotiable');
        $hasSupplierBadge = Schema::hasColumn('users', 'is_verified_badge');

        $material = DB::table('materials as m')
            ->join('users as u', 'u.id', '=', 'm.supplier_id')
            ->select(array_merge([
                'm.id',
                'm.supplier_id',
                'm.name',
                'm.category',
                'm.price',
                'm.stock_qty',
                'm.description',
                'm.status',
                'u.company',
                'u.full_name',
                'u.location',
                'u.profile_image_path as supplier_profile_image_path',
            ], [
                $hasPriceUnit ? 'm.price_unit' : DB::raw("'item' as price_unit"),
                $hasIsNegotiable ? 'm.is_negotiable' : DB::raw('0 as is_negotiable'),
                $hasSupplierBadge ? 'u.is_verified_badge' : DB::raw('0 as is_verified_badge'),
            ]))
            ->where('m.id', $materialId)
            ->where('m.status', 'active')
            ->first();

        if (! $material) {
            return response()->json(['message' => 'Material not found.'], 404);
        }

        $images = DB::table('material_images')
            ->select(['image_path'])
            ->where('material_id', $materialId)
            ->orderBy('display_order')
            ->orderBy('id')
            ->limit(4)
            ->get();

        $mainImagePath = (string) (($images->first()->image_path ?? '') ?: '');
        $supplierName = trim((string) ($material->company ?? '')) !== ''
            ? (string) $material->company
            : NameFormatter::title((string) ($material->full_name ?? 'Supplier'));
        $stockQuantity = (int) ($material->stock_qty ?? 0);
        $stockLabel = $stockQuantity > 0 ? number_format($stockQuantity).' in stock' : 'Out of stock';
        $isSaved = false;

        if (Schema::hasTable('saved_materials')) {
            $currentUserId = (int) ($request->user()?->id ?? 0);
            if ($currentUserId > 0) {
                $isSaved = DB::table('saved_materials')
                    ->where('user_id', $currentUserId)
                    ->where('material_id', $materialId)
                    ->exists();
            }
        }

        $savedCategories = collect();
        if (Schema::hasTable('saved_product_categories')) {
            $currentUserId = (int) ($request->user()?->id ?? 0);
            if ($currentUserId > 0) {
                $savedCategories = DB::table('saved_product_categories')
                    ->select(['id', 'name'])
                    ->where('user_id', $currentUserId)
                    ->orderBy('name')
                    ->get();
            }
        }

        $hasProductReviewsTable = Schema::hasTable('product_reviews');
        $hasSupplierReviewsTable = Schema::hasTable('supplier_reviews');

        $productReviews = collect();
        $supplierReviews = collect();
        $productRatingAvg = null;
        $productRatingCount = 0;
        $supplierRatingAvg = null;
        $supplierRatingCount = 0;
        $currentUserProductRating = null;
        $currentUserSupplierRating = null;

        if ($hasProductReviewsTable) {
            $productReviews = DB::table('product_reviews as pr')
                ->leftJoin('users as ru', 'ru.id', '=', 'pr.user_id')
                ->select([
                    'pr.rating',
                    'pr.review_text',
                    'pr.created_at',
                    'ru.full_name as reviewer_full_name',
                    'ru.company as reviewer_company',
                ])
                ->where('pr.material_id', $materialId)
                ->orderByDesc('pr.created_at')
                ->limit(self::REVIEW_PAGE_LIMIT)
                ->get()
                ->map(function ($review) {
                    $review->reviewer_full_name = NameFormatter::title((string) ($review->reviewer_full_name ?? 'Verified Buyer'), 'Verified Buyer');

                    return $review;
                });

            $productRatingSummary = DB::table('product_reviews')
                ->selectRaw('ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as total_reviews')
                ->where('material_id', $materialId)
                ->first();

            $productRatingAvg = $productRatingSummary && $productRatingSummary->avg_rating !== null
                ? (float) $productRatingSummary->avg_rating
                : null;
            $productRatingCount = (int) ($productRatingSummary->total_reviews ?? 0);
        }

        if ($hasSupplierReviewsTable) {
            $supplierReviews = DB::table('supplier_reviews as sr')
                ->leftJoin('users as ru', 'ru.id', '=', 'sr.user_id')
                ->select([
                    'sr.rating',
                    'sr.review_text',
                    'sr.created_at',
                    'ru.full_name as reviewer_full_name',
                    'ru.company as reviewer_company',
                ])
                ->where('sr.supplier_id', (int) $material->supplier_id)
                ->orderByDesc('sr.created_at')
                ->limit(self::REVIEW_PAGE_LIMIT)
                ->get()
                ->map(function ($review) {
                    $review->reviewer_full_name = NameFormatter::title((string) ($review->reviewer_full_name ?? 'Verified Buyer'), 'Verified Buyer');

                    return $review;
                });

            $supplierRatingSummary = DB::table('supplier_reviews')
                ->selectRaw('ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as total_reviews')
                ->where('supplier_id', (int) $material->supplier_id)
                ->first();

            $supplierRatingAvg = $supplierRatingSummary && $supplierRatingSummary->avg_rating !== null
                ? (float) $supplierRatingSummary->avg_rating
                : null;
            $supplierRatingCount = (int) ($supplierRatingSummary->total_reviews ?? 0);
        }

        $currentUserId = (int) ($request->user()?->id ?? 0);
        if ($currentUserId > 0) {
            if ($hasProductReviewsTable) {
                $currentUserProductRating = DB::table('product_reviews')
                    ->where('material_id', $materialId)
                    ->where('user_id', $currentUserId)
                    ->value('rating');
                $currentUserProductRating = $currentUserProductRating !== null ? (int) $currentUserProductRating : null;
            }

            if ($hasSupplierReviewsTable) {
                $currentUserSupplierRating = DB::table('supplier_reviews')
                    ->where('supplier_id', (int) $material->supplier_id)
                    ->where('user_id', $currentUserId)
                    ->value('rating');
                $currentUserSupplierRating = $currentUserSupplierRating !== null ? (int) $currentUserSupplierRating : null;
            }
        }

        return response()->json([
            'material' => $material,
            'images' => $images,
            'main_image_path' => $mainImagePath,
            'supplier_name' => $supplierName,
            'stock_quantity' => $stockQuantity,
            'stock_label' => $stockLabel,
            'is_saved' => $isSaved,
            'saved_categories' => $savedCategories,
            'product_reviews' => $productReviews,
            'supplier_reviews' => $supplierReviews,
            'product_rating_avg' => $productRatingAvg,
            'product_rating_count' => $productRatingCount,
            'supplier_rating_avg' => $supplierRatingAvg,
            'supplier_rating_count' => $supplierRatingCount,
            'current_user_product_rating' => $currentUserProductRating,
            'current_user_supplier_rating' => $currentUserSupplierRating,
        ]);
    }

    public function rateProduct(Request $request, int $materialId)
    {
        if (! Schema::hasTable('product_reviews')) {
            return response()->json(['message' => 'Product reviews are not available yet.'], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['nullable', 'string', 'max:1200'],
        ]);

        $userId = (int) $request->user()->id;

        $material = DB::table('materials')
            ->select(['id', 'supplier_id', 'status'])
            ->where('id', $materialId)
            ->where('status', 'active')
            ->first();

        if (! $material) {
            return response()->json(['message' => 'Material was not found.'], 404);
        }

        DB::table('product_reviews')->updateOrInsert(
            [
                'material_id' => $materialId,
                'user_id' => $userId,
            ],
            [
                'supplier_id' => (int) $material->supplier_id,
                'rating' => (int) $validated['rating'],
                'review_text' => trim((string) ($validated['review_text'] ?? '')),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['message' => 'Product rating saved.']);
    }

    public function rateSupplier(Request $request, int $supplierId)
    {
        if (! Schema::hasTable('supplier_reviews')) {
            return response()->json(['message' => 'Supplier reviews are not available yet.'], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['nullable', 'string', 'max:1200'],
        ]);

        $userId = (int) $request->user()->id;

        $supplierExists = DB::table('users')
            ->where('id', $supplierId)
            ->exists();

        if (! $supplierExists) {
            return response()->json(['message' => 'Supplier was not found.'], 404);
        }

        DB::table('supplier_reviews')->updateOrInsert(
            [
                'supplier_id' => $supplierId,
                'user_id' => $userId,
            ],
            [
                'rating' => (int) $validated['rating'],
                'review_text' => trim((string) ($validated['review_text'] ?? '')),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['message' => 'Supplier rating saved.']);
    }
}
