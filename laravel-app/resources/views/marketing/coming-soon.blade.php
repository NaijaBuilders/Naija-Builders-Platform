@php
    $faqItems = [
        [
            'question' => 'What is NaijaBuilders?',
            'answer' => 'NaijaBuilders is a Nigerian construction materials marketplace designed to help builders, contractors, developers, suppliers, and service providers connect around project sourcing.',
        ],
        [
            'question' => 'Is NaijaBuilders live yet?',
            'answer' => 'The full launch experience is coming soon. The waitlist is open for people and companies that want early updates.',
        ],
        [
            'question' => 'When will the app be available?',
            'answer' => 'The iOS and Android apps are in progress. We are not publishing a launch date until it is ready to share responsibly.',
        ],
        [
            'question' => 'Can suppliers join early?',
            'answer' => 'Yes. Supplier onboarding is opening soon, and suppliers can join the waitlist to receive early onboarding information.',
        ],
        [
            'question' => 'Is it only for materials?',
            'answer' => 'Materials are the core marketplace focus, and NaijaBuilders is also designed to help users discover construction-related services and support connections.',
        ],
        [
            'question' => 'Will it support both iOS and Android?',
            'answer' => 'Yes. NaijaBuilders is preparing mobile app experiences for both iOS and Android.',
        ],
    ];

    $statusCards = [
        ['label' => 'Supplier onboarding soon', 'value' => 'Opening next', 'tone' => 'amber'],
        ['label' => 'Built for Nigerian projects', 'value' => 'Local focus', 'tone' => 'blue'],
        ['label' => 'Materials marketplace', 'value' => 'In progress', 'tone' => 'graphite'],
        ['label' => 'iOS and Android coming soon', 'value' => 'Mobile ready', 'tone' => 'steel'],
    ];
@endphp

<x-marketing.layout
    title="Coming Soon"
    description="NaijaBuilders is the Nigerian construction materials marketplace helping builders, contractors, and suppliers connect, source, and scale with confidence."
