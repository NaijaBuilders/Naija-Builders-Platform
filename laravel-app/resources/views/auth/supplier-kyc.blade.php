@php
    $pageTitle = 'Supplier KYC';
    $applicationStatus = strtoupper((string) ($application->status ?? 'DRAFT'));
    $kycStatus = (string) ($user->kyc_status ?? '');
    if ($kycStatus === '') {
        $kycStatus = match ($applicationStatus) {
            'APPROVED' => 'approved',
            'REJECTED' => 'rejected',
            'MANUAL_REVIEW' => 'manual_review',
            'MORE_INFO_REQUIRED' => 'more_info_required',
            'VERIFYING' => 'verifying',
            'SUBMITTED' => 'submitted',
            default => 'pending',
        };
    }

    $statusLabel = match ($kycStatus) {
        'approved' => 'Approved',
        'submitted' => 'Submitted',
        'verifying' => 'Verifying',
        'manual_review' => 'Manual Review',
        'more_info_required' => 'More Info Required',
        'rejected' => 'Not Approved',
        'suspended' => 'Suspended',
        default => 'Draft',
    };
    $statusColor = match ($kycStatus) {
        'approved' => 'var(--accent-success)',
        'submitted', 'verifying' => 'var(--accent-info)',
        'manual_review', 'more_info_required' => 'var(--accent-warning)',
        'rejected', 'suspended' => 'var(--accent-danger)',
        default => 'var(--neutral-500)',
    };
    $statusBody = match ($kycStatus) {
        'approved' => 'Your supplier verification is approved.',
        'submitted', 'verifying' => "We're verifying your details.",
        'manual_review' => 'Your verification is being reviewed by the NaijaBuilders team.',
        'more_info_required' => 'We need a little more information.',
        'rejected' => 'Your verification could not be approved at this time.',
        'suspended' => 'Supplier verification is currently suspended.',
        default => 'Complete the form below to run supplier verification checks.',
    };
    $message = match ($errorCode ?? '') {
        'missing_fields' => 'Please complete all required KYC fields.',
        'invalid_fields' => 'Please review the highlighted KYC fields.',
        'missing_identity' => 'Enter either a BVN or NIN for identity verification.',
        'submit_failed' => 'We could not submit your KYC right now. Please try again.',
        'kyc_required' => 'Your supplier account is limited until KYC is approved.',
        default => '',
    };
    $successMessage = match ($successCode ?? '') {
        'signup' => 'Supplier account created. Complete KYC to unlock full supplier features.',
        'processed' => 'KYC submitted. Review the current status below.',
        'submitted' => 'KYC submitted successfully. Our team will review your details.',
        default => '',
    };
    $documentType = old('id_document_type', $application->id_document_type ?? 'national_id');
@endphp
@extends('layouts.app')

