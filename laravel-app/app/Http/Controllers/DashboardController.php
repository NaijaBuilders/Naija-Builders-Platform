<?php

namespace App\Http\Controllers;

use App\Support\CurrencyManager;
use App\Support\MoneyCalculator;
use App\Support\NameFormatter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = (array) $request->session()->get('legacy_user', []);
        $role = (string) ($currentUser['role'] ?? '');

        if ($role !== 'supplier') {
            return redirect('/buyer-dashboard.php');
        }

        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);
        $dashboardData = $this->buildSupplierDashboardData($currentUserId);

        return view('dashboard', array_merge(['currentUser' => $currentUser], $dashboardData));
    }

    public function analysis(Request $request)
    {
        $currentUser = (array) $request->session()->get('legacy_user', []);
        $role = (string) ($currentUser['role'] ?? '');

        if ($role !== 'supplier') {
            return redirect('/buyer-dashboard.php');
        }

        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);
        $dashboardData = $this->buildSupplierDashboardData($currentUserId);

        return view('dashboard-analysis', array_merge(['currentUser' => $currentUser], $dashboardData));
    }

    private function buildSupplierDashboardData(int $currentUserId): array
    {
        $hasPriceUnit = Schema::hasColumn('materials', 'price_unit');

        $totalListings = (int) DB::table('materials')->where('supplier_id', $currentUserId)->count();
        $activeListings = (int) DB::table('materials')->where('supplier_id', $currentUserId)->where('status', 'active')->count();
        $totalStock = (int) DB::table('materials')->where('supplier_id', $currentUserId)->sum('stock_qty');
        $inStockListings = (int) DB::table('materials')->where('supplier_id', $currentUserId)->where('stock_qty', '>', 0)->count();
        $unreadMessages = (int) DB::table('messages')->where('receiver_id', $currentUserId)->where('is_read', 0)->count();

        $recentListingSelect = ['id', 'name', 'category', 'price', 'stock_qty', 'status', 'created_at'];
        $recentListingSelect[] = $hasPriceUnit
            ? 'price_unit'
            : DB::raw("'item' as price_unit");

        $recentListings = DB::table('materials')
            ->select($recentListingSelect)
            ->where('supplier_id', $currentUserId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $inventoryValue = MoneyCalculator::amount(DB::table('materials')
            ->where('supplier_id', $currentUserId)
            ->where('status', 'active')
            ->where('stock_qty', '>', 0)
            ->sum(DB::raw('price * stock_qty')));

        $lowStockListings = (int) DB::table('materials')
            ->where('supplier_id', $currentUserId)
            ->where('status', 'active')
            ->whereBetween('stock_qty', [1, 10])
            ->count();

        $outOfStockListings = (int) DB::table('materials')
            ->where('supplier_id', $currentUserId)
            ->where('status', 'active')
            ->where('stock_qty', '<=', 0)
            ->count();

        $activeListingRate = MoneyCalculator::percentage($activeListings, $totalListings);

        $activeCatalogBase = max($activeListings, 0);

        $lowStockRate = MoneyCalculator::percentage($lowStockListings, $activeCatalogBase);

        $outOfStockRate = MoneyCalculator::percentage($outOfStockListings, $activeCatalogBase);

        if ($hasPriceUnit) {
            $unitDistribution = DB::table('materials')
                ->select(['price_unit', DB::raw('COUNT(*) as total')])
                ->where('supplier_id', $currentUserId)
                ->where('status', 'active')
                ->groupBy('price_unit')
                ->orderByDesc('total')
                ->get()
                ->map(function ($unit) use ($activeCatalogBase) {
                    $count = (int) ($unit->total ?? 0);
                    $unit->ratio = MoneyCalculator::percentage($count, $activeCatalogBase);

                    return $unit;
                });

            $averagePriceByUnit = DB::table('materials')
                ->select(['price_unit', DB::raw('AVG(price) as avg_price')])
                ->where('supplier_id', $currentUserId)
                ->where('status', 'active')
                ->groupBy('price_unit')
                ->orderBy('price_unit')
                ->get();

            $stockByUnitTotals = DB::table('materials')
                ->select(['price_unit', DB::raw('SUM(stock_qty) as stock_total')])
                ->where('supplier_id', $currentUserId)
                ->where('status', 'active')
                ->where('stock_qty', '>', 0)
                ->groupBy('price_unit')
                ->orderByDesc('stock_total')
                ->get();
        } else {
            $unitDistribution = collect();
            $averagePriceByUnit = collect();
            $stockByUnitTotals = collect();
        }

        $salesOverTime = collect();
        if (Schema::hasTable('orders')) {
            $seriesStart = Carbon::now()->startOfMonth()->subMonths(5);
            $salesRows = DB::table('orders')
                ->select([
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                    DB::raw('SUM(total_amount) as total_sales'),
                    DB::raw('COUNT(*) as total_orders'),
                ])
                ->where('supplier_id', $currentUserId)
                ->where('created_at', '>=', $seriesStart)
                ->whereNotIn('order_status', ['cancelled'])
                ->groupBy('month_key')
                ->orderBy('month_key')
                ->get()
                ->keyBy('month_key');

            $salesOverTime = collect(range(0, 5))->map(function (int $index) use ($seriesStart, $salesRows) {
                $month = (clone $seriesStart)->addMonths($index);
                $key = $month->format('Y-m');
                $row = $salesRows->get($key);

                return (object) [
                    'month_label' => $month->format('M Y'),
                    'month_key' => $key,
                    'total_sales' => MoneyCalculator::amount($row->total_sales ?? 0),
                    'total_orders' => (int) ($row->total_orders ?? 0),
                ];
            });
        }

        $topCategories = DB::table('materials')
            ->select(['category', DB::raw('COUNT(*) as listings'), DB::raw('SUM(stock_qty) as stock_total')])
            ->where('supplier_id', $currentUserId)
            ->where('status', 'active')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->groupBy('category')
            ->orderByDesc('listings')
            ->limit(5)
            ->get();

        $recentMessages = DB::table('messages as m')
            ->join('users as u', 'u.id', '=', 'm.sender_id')
            ->select(['m.content', 'm.created_at', 'u.full_name as sender_name'])
            ->where('m.receiver_id', $currentUserId)
            ->orderByDesc('m.created_at')
            ->limit(5)
            ->get()
            ->map(function ($message) {
                $message->sender_name = NameFormatter::title((string) ($message->sender_name ?? 'User'));

                return $message;
            });

        $analyticsUpdatedAt = now();

        return compact(
            'totalListings',
            'activeListings',
            'totalStock',
            'inStockListings',
            'unreadMessages',
            'recentListings',
            'recentMessages',
            'inventoryValue',
            'lowStockListings',
            'outOfStockListings',
            'activeListingRate',
            'lowStockRate',
            'outOfStockRate',
            'unitDistribution',
            'averagePriceByUnit',
            'stockByUnitTotals',
            'salesOverTime',
            'topCategories',
            'analyticsUpdatedAt'
        );
    }

    public function buyer(Request $request)
    {
        $currentUser = (array) $request->session()->get('legacy_user', []);
        $role = (string) ($currentUser['role'] ?? '');
        $hasPriceUnit = Schema::hasColumn('materials', 'price_unit');
        $hasSupplierBadge = Schema::hasColumn('users', 'is_verified_badge');
        $hasSavedTables = Schema::hasTable('saved_product_categories') && Schema::hasTable('saved_materials');
        $selectedSavedCategoryId = (int) $request->query('saved_category', 0);

        if ($role === 'supplier') {
            return redirect('/dashboard.php');
        }

        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);
        $cartItems = (array) $request->session()->get('cart', []);

        $cartItemCount = 0;
        foreach ($cartItems as $entry) {
            $cartItemCount += max(1, (int) ($entry['quantity'] ?? 1));
        }

        $orderCount = 0;
        $pendingOrders = 0;
        $totalSpent = 0.0;
        $recentOrders = collect();

        if (Schema::hasTable('orders')) {
            $orderCount = (int) DB::table('orders')->where('buyer_id', $currentUserId)->count();
            $pendingOrders = (int) DB::table('orders')
                ->where('buyer_id', $currentUserId)
                ->whereIn('order_status', ['pending', 'processing'])
                ->count();
            $totalSpent = MoneyCalculator::amount(DB::table('orders')->where('buyer_id', $currentUserId)->sum('total_amount'));

            $recentOrders = DB::table('orders as o')
                ->join('users as u', 'u.id', '=', 'o.supplier_id')
                ->select(['o.id', 'o.total_amount', 'o.order_status', 'o.created_at', 'u.company', 'u.full_name'])
                ->where('o.buyer_id', $currentUserId)
                ->orderByDesc('o.created_at')
                ->limit(5)
                ->get();
        }

        $unreadMessages = (int) DB::table('messages')->where('receiver_id', $currentUserId)->where('is_read', 0)->count();

        $recommendedMaterials = DB::table('materials as m')
            ->join('users as u', 'u.id', '=', 'm.supplier_id')
            ->select(array_merge([
                'm.id',
                'm.name',
                'm.price',
                'm.stock_qty',
                'u.company',
                'u.full_name',
            ], [
                $hasPriceUnit ? 'm.price_unit' : DB::raw("'item' as price_unit"),
                $hasSupplierBadge ? 'u.is_verified_badge' : DB::raw('0 as is_verified_badge'),
            ]))
            ->where('m.status', 'active')
            ->orderByDesc('m.created_at')
            ->limit(6)
            ->get();

        $savedCategories = collect();
        $savedMaterials = collect();
        $savedMaterialIds = [];

        if ($hasSavedTables) {
            $savedCategories = DB::table('saved_product_categories')
                ->select(['id', 'name'])
                ->where('user_id', $currentUserId)
                ->orderBy('name')
                ->get();

            $savedQuery = DB::table('saved_materials as sm')
                ->join('materials as m', 'm.id', '=', 'sm.material_id')
                ->join('users as u', 'u.id', '=', 'm.supplier_id')
                ->leftJoin('saved_product_categories as spc', 'spc.id', '=', 'sm.category_id')
                ->select(array_merge([
                    'sm.material_id',
                    'sm.category_id',
                    'm.name',
                    'm.price',
                    'm.category',
                    'u.company',
                    'u.full_name',
                    'spc.name as saved_category_name',
                ], [
                    $hasPriceUnit ? 'm.price_unit' : DB::raw("'item' as price_unit"),
                    $hasSupplierBadge ? 'u.is_verified_badge' : DB::raw('0 as is_verified_badge'),
                ]))
                ->where('sm.user_id', $currentUserId)
                ->orderByDesc('sm.updated_at');

            if ($selectedSavedCategoryId > 0) {
                $savedQuery->where('sm.category_id', $selectedSavedCategoryId);
            }

            $savedMaterials = $savedQuery->limit(20)->get();
            $savedMaterialIds = DB::table('saved_materials')
                ->where('user_id', $currentUserId)
                ->pluck('material_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $currencyManager = app(CurrencyManager::class);
        $displayCurrency = $currencyManager->effectiveCurrency($currencyManager->resolveForRequest($request));
        $formatMoney = fn ($amount, ?string $currency = null): string => $currencyManager->formatFromNgn(
            $amount,
            $currency ?: $displayCurrency
        );

        return view('dashboard-buyer', compact(
            'currentUser',
            'cartItemCount',
            'orderCount',
            'pendingOrders',
            'totalSpent',
            'unreadMessages',
            'recentOrders',
            'recommendedMaterials',
            'savedCategories',
            'savedMaterials',
            'savedMaterialIds',
            'selectedSavedCategoryId',
            'formatMoney'
        ));
    }
}
