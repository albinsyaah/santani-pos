<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? __($title).' · ' : '' }}{{ config('app.name') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="agropos">
    <div class="app-shell" x-data="{ sidebarOpen: false }"
         :class="{ 'sidebar-open': sidebarOpen }"
         @keydown.escape.window="sidebarOpen = false">

        @include('partials.header')

        <div class="body-grid">
            @include('partials.sidebar')
            <div class="sidebar-backdrop" x-show="sidebarOpen" x-cloak x-transition.opacity
                 @click="sidebarOpen = false" aria-hidden="true"></div>

            <section class="main">
                {{ $slot }}
            </section>
        </div>

        @include('partials.footer')
    </div>

    @livewireScripts
</body>
</html>
