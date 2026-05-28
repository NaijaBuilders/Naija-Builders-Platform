<x-marketing.layout
    title="How It Works"
    description="See how NaijaBuilders will help builders search materials, compare suppliers, contact or order, and build with confidence."
>
    <x-marketing.page-hero
        eyebrow="How it works"
        title="A simple sourcing flow for complex construction work."
        description="NaijaBuilders is designed to help buyers and builders move from material discovery to supplier comparison, enquiry, and confident project action."
        :image="asset('assets/images/dashboard-materials.jpg')"
    />

    <section class="nb-section">
        <div class="nb-container">
            <div class="nb-timeline nb-timeline-large">
                @foreach ([
                    ['step' => '01', 'title' => 'Search materials', 'copy' => 'Start with the products your project needs, from cement and steel to finishes, tools, wood, equipment, and related materials.'],
                    ['step' => '02', 'title' => 'Compare suppliers', 'copy' => 'Review supplier options and product context so your team can make more informed sourcing decisions.'],
                    ['step' => '03', 'title' => 'Contact or order', 'copy' => 'Move from discovery to enquiry or ordering once the right product and supplier path is available.'],
                    ['step' => '04', 'title' => 'Build with confidence', 'copy' => 'Use clearer sourcing paths and service discovery to reduce friction around project execution.'],
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

    <section class="nb-section nb-section-muted">
        <div class="nb-container nb-card-grid nb-card-grid-3">
            <x-marketing.card>
                <span class="nb-card-kicker">Discovery</span>
                <h3>Materials marketplace</h3>
                <p>NaijaBuilders is built around sourcing categories that matter to Nigerian construction teams.</p>
            </x-marketing.card>
            <x-marketing.card>
                <span class="nb-card-kicker">Decision support</span>
                <h3>Supplier comparison</h3>
                <p>Compare options in one focused environment instead of scattering sourcing across disconnected channels.</p>
            </x-marketing.card>
            <x-marketing.card>
                <span class="nb-card-kicker">Action</span>
                <h3>Enquiry and order paths</h3>
                <p>The launch roadmap supports contact and ordering flows without changing existing backend marketplace logic here.</p>
            </x-marketing.card>
        </div>
    </section>
</x-marketing.layout>
