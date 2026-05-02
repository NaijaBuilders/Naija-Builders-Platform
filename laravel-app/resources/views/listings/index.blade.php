@php($pageTitle = 'Manage Listings')
@extends('layouts.app')

@section('content')
<div class="container" style="margin: 2rem auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;"><h1 style="margin: 0;">My Listings</h1><a href="/create-listing.php" class="btn btn-primary">Create Listing</a></div>

    @if (request('success') === 'deleted')
        <div class="alert alert-success">Listing deleted successfully.</div>
    @endif
    @if (request('success') === 'created')
        <div class="alert alert-success">Listing created and published successfully.</div>
    @endif

    <div class="card" style="margin-bottom: 2rem;"><div class="card-body">
        <form method="GET" action="/manage-listings.php"><div class="grid-4 gap-md">
            <select name="category" style="padding: 0.75rem;"><option value="">All Categories</option>@foreach ($categories as $cat)<option value="{{ $cat->category }}" {{ $category === $cat->category ? 'selected' : '' }}>{{ $cat->category }}</option>@endforeach</select>
            <select name="status" style="padding: 0.75rem;"><option value="">All Status</option><option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option><option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option><option value="out_of_stock" {{ $status === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option></select>
            <select name="price_unit" style="padding: 0.75rem;"><option value="">All Units</option>@foreach ($priceUnits as $unit)<option value="{{ $unit->price_unit }}" {{ $priceUnit === $unit->price_unit ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $unit->price_unit)) }}</option>@endforeach</select>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search products..." style="padding: 0.75rem;">
            <button class="btn btn-outline" type="submit">Apply</button>
        </div></form>
    </div></div>

    <div class="card"><div style="overflow-x: auto;"><table style="width: 100%; border-collapse: collapse;"><thead style="background-color: var(--neutral-50);"><tr><th style="padding: 1rem; text-align: left;">Product Name</th><th style="padding: 1rem; text-align: left;">Category</th><th style="padding: 1rem; text-align: left;">Price</th><th style="padding: 1rem; text-align: left;">Pricing Type</th><th style="padding: 1rem; text-align: left;">Stock</th><th style="padding: 1rem; text-align: left;">Status</th><th style="padding: 1rem; text-align: left;">Action</th></tr></thead><tbody>
        @forelse ($listings as $listing)
            <tr style="border-bottom: 1px solid var(--neutral-200);"><td style="padding: 1rem;"><strong>{{ $listing->name }}</strong></td><td style="padding: 1rem;">{{ $listing->category ?: 'General' }}</td><td style="padding: 1rem; font-weight: 600;">{{ $formatMoney((float) $listing->price) }} / {{ ucfirst(str_replace('_', ' ', (string) ($listing->price_unit ?: 'item'))) }}</td><td style="padding: 1rem;">{{ (int) ($listing->is_negotiable ?? 0) === 1 ? 'Negotiable' : 'Fixed Price' }}</td><td style="padding: 1rem;">{{ number_format((int) $listing->stock_qty) }}</td><td style="padding: 1rem;">{{ ucwords(str_replace('_', ' ', $listing->status)) }}</td><td style="padding: 1rem;"><form method="POST" action="/manage-listings.php" style="display: inline;">@csrf<input type="hidden" name="action" value="delete_listing"><input type="hidden" name="listing_id" value="{{ (int) $listing->id }}"><button type="submit" style="color: var(--accent-danger); background: none; border: none; cursor: pointer;">Delete</button></form></td></tr>
        @empty
            <tr><td colspan="7" style="padding: 1rem; color: var(--neutral-600);">No listings found. Create a listing to publish materials to the marketplace.</td></tr>
        @endforelse
    </tbody></table></div></div>
</div>
@endsection
