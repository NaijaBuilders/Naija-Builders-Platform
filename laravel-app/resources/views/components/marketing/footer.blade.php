<footer class="nb-marketing-footer">
    <div class="nb-footer-cta nb-animated-bg nb-grid-bg">
        <span class="nb-glow-layer nb-glow-layer-one" aria-hidden="true"></span>
        <span class="nb-glow-layer nb-glow-layer-two" aria-hidden="true"></span>
        <div class="nb-container nb-footer-cta-inner nb-section-reveal" data-reveal>
            <p class="nb-eyebrow">Built for Nigerian construction teams</p>
            <h2>Get early updates before NaijaBuilders launches.</h2>
            <p>Join the waitlist for launch notes, supplier onboarding updates, and app availability on iOS and Android.</p>
            <a class="nb-btn nb-btn-primary nb-btn-lg nb-shine nb-magnetic" href="{{ route('marketing.contact') }}#waitlist">Join the Waitlist</a>
        </div>
    </div>

    <div class="nb-container nb-footer-grid">
        <div>
            <a class="nb-footer-brand" href="{{ route('marketing.home') }}">
                <img src="{{ asset('assets/images/logo.png') }}" alt="NaijaBuilders logo" width="42" height="42" loading="lazy" decoding="async">
                <span>NaijaBuilders</span>
            </a>
            <p>NaijaBuilders is a Nigerian construction materials marketplace connecting builders, contractors, developers, suppliers, and construction service providers.</p>
        </div>

        <div>
            <h3>Explore</h3>
            <a href="{{ route('marketing.about') }}">About</a>
            <a href="{{ route('marketing.how-it-works') }}">How It Works</a>
            <a href="{{ route('marketing.buyers') }}">For Buyers</a>
            <a href="{{ route('marketing.suppliers') }}">For Suppliers</a>
        </div>

        <div>
            <h3>Launch</h3>
            <a href="{{ route('marketing.services') }}">Services</a>
            <a href="{{ route('marketing.app') }}">App Coming Soon</a>
            <a href="{{ route('marketing.faq') }}">FAQ</a>
            <a href="{{ route('marketing.contact') }}">Contact</a>
        </div>

        <div>
            <h3>Legal</h3>
            <a href="{{ route('marketing.privacy') }}">Privacy Policy</a>
            <a href="{{ route('marketing.terms') }}">Terms of Use</a>
            <a href="mailto:info@naijabuilders.com">info@naijabuilders.com</a>
        </div>
    </div>

    <div class="nb-footer-bottom">
        <div class="nb-container">
            <span>&copy; {{ date('Y') }} NaijaBuilders. All rights reserved.</span>
            <a href="/index.php">Existing marketplace</a>
        </div>
    </div>
</footer>
