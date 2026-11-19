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
    @if (request()->routeIs('home'))<link rel="stylesheet" href="{{ asset('css/home-reference.css') }}">@endif
    @if (request()->routeIs('articles.*', 'news.*', 'events.*', 'scholarships.*'))<link rel="stylesheet" href="{{ asset('css/blogs.css') }}">@endif
    @if (request()->routeIs('news.*'))<link rel="stylesheet" href="{{ asset('css/news.css') }}">@endif
    @livewireStyles
</head>
<body class="h-full bg-white text-gray-900 antialiased">
    @php($dashboardUrl = rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/'))
    <div class="site-topbar">
        <div class="site-wrap site-topbar__inner">
            <a href="{{ route('schools.index') }}">Explore schools</a>
            <a href="{{ route('news.index') }}">Latest news</a>
            <a href="{{ route('articles.index') }}">Blogs</a>
            <a href="{{ route('events.index') }}">Events</a>
            <a href="{{ route('scholarships.index') }}">Scholarships</a>
            <a href="{{ route('compare') }}">Compare schools</a>
            @guest
                <a href="{{ $dashboardUrl }}/login?returnTo=%2Faccount">Log in</a>
                <a href="{{ $dashboardUrl }}/register?type=school_owner&returnTo=%2Faccount">Sign up</a>
            @else
                <a href="{{ auth()->user()->hasRole('school_owner') ? ($dashboardUrl.'/owner') : (auth()->user()->hasRole('admin') ? ($dashboardUrl.'/admin') : route('user.dashboard')) }}">My account</a>
            @endguest
        </div>
    </div>

    <header x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false" class="site-header">
        <div class="site-wrap site-header__inner">
            <a href="{{ route('home') }}" class="site-logo" aria-label="FindurSch home">
                <span>Find<b>UR</b>SCH</span>
            </a>

            <nav class="site-nav" aria-label="Primary navigation">
                <a href="{{ route('home') }}" @class(['is-active' => request()->routeIs('home')])>Home</a>
                <a href="{{ route('schools.index') }}" @class(['is-active' => request()->routeIs('schools.*', 'district.*', 'search')])>Schools</a>
                <a href="{{ route('compare') }}" @class(['is-active' => request()->routeIs('compare')])>Compare</a>
                <a href="{{ route('scholarships.index') }}" @class(['is-active' => request()->routeIs('scholarships.*')])>Scholarships</a>
                <a href="{{ route('news.index') }}" @class(['is-active' => request()->routeIs('news.*')])>News</a>
                <a href="{{ route('articles.index') }}" @class(['is-active' => request()->routeIs('articles.*')])>Blogs</a>
                <a href="{{ route('events.index') }}" @class(['is-active' => request()->routeIs('events.*')])>Events</a>
            </nav>

            <div class="site-header__actions">
                @auth
                    @if (auth()->user()->hasRole('admin'))
                        <a class="site-account-link" href="{{ $dashboardUrl }}/admin">Admin</a>
                    @elseif (auth()->user()->hasRole('school_owner'))
                        <a class="site-account-link" href="{{ $dashboardUrl }}/owner">School dashboard</a>
                    @else
                        <a class="site-account-link" href="{{ route('user.dashboard') }}">My account</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="site-logout-form">@csrf
                        <button type="submit" class="site-account-link">Log out</button>
                    </form>
                @else
                    <a class="site-account-link" href="{{ $dashboardUrl }}/login?returnTo=%2Faccount">Log in</a>
                @endauth
                <a href="{{ $dashboardUrl }}/register?type=school_owner" class="site-header__cta">List your school</a>
                <button type="button"
                    class="site-menu-toggle"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    :aria-expanded="mobileMenuOpen.toString()"
                    aria-controls="mobile-primary-navigation"
                    aria-label="Toggle navigation">
                    <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-cloak x-show="mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>

        <div id="mobile-primary-navigation" x-cloak x-show="mobileMenuOpen" x-transition.opacity.duration.150ms class="site-mobile-nav">
            <nav class="site-wrap" aria-label="Mobile navigation">
                <a @click="mobileMenuOpen = false" href="{{ route('home') }}">Home</a>
                <a @click="mobileMenuOpen = false" href="{{ route('schools.index') }}">Find a school</a>
                <a @click="mobileMenuOpen = false" href="{{ route('compare') }}">Compare schools</a>
                <a @click="mobileMenuOpen = false" href="{{ route('articles.index') }}">News &amp; guides</a>
                @guest
                    <a @click="mobileMenuOpen = false" href="{{ $dashboardUrl }}/login?returnTo=%2Faccount">Log in</a>
                @else
                    @if (auth()->user()->hasRole('admin'))
                        <a @click="mobileMenuOpen = false" href="{{ $dashboardUrl }}/admin">Admin dashboard</a>
                    @elseif (auth()->user()->hasRole('school_owner'))
                        <a @click="mobileMenuOpen = false" href="{{ $dashboardUrl }}/owner">School dashboard</a>
                    @else
                        <a @click="mobileMenuOpen = false" href="{{ route('user.dashboard') }}">My account</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="site-mobile-nav__logout">Log out</button>
                    </form>
                @endguest
                <a @click="mobileMenuOpen = false" href="{{ $dashboardUrl }}/register?type=school_owner" class="site-mobile-nav__cta">List your school</a>
            </nav>
        </div>
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
