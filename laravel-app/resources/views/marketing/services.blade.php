<x-marketing.layout
    title="Services"
    description="NaijaBuilders is preparing discovery for material sourcing, supplier discovery, contractor listings, and project support connections."
>
    <x-marketing.page-hero
        eyebrow="Services"
        title="Construction support connections around the materials marketplace."
        description="NaijaBuilders starts with material sourcing and supplier discovery, then expands usefulness through related construction service discovery."
        :image="asset('assets/images/transport.jpg')"
    />

    <section class="nb-section">
        <div class="nb-container">
            <x-marketing.section-heading
                eyebrow="Service lanes"
                title="Support for the work around sourcing."
                description="These service areas describe the launch direction without making claims about current availability."
            />
            <div class="nb-card-grid nb-card-grid-4">
                @foreach ([
                    ['title' => 'Material sourcing', 'copy' => 'A clearer path for finding materials needed on Nigerian construction projects.'],
                    ['title' => 'Supplier discovery', 'copy' => 'Marketplace paths that help buyers find and compare supplier options.'],
                    ['title' => 'Contractor listings', 'copy' => 'A future-friendly direction for service provider visibility.'],
                    ['title' => 'Project support connections', 'copy' => 'Connections around logistics, site needs, and construction support services.'],
                ] as $service)
                    <x-marketing.card>
                        <span class="nb-card-kicker">Coming soon</span>
                        <h3>{{ $service['title'] }}</h3>
                        <p>{{ $service['copy'] }}</p>
                    </x-marketing.card>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nb-section nb-section-muted">
        <div class="nb-container nb-contact-band nb-glass-card nb-premium-border nb-section-reveal" data-reveal>
            <div>
                <p class="nb-eyebrow">For service providers</p>
                <h2>Interested in being discoverable when service onboarding opens?</h2>
                <p>Join the waitlist and indicate your company type so the launch team can route future updates.</p>
            </div>
            <a class="nb-btn nb-btn-primary nb-shine" href="{{ route('marketing.contact') }}#waitlist">Join the Waitlist</a>
        </div>
    </section>
</x-marketing.layout>
