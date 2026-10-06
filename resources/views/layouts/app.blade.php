<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="@yield('description', __('Book intercity travel and shuttle tickets across Indonesia. Choose your own seat and pay via VA, e-wallet, or QRIS.'))">
        <meta name="theme-color" content="#3c3410">
        <title>@hasSection('title')@yield('title') | @endif RouteIndonesia</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-dvh flex-col">
        <a href="#konten" class="sr-only z-50 rounded-md bg-brand-600 px-4 py-2 font-semibold text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">{{ __('Skip to main content') }}</a>

        <x-navbar />

        <main id="konten" class="flex-1" @hasSection('module') data-module="@yield('module')" @endif>
            @yield('content')
        </main>

        <x-footer />

        {{-- z-index: navbar 30, bilah bawah 20, panel filter 40, toast 50 --}}
        <div data-toast-region class="pointer-events-none fixed inset-x-0 bottom-24 z-50 flex flex-col items-center gap-2 px-4 lg:bottom-6" role="status" aria-live="polite"></div>

        @stack('scripts')
    </body>
</html>
