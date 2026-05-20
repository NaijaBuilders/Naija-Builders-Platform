@php($pageTitle = 'Settings')
@extends('layouts.app')

@section('content')
<div class="container settings-page">
    <div class="settings-page__header">
        <div class="settings-page__intro">
            <h1>Application Settings</h1>
            <p>Manage your account preferences and settings.</p>
        </div>
        <a href="/edit-profile.php" class="btn btn-outline settings-page__header-btn">Edit Profile</a>
    </div>

    @php($isSupplier = (string) session('legacy_user.role', '') === 'supplier')
    @php($supplierKycStatus = (string) session('legacy_user.kyc_status', 'approved'))
    @php($isBuyer = !$isSupplier)

    @if (($successCode ?? '') === 'settings_saved')
        <div class="alert alert-success settings-page__success" data-auto-fade="3000">Settings updated successfully.</div>
    @endif

    {{-- KYC Section (Suppliers only) --}}
    @if ($isSupplier)
        <div class="card settings-card settings-card--kyc">
            <div class="card-header settings-card__header settings-card__header--kyc">
                <div class="settings-card__head-row">
                    <div>
                        <h3>KYC Verification</h3>
                        <p>Business verification required to post materials</p>
                    </div>
                    <div class="settings-card__badge-wrap">
                        <span class="settings-status-badge" style="background: {{ $supplierKycStatus === 'approved' ? 'rgba(8, 179, 120, 0.15)' : ($supplierKycStatus === 'submitted' ? 'rgba(243, 156, 18, 0.15)' : 'rgba(14, 28, 51, 0.1)') }}; color: {{ $supplierKycStatus === 'approved' ? 'var(--secondary-color)' : ($supplierKycStatus === 'submitted' ? 'var(--accent-warning)' : 'var(--neutral-600)') }};">
                            {{ ucfirst($supplierKycStatus) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <ul class="settings-list">
                    <li class="settings-item">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Update Your KYC Information</p>
                            <p class="settings-item__desc">Complete or update your business verification details</p>
                        </div>
                        <a href="/supplier-kyc.php" class="btn btn-primary settings-item__action">
                            {{ $supplierKycStatus === 'approved' ? 'Review' : 'Complete' }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    @endif

    @if ($isBuyer)
        <div class="card settings-card settings-card--kyc">
            <div class="card-header settings-card__header settings-card__header--kyc">
                <div class="settings-card__head-row">
                    <div>
                        <h3>Buyer Verification</h3>
                        <p>Optional one-time ID check for larger orders</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <ul class="settings-list">
                    <li class="settings-item">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Complete KYC Once</p>
                            <p class="settings-item__desc">You can keep browsing without ID. If you verify now, we can reuse it when an order needs verification.</p>
                        </div>
                        <a href="/buyer-kyc.php" class="btn btn-primary settings-item__action">Open KYC</a>
                    </li>
                </ul>
            </div>
        </div>
    @endif

    {{-- Settings Form --}}
    <form method="POST" action="/settings.php">
        @csrf

        {{-- Notifications Settings --}}
        <div class="card settings-card">
            <div class="card-header">
                <h3>Notifications</h3>
            </div>
            <div class="card-body">
                <ul class="settings-list">
                    <li class="settings-item settings-item--has-divider">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Email Notifications</p>
                            <p class="settings-item__desc">Receive email alerts for messages and updates</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="notifications_email" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="notifications_email" value="1" {{ !empty($settings['notifications_email']) ? 'checked' : '' }}>
                        </label>
                    </li>
                    <li class="settings-item settings-item--has-divider">
                        <div class="settings-item__content">
                            <p class="settings-item__title">SMS Notifications</p>
                            <p class="settings-item__desc">Get text messages for important account activity</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="notifications_sms" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="notifications_sms" value="1" {{ !empty($settings['notifications_sms']) ? 'checked' : '' }}>
                        </label>
                    </li>
                    <li class="settings-item">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Push Notifications</p>
                            <p class="settings-item__desc">Receive browser notifications for real-time updates</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="notifications_push" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="notifications_push" value="1" {{ !empty($settings['notifications_push']) ? 'checked' : '' }}>
                        </label>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Regional Preferences --}}
        <div class="card settings-card">
            <div class="card-header">
                <h3>Regional Preferences</h3>
            </div>
            <div class="card-body">
                <ul class="settings-list">
                    <li class="settings-item settings-item--has-divider settings-item--field">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Language</p>
                            <p class="settings-item__desc">Choose your preferred language</p>
                            </div>
                            <select class="settings-select" name="language">
                                <option value="en" {{ ($settings['language'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                <option value="ha" {{ ($settings['language'] ?? '') === 'ha' ? 'selected' : '' }}>Hausa</option>
                                <option value="yo" {{ ($settings['language'] ?? '') === 'yo' ? 'selected' : '' }}>Yoruba</option>
                                <option value="ig" {{ ($settings['language'] ?? '') === 'ig' ? 'selected' : '' }}>Igbo</option>
                            </select>
                    </li>
                    <li class="settings-item settings-item--has-divider settings-item--field">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Timezone</p>
                            <p class="settings-item__desc">Set your preferred timezone</p>
                            </div>
                            <select class="settings-select" name="timezone">
                                <option value="Africa/Lagos" {{ ($settings['timezone'] ?? 'Africa/Lagos') === 'Africa/Lagos' ? 'selected' : '' }}>Africa/Lagos (WAT)</option>
                                <option value="UTC" {{ ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="Europe/London" {{ ($settings['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                <option value="America/New_York" {{ ($settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                            </select>
                    </li>
                    <li class="settings-item settings-item--has-divider settings-item--field">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Currency Detection</p>
                            <p class="settings-item__desc">Automatically show prices in your local currency when your location can be detected</p>
                        </div>
                        <select class="settings-select" name="currency_mode" id="currency_mode">
                            <option value="auto" {{ ($settings['currency_mode'] ?? 'auto') === 'auto' ? 'selected' : '' }}>Auto-detect from location</option>
                            <option value="manual" {{ ($settings['currency_mode'] ?? 'auto') === 'manual' ? 'selected' : '' }}>Manual selection</option>
                        </select>
                    </li>
                    <li class="settings-item settings-item--field">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Manual Currency</p>
                            <p class="settings-item__desc">Used when manual selection is enabled, or when auto-detection is unavailable</p>
                        </div>
                        <input type="hidden" name="detected_timezone" id="detected_timezone" value="{{ $settings['detected_timezone'] ?? '' }}">
                        <select class="settings-select" name="currency">
                            @foreach (($supportedCurrencies ?? []) as $currencyCode => $currencyDetails)
                                <option value="{{ $currencyCode }}" {{ ($settings['currency'] ?? 'NGN') === $currencyCode ? 'selected' : '' }}>
                                    {{ $currencyDetails['name'] }} ({{ $currencyCode }})
                                </option>
                            @endforeach
                        </select>
                    </li>
                </ul>
            </div>
        </div>

        {{-- General Experience --}}
        <div class="card settings-card">
            <div class="card-header">
                <h3>General Experience</h3>
            </div>
            <div class="card-body">
                <ul class="settings-list">
                    <li class="settings-item settings-item--has-divider">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Auto-save Message Drafts</p>
                            <p class="settings-item__desc">Automatically save your message drafts</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="auto_save_drafts" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="auto_save_drafts" value="1" {{ !empty($settings['auto_save_drafts']) ? 'checked' : '' }}>
                        </label>
                    </li>
                    <li class="settings-item settings-item--has-divider">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Compact Dashboard</p>
                            <p class="settings-item__desc">Use compact widgets on the dashboard</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="compact_dashboard" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="compact_dashboard" value="1" {{ !empty($settings['compact_dashboard']) ? 'checked' : '' }}>
                        </label>
                    </li>
                    <li class="settings-item settings-item--has-divider">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Material Stock Alerts</p>
                            <p class="settings-item__desc">Get alerts when stock runs low</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="material_alerts" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="material_alerts" value="1" {{ !empty($settings['material_alerts']) ? 'checked' : '' }}>
                        </label>
                    </li>
                    <li class="settings-item">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Message Notification Sound</p>
                            <p class="settings-item__desc">Play sound when you receive messages</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="message_sound" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="message_sound" value="1" {{ !empty($settings['message_sound']) ? 'checked' : '' }}>
                        </label>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Advanced Settings --}}
        <div class="card settings-card">
            <div class="card-header settings-card__header--muted">
                <h3>Advanced Settings</h3>
            </div>
            <div class="card-body">
                <p class="settings-section-note">Optional power-user controls for advanced users.</p>
                <ul class="settings-list">
                    <li class="settings-item settings-item--has-divider">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Two-factor Login</p>
                            <p class="settings-item__desc">Require two-factor authentication for login</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="two_factor_login" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="two_factor_login" value="1" {{ !empty($settings['two_factor_login']) ? 'checked' : '' }}>
                        </label>
                    </li>
                    <li class="settings-item settings-item--has-divider">
                        <div class="settings-item__content">
                            <p class="settings-item__title">API Access Mode</p>
                            <p class="settings-item__desc">Enable API access to your account</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="api_access" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="api_access" value="1" {{ !empty($settings['api_access']) ? 'checked' : '' }}>
                        </label>
                    </li>
                    <li class="settings-item settings-item--has-divider">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Developer Mode</p>
                            <p class="settings-item__desc">Enable developer tools and debug information</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="developer_mode" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="developer_mode" value="1" {{ !empty($settings['developer_mode']) ? 'checked' : '' }}>
                        </label>
                    </li>
                    <li class="settings-item settings-item--has-divider">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Beta Features</p>
                            <p class="settings-item__desc">Opt-in to upcoming beta features</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="beta_features" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="beta_features" value="1" {{ !empty($settings['beta_features']) ? 'checked' : '' }}>
                        </label>
                    </li>
                    <li class="settings-item settings-item--has-divider">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Activity Logs</p>
                            <p class="settings-item__desc">View expanded account activity history</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="activity_logs" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="activity_logs" value="1" {{ !empty($settings['activity_logs']) ? 'checked' : '' }}>
                        </label>
                    </li>
                    <li class="settings-item">
                        <div class="settings-item__content">
                            <p class="settings-item__title">Short Session Timeout</p>
                            <p class="settings-item__desc">Logout after 15 minutes of inactivity</p>
                        </div>
                        <label class="settings-toggle">
                            <input type="hidden" name="session_timeout_short" value="0">
                            <input class="settings-toggle__input" type="checkbox" name="session_timeout_short" value="1" {{ !empty($settings['session_timeout_short']) ? 'checked' : '' }}>
                        </label>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="settings-actions">
            <a href="/dashboard.php" class="btn btn-outline settings-actions__btn">Back</a>
            <button type="submit" class="btn btn-primary settings-actions__btn" onclick="showSavingMessage()">Save Settings</button>
        </div>
    </form>
</div>

<script>
    (function () {
        var detectedTimezoneInput = document.getElementById('detected_timezone');
        if (!detectedTimezoneInput || detectedTimezoneInput.value) {
            return;
        }

        try {
            detectedTimezoneInput.value = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (error) {
            detectedTimezoneInput.value = '';
        }
    })();

    function showSavingMessage() {
        showNotification('Saving your settings...', 'info', 3000);
    }
</script>
@endsection
