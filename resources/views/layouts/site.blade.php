<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Analytics (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VGT633DV9S"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-VGT633DV9S');
    </script>

    <title>@yield('title', __('site.default_title'))</title>
    <meta name="description" content="@yield('description', __('site.home.meta_description'))">
    <link rel="icon" href="{{ asset('images/brand/favicon-32.png') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('images/brand/favicon-16.png') }}" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/apple-touch-icon.png') }}">
    <meta property="og:image" content="{{ asset('images/brand/og-share.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-warm-50 text-neutral-900 antialiased">

    <header class="sticky top-0 z-40 border-b border-neutral-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-3">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center">
                <img src="{{ asset('images/brand/logo.png') }}" alt="Unity — Connect. Share. Grow." class="h-8 w-auto">
            </a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-neutral-600 sm:flex">
                <a href="{{ route('home') }}#how-it-works" class="hover:text-brand-blue">{{ __('site.nav.how_it_works') }}</a>
                <a href="{{ route('blog.index') }}" class="hover:text-brand-blue">{{ __('site.nav.stories') }}</a>
                <a href="{{ route('about') }}" class="hover:text-brand-blue">{{ __('site.nav.about') }}</a>
            </nav>
            <div class="flex shrink-0 items-center gap-3">
                <x-language-switcher />
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden text-sm font-medium text-neutral-600 hover:text-brand-blue sm:inline">
                        {{ __('site.nav.dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden text-sm font-medium text-neutral-600 hover:text-brand-blue sm:inline">
                        {{ __('site.nav.login') }}
                    </a>
                @endauth
                <a href="{{ route('home') }}#download" class="rounded-full bg-brand-blue px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-brand-blue/30 hover:bg-brand-blue-dark">
                    {{ __('site.nav.get_app') }}
                </a>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-neutral-200 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <img src="{{ asset('images/brand/logo.png') }}" alt="Unity" class="h-7 w-auto">
                    <p class="mt-4 max-w-xs text-sm text-neutral-500">
                        {{ __('site.footer.tagline') }}
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('site.footer.explore') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-neutral-500">
                        <li><a href="{{ route('home') }}" class="hover:text-brand-blue">{{ __('site.footer.home') }}</a></li>
                        <li><a href="{{ route('blog.index') }}" class="hover:text-brand-blue">{{ __('site.nav.stories') }}</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-brand-blue">{{ __('site.nav.about') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('site.footer.legal_heading') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-neutral-500">
                        <li><a href="{{ route('legal.privacy') }}" class="hover:text-brand-blue">{{ __('site.legal.privacy_title') }}</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="hover:text-brand-blue">{{ __('site.legal.terms_title') }}</a></li>
                        <li><a href="{{ route('legal.data') }}" class="hover:text-brand-blue">{{ __('site.legal.data_title') }}</a></li>
                        <li><a href="{{ route('legal.refund') }}" class="hover:text-brand-blue">{{ __('site.legal.refund_title') }}</a></li>
                        <li><a href="{{ route('legal.child-safety') }}" class="hover:text-brand-blue">{{ __('site.legal.child_safety_title') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('site.footer.get_app_heading') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-neutral-500">
                        @if(config('unity.play_store_url'))
                            <li><a href="{{ config('unity.play_store_url') }}" class="hover:text-brand-blue">Google Play</a></li>
                        @endif
                        @if(config('unity.app_store_url'))
                            <li><a href="{{ config('unity.app_store_url') }}" class="hover:text-brand-blue">App Store</a></li>
                        @endif
                    </ul>
                    <h3 class="mt-6 text-sm font-semibold text-neutral-900">{{ __('site.footer.contact') }}</h3>
                    <p class="mt-4 text-sm text-neutral-500">
                        <a href="tel:{{ config('unity.contact_phone') }}" class="hover:text-brand-blue">{{ config('unity.contact_phone') }}</a>
                    </p>
                </div>
            </div>
            <div class="mt-12 flex flex-col items-start justify-between gap-4 border-t border-neutral-100 pt-6 text-xs text-neutral-400 sm:flex-row sm:items-center">
                <div class="space-y-1">
                    <p>&copy; {{ __('site.footer.copyright', ['year' => now()->year]) }}</p>
                    <p>{{ __('site.footer.developed_by', ['company' => 'Maveric Infotech']) }}</p>
                </div>
                <x-language-switcher />
            </div>
        </div>
    </footer>

    <x-whatsapp-button />

</body>
</html>
