@php
    $pageTitle = 'Sign Up';
    $message = match ($errorCode ?? '') {
        'missing_fields' => 'Please complete all required fields.',
        'invalid_email' => 'Please provide a valid email address.',
        'weak_password' => 'Password must be at least 8 characters.',
        'passwords_do_not_match' => 'Passwords do not match.',
        'email_exists' => 'This email is already registered. Please log in.',
        'terms_not_accepted' => 'You must read and accept the Terms & Agreement before creating an account.',
        default => '',
    };
    $acceptedTerms = (bool) ($termsAccepted ?? false);
    $serviceTypes = config('service_marketplace.service_types', []);
@endphp
@extends('layouts.app')

@section('content')
<div class="container page-shell-narrow">
    <div class="card"><div class="card-body">
        <h2 class="text-center title-reset">Create Your Account</h2>
        <p class="text-center text-muted">Join NaijaBuilders and get started</p>

        @if ($message !== '')
            <div class="alert alert-danger">{{ $message }}</div>
        @endif
        @if (session('terms_accepted_success'))
            <div class="alert alert-success">{{ session('terms_accepted_success') }}</div>
        @endif

        <div class="grid-3 gap-md signup-role-choice">
            <label class="signup-role-option" id="builderLabel">
                <input type="radio" name="account_type" value="builder" {{ ($accountType ?? 'builder') === 'builder' ? 'checked' : '' }}>
                <strong>Builder/Buyer</strong>
                <p class="signup-role-meta">Purchase materials</p>
            </label>
            <label class="signup-role-option" id="supplierLabel">
                <input type="radio" name="account_type" value="supplier" {{ ($accountType ?? 'builder') === 'supplier' ? 'checked' : '' }}>
                <strong>Supplier</strong>
                <p class="signup-role-meta">Sell materials</p>
            </label>
            <label class="signup-role-option" id="serviceProviderLabel">
                <input type="radio" name="account_type" value="service_provider" {{ ($accountType ?? 'builder') === 'service_provider' ? 'checked' : '' }}>
                <strong>Offer Services</strong>
                <p class="signup-role-meta">Architects, engineers, trades</p>
            </label>
        </div>

        <form method="POST" action="/auth/register.php">
            @csrf
            <input type="hidden" id="accountTypeHidden" name="account_type" value="{{ $accountType ?? 'builder' }}">
            <div class="form-group"><label for="name">Full Name</label><input type="text" id="name" name="name" required value="{{ old('name') }}" placeholder="John Doe"></div>
            <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="you@example.com"></div>
            <div class="form-group"><label for="phone">Phone Number</label><input type="tel" id="phone" name="phone" required value="{{ old('phone') }}" placeholder="+234 (0) 123 456 7890"></div>
            <div class="form-group">
                <label for="location">Location (State)</label>
                <select id="location" name="location" required>
                    <option value="">Select State</option><option {{ old('location') === 'Lagos' ? 'selected' : '' }}>Lagos</option><option {{ old('location') === 'Abuja' ? 'selected' : '' }}>Abuja</option><option {{ old('location') === 'Portharcourt' ? 'selected' : '' }}>Portharcourt</option><option {{ old('location') === 'Ibadan' ? 'selected' : '' }}>Ibadan</option><option {{ old('location') === 'Kano' ? 'selected' : '' }}>Kano</option><option {{ old('location') === 'Benin City' ? 'selected' : '' }}>Benin City</option><option {{ old('location') === 'Calabar' ? 'selected' : '' }}>Calabar</option><option {{ old('location') === 'Enugu' ? 'selected' : '' }}>Enugu</option><option {{ old('location') === 'Owerri' ? 'selected' : '' }}>Owerri</option><option {{ old('location') === 'Oyo' ? 'selected' : '' }}>Oyo</option>
                </select>
            </div>
            <div id="supplierFields" class="signup-supplier-note" style="display: none;">
                <h4 style="margin: 0 0 0.6rem;">Supplier KYC Happens After Signup</h4>
                <p style="margin: 0;"><span aria-hidden="true" style="color: #f5b301;">&#9888;</span> <span class="sr-only">Caution:</span> Create your account first. After signup, your dashboard will show a KYC reminder. You must complete KYC before posting materials. <span aria-hidden="true" style="color: #f5b301;">&#9888;</span></p>
            </div>
            <div id="serviceProviderFields" class="signup-supplier-note" style="display: none;">
                <h4 style="margin: 0 0 0.6rem;">Service Provider Account</h4>
                <p style="margin: 0 0 0.8rem;">Create your account first. Your dashboard will use the supplier workspace, and NaijaBuilders can route service enquiries to you after review.</p>
                <div class="grid-2 gap-md">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="service_category">Main service</label>
                        <select id="service_category" name="service_category">
                            <option value="">Select service</option>
                            @foreach ($serviceTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('service_category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="service_areas">Service areas</label>
                        <input type="text" id="service_areas" name="service_areas" value="{{ old('service_areas') }}" placeholder="Lagos, Abuja, Ogun">
                    </div>
                </div>
            </div>
            <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" required minlength="8"></div>
            <div class="form-group"><label for="confirm_password">Confirm Password</label><input type="password" id="confirm_password" name="confirm_password" required></div>
            <div class="signup-terms-panel">
                <p>
                    Step required: Read and accept the Terms & Agreement before creating your account.
                </p>
                <a id="termsAgreementLink" href="/terms-and-agreement.php" style="display: inline-block; margin-top: 0.2rem; font-weight: 600;">Terms & Agreement</a>
                @if ($acceptedTerms)
                    <p style="margin: 0; color: var(--accent-success);" class="text-strong">Terms accepted. You can continue.</p>
                @else
                    <p style="margin: 0.5rem 0 0;" class="text-note">Create Account stays locked until you reach the end and accept on the Terms page.</p>
                @endif
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg signup-submit" style="{{ $acceptedTerms ? '' : 'opacity: 0.65; cursor: not-allowed;' }}" {{ $acceptedTerms ? '' : 'disabled aria-disabled=true' }}>Create Account</button>
        </form>

        <div class="card-divider-top">
            <p style="margin-bottom: 0;">Already have an account? <a href="/login.php" class="text-strong">Sign in</a></p>
        </div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
    const signupDraftKey = 'signupDraft';
    const signupForm = document.querySelector('form[action="/auth/register.php"]');
    const termsAgreementLink = document.getElementById('termsAgreementLink');

    const supplierFields = document.getElementById('supplierFields');
    const serviceProviderFields = document.getElementById('serviceProviderFields');
    const toggleSupplierFields = function (accountType) {
        const isSupplier = accountType === 'supplier';
        const isServiceProvider = accountType === 'service_provider';

        if (supplierFields) {
            supplierFields.style.display = isSupplier ? 'block' : 'none';
        }
        if (serviceProviderFields) {
            serviceProviderFields.style.display = isServiceProvider ? 'block' : 'none';
        }
    };

    const readSignupDraft = function () {
        try {
            const raw = sessionStorage.getItem(signupDraftKey);
            return raw ? JSON.parse(raw) : null;
        } catch (error) {
            return null;
        }
    };

    const writeSignupDraft = function (draft) {
        try {
            sessionStorage.setItem(signupDraftKey, JSON.stringify(draft));
        } catch (error) {
            // Ignore storage failures (private mode or quota issues).
        }
    };

    const collectSignupDraft = function () {
        const accountTypeHidden = document.getElementById('accountTypeHidden');
        const accountTypeValue = accountTypeHidden ? accountTypeHidden.value : 'builder';
        return {
            name: (document.getElementById('name') || {}).value || '',
            email: (document.getElementById('email') || {}).value || '',
            phone: (document.getElementById('phone') || {}).value || '',
            location: (document.getElementById('location') || {}).value || '',
            accountType: accountTypeValue,
        };
    };

    const applySignupDraft = function (draft) {
        if (!draft) {
            return;
        }

        const nameField = document.getElementById('name');
        const emailField = document.getElementById('email');
        const phoneField = document.getElementById('phone');
        const locationField = document.getElementById('location');
        const accountTypeHidden = document.getElementById('accountTypeHidden');

        if (nameField && !nameField.value) {
            nameField.value = draft.name || '';
        }
        if (emailField && !emailField.value) {
            emailField.value = draft.email || '';
        }
        if (phoneField && !phoneField.value) {
            phoneField.value = draft.phone || '';
        }
        if (locationField && !locationField.value) {
            locationField.value = draft.location || '';
        }

        if (draft.accountType && accountTypeHidden) {
            const accountTypeRadio = document.querySelector('input[name="account_type"][value="' + draft.accountType + '"]');
            if (accountTypeRadio) {
                accountTypeRadio.checked = true;
                accountTypeRadio.dispatchEvent(new Event('change'));
            } else {
                accountTypeHidden.value = draft.accountType;
            }
        }
    };

    document.querySelectorAll('input[name="account_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const accountTypeHidden = document.getElementById('accountTypeHidden');
            if (accountTypeHidden) accountTypeHidden.value = this.value;
            toggleSupplierFields(this.value);
            document.querySelectorAll('label[id$="Label"]').forEach(label => {
                label.style.borderColor = 'var(--neutral-300)';
                label.style.backgroundColor = 'transparent';
            });
            const activeLabelMap = {
                builder: document.getElementById('builderLabel'),
                supplier: document.getElementById('supplierLabel'),
                service_provider: document.getElementById('serviceProviderLabel'),
            };
            const active = activeLabelMap[this.value] || document.getElementById('builderLabel');
            if (active) {
                active.style.borderColor = 'var(--primary-color)';
                active.style.backgroundColor = 'var(--primary-light)';
            }
        });
    });
    const checked = document.querySelector('input[name="account_type"]:checked');
    if (checked) {
        checked.dispatchEvent(new Event('change'));
    } else {
        toggleSupplierFields('builder');
    }

    applySignupDraft(readSignupDraft());

    if (termsAgreementLink) {
        termsAgreementLink.addEventListener('click', function () {
            writeSignupDraft(collectSignupDraft());
        });
    }

    if (signupForm) {
        signupForm.addEventListener('submit', function () {
            sessionStorage.removeItem(signupDraftKey);
        });
    }
</script>
@endpush
