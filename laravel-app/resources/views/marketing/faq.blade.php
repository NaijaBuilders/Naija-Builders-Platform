@php
    $faqItems = [
        [
            'question' => 'What is NaijaBuilders?',
            'answer' => 'NaijaBuilders is a Nigerian construction materials marketplace connecting builders, contractors, developers, suppliers, and construction service providers.',
        ],
        [
            'question' => 'Is NaijaBuilders live yet?',
            'answer' => 'The informational launch site is live in this Laravel frontend. The full marketplace launch experience and app availability are coming soon.',
        ],
        [
            'question' => 'When will the app be available?',
            'answer' => 'The iOS and Android apps are in progress. A launch date should only be shared once the team is ready to commit to it.',
        ],
        [
            'question' => 'Can suppliers join early?',
            'answer' => 'Suppliers can join the waitlist now to receive early onboarding information when supplier onboarding opens.',
        ],
        [
            'question' => 'Is it only for materials?',
            'answer' => 'Materials are the core marketplace focus, and NaijaBuilders is also designed to support construction-related service discovery.',
        ],
        [
            'question' => 'Will it support both iOS and Android?',
            'answer' => 'Yes. The app experience is being prepared for both iOS and Android.',
        ],
        [
            'question' => 'What materials will users be able to discover?',
            'answer' => 'The launch direction includes cement, steel, wood, finishes, tools, equipment, and other construction material categories.',
        ],
        [
            'question' => 'Does the waitlist submit to the backend?',
            'answer' => 'This launch site currently uses a safe frontend waitlist UI only, so no backend, auth, payment, cart, or marketplace logic is changed.',
        ],
    ];
@endphp

<x-marketing.layout
    title="FAQ"
    description="Answers to common questions about NaijaBuilders, the marketplace launch, supplier onboarding, and iOS and Android apps."
>
    <x-marketing.page-hero
        eyebrow="FAQ"
        title="Answers before launch."
        description="Simple guidance on what NaijaBuilders is, what is coming soon, and how buyers and suppliers can prepare."
        :image="asset('assets/images/support-247.jpg')"
    />

    <section class="nb-section">
        <div class="nb-container nb-faq-page">
            <x-marketing.faq :items="$faqItems" />
        </div>
    </section>
</x-marketing.layout>