@push('head')
<style>
    .kyc-shell {
        max-width: 1040px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .kyc-status-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: start;
        margin-bottom: 1.25rem;
    }

    .kyc-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: var(--neutral-100);
        font-weight: 800;
        white-space: nowrap;
    }

    .kyc-status-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--kyc-status-color);
    }

    .kyc-form {
        display: grid;
        gap: 1rem;
    }

    .kyc-section {
        border: 1px solid var(--neutral-200);
        border-radius: var(--rounded-lg);
        padding: 1.25rem;
        background: var(--white);
    }

    .kyc-section h3 {
        margin: 0 0 0.35rem;
        color: var(--neutral-900);
    }

    .kyc-section p {
        margin: 0 0 1rem;
        color: var(--neutral-600);
        line-height: 1.55;
    }

    .kyc-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .kyc-actions {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 0.5rem;
    }

    .field-hint {
        display: block;
        margin-top: 0.35rem;
        color: var(--neutral-500);
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .field-error {
        display: block;
        margin-top: 0.35rem;
        color: var(--accent-danger);
        font-size: 0.82rem;
        font-weight: 700;
    }

    @media (max-width: 760px) {
        .kyc-status-card,
        .kyc-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="kyc-shell">
    <div class="card kyc-status-card">
        <div class="card-body">
            <h2 style="margin-top: 0;">Supplier Verification</h2>
            <p style="margin-bottom: 1rem; color: var(--neutral-700);">
                Submit business registration, identity, and bank details for supplier verification.
            </p>
            <p style="margin-bottom: 0; color: var(--neutral-700);">{{ $statusBody }}</p>
            @if ($application->more_info_message && $kycStatus === 'more_info_required')
                <p style="margin-top: 0.75rem; font-weight: 700;">{{ $application->more_info_message }}</p>
            @endif
        </div>
        <div class="card-body">
            <div class="kyc-status-pill" style="--kyc-status-color: {{ $statusColor }};">
                <span class="kyc-status-dot"></span>
                <span>{{ $statusLabel }}</span>
            </div>
            <div style="margin-top: 0.75rem; color: var(--neutral-600); font-size: 0.9rem;">
                Stage: {{ str_replace('_', ' ', $application->current_stage ?? 'ACCOUNT') }}
            </div>
        </div>
    </div>

    @if ($message !== '')
        <div class="alert alert-danger">{{ $message }}</div>
    @endif

    @if ($successMessage !== '')
        <div class="alert alert-success">{{ $successMessage }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following:</strong>
            <ul style="margin: 0.5rem 0 0; padding-left: 1.2rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/supplier-kyc.php" enctype="multipart/form-data" class="kyc-form" autocomplete="off">
        @csrf

        <section class="kyc-section">
            <h3>Business Registration</h3>
            <p>Use the CAC registration details for the business you will sell materials under.</p>
            <div class="kyc-grid">
                <div class="form-group">
                    <label for="cac_number">CAC number</label>
                    <input type="text" id="cac_number" name="cac_number" required placeholder="RC123456" value="{{ old('cac_number', $application->cac_number ?? '') }}">
                    @error('cac_number')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="business_name">Business name</label>
                    <input type="text" id="business_name" name="business_name" required value="{{ old('business_name', $application->business_name ?? $user->company ?? '') }}">
                    @error('business_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="business_type">Business type</label>
                    <input type="text" id="business_type" name="business_type" required placeholder="Distributor, manufacturer, wholesaler" value="{{ old('business_type', $application->business_type ?? $user->business_category ?? '') }}">
                    @error('business_type')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="state">State/location</label>
                    <input type="text" id="state" name="state" required value="{{ old('state', $application->state ?? $user->location ?? '') }}">
                    @error('state')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-group">
                <label for="business_address">Business address</label>
                <input type="text" id="business_address" name="business_address" required value="{{ old('business_address', $application->business_address ?? $user->business_address ?? '') }}">
                @error('business_address')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="kyc-grid">
                <div class="form-group">
                    <label for="contact_name">Contact name</label>
                    <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name', $application->contact_name ?? $user->full_name ?? '') }}">
                    @error('contact_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="contact_email">Business email</label>
                    <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $application->contact_email ?? $user->email ?? '') }}">
                    @error('contact_email')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="contact_phone">Business phone</label>
                    <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $application->contact_phone ?? $user->phone ?? '') }}">
                    @error('contact_phone')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </section>

        <section class="kyc-section">
            <h3>Identity Verification</h3>
            <p>Enter either BVN or NIN, then upload a clear ID document and selfie for review or face checks.</p>
            <div class="kyc-grid">
                <div class="form-group">
                    <label for="bvn">BVN</label>
                    <input type="password" id="bvn" name="bvn" inputmode="numeric" maxlength="11" placeholder="{{ $application->bvn_mask ? 'Stored: '.$application->bvn_mask : '11 digits' }}" value="">
                    <span class="field-hint">Not saved raw. Re-enter when submitting KYC.</span>
                    @error('bvn')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="nin">NIN</label>
                    <input type="password" id="nin" name="nin" inputmode="numeric" maxlength="11" placeholder="{{ $application->nin_mask ? 'Stored: '.$application->nin_mask : '11 digits' }}" value="">
                    <span class="field-hint">Provide BVN or NIN. Both are hashed/masked after submission.</span>
                    @error('nin')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="id_document_type">ID document type</label>
                    <select id="id_document_type" name="id_document_type">
                        <option value="national_id" @selected($documentType === 'national_id')>National ID</option>
                        <option value="voter_card" @selected($documentType === 'voter_card')>Voter card</option>
                        <option value="drivers_license" @selected($documentType === 'drivers_license')>Driver licence</option>
                        <option value="passport" @selected($documentType === 'passport')>Passport</option>
                    </select>
                    @error('id_document_type')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="selfie">Selfie photo</label>
                    <input type="file" id="selfie" name="selfie" accept="image/jpeg,image/png,image/webp">
                    <span class="field-hint">{{ $application->selfie_path ? 'A selfie is already on file. Upload again to replace it.' : 'JPG, PNG, or WEBP up to 5 MB.' }}</span>
                    @error('selfie')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="id_document">ID document</label>
                    <input type="file" id="id_document" name="id_document" accept="image/jpeg,image/png,image/webp,application/pdf">
                    <span class="field-hint">{{ $application->id_document_path ? 'An ID document is already on file. Upload again to replace it.' : 'Image preferred for automated checks. PDF may require manual review.' }}</span>
                    @error('id_document')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </section>

        <section class="kyc-section">
            <h3>Bank Verification</h3>
            <p>Use the payout account for the verified supplier business.</p>
            <div class="kyc-grid">
                <div class="form-group">
                    <label for="bank_name">Bank name</label>
                    <input type="text" id="bank_name" name="bank_name" required value="{{ old('bank_name', $application->bank_name ?? $user->bank_name ?? '') }}">
                    @error('bank_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="bank_code">Bank code</label>
                    <input type="text" id="bank_code" name="bank_code" required placeholder="044" value="{{ old('bank_code', $application->bank_code ?? '') }}">
                    @error('bank_code')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="account_number">Account number</label>
                    <input type="password" id="account_number" name="account_number" inputmode="numeric" maxlength="12" required placeholder="{{ $application->account_number_mask ? 'Stored: '.$application->account_number_mask : '10 digits' }}" value="">
                    <span class="field-hint">Not saved raw. Re-enter when submitting KYC.</span>
                    @error('account_number')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="account_name">Account name</label>
                    <input type="text" id="account_name" name="account_name" value="{{ old('account_name', $application->account_name ?? $application->business_name ?? $user->company ?? '') }}">
                    @error('account_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </section>

        <div class="kyc-actions">
            <a href="/dashboard.php" class="btn btn-outline">Back to Dashboard</a>
            <button type="submit" class="btn btn-primary">Submit Verification</button>
        </div>
    </form>
</div>
@endsection
