<x-marketing.layout
    title="About"
    description="Learn about NaijaBuilders, the Nigerian construction materials marketplace built for builders, contractors, developers, suppliers, and service providers."
>
    <x-marketing.page-hero
        eyebrow="About NaijaBuilders"
        title="A focused marketplace for Nigerian construction sourcing."
        description="NaijaBuilders is being built to help the people behind Nigerian construction projects find materials, suppliers, and useful service connections with greater confidence."
        :image="asset('assets/images/home.jpg')"
    />

    <section class="nb-section">
        <div class="nb-container nb-split-section">
            <div class="nb-section-reveal" data-reveal>
                <p class="nb-eyebrow">The mission</p>
                <h2>Make construction sourcing clearer, faster, and more connected.</h2>
                <p>Construction teams often need to move quickly while comparing material options, supplier reliability, service availability, and project timelines. NaijaBuilders is designed as a practical digital layer for that work.</p>
                <p>The marketplace is focused on Nigerian project realities, including local material categories, supplier discovery, service connections, and mobile-first access.</p>
            </div>
            <div class="nb-card-stack">
                <x-marketing.card>
                    <span class="nb-card-kicker">Who it serves</span>
                    <h3>Builders, contractors, developers, suppliers, and service providers</h3>
                    <p>NaijaBuilders brings the core construction supply conversation into one coordinated launch experience.</p>
                </x-marketing.card>
                <x-marketing.card>
                    <span class="nb-card-kicker">What it supports</span>
                    <h3>Materials, comparisons, enquiries, and services</h3>
                    <p>The platform is designed around sourcing decisions, supplier visibility, and related project support.</p>
                </x-marketing.card>
            </div>
        </div>
    </section>

    <section class="nb-section nb-section-muted">
        <div class="nb-container">
            <x-marketing.section-heading
                eyebrow="Marketplace focus"
                title="Built around the materials that move projects."
                description="The launch direction includes common construction material categories and the service connections that help teams act on them."
            />
            <div class="nb-card-grid nb-card-grid-4">
                @foreach (['Cement', 'Steel', 'Wood', 'Finishes', 'Tools', 'Equipment', 'Supplier discovery', 'Project services'] as $item)
                    <x-marketing.card>
                        <span class="nb-card-kicker">Category</span>
                        <h3>{{ $item }}</h3>
                        <p>Part of the NaijaBuilders launch roadmap for construction teams and suppliers.</p>
                    </x-marketing.card>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nb-section">
        <div class="nb-container">
            <x-marketing.section-heading
                eyebrow="Values"
                title="Premium does not need to be noisy."
                description="The product direction is practical, trustworthy, and focused on the sourcing decisions that matter."
            />
            <div class="nb-card-grid nb-card-grid-3">
                @foreach ([
                    ['title' => 'Clarity', 'copy' => 'Help project teams understand options before they commit.'],
                    ['title' => 'Confidence', 'copy' => 'Create better pathways between buyers, suppliers, and service providers.'],
                    ['title' => 'Local relevance', 'copy' => 'Shape the marketplace around Nigerian construction needs.'],
                ] as $value)
                    <x-marketing.card>
                        <span class="nb-card-kicker">Value</span>
                        <h3>{{ $value['title'] }}</h3>
                        <p>{{ $value['copy'] }}</p>
                    </x-marketing.card>
                @endforeach
            </div>
        </div>
    </section>
</x-marketing.layout>
