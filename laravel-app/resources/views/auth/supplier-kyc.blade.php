@php
    $pageTitle = 'Supplier KYC';
    $kycStatus = (string) ($user->kyc_status ?? 'pending');
    $statusLabel = match ($kycStatus) {
        'approved' => 'Approved',
        'submitted' => 'Submitted for Review',
        'rejected' => 'Needs Update',
        default => 'Pending Submission',
    };
    $statusColor = match ($kycStatus) {
        'approved' => 'var(--accent-success)',
        'submitted' => 'var(--accent-info)',
        'rejected' => 'var(--accent-danger)',
        default => 'var(--accent-warning)',
    };
    $message = match ($errorCode ?? '') {
        'missing_fields' => 'Please complete all required KYC fields.',
        'kyc_required' => 'Your supplier account is limited until KYC is approved.',
        default => '',
    };
    $successMessage = match ($successCode ?? '') {
        'signup' => 'Supplier account created. Complete KYC to unlock full supplier features.',
        'submitted' => 'KYC submitted successfully. Our team will review your details.',
        default => '',
    };
@endphp
@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 760px; margin: 2rem auto;">
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <h2 style="margin-top: 0;">Supplier Verification (KYC)</h2>
            <p style="margin-bottom: 1rem; color: var(--neutral-700);">Submit your business details for verification. Until approved, listing and inventory features remain limited.</p>
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.75rem; border-radius: 999px; background: var(--neutral-100);">
                <span style="width: 10px; height: 10px; border-radius: 999px; background: {{ $statusColor }};"></span>
                <strong>Status: {{ $statusLabel }}</strong>
            </div>
        </div>
    </div>

    @if ($message !== '')
        <div class="alert alert-danger">{{ $message }}</div>
    @endif

    @if ($successMessage !== '')
        <div class="alert alert-success">{{ $successMessage }}</div>
    @endif

    @if ($kycStatus === 'approved')
        <div class="alert alert-success">Your KYC is approved. You can now manage and publish listings.</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="/supplier-kyc.php">
                @csrf
                <div class="grid-2 gap-lg">
                    <div class="form-group"><label for="company">Business Name</label><input type="text" id="company" name="company" required value="{{ old('company', $user->company ?? '') }}"></div>
                    <div class="form-group"><label for="business_category">Business Category</label><input type="text" id="business_category" name="business_category" required value="{{ old('business_category', $user->business_category ?? '') }}"></div>
                </div>
                <div class="grid-2 gap-lg">
                    <div class="form-group"><label for="location">Location (State)</label><input type="text" id="location" name="location" required value="{{ old('location', $user->location ?? '') }}"></div>
                    <div class="form-group"><label for="business_address">Business Address</label><input type="text" id="business_address" name="business_address" required value="{{ old('business_address', $user->business_address ?? '') }}"></div>
                </div>
                <div class="form-group"><label for="business_description">Business Description (Optional)</label><textarea id="business_description" name="business_description" rows="4">{{ old('business_description', $user->business_description ?? '') }}</textarea></div>
                <div class="grid-2 gap-lg">
                    <div class="form-group"><label for="bank_name">Bank Name</label><input type="text" id="bank_name" name="bank_name" required value="{{ old('bank_name', $user->bank_name ?? '') }}"></div>
                    <div class="form-group"><label for="account_number">Account Number</label><input type="text" id="account_number" name="account_number" required value="{{ old('account_number', $user->account_number ?? '') }}"></div>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem;">
                    <a href="/dashboard.php" class="btn btn-outline">Back to Dashboard</a>
                    <button type="submit" class="btn btn-primary">Submit KYC</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
