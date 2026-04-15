@php
    $legacyUser = session('legacy_user');
    $legacyUserRole = (string) ($legacyUser['role'] ?? '');
    $isSupplier = $legacyUserRole === 'supplier';
    $supplierKycStatus = (string) ($legacyUser['kyc_status'] ?? 'approved');
    if ($supplierKycStatus === '') {
        $supplierKycStatus = 'approved';
    }
    $supplierKycApproved = $supplierKycStatus === 'approved';
    $dashboardPath = $isSupplier ? '/dashboard.php' : '/buyer-dashboard.php';
    $isVerifiedUser = (bool) ($legacyUser['is_verified_badge'] ?? false);
    $subscriptionPlanCode = (string) ($legacyUser['subscription_plan'] ?? 'standard');
    $subscriptionPlanLabel = $isSupplier
        ? ($subscriptionPlanCode === 'enterprise' ? 'Enterprise' : ($subscriptionPlanCode === 'pro' ? 'Pro' : 'Standard'))
        : ($subscriptionPlanCode === 'enterprise' ? 'Buyer Enterprise' : ($subscriptionPlanCode === 'pro' ? 'Buyer Pro' : 'Buyer Standard'));
    $themeFromCookie = (string) request()->cookie('naijabuilders-theme', '');
    if (!in_array($themeFromCookie, ['dark', 'light'], true)) {
        $themeFromCookie = '';
    }
    $cssVersion = @filemtime(public_path('assets/css/professional.css')) ?: 1;
    $jsVersion = @filemtime(public_path('assets/js/app.js')) ?: 1;
    $cartItems = (array) session('cart', []);
    $cartCount = 0;
    foreach ($cartItems as $entry) {
        $cartCount += max(1, (int) ($entry['quantity'] ?? 1));
    }
    $shouldRunOnboardingTour = (bool) session()->pull('show_onboarding_tour', false);
@endphp
<!DOCTYPE html>
<html lang="en" @if ($themeFromCookie !== '') data-theme="{{ $themeFromCookie }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="NaijaBuilders - Nigerian Construction Materials Marketplace connecting builders, developers and homeowners with trusted local suppliers.">
    <meta name="keywords" content="construction materials, cement, steel, wood, Nigeria, builders, suppliers">
    <meta name="author" content="NaijaBuilders Team">
    <meta property="og:title" content="NaijaBuilders – Nigerian Construction Materials Marketplace">
    <meta property="og:description" content="Connect with trusted construction material suppliers across Nigeria">
    <meta property="og:type" content="website">
    <script>
        (function () {
            var theme = null;

            var readThemeFromCookie = function () {
                var cookieMatch = document.cookie.match(/(?:^|; )naijabuilders-theme=([^;]+)/);
                return cookieMatch ? decodeURIComponent(cookieMatch[1]) : null;
            };

            try {
                var savedTheme = localStorage.getItem('naijabuilders-theme');
                if (savedTheme === 'dark' || savedTheme === 'light') {
                    theme = savedTheme;
                }
            } catch (error) {
                // Ignore storage errors
            }

            if (theme !== 'dark' && theme !== 'light') {
                var cookieTheme = readThemeFromCookie();
                if (cookieTheme === 'dark' || cookieTheme === 'light') {
                    theme = cookieTheme;
                }
            }

            if (theme === 'dark' || theme === 'light') {
                document.documentElement.setAttribute('data-theme', theme);
            }
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('assets/css/professional.css') }}?v={{ $cssVersion }}">
    <title>{{ isset($pageTitle) ? $pageTitle . ' | NaijaBuilders' : 'NaijaBuilders – Nigerian Construction Materials Marketplace' }}</title>
