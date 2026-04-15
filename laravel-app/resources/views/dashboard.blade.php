@php
    $pageTitle = 'Dashboard';
@endphp
@extends('layouts.app')

@section('content')
<div class="container dashboard-layout dashboard-page dashboard-page--supplier">
    @php
        $supplierKycStatus = (string) session('legacy_user.kyc_status', 'approved');
        if ($supplierKycStatus === '') {
            $supplierKycStatus = 'approved';
        }
        $supplierKycApproved = $supplierKycStatus === 'approved';
    @endphp
    <aside class="dashboard-sidebar-panel">
        <h3 style="margin-top: 0;">Dashboard Menu</h3>
        <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
            <a href="/dashboard.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); background: var(--primary-light); color: var(--primary-color); font-weight: 600; text-decoration: none;">Overview</a>
            <a href="/analysis.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">Analysis</a>
            @if (($supplierKycApproved ?? false))
                <a href="/create-listing.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">Create Listing</a>
                <a href="/manage-listings.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">View Listings</a>
            @else
                <a href="/supplier-kyc.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--primary-color); text-decoration: none; font-weight: 600;">Complete KYC</a>
            @endif
            <a href="/messages.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">Messages</a>
            <a href="/materials.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">Browse Materials</a>
            <a href="/settings.php" style="padding: 0.75rem 1rem; border-radius: var(--rounded-md); color: var(--neutral-700); text-decoration: none;">Settings</a>
        </nav>
    </aside>

    <div>
        @if (request()->query('success', '') === 'signup' && !($supplierKycApproved ?? false))
            <div class="alert alert-info" style="margin-bottom: 1rem;">
                Account created successfully. Please complete your KYC to unlock material posting.
                <a href="/supplier-kyc.php" style="font-weight: 700; margin-left: 0.4rem;">Complete KYC now</a>
            </div>
        @endif

        <div style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 2rem; border-radius: var(--rounded-lg); margin-bottom: 2rem;">
            <h1 style="color: white; margin: 0 0 0.5rem;">Welcome back, {{ $currentUser['name'] ?? 'User' }}</h1>
            <p style="margin: 0; color: rgba(255, 255, 255, 0.9);">A simple summary of your account performance.</p>
        </div>

        @if (!($supplierKycApproved ?? false))
            <div class="alert alert-warning" style="margin-bottom: 1.5rem;">
                Supplier account is limited until KYC approval. Current status: <strong>{{ ucfirst($supplierKycStatus) }}</strong>.
                <a href="/supplier-kyc.php" style="font-weight: 700; margin-left: 0.4rem;">Complete or update KYC</a>
            </div>
        @endif

        <div class="grid-4 gap-lg" style="margin-bottom: 2rem;">
            <div class="card"><div class="card-body"><p style="color: var(--neutral-600); margin: 0 0 0.5rem;">Total Listings</p><h2 style="color: var(--primary-color); margin: 0;">{{ number_format((int) $totalListings) }}</h2></div></div>
            <div class="card"><div class="card-body"><p style="color: var(--neutral-600); margin: 0 0 0.5rem;">Active Listings</p><h2 style="color: var(--secondary-color); margin: 0;">{{ number_format((int) $activeListings) }}</h2></div></div>
            <div class="card"><div class="card-body"><p style="color: var(--neutral-600); margin: 0 0 0.5rem;">Listings With Stock</p><h2 style="color: var(--accent-success); margin: 0;">{{ number_format((int) $inStockListings) }}</h2></div></div>
            <div class="card"><div class="card-body"><p style="color: var(--neutral-600); margin: 0 0 0.5rem;">Unread Messages</p><h2 style="color: var(--accent-info); margin: 0;">{{ number_format((int) $unreadMessages) }}</h2></div></div>
        </div>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <h3 style="margin: 0;">Quick Performance</h3>
                <a href="/analysis.php" class="btn btn-outline" style="text-decoration: none;">Open Full Analysis</a>
            </div>
            <div class="card-body">
                <div class="grid-3 gap-lg" style="margin-bottom: 1rem;">
                    <div>
                        <p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Inventory Value (Active Only)</p>
                        <h4 style="margin: 0; color: var(--primary-color);">N{{ number_format((float) $inventoryValue, 2) }}</h4>
                    </div>
                    <div>
                        <p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Active Listing Rate</p>
                        <h4 style="margin: 0; color: var(--secondary-color);">{{ number_format((float) $activeListingRate, 1) }}%</h4>
                    </div>
                    <div>
                        <p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Low Stock (Active Only)</p>
                        <h4 style="margin: 0; color: var(--accent-warning);">{{ number_format((int) $lowStockListings) }} ({{ number_format((float) $lowStockRate, 1) }}%)</h4>
                    </div>
                </div>
                <p style="margin: 0; color: var(--neutral-600);">These stock and risk rates are calculated from your active listings only.</p>
            </div>
        </div>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header"><h3 style="margin: 0;">Recent Listings</h3></div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background-color: var(--neutral-50);">
                        <tr>
                            <th style="padding: 1rem; text-align: left;">Listing</th>
                            <th style="padding: 1rem; text-align: left;">Category</th>
                            <th style="padding: 1rem; text-align: left;">Price</th>
                            <th style="padding: 1rem; text-align: left;">Stock</th>
                            <th style="padding: 1rem; text-align: left;">Status</th>
                            <th style="padding: 1rem; text-align: left;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentListings as $listing)
                            <tr style="border-bottom: 1px solid var(--neutral-200);">
                                <td style="padding: 1rem;">{{ $listing->name }}</td>
                                <td style="padding: 1rem;">{{ $listing->category ?: 'General' }}</td>
                                <td style="padding: 1rem;">N{{ number_format((float) $listing->price, 2) }} / {{ ucfirst(str_replace('_', ' ', (string) ($listing->price_unit ?: 'item'))) }}</td>
                                <td style="padding: 1rem;">{{ number_format((int) $listing->stock_qty) }}</td>
                                <td style="padding: 1rem;">{{ ucwords(str_replace('_', ' ', (string) $listing->status)) }}</td>
                                <td style="padding: 1rem;">{{ \Carbon\Carbon::parse($listing->created_at)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="padding: 1rem; color: var(--neutral-600);">No listings yet. Create your first listing to start selling.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
