<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    @php
        $siteSettings = \App\Models\Setting::query()
            ->whereIn('key', [
                'site_name', 'site_tagline', 'site_description', 'site_logo', 'site_horizontal_logo', 'site_homepage_logo',
                'site_footer_logo', 'site_mobile_logo', 'site_favicon', 'site_apple_touch_icon', 'site_social_image',
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
        $siteTagline = $siteSettings['site_tagline'] ?? 'Discover schools across Uganda';
        $siteDescription = $siteSettings['site_description'] ?? 'Uganda’s school discovery platform.';
        $siteLogoUrl = filled($siteSettings['site_logo'] ?? null) ? $siteSettings['site_logo'] : asset('brand/findursch-logo.png');
        $defaultHorizontalLogoUrl = 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_horizontal_logo/062d6754-5266-46a5-9f83-1a97bc393a19.png';
        $siteHorizontalLogoUrl = filled($siteSettings['site_horizontal_logo'] ?? null) ? $siteSettings['site_horizontal_logo'] : $defaultHorizontalLogoUrl;
        $siteHomepageLogoUrl = filled($siteSettings['site_homepage_logo'] ?? null) ? $siteSettings['site_homepage_logo'] : $siteHorizontalLogoUrl;
        $siteHeaderLogoUrl = request()->routeIs('home') ? $siteHomepageLogoUrl : $siteHorizontalLogoUrl;
        $siteMobileLogoUrl = filled($siteSettings['site_mobile_logo'] ?? null) ? $siteSettings['site_mobile_logo'] : $siteHeaderLogoUrl;
        $siteFooterLogoUrl = filled($siteSettings['site_footer_logo'] ?? null) ? $siteSettings['site_footer_logo'] : $siteHorizontalLogoUrl;
        $siteSocialImageUrl = filled($siteSettings['site_social_image'] ?? null) ? $siteSettings['site_social_image'] : $siteLogoUrl;
        $siteFaviconUrl = filled($siteSettings['site_favicon'] ?? null) ? $siteSettings['site_favicon'] : asset('brand/findursch-icon.png');
        $siteAppleTouchIconUrl = filled($siteSettings['site_apple_touch_icon'] ?? null) ? $siteSettings['site_apple_touch_icon'] : asset('brand/apple-touch-icon.png');
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
    @if($siteSocialImageUrl)
        <meta property="og:image" content="{{ $siteSocialImageUrl }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $siteSocialImageUrl }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <link rel="icon" href="{{ $siteFaviconUrl }}" onerror="this.onerror=null;this.href='{{ asset('brand/findursch-icon.png') }}';">
    <link rel="apple-touch-icon" href="{{ $siteAppleTouchIconUrl }}" onerror="this.onerror=null;this.href='{{ asset('brand/apple-touch-icon.png') }}';">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- These page styles are safely scoped to their page wrappers; load them directly so Livewire route rendering cannot skip them. --}}
    <link rel="stylesheet" href="{{ asset('css/events-reference.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/events-listing-reference.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/schools-reference.css') }}?v=11">
    @if (request()->routeIs('district.show'))<link rel="stylesheet" href="{{ asset('css/district-reference.css') }}?v=1">@endif
    @if (request()->routeIs('jobs.*'))<link rel="stylesheet" href="{{ asset('css/jobs-reference.css') }}?v=1">@endif
    <link rel="stylesheet" href="{{ asset('css/mobile-drawer-reference.css') }}?v=3">
    @if (request()->routeIs('articles.*', 'news.*', 'events.*', 'school-news.*', 'scholarships.*'))<link rel="stylesheet" href="{{ asset('css/blogs.css') }}?v=4">@endif
    @if (request()->routeIs('scholarships.*'))<link rel="stylesheet" href="{{ asset('css/scholarships-reference.css') }}?v=6">@endif
    @if (request()->routeIs('news.*'))<link rel="stylesheet" href="{{ asset('css/news.css') }}?v=5">@endif
    <link rel="stylesheet" href="{{ asset('css/site-ad-banners.css') }}?v=4">
    @livewireStyles
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
    <link rel="stylesheet" href="{{ asset('css/site-design-system.css') }}?v=8">
    <link rel="stylesheet" href="{{ asset('css/site-page-hero.css') }}?v=1">
    @if (request()->routeIs('home'))<link rel="stylesheet" href="{{ asset('css/home-reference.css') }}?v=17">@endif
    <link rel="stylesheet" href="{{ asset('css/school-tile-cards.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/site-footer.css') }}?v=3">
