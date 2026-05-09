@php($pageTitle = 'Home')
@extends('layouts.app')

@section('meta_description', 'Source construction materials from trusted Nigerian suppliers, compare listings, and manage buyer or supplier workflows on NaijaBuilders.')

@section('content')
<section id="home" class="hero">
    <div class="container hero-container">
        <div class="hero-content">
            <p class="subtitle">The Nigerian Construction Materials Marketplace</p>
            <h1>Build Better. Faster. Together.</h1>
            <p>
                Connect with trusted local suppliers of cement, steel, wood, finishes and other construction materials across Nigeria.
                Streamline your sourcing, verify quality, and scale your projects with confidence.
            </p>
            <div class="hero-actions">
                <a href="/materials.php" class="btn btn-primary btn-lg">Browse Materials</a>
                <a href="/support.php" class="btn btn-outline btn-lg">Request Sourcing Help</a>
                <a href="/signup.php" class="btn btn-secondary btn-lg">Become a Supplier</a>
            </div>
        </div>
        <div class="hero-image" aria-hidden="true"></div>
    </div>
</section>

<section class="value-section">
    <div class="container">
        <div class="value-header">
            <p class="value-eyebrow">Why Choose NaijaBuilders?</p>
            <h2>Built for Professional Construction Teams</h2>
            <p class="value-lead">From supplier verification to delivery and post-order support, NaijaBuilders helps projects move faster with less risk and better cost control.</p>
        </div>

        <div class="value-layout" id="why-choose-layout">
            <article class="value-card value-highlight value-card--trust" id="value-highlight-card">
                <div class="value-card-media" aria-hidden="true"></div>
                <div class="value-card-body">
                    <p class="value-kicker" id="value-highlight-kicker">Trust & Reliability</p>
                    <h3 id="value-highlight-title">Verified Suppliers You Can Depend On</h3>
                    <p id="value-highlight-description">All our suppliers are thoroughly vetted and verified to ensure quality, reliability, and consistency across every order.</p>
                    <ul class="value-points" id="value-highlight-points">
                        <li>Structured supplier onboarding and verification</li>
                        <li>Quality-focused listings and transparent profiles</li>
                        <li>Built to support projects of all sizes across Nigeria</li>
                    </ul>
                </div>
            </article>

            <div class="value-stack">
                <article class="value-card value-card--compact value-card--trust js-value-option is-active" role="button" tabindex="0" aria-label="Show Verified Suppliers" data-theme="trust" data-kicker="Trust & Reliability" data-title="Verified Suppliers You Can Depend On" data-description="All our suppliers are thoroughly vetted and verified to ensure quality, reliability, and consistency across every order." data-point-one="Structured supplier onboarding and verification" data-point-two="Quality-focused listings and transparent profiles" data-point-three="Built to support projects of all sizes across Nigeria">
                    <div class="value-card-media" aria-hidden="true"></div>
                    <div class="value-card-body"><p class="value-kicker">Trust & Reliability</p><h3>Verified Suppliers</h3><p>Work with thoroughly vetted suppliers for consistent project delivery.</p></div>
                </article>
                <article class="value-card value-card--compact value-card--security js-value-option" role="button" tabindex="0" aria-label="Show Secure Transactions" data-theme="security" data-kicker="Security" data-title="Secure Transactions" data-description="Protected payments and transparent pricing with no hidden charges." data-point-one="Protected buyer and supplier transaction flow" data-point-two="Clear pricing and transparent checkout process" data-point-three="Designed to reduce payment-related risks">
                    <div class="value-card-media" aria-hidden="true"></div>
                    <div class="value-card-body"><p class="value-kicker">Security</p><h3>Secure Transactions</h3><p>Protected payments and transparent pricing with no hidden charges.</p></div>
                </article>
                <article class="value-card value-card--compact value-card--logistics js-value-option" role="button" tabindex="0" aria-label="Show Fast Delivery" data-theme="logistics" data-kicker="Logistics" data-title="Fast Delivery" data-description="Reliable logistics network ensuring timely delivery across Nigeria." data-point-one="Delivery-focused network across key locations" data-point-two="Reliable order movement from supplier to site" data-point-three="Faster project execution with predictable timelines">
                    <div class="value-card-media" aria-hidden="true"></div>
                    <div class="value-card-body"><p class="value-kicker">Logistics</p><h3>Fast Delivery</h3><p>Reliable logistics network ensuring timely delivery across Nigeria.</p></div>
                </article>
                <article class="value-card value-card--compact value-card--pricing js-value-option" role="button" tabindex="0" aria-label="Show Competitive Pricing" data-theme="pricing" data-kicker="Pricing" data-title="Competitive Pricing" data-description="Compare prices from multiple suppliers and get the best deals." data-point-one="Multi-supplier comparison in one place" data-point-two="Transparent rates to support better budgeting" data-point-three="Improved cost control for project teams">
                    <div class="value-card-media" aria-hidden="true"></div>
                    <div class="value-card-body"><p class="value-kicker">Pricing</p><h3>Competitive Pricing</h3><p>Compare prices from multiple suppliers and get the best deals.</p></div>
                </article>
                <article class="value-card value-card--compact value-card--support js-value-option" role="button" tabindex="0" aria-label="Show 24/7 Support" data-theme="support" data-kicker="Support" data-title="24/7 Support" data-description="Dedicated customer support team ready to help anytime." data-point-one="Round-the-clock support for urgent project needs" data-point-two="Practical help for orders and account issues" data-point-three="Faster issue resolution for smoother operations">
                    <div class="value-card-media" aria-hidden="true"></div>
                    <div class="value-card-body"><p class="value-kicker">Support</p><h3>24/7 Support</h3><p>Dedicated customer support team ready to help anytime.</p></div>
                </article>
                <article class="value-card value-card--compact value-card--insights js-value-option" role="button" tabindex="0" aria-label="Show Real-time Analytics" data-theme="insights" data-kicker="Insights" data-title="Real-time Analytics" data-description="Track your orders and inventory with our advanced dashboard." data-point-one="Track key supply and order metrics in real time" data-point-two="Monitor inventory movement more accurately" data-point-three="Make faster, data-informed procurement decisions">
                    <div class="value-card-media" aria-hidden="true"></div>
                    <div class="value-card-body"><p class="value-kicker">Insights</p><h3>Real-time Analytics</h3><p>Track your orders and inventory with our advanced dashboard.</p></div>
                </article>
            </div>
        </div>
    </div>
