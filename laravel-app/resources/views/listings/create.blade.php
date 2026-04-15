@php($pageTitle = 'Create Listing')
@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 860px; margin: 2rem auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="margin: 0;">Create New Listing</h1>
        <a href="/manage-listings.php" class="btn btn-outline">Back to Listings</a>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card"><div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="/create-listing.php">
            @csrf
            <div class="grid-2 gap-lg">
                <div class="form-group"><label>Material Name</label><input type="text" name="name" required value="{{ old('name') }}"></div>
                <div class="form-group"><label>Category</label><select name="category" required><option value="">Select category</option><option>Cement</option><option>Steel</option><option>Wood</option><option>Tiles</option><option>Electrical</option><option>Plumbing</option><option>General</option></select></div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="5">{{ old('description') }}</textarea></div>
            <div class="grid-4 gap-lg">
                <div class="form-group"><label>Price (₦)</label><input type="number" min="0" step="0.01" name="price" required value="{{ old('price') }}"></div>
                <div class="form-group"><label>Price Unit</label><select name="price_unit" required>@foreach ($priceUnits as $unit)<option value="{{ $unit }}" {{ old('price_unit', 'item') === $unit ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $unit)) }}</option>@endforeach</select></div>
                <div class="form-group"><label>Stock Quantity</label><input type="number" min="0" step="1" name="stock_qty" required value="{{ old('stock_qty', 0) }}"></div>
                <div class="form-group"><label>Status</label><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option><option value="out_of_stock">Out of Stock</option></select></div>
            </div>
            <div class="form-group" style="margin-top: 1rem;">
                <label>Pricing Type</label>
                <select name="is_negotiable" required>
                    <option value="0" {{ old('is_negotiable', '0') === '0' ? 'selected' : '' }}>Fixed Price</option>
                    <option value="1" {{ old('is_negotiable') === '1' ? 'selected' : '' }}>Negotiable</option>
                </select>
            </div>
            <div class="form-group" style="margin-top: 1rem;"><label>Product Images (minimum 3)</label><input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required></div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;"><a href="/manage-listings.php" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Publish Listing</button></div>
        </form>
    </div></div>
</div>
@endsection
