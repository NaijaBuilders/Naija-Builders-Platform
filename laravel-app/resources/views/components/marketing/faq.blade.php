@props(['items' => []])
<div {{ $attributes->merge(['class' => 'nb-faq-list']) }}>
    @foreach ($items as $index => $item)
        <article class="nb-faq-item nb-glass-card nb-premium-border nb-section-reveal" data-reveal>
            <button class="nb-faq-trigger" type="button" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                <span>{{ $item['question'] }}</span>
                <span class="nb-faq-icon" aria-hidden="true"></span>
            </button>
            <div class="nb-faq-panel" @if ($index === 0) style="max-height: 220px;" @endif>
                <p>{{ $item['answer'] }}</p>
            </div>
        </article>
    @endforeach
</div>