</section>

<section id="how-it-works" class="features-section">
    <div class="container">
        <h2 class="text-center mb-xl">How It Works</h2>
        <div class="feature-item">
            <div class="feature-content">
                <h3>For Builders & Buyers</h3>
                <p>Get access to the biggest marketplace of construction materials in Nigeria.</p>
                <ul class="feature-list">
                    <li>Browse thousands of materials from trusted suppliers</li>
                    <li>Compare prices and quality instantly</li>
                    <li>Place orders and track delivery in real-time</li>
                    <li>Secure payment options and buyer protection</li>
                    <li>Build your project timeline with bulk ordering</li>
                </ul>
                <a href="/materials.php" class="btn btn-primary">Start Browsing</a>
            </div>
            <div class="feature-image feature-image--buyers"><span class="feature-image-label">For Buyers</span></div>
        </div>

        <div class="feature-item">
            <div class="feature-content">
                <h3>For Suppliers & Distributors</h3>
                <p>Grow your business by reaching builders and projects across Nigeria.</p>
                <ul class="feature-list">
                    <li>List your materials to millions of potential buyers</li>
                    <li>Manage inventory and orders from one dashboard</li>
                    <li>Build your reputation with verified reviews</li>
                    <li>Access real-time market data and analytics</li>
                    <li>Expand your business without heavy marketing costs</li>
                </ul>
                <a href="/signup.php" class="btn btn-primary">Get Started</a>
            </div>
            <div class="feature-image feature-image--suppliers"><span class="feature-image-label">For Suppliers</span></div>
        </div>
    </div>
</section>

<section id="about" class="value-section">
    <div class="container">
        <h2 class="text-center mb-xl">About NaijaBuilders</h2>
        <div class="grid-2 gap-xl">
            <div><h3>Our Mission</h3><p>To revolutionize the construction materials supply chain in Nigeria by creating a transparent, efficient, and scalable marketplace that connects builders with trusted suppliers.</p></div>
            <div><h3>Our Vision</h3><p>To become Africa's leading construction materials marketplace, enabling seamless transactions and partnerships across the continent.</p></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        var section = document.getElementById('why-choose-layout');
        if (!section) {
            return;
        }

        var highlightCard = document.getElementById('value-highlight-card');
        var highlightKicker = document.getElementById('value-highlight-kicker');
        var highlightTitle = document.getElementById('value-highlight-title');
        var highlightDescription = document.getElementById('value-highlight-description');
        var highlightPoints = document.getElementById('value-highlight-points');
        var optionCards = section.querySelectorAll('.js-value-option');
        var themes = ['trust', 'security', 'logistics', 'pricing', 'support', 'insights'];

        var setHighlightTheme = function (theme) {
            themes.forEach(function (currentTheme) {
                highlightCard.classList.remove('value-card--' + currentTheme);
            });

            if (theme) {
                highlightCard.classList.add('value-card--' + theme);
            }
        };

        var setHighlightPoints = function (card) {
            highlightPoints.innerHTML = '';

            var points = [card.dataset.pointOne, card.dataset.pointTwo, card.dataset.pointThree].filter(Boolean);
            points.forEach(function (point) {
                var listItem = document.createElement('li');
                listItem.textContent = point;
                highlightPoints.appendChild(listItem);
            });
        };

        var activateCard = function (card) {
            if (!card) {
                return;
            }

            optionCards.forEach(function (optionCard) {
                optionCard.classList.toggle('is-active', optionCard === card);
            });

            setHighlightTheme(card.dataset.theme || 'trust');
            highlightKicker.textContent = card.dataset.kicker || '';
            highlightTitle.textContent = card.dataset.title || '';
            highlightDescription.textContent = card.dataset.description || '';
            setHighlightPoints(card);
        };

        optionCards.forEach(function (card) {
            card.addEventListener('click', function () {
                activateCard(card);
            });

            card.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    activateCard(card);
                }
            });
        });

        var defaultCard = section.querySelector('.js-value-option[data-theme="trust"]') || optionCards[0];
        activateCard(defaultCard);
    })();
</script>
@endpush
