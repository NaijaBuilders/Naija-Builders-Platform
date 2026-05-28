<x-marketing.layout
    title="For Suppliers"
    description="NaijaBuilders supplier onboarding is coming soon, helping suppliers showcase materials, reach serious buyers, and grow visibility."
>
    <x-marketing.page-hero
        eyebrow="For suppliers"
        title="Showcase construction materials to serious buyers."
        description="Supplier onboarding is opening soon for companies that want to list products, present material categories, and prepare for buyer enquiries."
        :image="asset('assets/images/home-suppliers.jpg')"
    />

    <section class="nb-section">
        <div class="nb-container nb-card-grid nb-card-grid-4">
            @foreach ([
                ['title' => 'Reach more buyers', 'copy' => 'Put your materials where builders, contractors, and developers are searching.'],
                ['title' => 'Showcase materials', 'copy' => 'Prepare product categories and supplier details for a cleaner buying journey.'],
                ['title' => 'Receive serious enquiries', 'copy' => 'Build visibility around buyers with construction sourcing intent.'],
                ['title' => 'Grow visibility', 'copy' => 'Create a stronger marketplace presence as NaijaBuilders launches.'],
            ] as $benefit)
                <x-marketing.card>
                    <span class="nb-card-kicker">Supplier benefit</span>
                    <h3>{{ $benefit['title'] }}</h3>
                    <p>{{ $benefit['copy'] }}</p>
                </x-marketing.card>
            @endforeach
        </div>
    </section>

    <section class="nb-section nb-section-muted">
        <div class="nb-container nb-split-section">
            <div class="nb-image-stack nb-section-reveal" data-reveal>
                <img src="{{ asset('assets/images/dashboard-enterprise.jpg') }}" alt="Supplier dashboard preview" loading="lazy" decoding="async">
                <div class="nb-glass-card nb-premium-border">
                    <strong>Supplier onboarding soon</strong>
                    <span>Join the waitlist to receive early onboarding updates.</span>
                </div>
            </div>
            <div class="nb-section-reveal" data-reveal>
                <p class="nb-eyebrow">Launch preparation</p>
                <h2>Prepare your product catalog before onboarding opens.</h2>
                <p>Suppliers can get ready by organizing product names, categories, service areas, delivery preferences, company details, and response processes.</p>
                <ul class="nb-check-list">
                    <li>Materials and product categories</li>
                    <li>Company and service area details</li>
                    <li>Buyer enquiry response process</li>
                    <li>Mobile-ready supplier visibility</li>
                </ul>
                <a class="nb-btn nb-btn-primary nb-shine" href="{{ route('marketing.contact') }}#waitlist">Join Supplier Waitlist</a>
            </div>
        </div>
    </section>
</x-marketing.layout>
