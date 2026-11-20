<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'FindURSCH — Find the right school in Uganda' }}</title>
    <meta name="description" content="{{ $description ?? 'Discover, compare and learn about schools across Uganda with FindurSch.' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- These page styles are safely scoped to their page wrappers; load them directly so Livewire route rendering cannot skip them. --}}
    <link rel="stylesheet" href="{{ asset('css/events-reference.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/events-listing-reference.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/schools-reference.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/mobile-drawer-reference.css') }}?v=3">
    @if (request()->routeIs('home'))<link rel="stylesheet" href="{{ asset('css/home-reference.css') }}">@endif
    @if (request()->routeIs('articles.*', 'news.*', 'events.*', 'scholarships.*'))<link rel="stylesheet" href="{{ asset('css/blogs.css') }}">@endif
    @if (request()->routeIs('scholarships.*'))<link rel="stylesheet" href="{{ asset('css/scholarships-reference.css') }}">@endif
    @if (request()->routeIs('news.*'))<link rel="stylesheet" href="{{ asset('css/news.css') }}">@endif
    @livewireStyles
</head>
<body class="h-full bg-white text-gray-900 antialiased">
    @php
        $dashboardUrl = rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/');
        $signInUrl = $dashboardUrl.'/login?returnTo=%2Faccount';
        $schoolRegistrationUrl = $dashboardUrl.'/register?type=school_owner&returnTo=%2Faccount';

        // One navigation definition drives both desktop and mobile menus.
        $primaryNavigation = [
            ['label' => 'Home', 'route' => 'home', 'active' => request()->routeIs('home')],
            ['label' => 'Schools', 'route' => 'schools.index', 'active' => request()->routeIs('schools.*', 'district.*', 'search')],
            ['label' => 'Compare', 'route' => 'compare', 'active' => request()->routeIs('compare')],
            ['label' => 'Scholarships', 'route' => 'scholarships.index', 'active' => request()->routeIs('scholarships.*')],
            ['label' => 'News', 'route' => 'news.index', 'active' => request()->routeIs('news.*')],
            ['label' => 'Events', 'route' => 'events.index', 'active' => request()->routeIs('events.*')],
            ['label' => 'Blogs', 'route' => 'articles.index', 'active' => request()->routeIs('articles.*')],
        ];

        $accountLinkLabel = null;
        $accountLinkUrl = null;

        if (auth()->check()) {
            if (auth()->user()->hasRole('admin')) {
                $accountLinkLabel = 'Admin dashboard';
                $accountLinkUrl = $dashboardUrl.'/admin';
            } elseif (auth()->user()->hasRole('school_owner')) {
                $accountLinkLabel = 'School dashboard';
                $accountLinkUrl = $dashboardUrl.'/owner';
            } else {
                $accountLinkLabel = 'My account';
                $accountLinkUrl = route('user.home');
            }
        }
    @endphp

    {{-- Utility information only: page navigation and account actions live in the main header. --}}
    <div class="site-topbar" role="region" aria-label="Site information and account links">
        <div class="site-wrap site-topbar__inner">
            <span class="site-topbar__message">Discover and compare schools across Uganda</span>
            <div class="site-topbar__actions">
                @guest
                    <a class="site-topbar__signin" href="{{ $signInUrl }}">Sign in</a>
                    <a class="site-topbar__signup" href="{{ $schoolRegistrationUrl }}">Sign up</a>
                @endguest
                <a class="site-topbar__contact" href="{{ route('contact') }}">Contact us <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>

    <header
        x-data="{ mobileMenuOpen: false }"
        x-init="$watch('mobileMenuOpen', value => { document.body.classList.toggle('site-menu-open', value); if (value) { $nextTick(() => $refs.mobileClose.focus()); } else { $nextTick(() => $refs.mobileOpen.focus()); } })"
        @keydown.escape.window="mobileMenuOpen = false"
        class="site-header">
        <div class="site-wrap site-header__inner">
            <a href="{{ route('home') }}" class="site-logo" aria-label="FindurSch home">Find<b>UR</b>SCH</a>

            <nav class="site-nav" aria-label="Primary navigation">
                @foreach($primaryNavigation as $item)
                    <a href="{{ route($item['route']) }}" @class(['is-active' => $item['active']])>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="site-header__actions">
                @auth
                    <a class="site-account-link" href="{{ $accountLinkUrl }}">{{ $accountLinkLabel }}</a>
                    <form method="POST" action="{{ route('logout') }}" class="site-logout-form">@csrf
                        <button type="submit" class="site-account-link">Log out</button>
                    </form>
                @else
                    <a class="site-account-link" href="{{ $signInUrl }}">Sign in</a>
                    <a href="{{ $schoolRegistrationUrl }}" class="site-header__cta">List your school</a>
                @endauth
                <button type="button"
                    class="site-menu-toggle"
                    x-ref="mobileOpen"
                    @click="mobileMenuOpen = true"
                    :aria-expanded="mobileMenuOpen.toString()"
                    aria-controls="mobile-primary-navigation"
                    aria-label="Open menu">
                    <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-cloak x-show="mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>

        <button type="button" class="site-mobile-overlay" x-cloak x-show="mobileMenuOpen"
            x-transition.opacity.duration.250ms @click="mobileMenuOpen = false" aria-label="Close navigation"></button>

        <aside id="mobile-primary-navigation" class="site-mobile-drawer" role="dialog" aria-modal="true"
            aria-label="FindURSCH navigation" x-cloak x-show="mobileMenuOpen"
            x-transition:enter="site-drawer-transition" x-transition:enter-start="site-drawer-from" x-transition:enter-end="site-drawer-to"
            x-transition:leave="site-drawer-transition" x-transition:leave-start="site-drawer-to" x-transition:leave-end="site-drawer-from"
            @keydown.tab="
                const focusable = [...$el.querySelectorAll('a[href], button:not([disabled])')];
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); }
                else if (!$event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }
            ">
            <div class="site-mobile-drawer__head">
                <a href="{{ route('home') }}" class="site-logo" aria-label="FindurSch home">Find<b>UR</b>SCH</a>
                <button type="button" class="site-mobile-drawer__close" x-ref="mobileClose" @click="mobileMenuOpen = false" aria-label="Close menu">×</button>
            </div>

            <nav class="site-mobile-drawer__links" aria-label="Mobile navigation">
                @foreach($primaryNavigation as $item)
                    <a @click="mobileMenuOpen = false" href="{{ route($item['route']) }}" @class(['is-active' => $item['active']])>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="site-mobile-drawer__footer">
                @guest
                    <a class="site-mobile-drawer__button site-mobile-drawer__button--outline" href="{{ $signInUrl }}" @click="mobileMenuOpen = false">Sign in</a>
                    <a class="site-mobile-drawer__button site-mobile-drawer__button--gold" href="{{ $schoolRegistrationUrl }}" @click="mobileMenuOpen = false">List your school</a>
                @else
                    <a class="site-mobile-drawer__button site-mobile-drawer__button--outline" href="{{ $accountLinkUrl }}" @click="mobileMenuOpen = false">{{ $accountLinkLabel }}</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="site-mobile-drawer__button site-mobile-drawer__button--gold">Log out</button>
                    </form>
                @endguest
            </div>
        </aside>
    </header>

    <div x-data="compareBar" x-cloak x-show="schools.length > 0" class="compare-bar">
        <div class="site-wrap compare-bar__inner">
            <p><strong x-text="schools.length"></strong> of 3 schools selected to compare</p>
            <div class="compare-bar__actions">
                <button type="button" @click="clear()">Clear</button>
                <a :href="'/compare?schools=' + encodeURIComponent(schools.join(','))" class="btn btn-brand">Compare schools</a>
            </div>
        </div>
    </div>

    <main class="min-h-screen">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-wrap">
            <div class="site-footer__grid">
                <div class="site-footer__brand">
                    <a href="{{ route('home') }}" class="site-logo site-logo--footer">Find<b>UR</b>SCH</a>
                    <p>Uganda's school discovery, comparison and admissions platform, with school profiles, news, events, jobs and scholarships.</p>
                    <p class="site-footer__location">Kampala, Uganda</p>
                </div>
                <div>
                    <h2>Explore schools</h2>
                    <ul>
                        <li><a href="{{ route('schools.index') }}">Find a school</a></li>
                        <li><a href="{{ route('compare') }}">Compare schools</a></li>
                        <li><a href="{{ route('schools.index', ['sort' => 'newest']) }}">Recently added</a></li>
                        <li><a href="{{ route('schools.index', ['verified_only' => 1]) }}">Verified schools</a></li>
                    </ul>
                </div>
                <div>
                    <h2>Resources</h2>
                    <ul>
                        <li><a href="{{ route('articles.index') }}">Blogs</a></li>
                        <li><a href="{{ route('about') }}">About FindurSch</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h2>Support</h2>
                    <ul>
                        <li><a href="{{ $dashboardUrl }}/register?type=school_owner">List your school</a></li>
                        <li><a href="{{ $dashboardUrl }}/login?returnTo=%2Faccount">School owner login</a></li>
                        <li><a href="{{ route('contact') }}">Get in touch</a></li>
                        <li><a href="{{ route('legal.terms') }}">Terms and conditions</a></li>
                        <li><a href="{{ route('legal.privacy') }}">Privacy policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="site-footer__bar">
                <div>
                    <span>© {{ date('Y') }} FindURSCH. All rights reserved.</span>
                    <span class="site-footer__legal"><a href="{{ route('legal.privacy') }}">Privacy</a><a href="{{ route('legal.terms') }}">Terms</a></span>
                </div>
                <div class="site-footer__promo">
                    <span>Want to reach parents and students every month?</span>
                    <a class="btn btn-footer" href="{{ route('contact') }}">Advertise with us</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
