<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SavedMaterialController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) $request->session()->get('legacy_user_id', 0);
        if ($userId <= 0) {
            return redirect('/login.php');
        }

        if (!Schema::hasTable('saved_materials') || !Schema::hasTable('saved_product_categories')) {
            return redirect('/buyer-dashboard.php')->with('save_error', 'Save feature is not ready yet. Please run migrations.');
        }

        $search = trim((string) $request->query('search', ''));
        $savedCategoryId = (int) $request->query('saved_category', 0);
        $perPage = 12;
        $currentPage = max(1, (int) $request->query('page', 1));
        $offset = ($currentPage - 1) * $perPage;

        $savedCategories = DB::table('saved_product_categories')
            ->select(['id', 'name'])
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        $baseQuery = DB::table('saved_materials as sm')
            ->join('materials as m', 'm.id', '=', 'sm.material_id')
            ->join('users as u', 'u.id', '=', 'm.supplier_id')
            ->leftJoin('saved_product_categories as spc', 'spc.id', '=', 'sm.category_id')
            ->select([
                'sm.material_id',
                'sm.category_id',
                'sm.updated_at as saved_at',
                'm.name',
                'm.category',
                'm.price',
                Schema::hasColumn('materials', 'price_unit') ? 'm.price_unit' : DB::raw("'item' as price_unit"),
                'u.company',
                'u.full_name',
                Schema::hasColumn('users', 'is_verified_badge') ? 'u.is_verified_badge' : DB::raw('0 as is_verified_badge'),
                'spc.name as saved_category_name',
            ])
            ->where('sm.user_id', $userId);

        if ($savedCategoryId > 0) {
            $baseQuery->where('sm.category_id', $savedCategoryId);
        }

        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search): void {
                $query->where('m.name', 'like', '%' . $search . '%')
                    ->orWhere('m.category', 'like', '%' . $search . '%')
                    ->orWhere('u.company', 'like', '%' . $search . '%')
                    ->orWhere('u.full_name', 'like', '%' . $search . '%');
            });
        }

        $totalRecords = (clone $baseQuery)->count();
        $savedMaterials = $baseQuery
            ->orderByDesc('sm.updated_at')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $totalPages = max(1, (int) ceil($totalRecords / $perPage));

        return view('saved-products.index', compact(
            'savedMaterials',
            'savedCategories',
            'savedCategoryId',
            'search',
            'currentPage',
            'totalPages',
            'totalRecords'
        ));
    }

    public function store(Request $request)
    {
        $userId = (int) $request->session()->get('legacy_user_id', 0);
        if ($userId <= 0) {
            return redirect('/login.php');
        }

        if (!Schema::hasTable('saved_materials') || !Schema::hasTable('saved_product_categories')) {
            return back()->with('save_error', 'Save feature is not ready yet. Please run migrations.');
        }

        $materialId = (int) $request->input('material_id', 0);
        if ($materialId <= 0) {
            return back()->with('save_error', 'Invalid material selected.');
        }

        $materialExists = DB::table('materials')->where('id', $materialId)->where('status', 'active')->exists();
        if (!$materialExists) {
            return back()->with('save_error', 'Material not found or inactive.');
        }

        $categoryId = (int) $request->input('category_id', 0);
        $newCategoryName = trim((string) $request->input('new_category_name', ''));

        if ($newCategoryName !== '') {
            $normalizedName = mb_substr($newCategoryName, 0, 120);
            $existingCategory = DB::table('saved_product_categories')
                ->where('user_id', $userId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($normalizedName)])
                ->first();

            if ($existingCategory) {
                $categoryId = (int) $existingCategory->id;
            } else {
                $categoryId = (int) DB::table('saved_product_categories')->insertGetId([
                    'user_id' => $userId,
                    'name' => $normalizedName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($categoryId > 0) {
            $categoryExists = DB::table('saved_product_categories')
                ->where('id', $categoryId)
                ->where('user_id', $userId)
                ->exists();

            if (!$categoryExists) {
                return back()->with('save_error', 'Selected save category is invalid.');
            }
        } else {
            $categoryId = null;
        }

        $alreadySaved = DB::table('saved_materials')
            ->where('user_id', $userId)
            ->where('material_id', $materialId)
            ->first();

        if ($alreadySaved) {
            DB::table('saved_materials')
                ->where('id', $alreadySaved->id)
                ->update([
                    'category_id' => $categoryId,
                    'updated_at' => now(),
                ]);

            return back()->with('save_success', 'Saved product category updated.');
        }

        DB::table('saved_materials')->insert([
            'user_id' => $userId,
            'material_id' => $materialId,
            'category_id' => $categoryId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('save_success', 'Product saved successfully.');
    }

    public function destroy(Request $request)
    {
        $userId = (int) $request->session()->get('legacy_user_id', 0);
        $materialId = (int) $request->input('material_id', 0);

        if ($userId > 0 && $materialId > 0 && Schema::hasTable('saved_materials')) {
            DB::table('saved_materials')
                ->where('user_id', $userId)
                ->where('material_id', $materialId)
                ->delete();
        }

        return back()->with('save_success', 'Product removed from saved items.');
    }
}
