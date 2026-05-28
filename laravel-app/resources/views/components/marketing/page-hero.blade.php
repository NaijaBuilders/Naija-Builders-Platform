@props([
    'eyebrow' => '',
    'title' => '',
    'description' => '',
    'image' => '',
])
<section class="nb-subhero nb-animated-bg nb-grid-bg">
    <span class="nb-glow-layer nb-glow-layer-one" aria-hidden="true"></span>
    <span class="nb-glow-layer nb-glow-layer-two" aria-hidden="true"></span>
    <div class="nb-container nb-subhero-grid">
        <div class="nb-subhero-copy nb-section-reveal" data-reveal>
            @if ($eyebrow !== '')
                <p class="nb-eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1>{{ $title }}</h1>
            <p>{{ $description }}</p>
        </div>
        @if ($image !== '')
            <div class="nb-subhero-media nb-section-reveal nb-parallax-layer" data-reveal data-parallax="0.12">
                <img src="{{ $image }}" alt="" decoding="async" fetchpriority="high">
                <div class="nb-subhero-media-card nb-glass-card">
                    <span>Coming Soon</span>
                    <strong>NaijaBuilders launch hub</strong>
                </div>
            </div>
        @endif
    </div>
</section>