>
    <section class="nb-hero nb-animated-bg nb-grid-bg">
        <span class="nb-glow-layer nb-glow-layer-one" aria-hidden="true"></span>
        <span class="nb-glow-layer nb-glow-layer-two" aria-hidden="true"></span>
        <span class="nb-glow-layer nb-glow-layer-three" aria-hidden="true"></span>
        <div class="nb-construction-shapes" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="nb-container nb-hero-grid">
            <div class="nb-hero-copy">
                <p class="nb-eyebrow nb-hero-reveal">Nigerian construction marketplace</p>
                <h1 class="nb-hero-title nb-hero-reveal">
                    Build Better. Faster. <span class="nb-gradient-text">Together.</span>
                </h1>
                <p class="nb-hero-subtitle nb-hero-reveal">
                    NaijaBuilders is the Nigerian construction materials marketplace helping builders, contractors, and suppliers connect, source, and scale with confidence.
                </p>
                <div class="nb-hero-actions nb-hero-reveal">
                    <a class="nb-btn nb-btn-primary nb-btn-lg nb-shine nb-magnetic" href="{{ route('marketing.contact') }}#waitlist">Join the Waitlist</a>
                    <a class="nb-btn nb-btn-secondary nb-btn-lg nb-magnetic" href="#what-coming">Explore What's Coming</a>
                </div>
                <div class="nb-hero-trust nb-hero-reveal" aria-label="Launch status">
                    <span><strong>Coming Soon</strong> marketplace launch</span>
                    <span><strong>Supplier</strong> onboarding soon</span>
                    <span><strong>Apps</strong> in progress</span>
                </div>
            </div>

            <div class="nb-hero-visual nb-section-reveal" data-reveal>
                <div class="nb-market-preview nb-parallax-layer" data-parallax="0.14" data-tilt>
                    <img src="{{ asset('assets/images/home-platform.jpg') }}" alt="Construction marketplace preview" decoding="async" fetchpriority="high">
                    <div class="nb-preview-panel nb-glass-card">
                        <span class="nb-live-dot"></span>
                        <strong>Materials marketplace</strong>
                        <small>cement, steel, wood, finishes, tools</small>
                    </div>
                    <div class="nb-preview-stack">
                        <span>Compare suppliers</span>
                        <span>Discover services</span>
                        <span>Source faster</span>
                    </div>
                </div>
                <x-marketing.phone-mockup />
            </div>
        </div>
    </section>

    <section class="nb-section nb-launch-strip">
        <div class="nb-container">
            <div class="nb-launch-status nb-glass-card nb-premium-border nb-section-reveal" data-reveal>
                <div>
                    <p class="nb-eyebrow">Launch status</p>
                    <h2>Coming Soon</h2>
                    <p>No fake countdowns. No inflated claims. Just a focused launch pipeline for Nigerian construction sourcing.</p>
                </div>
                <div class="nb-status-pulse">
                    <span></span>
                    <strong>In build</strong>
                </div>
            </div>

            <div class="nb-status-grid">
                @foreach ($statusCards as $card)
                    <article class="nb-status-card nb-status-card-{{ $card['tone'] }} nb-glass-card nb-premium-border nb-hover-lift nb-section-reveal" data-reveal data-tilt>
                        <span>{{ $card['label'] }}</span>
                        <strong>{{ $card['value'] }}</strong>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="what-coming" class="nb-section">
        <div class="nb-container">
            <x-marketing.section-heading
                eyebrow="What NaijaBuilders does"
                title="A marketplace layer for construction sourcing."
                description="NaijaBuilders is being built to help buyers, builders, contractors, developers, suppliers, and service providers find each other with less friction."
            />

            <div class="nb-card-grid nb-card-grid-3">
                <x-marketing.card>
                    <span class="nb-card-kicker">For builders</span>
                    <h3>Source materials faster</h3>
                    <p>Discover cement, steel, wood, finishes, tools, equipment, and other building materials from one focused marketplace.</p>
                </x-marketing.card>
                <x-marketing.card>
                    <span class="nb-card-kicker">For contractors</span>
                    <h3>Compare suppliers</h3>
                    <p>Review products, supplier options, service providers, and project support connections before moving forward.</p>
                </x-marketing.card>
                <x-marketing.card>
                    <span class="nb-card-kicker">For suppliers</span>
                    <h3>Reach serious buyers</h3>
                    <p>List products, showcase materials, and prepare for enquiries from buyers working on real construction needs.</p>
                </x-marketing.card>
            </div>
        </div>
    </section>

    <section class="nb-section nb-section-muted">
        <div class="nb-container">
            <x-marketing.section-heading
                eyebrow="How it works"
                title="From search to site confidence."
                description="The launch experience is designed around simple sourcing steps that project teams already understand."
            />

            <div class="nb-timeline">
                @foreach ([
                    ['step' => '01', 'title' => 'Search materials', 'copy' => 'Look for project essentials across cement, steel, wood, finishes, tools, equipment, and more.'],
                    ['step' => '02', 'title' => 'Compare suppliers', 'copy' => 'Review supplier options and product details in a marketplace built for Nigerian construction needs.'],
                    ['step' => '03', 'title' => 'Contact or order', 'copy' => 'Move from discovery to enquiry or ordering when the right supplier is available.'],
                    ['step' => '04', 'title' => 'Build with confidence', 'copy' => 'Use clearer sourcing paths and service connections to reduce project friction.'],
                ] as $item)
                    <article class="nb-timeline-item nb-section-reveal" data-reveal>
                        <span>{{ $item['step'] }}</span>
                        <div class="nb-glass-card nb-premium-border">
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['copy'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nb-section">
        <div class="nb-container nb-split-section">
            <div class="nb-section-reveal" data-reveal>
                <p class="nb-eyebrow">For buyers and builders</p>
                <h2>Find materials, compare options, and discover project support.</h2>
                <p>NaijaBuilders is designed for builders, contractors, developers, and buyers who need faster ways to source materials and related services.</p>
                <ul class="nb-check-list">
                    <li>Find materials faster</li>
                    <li>Compare suppliers</li>
                    <li>Source with confidence</li>
                    <li>Discover services</li>
                </ul>
                <a class="nb-text-link" href="{{ route('marketing.buyers') }}">Explore the buyer experience</a>
            </div>
            <div class="nb-image-stack nb-section-reveal" data-reveal>
                <img src="{{ asset('assets/images/home-buyers.jpg') }}" alt="Construction buyers sourcing materials" loading="lazy" decoding="async">
                <div class="nb-glass-card nb-premium-border">
                    <strong>Built for project sourcing</strong>
                    <span>Materials, suppliers, and service discovery in one launch roadmap.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="nb-section nb-section-muted">
        <div class="nb-container nb-split-section nb-split-section-reverse">
            <div class="nb-image-stack nb-section-reveal" data-reveal>
                <img src="{{ asset('assets/images/home-suppliers.jpg') }}" alt="Construction supplier preparing materials" loading="lazy" decoding="async">
                <div class="nb-glass-card nb-premium-border">
                    <strong>Supplier onboarding soon</strong>
                    <span>Prepare your catalog, categories, and buyer response flow.</span>
                </div>
            </div>
            <div class="nb-section-reveal" data-reveal>
                <p class="nb-eyebrow">For suppliers</p>
                <h2>Showcase materials and grow visibility with serious buyers.</h2>
                <p>Suppliers will be able to present product categories, receive enquiries, and build visibility across construction demand.</p>
                <ul class="nb-check-list">
                    <li>Reach more buyers</li>
                    <li>Showcase materials</li>
                    <li>Receive serious enquiries</li>
                    <li>Grow visibility</li>
                </ul>
                <a class="nb-text-link" href="{{ route('marketing.suppliers') }}">Explore supplier onboarding</a>
            </div>
        </div>
    </section>

    <section class="nb-section">
        <div class="nb-container">
            <x-marketing.section-heading
                eyebrow="Construction services"
                title="Materials are the core. Services make sourcing more useful."
                description="NaijaBuilders is also preparing discovery paths for construction-related services and project support connections."
            />
            <div class="nb-card-grid nb-card-grid-4">
                @foreach ([
                    ['title' => 'Material sourcing', 'copy' => 'A focused path for finding project materials.'],
                    ['title' => 'Supplier discovery', 'copy' => 'Compare suppliers and product availability.'],
                    ['title' => 'Contractor listings', 'copy' => 'Discover service providers around construction needs.'],
                    ['title' => 'Project support', 'copy' => 'Connect around logistics, site support, and related needs.'],
                ] as $service)
                    <x-marketing.card>
                        <span class="nb-card-kicker">Service lane</span>
                        <h3>{{ $service['title'] }}</h3>
                        <p>{{ $service['copy'] }}</p>
                    </x-marketing.card>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nb-section nb-app-section nb-animated-bg nb-grid-bg">
        <span class="nb-glow-layer nb-glow-layer-one" aria-hidden="true"></span>
        <span class="nb-glow-layer nb-glow-layer-two" aria-hidden="true"></span>
        <div class="nb-container nb-app-grid">
            <div class="nb-section-reveal" data-reveal>
                <p class="nb-eyebrow">Coming Soon on iOS and Android</p>
                <h2>The NaijaBuilders mobile app is in progress.</h2>
                <p>Mobile access is part of the roadmap, so buyers and suppliers can move from discovery to action wherever project decisions happen.</p>
                <x-marketing.app-buttons />
            </div>
            <x-marketing.phone-mockup />
        </div>
    </section>

    <section class="nb-section">
        <div class="nb-container nb-contact-band nb-glass-card nb-premium-border nb-section-reveal" data-reveal>
            <div>
                <p class="nb-eyebrow">Join the waitlist</p>
                <h2>Be first to hear when NaijaBuilders opens supplier onboarding and app access.</h2>
                <p>Leave your details and the frontend will save your interest locally for now. No backend flow is changed.</p>
            </div>
            <form class="nb-waitlist-form" data-waitlist-form>
                <label>
                    <span>Name or company</span>
                    <input type="text" name="name" placeholder="Your name or company">
                </label>
                <label>
                    <span>Email address</span>
                    <input type="email" name="email" placeholder="you@example.com" required>
                </label>
                <button class="nb-btn nb-btn-primary nb-shine" type="submit">Join the Waitlist</button>
                <p class="nb-form-note" data-waitlist-message aria-live="polite"></p>
            </form>
        </div>
    </section>

    <section class="nb-section nb-section-muted">
        <div class="nb-container">
            <x-marketing.section-heading
                eyebrow="FAQ"
                title="Quick answers before launch."
                description="Short, honest answers about the launch state, apps, suppliers, and marketplace scope."
            />
            <x-marketing.faq :items="$faqItems" />
        </div>
    </section>
</x-marketing.layout>
