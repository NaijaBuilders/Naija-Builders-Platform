@props([
    'title' => 'Coming Soon',
    'description' => 'NaijaBuilders is the Nigerian construction materials marketplace helping builders, contractors, and suppliers connect, source, and scale with confidence.',
])
@php
    $themeFromCookie = (string) request()->cookie('naijabuilders-theme', '');
    if (! in_array($themeFromCookie, ['dark', 'light'], true)) {
        $themeFromCookie = '';
    }
@endphp
<!DOCTYPE html>
<html lang="en" @if ($themeFromCookie !== '') data-theme="{{ $themeFromCookie }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="NaijaBuilders, Nigerian construction marketplace, cement, steel, wood, building materials, suppliers, contractors">
    <meta name="author" content="NaijaBuilders">
    <meta property="og:title" content="{{ $title }} | NaijaBuilders">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('assets/images/home-platform.jpg') }}">
    <meta name="theme-color" content="#0b2a5b">
    <script>
        (function () {
            var theme = '';

            try {
                theme = localStorage.getItem('naijabuilders-theme') || '';
            } catch (error) {
                theme = '';
            }

            if (theme !== 'dark' && theme !== 'light') {
                theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>{{ $title }} | NaijaBuilders</title>
</head>
<body class="nb-marketing-page">
    <a class="nb-skip-link" href="#main">Skip to content</a>
    <x-marketing.nav />

    <main id="main">
        {{ $slot }}
    </main>

    <x-marketing.footer />
</body>
</html>
