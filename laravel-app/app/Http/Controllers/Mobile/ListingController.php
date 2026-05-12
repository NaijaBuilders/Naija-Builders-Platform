<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ListingController extends Controller
{
    private const PRICE_UNITS = ['item', 'kg', 'bag', 'ton', 'piece', 'meter', 'liter', 'cubic_meter', 'truckload'];

    public function index(Request $request)
    {
        $currentUserId = (int) $request->user()->id;
        if (! $this->isSupplier($currentUserId)) {
            return response()->json(['message' => 'Supplier listings only.'], 403);
        }

        if (! $this->isSupplierKycApproved($currentUserId)) {
            return response()->json(['message' => 'KYC approval required.'], 403);
        }

        $hasPriceUnit = Schema::hasColumn('materials', 'price_unit');
        $hasIsNegotiable = Schema::hasColumn('materials', 'is_negotiable');
        $hasMaterialImages = Schema::hasTable('material_images');

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $category = trim((string) $request->query('category', ''));
        $priceUnit = trim((string) $request->query('price_unit', ''));

        $query = DB::table('materials')
            ->select(array_merge(
                ['id', 'name', 'category', 'price', 'stock_qty', 'status', 'created_at'],
                [
                    $hasPriceUnit ? 'price_unit' : DB::raw("'item' as price_unit"),
                    $hasIsNegotiable ? 'is_negotiable' : DB::raw('0 as is_negotiable'),
                ]
            ))
            ->where('supplier_id', $currentUserId);

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($hasPriceUnit && $priceUnit !== '') {
            $query->where('price_unit', $priceUnit);
        }

        $listings = $query->orderByDesc('created_at')->get();
        $listingImages = collect();

        if ($hasMaterialImages && $listings->isNotEmpty()) {
            $listingImages = DB::table('material_images')
                ->select(['material_id', 'image_path', 'display_order'])
                ->whereIn('material_id', $listings->pluck('id')->map(fn ($id) => (int) $id)->all())
                ->orderBy('display_order')
                ->orderBy('id')
                ->get()
                ->groupBy('material_id');
        }

        $listings = $listings->map(function ($listing) use ($listingImages, $request) {
            $images = $listingImages->get((int) $listing->id, collect())
                ->map(function ($image) use ($request) {
                    $imagePath = (string) ($image->image_path ?? '');

                    return [
                        'image_path' => $imagePath,
                        'image_url' => $this->publicAssetUrl($request, $imagePath),
                        'display_order' => (int) ($image->display_order ?? 0),
                    ];
                })
                ->values();

            $firstImage = $images->first();
            $listing->image_path = (string) ($firstImage['image_path'] ?? '');
            $listing->image_url = (string) ($firstImage['image_url'] ?? '');
            $listing->images = $images;

            return $listing;
        });

        $categories = DB::table('materials')
            ->select('category')
            ->where('supplier_id', $currentUserId)
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->get();

        $priceUnits = $hasPriceUnit
            ? DB::table('materials')
                ->select('price_unit')
                ->where('supplier_id', $currentUserId)
                ->whereNotNull('price_unit')
                ->where('price_unit', '<>', '')
                ->distinct()
                ->orderBy('price_unit')
                ->get()
            : collect();

        return response()->json([
            'data' => $listings,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'category' => $category,
                'price_unit' => $priceUnit,
            ],
            'lookups' => [
                'categories' => $categories,
                'price_units' => $priceUnits,
                'allowed_price_units' => self::PRICE_UNITS,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $currentUserId = (int) $request->user()->id;
        if (! $this->isSupplier($currentUserId)) {
            return response()->json(['message' => 'Supplier listings only.'], 403);
        }

        if (! $this->isSupplierKycApproved($currentUserId)) {
            return response()->json(['message' => 'KYC approval required.'], 403);
        }

        $hasPriceUnit = Schema::hasColumn('materials', 'price_unit');
        $hasIsNegotiable = Schema::hasColumn('materials', 'is_negotiable');
        $hasMaterialsCreatedAt = Schema::hasColumn('materials', 'created_at');
        $hasMaterialsUpdatedAt = Schema::hasColumn('materials', 'updated_at');
        $hasMaterialImagesCreatedAt = Schema::hasColumn('material_images', 'created_at');
        $hasMaterialImagesUpdatedAt = Schema::hasColumn('material_images', 'updated_at');

        $name = trim((string) $request->input('name', ''));
        $category = trim((string) $request->input('category', ''));
        $description = trim((string) $request->input('description', ''));
        $price = (float) $request->input('price', 0);
        $priceUnit = trim((string) $request->input('price_unit', 'item'));
        $isNegotiable = (string) $request->input('is_negotiable', '0') === '1';
        $stockQty = (int) $request->input('stock_qty', 0);
        $status = (string) $request->input('status', 'active');
        $imagesInput = $request->file('images', []);
        $images = is_array($imagesInput)
            ? array_values(array_filter($imagesInput, fn ($file) => $file instanceof UploadedFile))
            : (($imagesInput instanceof UploadedFile) ? [$imagesInput] : []);

        if ($name === '' || $category === '' || $price <= 0 || $stockQty < 0) {
            return response()->json(['message' => 'Please provide valid listing details.'], 422);
        }

        if (! in_array($status, ['active', 'inactive', 'out_of_stock'], true)) {
            $status = 'active';
        }

        if (! in_array($priceUnit, self::PRICE_UNITS, true)) {
            $priceUnit = 'item';
        }

        if (count($images) < 3) {
            return response()->json(['message' => 'Please upload at least 3 product images.'], 422);
        }

        if (count($images) > 10) {
            return response()->json(['message' => 'You can upload a maximum of 10 images per listing.'], 422);
        }

        $uploadedPaths = [];

        try {
            DB::beginTransaction();

            $materialPayload = [
                'supplier_id' => $currentUserId,
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'price' => $price,
                'stock_qty' => $stockQty,
                'status' => $status,
            ]
                + ($hasPriceUnit ? ['price_unit' => $priceUnit] : [])
                + ($hasIsNegotiable ? ['is_negotiable' => $isNegotiable] : []);

            if ($hasMaterialsCreatedAt) {
                $materialPayload['created_at'] = now();
            }

            if ($hasMaterialsUpdatedAt) {
                $materialPayload['updated_at'] = now();
            }

            $materialId = DB::table('materials')->insertGetId($materialPayload);

            $uploadDirectory = public_path('assets/images/listings');
            if (! File::isDirectory($uploadDirectory)) {
                File::makeDirectory($uploadDirectory, 0775, true);
            }

            foreach ($images as $index => $image) {
                if (! $image->isValid()) {
                    throw new \RuntimeException('One or more image uploads failed. Please try again.');
                }

                if ($image->getSize() > (5 * 1024 * 1024)) {
                    throw new \RuntimeException('Each image must be smaller than 5MB.');
                }

                $mime = (string) $image->getMimeType();
                $extension = match ($mime) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    default => null,
                };

                if (! $extension) {
                    throw new \RuntimeException('Only JPG, PNG, and WEBP images are allowed.');
                }

                $filename = 'material-'.$materialId.'-supplier-'.$currentUserId.'-'.($index + 1).'-'.Str::lower(Str::random(10)).'.'.$extension;
                $image->move($uploadDirectory, $filename);

                $relativePath = 'assets/images/listings/'.$filename;
                $uploadedPaths[] = $relativePath;

                $imagePayload = [
                    'material_id' => $materialId,
                    'image_path' => $relativePath,
                    'display_order' => $index + 1,
                ];

                if ($hasMaterialImagesCreatedAt) {
                    $imagePayload['created_at'] = now();
                }

                if ($hasMaterialImagesUpdatedAt) {
                    $imagePayload['updated_at'] = now();
                }

                DB::table('material_images')->insert($imagePayload);
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            foreach ($uploadedPaths as $relativePath) {
                $absolutePath = public_path($relativePath);
                if (File::exists($absolutePath)) {
                    File::delete($absolutePath);
                }
            }

            return response()->json(['message' => $exception->getMessage() ?: 'Failed to create listing.'], 422);
        }

        return response()->json([
            'message' => 'Listing created.',
            'listing_id' => $materialId,
        ], 201);
    }

    public function destroy(Request $request, int $listingId)
    {
        $currentUserId = (int) $request->user()->id;
        if (! $this->isSupplier($currentUserId)) {
            return response()->json(['message' => 'Supplier listings only.'], 403);
        }

        if (! $this->isSupplierKycApproved($currentUserId)) {
            return response()->json(['message' => 'KYC approval required.'], 403);
        }

        if ($listingId > 0) {
            DB::table('materials')
                ->where('id', $listingId)
                ->where('supplier_id', $currentUserId)
                ->delete();
        }

        return response()->json(['message' => 'Listing deleted.']);
    }

    private function isSupplier(int $userId): bool
    {
        return (string) DB::table('users')->where('id', $userId)->value('role') === 'supplier';
    }

    private function isSupplierKycApproved(int $userId): bool
    {
        if (! Schema::hasColumn('users', 'kyc_status')) {
            return true;
        }

        if ($userId <= 0) {
            return false;
        }

        $kycStatus = (string) DB::table('users')
            ->where('id', $userId)
            ->value('kyc_status');

        if ($kycStatus === '') {
            $kycStatus = 'approved';
        }

        return $kycStatus === 'approved';
    }

    private function publicAssetUrl(Request $request, string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $path;
        }

        return rtrim($request->getSchemeAndHttpHost(), '/').'/'.ltrim($path, '/');
    }
}
