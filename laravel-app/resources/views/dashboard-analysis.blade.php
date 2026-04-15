@php
    $pageTitle = 'Analysis';
    $analyticsTimestamp = $analyticsUpdatedAt ?? now();
@endphp
@extends('layouts.app')

@section('content')
<div class="container dashboard-layout dashboard-page dashboard-page--analysis">
    <aside class="dashboard-sidebar-panel">
        <h3 style="margin-top: 0;">Dashboard Menu</h3>
        <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
            <a href="/dashboard.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">Overview</a>
            <a href="/analysis.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); background: var(--primary-light); color: var(--primary-color); font-weight: 600; text-decoration: none;">Analysis</a>
            <a href="/create-listing.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">Create Listing</a>
            <a href="/manage-listings.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">View Listings</a>
            <a href="/messages.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">Messages</a>
            <a href="/settings.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">Settings</a>
        </nav>
    </aside>

    <div>
        <div style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 2rem; border-radius: var(--rounded-lg); margin-bottom: 2rem;">
            <h1 style="color: white; margin: 0 0 0.45rem;">Simple Analysis</h1>
            <p style="margin: 0; color: rgba(255, 255, 255, 0.9);">Clear numbers only. Stock metrics below are based on your active listings to keep the math consistent.</p>
        </div>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <p style="margin: 0; color: var(--neutral-600);">Last updated: {{ $analyticsTimestamp->diffForHumans() }}</p>
                <p style="margin: 0; color: var(--neutral-600); font-size: 0.9rem;">At {{ $analyticsTimestamp->format('M d, Y h:i A') }}</p>
            </div>
        </div>

        <div class="grid-3 gap-lg" style="margin-bottom: 1.5rem;">
            <div class="card"><div class="card-body"><p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Inventory Value (Active Only)</p><h3 style="margin: 0; color: var(--primary-color);">N{{ number_format((float) $inventoryValue, 2) }}</h3></div></div>
            <div class="card"><div class="card-body"><p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Active Listing Rate</p><h3 style="margin: 0; color: var(--secondary-color);">{{ number_format((float) $activeListingRate, 1) }}%</h3></div></div>
            <div class="card"><div class="card-body"><p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Low Stock Rate (Active Only)</p><h3 style="margin: 0; color: var(--accent-warning);">{{ number_format((float) $lowStockRate, 1) }}%</h3></div></div>
        </div>

        <div class="grid-3 gap-lg" style="margin-bottom: 1.5rem;">
            <div class="card"><div class="card-body"><p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Total Listings</p><h3 style="margin: 0;">{{ number_format((int) $totalListings) }}</h3></div></div>
            <div class="card"><div class="card-body"><p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Active Listings</p><h3 style="margin: 0;">{{ number_format((int) $activeListings) }}</h3></div></div>
            <div class="card"><div class="card-body"><p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Out of Stock (Active Only)</p><h3 style="margin: 0; color: var(--accent-danger);">{{ number_format((int) $outOfStockListings) }} ({{ number_format((float) $outOfStockRate, 1) }}%)</h3></div></div>
        </div>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 style="margin: 0;">Monthly Sales (Last 6 Months)</h3>
                <p style="margin: 0.35rem 0 0; color: var(--neutral-600); font-size: 0.9rem;">Compact chart view</p>
            </div>
            @if (count($salesOverTime) > 0)
                @php
                    $maxMonthlySales = 1.0;
                    foreach ($salesOverTime as $salesPoint) {
                        $maxMonthlySales = max($maxMonthlySales, (float) ($salesPoint->total_sales ?? 0));
                    }
                @endphp
                <div style="overflow-x: auto;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(70px, 1fr)); gap: 0.75rem; align-items: end; min-height: 180px; padding: 1rem;">
                        @foreach ($salesOverTime as $point)
                            @php
                                $monthlySales = (float) $point->total_sales;
                                $chartBase = $maxMonthlySales > 0 ? $maxMonthlySales : 1.0;
                                $barHeight = (int) max(10, round(($monthlySales / $chartBase) * 120));
                            @endphp
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.35rem;">
                                <div style="width: 100%; max-width: 52px; height: 130px; display: flex; align-items: flex-end;">
                                    <div
                                        title="{{ $point->month_label }}: N{{ number_format($monthlySales, 2) }}"
                                        style="width: 100%; height: {{ $barHeight }}px; border-radius: 8px 8px 4px 4px; background: linear-gradient(180deg, var(--secondary-color), var(--primary-color));"
                                    ></div>
                                </div>
                                <span style="font-size: 0.8rem; color: var(--neutral-700);">{{ $point->month_label }}</span>
                                <span style="font-size: 0.78rem; color: var(--neutral-600);">N{{ number_format($monthlySales, 0) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p style="padding: 1rem; margin: 0; color: var(--neutral-600);">No sales records yet.</p>
            @endif
        </div>

        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header"><h3 style="margin: 0;">Top Categories (Active Listings)</h3></div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: var(--neutral-50);">
                        <tr>
                            <th style="padding: 0.85rem; text-align: left;">Category</th>
                            <th style="padding: 0.85rem; text-align: left;">Listings</th>
                            <th style="padding: 0.85rem; text-align: left;">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topCategories as $category)
                            <tr style="border-bottom: 1px solid var(--neutral-200);">
                                <td style="padding: 0.85rem;">{{ $category->category }}</td>
                                <td style="padding: 0.85rem;">{{ number_format((int) $category->listings) }}</td>
                                <td style="padding: 0.85rem;">{{ number_format((int) $category->stock_total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="padding: 0.85rem; color: var(--neutral-600);">No category data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 style="margin: 0;">What To Do Next</h3></div>
            <div class="card-body">
                <ul style="margin: 0; padding-left: 1rem; color: var(--neutral-700); line-height: 1.75;">
                    <li>If low stock rate is above 30%, restock your top sellers first.</li>
                    <li>If active listing rate is below 70%, reactivate or update inactive listings.</li>
                    <li>If out-of-stock rate is high, reduce inactive dead stock and keep quantities updated.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
