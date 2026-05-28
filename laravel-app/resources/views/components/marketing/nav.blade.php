@php
    $links = [
        ['label' => 'Home', 'route' => 'marketing.home'],
        ['label' => 'About', 'route' => 'marketing.about'],
        ['label' => 'How It Works', 'route' => 'marketing.how-it-works'],
        ['label' => 'Buyers', 'route' => 'marketing.buyers'],
        ['label' => 'Suppliers', 'route' => 'marketing.suppliers'],
        ['label' => 'Services', 'route' => 'marketing.services'],
        ['label' => 'App', 'route' => 'marketing.app'],
        ['label' => 'FAQ', 'route' => 'marketing.faq'],
        ['label' => 'Contact', 'route' => 'marketing.contact'],
    ];
@endphp
<header class="nb-marketing-nav" data-marketing-nav>
    <div class="nb-nav-shell">
        <a class="nb-brand" href="{{ route('marketing.home') }}" aria-label="NaijaBuilders Coming Soon Home">
            <span class="nb-brand-logo">
                <img src="{{ asset('assets/images/logo.png') }}" alt="NaijaBuilders logo" width="34" height="34" decoding="async">
            </span>
            <span class="nb-brand-copy">
                <span>NaijaBuilders</span>
                <small>Marketplace launch</small>
            </span>
        </a>

        <nav class="nb-nav-links" aria-label="Coming soon navigation">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}" class="{{ request()->routeIs($link['route']) ? 'is-active' : '' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="nb-nav-actions">
            <button class="nb-theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme">
                <span class="nb-theme-toggle-track" aria-hidden="true">
                    <span class="nb-theme-toggle-thumb"></span>
                </span>
            </button>
            <a class="nb-btn nb-btn-primary nb-btn-sm nb-shine nb-magnetic" href="{{ route('marketing.contact') }}#waitlist">
                Join Waitlist
            </a>
            <button class="nb-mobile-toggle" type="button" data-mobile-menu-toggle aria-label="Open navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>

    <div class="nb-mobile-menu" data-mobile-menu>
        <nav aria-label="Mobile coming soon navigation">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}" class="{{ request()->routeIs($link['route']) ? 'is-active' : '' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a class="nb-btn nb-btn-primary nb-shine" href="{{ route('marketing.contact') }}#waitlist">Join Waitlist</a>
        </nav>
    </div>
</header>
