@php($pageTitle = 'Edit Profile')
@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px; margin: 2rem auto;">
    @php($subscriptionPlan = (string) ($user->subscription_plan ?? 'standard'))
    @php($isSupplierRole = (string) ($user->role ?? 'builder') === 'supplier')
    @php($isVerifiedBadge = (int) ($user->is_verified_badge ?? 0) === 1)

    <h1 style="margin-top: 0;">Edit Your Profile</h1>

    <div class="card" style="margin-bottom: 1rem; border: 1px solid var(--neutral-200);">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div>
                <p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Current Subscription</p>
                <h3 style="margin: 0 0 0.35rem; font-size: 1.2rem;">
                    {{ $isSupplierRole
                        ? ($subscriptionPlan === 'enterprise' ? 'Enterprise Marketplace' : ($subscriptionPlan === 'pro' ? 'Pro Marketplace' : 'Standard Marketplace'))
                        : ($subscriptionPlan === 'enterprise' ? 'Buyer Enterprise' : ($subscriptionPlan === 'pro' ? 'Buyer Pro' : 'Buyer Standard')) }}
                </h3>
                @if ($isVerifiedBadge)
                    <p style="margin: 0 0 0.45rem;">
                        <span style="display: inline-block; font-size: 0.78rem; background: var(--secondary-color); color: white; border-radius: 999px; padding: 0.2rem 0.55rem;">Verified Badge Active</span>
                    </p>
                @endif
                <p style="margin: 0; color: var(--neutral-600); font-size: 0.9rem;">
                    {{ $isVerifiedBadge ? 'Verified badge active on your account.' : 'Upgrade to Pro or Enterprise for a verified badge.' }}
                </p>
            </div>
            <a href="/subscription.php" class="btn btn-secondary" style="text-decoration: none;">View or Upgrade Plan</a>
        </div>
    </div>

    @if (($successCode ?? '') === 'profile_updated')
        <div class="alert alert-success">Profile updated successfully.</div>
    @endif
    @if (($errorCode ?? '') === 'email_exists')
        <div class="alert alert-danger">The email address is already in use by another account.</div>
    @endif
    @if (($errorCode ?? '') === 'invalid_profile')
        <div class="alert alert-danger">Please provide a valid name, email, phone number, and location.</div>
    @endif
    @if (($errorCode ?? '') === 'image_invalid')
        <div class="alert alert-danger">Profile picture must be a JPG, PNG, or WEBP image.</div>
    @endif
    @if (($errorCode ?? '') === 'image_too_large')
        <div class="alert alert-danger">Profile picture must be 2MB or smaller.</div>
    @endif
    @if (($errorCode ?? '') === 'image_upload')
        <div class="alert alert-danger">Profile picture upload failed. Please try again.</div>
    @endif

    <div class="card"><div class="card-body">
        <form method="POST" action="/api/update-profile.php" enctype="multipart/form-data">
            @csrf
            <h3 style="margin-top: 0;">Personal Information</h3>
            <div class="form-group">
                <label>Profile Picture</label>
                <input id="profile_picture_input" type="file" name="profile_picture" accept="image/png,image/jpeg,image/webp" style="display: none;">
                <label for="profile_picture_input" style="display: inline-flex; align-items: center; gap: 1rem; cursor: pointer; margin: 0.5rem 0 0.75rem;">
                    <span style="position: relative; display: inline-flex;">
                    <span id="profile_picture_circle" style="width: 96px; height: 96px; border-radius: 999px; border: 2px solid var(--neutral-200); overflow: hidden; display: inline-flex; align-items: center; justify-content: center; background: var(--neutral-100);">
                        @if (($user->profile_image_path ?? '') !== '')
                            <img id="profile_picture_preview" src="/{{ $user->profile_image_path }}" alt="Current profile picture" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            <span id="profile_picture_icon" aria-hidden="true" style="font-size: 2rem; color: var(--neutral-500);">👤</span>
                            <img id="profile_picture_preview" src="" alt="Profile picture preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                        @endif
                    </span>
                    @if ($isVerifiedBadge)
                        <span title="Verified account" style="position: absolute; right: -4px; bottom: -4px; width: 24px; height: 24px; border-radius: 999px; background: var(--secondary-color); color: white; border: 2px solid white; display: inline-flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 700;">✓</span>
                    @endif
                    </span>
                    <span style="display: inline-block; color: var(--neutral-700); font-weight: 600;">Tap to change picture</span>
                </label>
                <small style="display: block; margin-top: 0.4rem; color: var(--neutral-600);">Accepted formats: JPG, PNG, WEBP (max 2MB).</small>
            </div>
            <div class="grid-2 gap-lg">
                <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="{{ $firstName }}" required></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="{{ $lastName }}" required></div>
            </div>
            <div class="form-group"><label>Email Address</label><input type="email" name="email" value="{{ $user->email ?? '' }}" required></div>
            <div class="form-group"><label>Phone Number</label><input type="tel" name="phone" value="{{ $user->phone ?? '' }}" required></div>

            <hr style="border: none; border-top: 1px solid var(--neutral-200); margin: 2rem 0;">
            <h3>Business Information</h3>
            <div class="form-group"><label>Company Name</label><input type="text" name="company" value="{{ $user->company ?? '' }}" required></div>
            <div class="grid-2 gap-lg">
                <div class="form-group"><label>Business Category</label><input type="text" name="business_category" value="{{ $user->business_category ?? '' }}" required></div>
                <div class="form-group"><label>Location (State)</label><input type="text" name="location" value="{{ $user->location ?? '' }}" required></div>
            </div>
            <div class="form-group"><label>Business Address</label><input type="text" name="business_address" value="{{ $user->business_address ?? '' }}" required></div>
            <div class="form-group"><label>Business Description</label><textarea name="business_description" rows="4">{{ $user->business_description ?? '' }}</textarea></div>

            <hr style="border: none; border-top: 1px solid var(--neutral-200); margin: 2rem 0;">
            <h3>Bank Details (For Payouts)</h3>
            <div class="grid-2 gap-lg">
                <div class="form-group"><label>Bank Name</label><input type="text" name="bank_name" value="{{ $user->bank_name ?? '' }}" required></div>
                <div class="form-group"><label>Account Number</label><input type="text" name="account_number" value="{{ $user->account_number ?? '' }}" required></div>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;"><button type="reset" class="btn btn-outline">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
        </form>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('profile_picture_input');
    const preview = document.getElementById('profile_picture_preview');
    const icon = document.getElementById('profile_picture_icon');

    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = input.files && input.files[0] ? input.files[0] : null;
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (event) {
            const result = event.target && event.target.result ? event.target.result : '';
            if (!result) return;
            preview.src = result;
            preview.style.display = 'block';
            if (icon) {
                icon.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    });
})();
</script>
@endpush