</head>
<body @if ($shouldRunOnboardingTour) data-start-onboarding-tour="1" @endif>
<header class="navbar">
    <div class="navbar-container">
        <button class="menu-trigger" id="sideMenuToggle" type="button" aria-label="Open menu" aria-controls="sideMenu" aria-expanded="false" data-tour-id="menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="navbar-brand">
            <a href="/index.php" class="logo" aria-label="NaijaBuilders Home">
                @if (file_exists(public_path('assets/images/logo.png')))
                    <img src="{{ asset('assets/images/logo.png') }}" alt="NaijaBuilders logo" class="logo-image">
                @elseif (file_exists(public_path('assets/images/logo.jpeg')))
                    <img src="{{ asset('assets/images/logo.jpeg') }}" alt="NaijaBuilders logo" class="logo-image">
                @else
                    <span class="logo-mark" aria-hidden="true"></span>
                @endif
            </a>
        </div>

        <div class="navbar-search" id="navbarMenu">
            <form method="GET" action="/materials.php" class="navbar-search-form" role="search" aria-label="Search materials" data-tour-id="search">
                <label for="navbar-search-input" class="sr-only">Search materials</label>
                <input
                    id="navbar-search-input"
                    type="search"
                    name="search"
                    class="navbar-search-input"
                    placeholder="Search materials"
                    value="{{ request()->query('search', '') }}"
                    list="navbar-material-suggestions"
                    autocomplete="off"
                >
                <button type="submit" class="navbar-search-button">Search</button>
                <datalist id="navbar-material-suggestions">
                    @foreach (($navbarMaterialSuggestions ?? []) as $suggestedMaterial)
                        <option value="{{ $suggestedMaterial }}"></option>
                    @endforeach
                </datalist>
            </form>
        </div>

        <div class="navbar-actions">
            @if ($isSupplier)
                <a href="/analysis.php" class="top-icon" aria-label="Analysis" data-tour-id="primary-action">
                    <span class="top-icon-mark">📊</span>
                </a>
            @else
                <a href="/cart.php" class="top-icon top-cart" aria-label="Cart" data-tour-id="primary-action">
                    <span class="top-icon-mark">🛒</span>
                    <span class="top-badge">{{ $cartCount }}</span>
                </a>
            @endif

            @if ($legacyUser)
                <a href="/saved-products" class="top-icon" aria-label="Saved products">
                    <span class="top-icon-mark">⭐</span>
                </a>
            @endif

            @if ($legacyUser)
                <div class="nav-user-menu">
                    <button class="btn-user-profile" type="button" aria-haspopup="true" aria-expanded="false" aria-label="Profile menu" style="position: relative;" data-tour-id="profile-menu">
                        @if (($legacyUser['profile_image_path'] ?? '') !== '')
                            <img
                                src="/{{ $legacyUser['profile_image_path'] }}"
                                alt="{{ $legacyUser['name'] ?? 'User' }}"
                                class="user-avatar"
                            >
                        @else
                            <span class="user-avatar">{{ strtoupper(substr($legacyUser['name'] ?? 'U', 0, 1)) }}</span>
                        @endif
                        @if ($isVerifiedUser)
                            <span title="Verified account" style="position: absolute; right: -3px; bottom: -3px; width: 18px; height: 18px; border-radius: 999px; background: var(--secondary-color); color: white; border: 2px solid white; display: inline-flex; align-items: center; justify-content: center; font-size: 0.62rem; font-weight: 700;">✓</span>
                        @endif
                    </button>
                    <div class="dropdown-menu">
                        <div class="dropdown-item" style="cursor: default; color: var(--neutral-600);">
                            Plan: {{ $subscriptionPlanLabel }}
                            @if ($isVerifiedUser)
                                <span style="display: inline-block; margin-left: 0.3rem; font-size: 0.72rem; background: var(--secondary-color); color: white; border-radius: 999px; padding: 0.12rem 0.4rem;">Verified</span>
                            @endif
                        </div>
                        <hr>
                        <a href="{{ $dashboardPath }}" class="dropdown-item">Dashboard</a>
                        @if ($isSupplier)
                            <a href="/analysis.php" class="dropdown-item">Analysis</a>
                        @endif
                        <a href="/saved-products" class="dropdown-item">Saved Products</a>
                        <a href="/messages.php" class="dropdown-item">Messages</a>
                        <a href="/subscription.php" class="dropdown-item">Subscription</a>
                        <a href="/settings.php" class="dropdown-item">Settings</a>
                        @if ($isSupplier && $supplierKycApproved)
                            <a href="/manage-listings.php" class="dropdown-item">My Listings</a>
                        @endif
                        <hr>
                        <a href="/logout.php" class="dropdown-item danger">Logout</a>
                    </div>
                </div>
            @else
                <a href="/login.php" class="btn btn-outline">Login</a>
                <a href="/signup.php" class="btn btn-primary">Sign Up</a>
            @endif
        </div>
    </div>
</header>