</head>
<body class="h-full bg-white text-gray-900 antialiased">
    @php
        $dashboardUrl = rtrim(config('services.dashboard.url', 'https://admin.riotinvestments.space'), '/');
        $accountDashboardUrl = rtrim(config('services.dashboard.account_url', 'https://account.riotinvestments.space'), '/');
        $signInUrl = $accountDashboardUrl.'/login?returnTo=%2Faccount';
        $schoolRegistrationUrl = $accountDashboardUrl.'/register?type=school_owner&returnTo=%2Faccount';

        // One canonical navigation list keeps the public header identical across pages.
        // The settings page can disable standard items, but the order and labels match
        // the Schools reference header.
        $savedNavigation = json_decode((string) ($siteSettings['site_navigation'] ?? '[]'), true);
        $savedNavigation = is_array($savedNavigation) ? collect($savedNavigation) : collect();
        $navigationFallback = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Schools', 'href' => route('schools.index')],
            ['label' => 'Scholarships', 'href' => route('scholarships.index')],
            ['label' => 'News', 'href' => route('news.index')],
            ['label' => 'Jobs', 'href' => route('jobs.index')],
            ['label' => 'Events', 'href' => route('events.index')],
        ];
        $currentPath = '/'.trim(request()->path(), '/');
        $primaryNavigation = collect($navigationFallback)
            ->filter(function ($item) use ($savedNavigation) {
                $saved = $savedNavigation->firstWhere('href', $item['href']);
                return ! is_array($saved) || ($saved['enabled'] ?? true);
            })
            ->map(function ($item) use ($currentPath) {
                $path = parse_url($item['href'], PHP_URL_PATH) ?: '/';
                $active = $path === '/'
                    ? $currentPath === '/'
                    : ($currentPath === $path || str_starts_with($currentPath, rtrim($path, '/').'/'));
                return ['label' => $item['label'], 'href' => $item['href'], 'active' => $active];
            })
            ->values();

        $dashboardIdentity = session('dashboard_identity');
        $dashboardIdentityRole = is_array($dashboardIdentity) ? ($dashboardIdentity['role'] ?? null) : null;
        $accountLinkLabel = null;
        $accountLinkUrl = null;
        $headerAccountUser = auth()->user();
        $headerAccountSchool = null;
        $headerAccountAvatarUrl = null;
        $headerAccountName = (string) ($headerAccountUser?->name ?? '');
        $headerAccountNameParts = preg_split('/\\s+/', trim($headerAccountName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $headerAccountInitials = mb_strtoupper(
            mb_substr($headerAccountNameParts[0] ?? 'U', 0, 1)
            . (count($headerAccountNameParts) > 1 ? mb_substr($headerAccountNameParts[count($headerAccountNameParts) - 1], 0, 1) : '')
        );
        $savedSchoolCount = auth()->check() ? auth()->user()->savedSchools()->count() : 0;

        if (auth()->check()) {
            if ($headerAccountUser->hasRole('school_owner')) {
                $headerAccountSchool = $headerAccountUser->ownedSchools()
                    ->with('media')
                    ->latest('updated_at')
                    ->first();
                $headerAccountAvatarUrl = $headerAccountSchool?->getFirstMedia('badge')?->getUrl()
                    ?: $headerAccountSchool?->getFirstMedia('logo')?->getUrl();
            }

            if ($headerAccountUser->hasRole('admin')) {
                $accountLinkLabel = 'Admin dashboard';
                $accountLinkUrl = route('auth.dashboard-login', ['returnTo' => '/admin']);
            } elseif (auth()->user()->hasRole('school_owner')) {
                $accountLinkLabel = 'School dashboard';
                $accountLinkUrl = route('auth.dashboard-login', ['returnTo' => '/owner']);
            } else {
                $accountLinkLabel = 'My account';
                $accountLinkUrl = route('user.home');
            }
        } elseif ($dashboardIdentityRole === 'admin') {
            $accountLinkLabel = 'Admin dashboard';
            $accountLinkUrl = route('auth.dashboard-login', ['returnTo' => '/admin']);
        } elseif ($dashboardIdentityRole === 'school_owner') {
            $accountLinkLabel = 'School dashboard';
            $accountLinkUrl = route('auth.dashboard-login', ['returnTo' => '/owner']);
        }
    @endphp

    {{-- Shared public header — aligned with the Schools directory reference. --}}
    @if($siteTopbarEnabled)
    <div class="site-topbar" role="region" aria-label="Quick links">
        <div class="site-wrap site-topbar__inner">
            <a class="site-topbar__link" href="{{ route('scholarships.index') }}">Scholarships</a>
            <a class="site-topbar__link" href="{{ route('events.index') }}">Events</a>
            <a class="site-topbar__link" href="{{ route('articles.index') }}">Articles</a>
            <a class="site-topbar__link" href="{{ route('contact') }}">Contact</a>
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
                @if($siteHeaderLogoUrl)
                    <img src="{{ $siteHeaderLogoUrl }}" alt="{{ $siteName }}" width="168" height="37" fetchpriority="high" onerror="this.onerror=null;this.src='https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_horizontal_logo/062d6754-5266-46a5-9f83-1a97bc393a19.png';">
                @else{{ $siteName }}@endif
            </a>

            @if($siteNavigationEnabled)
            <nav class="site-nav" aria-label="Main navigation">
                @foreach($primaryNavigation as $item)
                    <a href="{{ $item['href'] }}" @class(['is-active' => $item['active']])>{{ $item['label'] }}</a>
                @endforeach
            </nav>
            @endif

            <div class="site-header__actions">
                @if($siteAccountActionsEnabled)
                    <a class="site-header-icon" href="{{ auth()->check() ? route('user.saved-schools') : route('login') }}" aria-label="Saved schools" title="Saved schools">
                        <svg class="site-header-icon__svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4.5h12v16l-6-3.5-6 3.5z"/></svg>
                        @if(auth()->check() && $savedSchoolCount > 0)<em>{{ $savedSchoolCount }}</em>@endif
                    </a>
                    <a class="site-header-icon site-header-account-avatar @if(auth()->check() && !$headerAccountAvatarUrl) site-header-account-avatar--with-name @endif" href="{{ $accountLinkUrl ?? route('login') }}"
                       aria-label="{{ auth()->check() ? (($accountLinkLabel ?? 'My account').' for '.$headerAccountName) : 'Sign in to your account' }}"
                       title="{{ auth()->check() ? ($headerAccountSchool?->name ?? $headerAccountName) : 'Account' }}">
                        @if($headerAccountAvatarUrl)
                            <img class="site-header-account-avatar__image" src="{{ $headerAccountAvatarUrl }}" alt="{{ $headerAccountSchool?->name ?? $headerAccountName }}">
                        @elseif(auth()->check())
                            <span class="site-header-account-avatar__initials" aria-hidden="true">{{ $headerAccountInitials }}</span>
                            <span class="site-header-account-avatar__name" aria-hidden="true">{{ $headerAccountName }}</span>
                        @else
                            <svg class="site-header-icon__svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.6-3.6 3-5.5 7-5.5s6.4 1.9 7 5.5"/></svg>
                        @endif
                    </a>
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
                    @if($siteMobileLogoUrl)
                        <img src="{{ $siteMobileLogoUrl }}" alt="{{ $siteName }}" width="168" height="37" onerror="this.onerror=null;this.src='https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_horizontal_logo/062d6754-5266-46a5-9f83-1a97bc393a19.png';">
                    @else{{ $siteName }}@endif
                </a>
                <div class="site-mobile-drawer__head-actions">
                    @if($siteAccountActionsEnabled)
                    <a class="site-header-icon site-header-icon--drawer site-header-account-avatar @if(auth()->check() && !$headerAccountAvatarUrl) site-header-account-avatar--with-name @endif" href="{{ $accountLinkUrl ?? route('login') }}"
                       aria-label="{{ auth()->check() ? (($accountLinkLabel ?? 'My account').' for '.$headerAccountName) : 'Sign in to your account' }}"
                       title="{{ auth()->check() ? ($headerAccountSchool?->name ?? $headerAccountName) : 'Account' }}">
                        @if($headerAccountAvatarUrl)
                            <img class="site-header-account-avatar__image" src="{{ $headerAccountAvatarUrl }}" alt="{{ $headerAccountSchool?->name ?? $headerAccountName }}">
                        @elseif(auth()->check())
                            <span class="site-header-account-avatar__initials" aria-hidden="true">{{ $headerAccountInitials }}</span>
                            <span class="site-header-account-avatar__name" aria-hidden="true">{{ $headerAccountName }}</span>
                        @else
                            <svg class="site-header-icon__svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.6-3.6 3-5.5 7-5.5s6.4 1.9 7 5.5"/></svg>
                        @endif
                    </a>
                    @endif
                    <button type="button" class="site-mobile-drawer__close" x-ref="mobileClose" @click="mobileMenuOpen = false" aria-label="Close menu">×</button>
                </div>
            </div>

            <nav class="site-mobile-drawer__links" aria-label="Mobile navigation">
                @foreach($primaryNavigation as $item)
                    <a @click="mobileMenuOpen = false" href="{{ $item['href'] }}" @class(['is-active' => $item['active']])>{{ $item['label'] }}</a>
                @endforeach
                <hr>
                <div class="site-mobile-drawer__secondary">
                    <a @click="mobileMenuOpen = false" href="{{ route('scholarships.index') }}">Scholarships</a>
                    <a @click="mobileMenuOpen = false" href="{{ route('events.index') }}">Events</a>
                    <a @click="mobileMenuOpen = false" href="{{ route('articles.index') }}">Articles</a>
                    <a @click="mobileMenuOpen = false" href="{{ route('contact') }}">Contact</a>
                </div>
            </nav>
        </aside>
        @endif
    </header>

    @unless(request()->routeIs('home', 'schools.index'))
        <a class="site-register-tab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>
    @endunless

    <main class="min-h-screen">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <x-site-footer />

    @livewireScripts
</body>
</html>
