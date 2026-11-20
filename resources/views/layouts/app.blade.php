<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    @php
        $siteSettings = \App\Models\Setting::query()
            ->whereIn('key', [
                'site_name', 'site_tagline', 'site_description', 'site_logo', 'site_favicon',
                'meta_title', 'meta_description', 'meta_keywords',
                'support_email', 'support_phone', 'support_whatsapp', 'contact_address',
                'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url',
                'site_navigation', 'site_topbar_enabled', 'site_navigation_enabled',
                'site_account_actions_enabled', 'site_header_cta_label', 'site_footer_enabled',
                'site_footer_explore_enabled', 'site_footer_resources_enabled', 'site_footer_support_enabled',
                'site_footer_social_enabled', 'site_footer_promo_enabled', 'site_footer_copyright_text',
                'primary_color', 'accent_color', 'font_display', 'font_body',
            ])
            ->pluck('value', 'key');
        $siteName = $siteSettings['site_name'] ?? 'FindURSCH';
        $siteTagline = $siteSettings['site_tagline'] ?? 'Discover and compare schools across Uganda';
        $siteDescription = $siteSettings['site_description'] ?? 'Uganda’s school discovery and comparison platform.';
        $siteLogoUrl = $siteSettings['site_logo'] ?? '';
        $siteFaviconUrl = $siteSettings['site_favicon'] ?? '';
        $siteAddress = $siteSettings['contact_address'] ?? 'Kampala, Uganda';
        $siteSupportEmail = $siteSettings['support_email'] ?? '';
        $siteSupportPhone = $siteSettings['support_phone'] ?? '';
        $siteSupportWhatsapp = preg_replace('/\\D+/', '', (string) ($siteSettings['support_whatsapp'] ?? ''));
        $siteTopbarEnabled = filter_var($siteSettings['site_topbar_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $siteNavigationEnabled = filter_var($siteSettings['site_navigation_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $siteAccountActionsEnabled = filter_var($siteSettings['site_account_actions_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $siteFooterEnabled = filter_var($siteSettings['site_footer_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $siteFooterExploreEnabled = filter_var($siteSettings['site_footer_explore_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $siteFooterResourcesEnabled = filter_var($siteSettings['site_footer_resources_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $siteFooterSupportEnabled = filter_var($siteSettings['site_footer_support_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $siteFooterSocialEnabled = filter_var($siteSettings['site_footer_social_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $siteFooterPromoEnabled = filter_var($siteSettings['site_footer_promo_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $siteHeaderCtaLabel = $siteSettings['site_header_cta_label'] ?? 'List your school';
        $siteFooterCopyright = $siteSettings['site_footer_copyright_text'] ?? '';
        $sitePrimaryColor = is_string($siteSettings['primary_color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $siteSettings['primary_color']) ? $siteSettings['primary_color'] : '#0c5a49';
        $siteAccentColor = is_string($siteSettings['accent_color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $siteSettings['accent_color']) ? $siteSettings['accent_color'] : '#f0b030';
        $siteDisplayFont = in_array($siteSettings['font_display'] ?? '', ['Playfair Display', 'DM Serif Display'], true) ? $siteSettings['font_display'] : 'Playfair Display';
        $siteBodyFont = in_array($siteSettings['font_body'] ?? '', ['Inter', 'system-ui'], true) ? $siteSettings['font_body'] : 'Inter';
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? ($siteSettings['meta_title'] ?? 'FindURSCH — Find the right school in Uganda') }}</title>
    <meta name="description" content="{{ $description ?? ($siteSettings['meta_description'] ?? $siteDescription) }}">
    @if(!empty($siteSettings['meta_keywords']))<meta name="keywords" content="{{ $siteSettings['meta_keywords'] }}">@endif
    <meta property="og:title" content="{{ $title ?? ($siteSettings['meta_title'] ?? $siteName) }}">
    <meta property="og:description" content="{{ $description ?? ($siteSettings['meta_description'] ?? $siteDescription) }}">
    @if($siteLogoUrl)<meta property="og:image" content="{{ $siteLogoUrl }}">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: {{ $sitePrimaryColor }};
            --brand-dark: color-mix(in srgb, {{ $sitePrimaryColor }} 78%, #000);
            --brand-deep: color-mix(in srgb, {{ $sitePrimaryColor }} 55%, #000);
            --gold: {{ $siteAccentColor }};
            --gold-dark: color-mix(in srgb, {{ $siteAccentColor }} 78%, #000);
            --accent: {{ $siteAccentColor }};
            --accent-dk: color-mix(in srgb, {{ $siteAccentColor }} 78%, #000);
            --display-font: '{{ $siteDisplayFont }}', Georgia, serif;
            --font-display: '{{ $siteDisplayFont }}', Georgia, serif;
            --font-body: '{{ $siteBodyFont }}', system-ui, sans-serif;
            --color-primary: {{ $sitePrimaryColor }};
            --color-primary-500: {{ $sitePrimaryColor }};
            --color-primary-600: color-mix(in srgb, {{ $sitePrimaryColor }} 78%, #000);
            --color-primary-700: color-mix(in srgb, {{ $sitePrimaryColor }} 55%, #000);
            --color-accent: {{ $siteAccentColor }};
            --color-accent-500: {{ $siteAccentColor }};
            --color-accent-600: color-mix(in srgb, {{ $siteAccentColor }} 78%, #000);
        }
        body { font-family: '{{ $siteBodyFont }}', system-ui, sans-serif; }
    </style>
    <link rel="icon" href="{{ $siteFaviconUrl ?: '/favicon.png' }}">
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

        // One settings-backed navigation list drives desktop and mobile menus.
        $savedNavigation = json_decode((string) ($siteSettings['site_navigation'] ?? '[]'), true);
        $navigationFallback = [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Schools', 'href' => '/schools'],
            ['label' => 'Compare', 'href' => '/compare'],
            ['label' => 'Scholarships', 'href' => '/scholarships'],
            ['label' => 'News', 'href' => '/news'],
            ['label' => 'Events', 'href' => '/events'],
            ['label' => 'Blogs', 'href' => '/articles'],
        ];
        $navigationItems = is_array($savedNavigation) ? $savedNavigation : $navigationFallback;
        if ($navigationItems === []) $navigationItems = $navigationFallback;
        $currentPath = '/'.trim(request()->path(), '/');
        $primaryNavigation = collect($navigationItems)
            ->filter(fn ($item) => is_array($item)
                && ($item['enabled'] ?? true)
                && is_string($item['href'] ?? null)
                && str_starts_with($item['href'], '/')
                && ! str_starts_with($item['href'], '//')
                && filled($item['label'] ?? null))
            ->map(function ($item) use ($currentPath) {
                $href = $item['href'];
                $path = parse_url($href, PHP_URL_PATH) ?: '/';
                $active = $path === '/'
                    ? $currentPath === '/'
                    : ($currentPath === $path || str_starts_with($currentPath, rtrim($path, '/').'/'));
                return ['label' => $item['label'], 'href' => $href, 'active' => $active];
            })
            ->values();

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
    @if($siteTopbarEnabled)
    <div class="site-topbar" role="region" aria-label="Site information and account links">
        <div class="site-wrap site-topbar__inner">
            <span class="site-topbar__message">{{ $siteTagline }}</span>
            <div class="site-topbar__actions">
                @if($siteAccountActionsEnabled)
                    @guest
                        <a class="site-topbar__signin" href="{{ $signInUrl }}">Sign in</a>
                        <a class="site-topbar__signup" href="{{ $schoolRegistrationUrl }}">Sign up</a>
                    @endguest
                @endif
                <a class="site-topbar__contact" href="{{ route('contact') }}">Contact us <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>
    @endif

    <header
        x-data="{ mobileMenuOpen: false }"
        x-init="$watch('mobileMenuOpen', value => { document.body.classList.toggle('site-menu-open', value); if (value) { $nextTick(() => $refs.mobileClose.focus()); } else { $nextTick(() => $refs.mobileOpen.focus()); } })"
        @keydown.escape.window="mobileMenuOpen = false"
        class="site-header">
        <div class="site-wrap site-header__inner">
            <a href="{{ route('home') }}" class="site-logo" aria-label="{{ $siteName }} home">
                @if($siteLogoUrl)<img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" style="max-height:44px;max-width:200px;width:auto;object-fit:contain">@else{{ $siteName }}@endif
            </a>

            @if($siteNavigationEnabled)
            <nav class="site-nav" aria-label="Primary navigation">
                @foreach($primaryNavigation as $item)
                    <a href="{{ $item['href'] }}" @class(['is-active' => $item['active']])>{{ $item['label'] }}</a>
                @endforeach
            </nav>
            @endif

            <div class="site-header__actions">
                @if($siteAccountActionsEnabled)
                @auth
                    <a class="site-account-link" href="{{ $accountLinkUrl }}">{{ $accountLinkLabel }}</a>
                    <form method="POST" action="{{ route('logout') }}" class="site-logout-form">@csrf
                        <button type="submit" class="site-account-link">Log out</button>
                    </form>
                @else
                    <a href="{{ $schoolRegistrationUrl }}" class="site-header__cta">{{ $siteHeaderCtaLabel }}</a>
                @endauth
                @endif
                @if($siteNavigationEnabled)
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
                @endif
            </div>
        </div>

        @if($siteNavigationEnabled)
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
                <a href="{{ route('home') }}" class="site-logo" aria-label="{{ $siteName }} home">
                @if($siteLogoUrl)<img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" style="max-height:44px;max-width:200px;width:auto;object-fit:contain">@else{{ $siteName }}@endif
            </a>
                <button type="button" class="site-mobile-drawer__close" x-ref="mobileClose" @click="mobileMenuOpen = false" aria-label="Close menu">×</button>
            </div>

            <nav class="site-mobile-drawer__links" aria-label="Mobile navigation">
                @foreach($primaryNavigation as $item)
                    <a @click="mobileMenuOpen = false" href="{{ $item['href'] }}" @class(['is-active' => $item['active']])>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            @if($siteAccountActionsEnabled)
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
            @endif
        </aside>
        @endif
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

    @if($siteFooterEnabled)
    <footer class="site-footer">
        <div class="site-wrap">
            <div class="site-footer__grid">
                <div class="site-footer__brand">
                    <a href="{{ route('home') }}" class="site-logo site-logo--footer">
                        @if($siteLogoUrl)<img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" style="max-height:48px;max-width:220px;width:auto;object-fit:contain">@else{{ $siteName }}@endif
                    </a>
                    <p>{{ $siteDescription }}</p>
                    @if($siteAddress)<p class="site-footer__location">{{ $siteAddress }}</p>@endif
                    @if($siteFooterSocialEnabled)
                    <div class="site-footer__social" aria-label="Social media links">
                        @foreach(['Facebook' => 'facebook_url', 'X' => 'twitter_url', 'Instagram' => 'instagram_url', 'YouTube' => 'youtube_url'] as $socialLabel => $socialKey)
                            @if(!empty($siteSettings[$socialKey]))<a href="{{ $siteSettings[$socialKey] }}" target="_blank" rel="noopener noreferrer">{{ $socialLabel }}</a>@endif
                        @endforeach
                    </div>
                    @endif
                </div>
                @if($siteFooterExploreEnabled)
                <div>
                    <h2>Explore schools</h2>
                    <ul>
                        <li><a href="{{ route('schools.index') }}">Find a school</a></li>
                        <li><a href="{{ route('compare') }}">Compare schools</a></li>
                        <li><a href="{{ route('schools.index', ['sort' => 'newest']) }}">Recently added</a></li>
                        <li><a href="{{ route('schools.index', ['verified_only' => 1]) }}">Verified schools</a></li>
                    </ul>
                </div>
                @endif
                @if($siteFooterResourcesEnabled)
                <div>
                    <h2>Resources</h2>
                    <ul>
                        <li><a href="{{ route('articles.index') }}">Blogs</a></li>
                        <li><a href="{{ route('about') }}">About FindurSch</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                    </ul>
                </div>
                @endif
                @if($siteFooterSupportEnabled)
                <div>
                    <h2>Support</h2>
                    <ul>
                        <li><a href="{{ $dashboardUrl }}/register?type=school_owner">List your school</a></li>
                        <li><a href="{{ $dashboardUrl }}/login?returnTo=%2Faccount">School owner login</a></li>
                        <li><a href="{{ route('contact') }}">Get in touch</a></li>
                        @if($siteSupportEmail)<li><a href="mailto:{{ $siteSupportEmail }}">{{ $siteSupportEmail }}</a></li>@endif
                        @if($siteSupportPhone)<li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteSupportPhone) }}">{{ $siteSupportPhone }}</a></li>@endif
                        @if($siteSupportWhatsapp)<li><a href="https://wa.me/{{ $siteSupportWhatsapp }}" target="_blank" rel="noopener">WhatsApp support</a></li>@endif
                        <li><a href="{{ route('legal.terms') }}">Terms and conditions</a></li>
                        <li><a href="{{ route('legal.privacy') }}">Privacy policy</a></li>
                    </ul>
                </div>
                @endif
            </div>
            <div class="site-footer__bar">
                <div>
                    <span>{{ $siteFooterCopyright ?: ('© '.date('Y').' '.$siteName.'. All rights reserved.') }}</span>
                    <span class="site-footer__legal"><a href="{{ route('legal.privacy') }}">Privacy</a><a href="{{ route('legal.terms') }}">Terms</a></span>
                </div>
                @if($siteFooterPromoEnabled)
                <div class="site-footer__promo">
                    <span>Want to reach parents and students every month?</span>
                    <a class="btn btn-footer" href="{{ route('contact') }}">Advertise with us</a>
                </div>
                @endif
            </div>
        </div>
    </footer>
    @endif

    @livewireScripts
</body>
</html>