<div class="side-menu-backdrop" id="sideMenuBackdrop"></div>
<aside class="side-menu" id="sideMenu" aria-hidden="true">
    <div class="side-menu-header">
        <strong>Menu</strong>
        <button type="button" class="side-menu-close" id="sideMenuClose" aria-label="Close menu">×</button>
    </div>
    <div class="side-menu-search-wrap">
        <form method="GET" action="/materials.php" class="side-menu-search-form" role="search" aria-label="Search materials">
            <label for="side-menu-search-input" class="sr-only">Search materials</label>
            <input
                id="side-menu-search-input"
                type="search"
                name="search"
                class="side-menu-search-input"
                placeholder="Search materials"
                value="{{ request()->query('search', '') }}"
                list="side-menu-material-suggestions"
                autocomplete="off"
            >
            <button type="submit" class="side-menu-search-button">Search</button>
            <datalist id="side-menu-material-suggestions">
                @foreach (($navbarMaterialSuggestions ?? []) as $suggestedMaterial)
                    <option value="{{ $suggestedMaterial }}"></option>
                @endforeach
            </datalist>
        </form>
    </div>
    <nav class="side-menu-links">
        <a href="/index.php#home" class="nav-link">Home</a>
        <a href="/index.php#how-it-works" class="nav-link">How It Works</a>
        <a href="/materials.php" class="nav-link">Browse Materials</a>
        <a href="/index.php#about" class="nav-link">About</a>
        @if (!$isSupplier)
            <a href="/cart.php" class="nav-link">Cart ({{ $cartCount }})</a>
        @endif
        @if ($legacyUser)
            <a href="{{ $dashboardPath }}" class="nav-link">Dashboard</a>
            @if ($isSupplier)
                <a href="/analysis.php" class="nav-link">Analysis</a>
                <a href="/supplier-kyc.php" class="nav-link">KYC: {{ ucfirst($supplierKycStatus) }}</a>
            @endif
            <a href="/saved-products" class="nav-link">Saved Products</a>
            <a href="/messages.php" class="nav-link">Messages</a>
            <a href="/subscription.php" class="nav-link">Subscription</a>
            <a href="/settings.php" class="nav-link">Settings</a>
            @if ($isSupplier && $supplierKycApproved)
                <a href="/manage-listings.php" class="nav-link">My Listings</a>
            @elseif ($isSupplier)
                <a href="/supplier-kyc.php" class="nav-link">Complete KYC</a>
            @endif
            <a href="/logout.php" class="nav-link">Logout</a>
        @else
            <a href="/login.php" class="nav-link">Login</a>
            <a href="/signup.php" class="nav-link">Sign Up</a>
        @endif
    </nav>
</aside>

<main>
    @yield('content')
</main>

<a href="/support.php" class="support-fab" aria-label="Open support" title="Support" data-tour-id="support">
    <span class="support-fab-icons" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" role="img" focusable="false">
            <path d="M4 12a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <rect x="3" y="12" width="4" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
            <rect x="17" y="12" width="4" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 19v2.5a1.5 1.5 0 0 0 1.5 1.5H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        <svg viewBox="0 0 24 24" fill="none" role="img" focusable="false">
            <rect x="9" y="4" width="6" height="10" rx="3" stroke="currentColor" stroke-width="1.8"/>
            <path d="M6.5 11.5a5.5 5.5 0 0 0 11 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M12 17v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M9 20h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
    </span>
</a>

<button type="button" class="theme-toggle-fab" id="themeToggle" aria-label="Toggle dark mode" title="Toggle dark mode" data-tour-id="theme-toggle">
    <span id="themeToggleIcon">🌙</span>
</button>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>About NaijaBuilders</h3>
            <p>Connecting builders, developers, and homeowners with trusted construction material suppliers across Nigeria.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <a href="/index.php">Home</a>
            <a href="/materials.php">Materials</a>
            <a href="/index.php#about">About</a>
            <a href="/support.php">Contact Support</a>
        </div>
        <div class="footer-section">
            <h3>For Suppliers</h3>
            <a href="/signup.php">Become a Supplier</a>
            <a href="/dashboard.php">Dashboard</a>
            <a href="#">Pricing</a>
        </div>
        <div class="footer-section">
            <h3>Contact</h3>
            <p>Email: <a href="mailto:info@naijabuilders.com">info@naijabuilders.com</a></p>
            <p>Phone: +234 (0) 123 456 7890</p>
            <p>Location: Lagos, Nigeria</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} NaijaBuilders. All rights reserved.</p>
    </div>
</footer>

<script src="{{ asset('assets/js/app.js') }}?v={{ $jsVersion }}"></script>
@stack('scripts')
</body>
</html>
