@props([
    'eyebrow' => '',
    'title' => '',
    'description' => '',
    'align' => 'center',
])
<div {{ $attributes->merge(['class' => 'nb-section-heading nb-section-heading-' . $align . ' nb-section-reveal']) }} data-reveal>
    @if ($eyebrow !== '')
        <p class="nb-eyebrow">{{ $eyebrow }}</p>
    @endif
    @if ($title !== '')
        <h2>{{ $title }}</h2>
    @endif
    @if ($description !== '')
        <p>{{ $description }}</p>
    @endif
</div>
