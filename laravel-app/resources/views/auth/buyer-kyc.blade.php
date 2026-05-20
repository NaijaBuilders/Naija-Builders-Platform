@php($pageTitle = 'Buyer Verification')
@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 2rem; max-width: 980px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
        <div>
            <p style="margin: 0 0 0.4rem; color: var(--primary-color); font-weight: 700;">Optional buyer verification</p>
            <h1 style="margin: 0 0 0.6rem;">Complete KYC once, when you are ready</h1>
            <p style="margin: 0; color: var(--neutral-600); max-width: 680px; line-height: 1.6;">
                You can browse and save materials without uploading ID. We only need ID when an order reaches a verification level that requires it. If you complete this now, we will reuse the approved verification for future eligible orders.
            </p>
        </div>
        <a href="/buyer-dashboard.php" class="btn btn-outline" style="text-decoration: none;">Back to Dashboard</a>
    </div>

    @if (($successCode ?? '') === 'verified')
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">Your ID has been verified.</div>
    @elseif (($successCode ?? '') === 'reviewing')
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">Your ID has been submitted and is being reviewed.</div>
    @elseif (($successCode ?? '') === 'rejected')
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">We could not verify that ID. Please check the details and try again.</div>
    @endif

    @if (($errorCode ?? '') === 'invalid_fields')
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">Please check the verification form and try again.</div>
    @endif

    <div class="grid-2 gap-lg" style="align-items: start;">
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">How buyer KYC works</h3>
            </div>
            <div class="card-body">
                <ol style="margin: 0; padding-left: 1.2rem; color: var(--neutral-700); line-height: 1.65;">
                    <li>Small orders can continue without ID.</li>
                    <li>Larger orders may ask for a photo of your ID and a selfie.</li>
                    <li>Once approved, your verification can be used again for future orders.</li>
                    <li>If anything needs checking, we will show “Your order is being reviewed”.</li>
                </ol>
                <div style="background: var(--neutral-50); border: 1px solid var(--neutral-200); border-radius: var(--rounded-md); padding: 1rem; margin-top: 1rem;">
                    <p style="margin: 0; color: var(--neutral-700); line-height: 1.55;">
                        Accepted documents: NIN slip, international passport, or driver’s licence. Files are stored privately and only used for verification.
                    </p>
                </div>

                @if ($verification)
                    <div style="margin-top: 1rem; padding: 1rem; border-radius: var(--rounded-md); border: 1px solid var(--neutral-200);">
                        <p style="margin: 0 0 0.35rem; color: var(--neutral-600);">Current status</p>
                        <h3 style="margin: 0; color: {{ $verification->status === 'verified' ? 'var(--secondary-color)' : ($verification->status === 'rejected' ? 'var(--accent-danger)' : 'var(--accent-warning)') }};">
                            {{ ucfirst((string) $verification->status) }}
                        </h3>
                        <p style="margin: 0.45rem 0 0; color: var(--neutral-600);">
                            {{ str_replace('_', ' ', ucfirst((string) $verification->document_type)) }}
                            @if ($verification->verified_at)
                                · Verified {{ $verification->verified_at->format('M d, Y') }}
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">KYC form</h3>
                <p style="margin: 0.35rem 0 0; color: var(--neutral-600);">Upload a photo of your ID, then take or upload a selfie.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="/buyer-kyc.php" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="document_type">Document type</label>
                        <select id="document_type" name="document_type" required>
                            <option value="nin_slip" {{ old('document_type', $verification->document_type ?? '') === 'nin_slip' ? 'selected' : '' }}>NIN slip</option>
                            <option value="international_passport" {{ old('document_type', $verification->document_type ?? '') === 'international_passport' ? 'selected' : '' }}>International passport</option>
                            <option value="drivers_licence" {{ old('document_type', $verification->document_type ?? '') === 'drivers_licence' ? 'selected' : '' }}>Driver’s licence</option>
                        </select>
                        @error('document_type')<p style="color: var(--accent-danger); margin: 0.35rem 0 0;">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="verified_id_name">Name on ID</label>
                        <input id="verified_id_name" name="verified_id_name" type="text" maxlength="150" value="{{ old('verified_id_name', $verification->verified_id_name ?? ($currentUser['name'] ?? '')) }}" placeholder="Enter the name exactly as shown on your ID">
                        @error('verified_id_name')<p style="color: var(--accent-danger); margin: 0.35rem 0 0;">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="id_document">ID photo</label>
                        <input id="id_document" name="id_document" type="file" accept=".jpg,.jpeg,.png,.pdf">
                        @error('id_document')<p style="color: var(--accent-danger); margin: 0.35rem 0 0;">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="selfie">Selfie</label>
                        <input id="selfie" name="selfie" type="file" accept=".jpg,.jpeg,.png">
                        @error('selfie')<p style="color: var(--accent-danger); margin: 0.35rem 0 0;">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Submit verification</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
