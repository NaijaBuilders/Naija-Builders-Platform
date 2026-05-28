<x-marketing.layout
    title="For Buyers and Builders"
    description="NaijaBuilders helps buyers, builders, contractors, and developers source materials faster, compare suppliers, and discover construction services."
>
    <x-marketing.page-hero
        eyebrow="For buyers and builders"
        title="Find construction materials faster and source with confidence."
        description="NaijaBuilders is being designed for buyers, builders, contractors, and developers who need better material discovery and supplier comparison."
        :image="asset('assets/images/home-buyers.jpg')"
    />

    <section class="nb-section">
        <div class="nb-container nb-card-grid nb-card-grid-4">
            @foreach ([
                ['title' => 'Find materials faster', 'copy' => 'Search for building materials across the categories your projects need.'],
                ['title' => 'Compare suppliers', 'copy' => 'Review supplier options and product context before taking the next step.'],
                ['title' => 'Source with confidence', 'copy' => 'Use a focused marketplace built around Nigerian construction needs.'],
                ['title' => 'Discover services', 'copy' => 'Find construction-related services and project support connections.'],
            ] as $benefit)
                <x-marketing.card>
                    <span class="nb-card-kicker">Buyer benefit</span>
                    <h3>{{ $benefit['title'] }}</h3>
                    <p>{{ $benefit['copy'] }}</p>
                </x-marketing.card>
            @endforeach
        </div>
    </section>

    <section class="nb-section nb-section-muted">
        <div class="nb-container nb-split-section">
            <div class="nb-section-reveal" data-reveal>
                <p class="nb-eyebrow">Built for repeat sourcing</p>
                <h2>From small material checks to larger project sourcing.</h2>
                <p>Construction buying is rarely one decision. NaijaBuilders is shaped around the search, compare, contact, and build rhythm that buyers and contractors already use.</p>
                <ul class="nb-check-list">
                    <li>Cement, steel, wood, finishes, tools, and equipment discovery</li>
                    <li>Supplier and product comparison paths</li>
                    <li>Service provider discovery for construction-related needs</li>
                    <li>Mobile app access coming soon</li>
                </ul>
            </div>
            <div class="nb-image-stack nb-section-reveal" data-reveal>
                <img src="{{ asset('assets/images/dashboard-logistics.jpg') }}" alt="Construction sourcing dashboard preview" loading="lazy" decoding="async">
                <div class="nb-glass-card nb-premium-border">
                    <strong>iOS and Android in progress</strong>
                    <span>Designed for project teams that source on the move.</span>
                </div>
            </div>
        </div>
    </section>
</x-marketing.layout>
