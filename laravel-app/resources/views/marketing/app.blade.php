<x-marketing.layout
    title="App Coming Soon"
    description="NaijaBuilders iOS and Android apps are coming soon for mobile-first construction material sourcing and supplier discovery."
>
    <section class="nb-section nb-app-hero nb-animated-bg nb-grid-bg">
        <span class="nb-glow-layer nb-glow-layer-one" aria-hidden="true"></span>
        <span class="nb-glow-layer nb-glow-layer-two" aria-hidden="true"></span>
        <div class="nb-container nb-app-grid">
            <div class="nb-section-reveal" data-reveal>
                <p class="nb-eyebrow">App coming soon</p>
                <h1>NaijaBuilders is coming to iOS and Android.</h1>
                <p>The mobile app experience is in progress for builders, buyers, contractors, suppliers, and construction service providers who need sourcing access on the move.</p>
                <x-marketing.app-buttons />
            </div>
            <x-marketing.phone-mockup />
        </div>
    </section>

    <section class="nb-section">
        <div class="nb-container">
            <x-marketing.section-heading
                eyebrow="Mobile roadmap"
                title="Designed around the moments when sourcing decisions happen."
                description="The app preview focuses on practical marketplace needs rather than fake download claims."
            />
            <div class="nb-card-grid nb-card-grid-4">
                @foreach ([
                    ['title' => 'Search materials', 'copy' => 'Mobile discovery for cement, steel, wood, finishes, tools, equipment, and more.'],
                    ['title' => 'Compare suppliers', 'copy' => 'Review options from a phone before moving into deeper project conversations.'],
                    ['title' => 'Supplier visibility', 'copy' => 'Supplier profiles and material showcases are part of the mobile direction.'],
                    ['title' => 'Service discovery', 'copy' => 'Find useful construction-related services and project support connections.'],
                ] as $feature)
                    <x-marketing.card>
                        <span class="nb-card-kicker">App feature preview</span>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['copy'] }}</p>
                    </x-marketing.card>
                @endforeach
            </div>
        </div>
    </section>
</x-marketing.layout>
