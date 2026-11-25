@php
    $user = auth()->user();

    $navItems = [
        ['label' => 'Account overview', 'url' => route('user.home'), 'active' => 'user.home', 'icon' => 'home'],
        ['label' => 'Saved schools', 'url' => route('user.saved-schools'), 'active' => 'user.saved-schools', 'icon' => 'bookmark'],
        ['label' => 'My inquiries', 'url' => route('user.inquiries'), 'active' => 'user.inquiries', 'icon' => 'inbox'],
        ['label' => 'My reviews', 'url' => route('user.reviews'), 'active' => 'user.reviews', 'icon' => 'star'],
        ['label' => 'Profile settings', 'url' => route('user.profile'), 'active' => 'user.profile', 'icon' => 'user'],
    ];

    $currentPage = match (request()->route()?->getName()) {
        'user.home' => 'Account overview',
        'user.saved-schools' => 'Saved schools',
        'user.inquiries' => 'My inquiries',
        'user.reviews' => 'My reviews',
        'user.profile' => 'Profile settings',
        default => 'My account',
    };
    $accountThemeSettings = \App\Models\Setting::query()
        ->whereIn('key', ['primary_color', 'accent_color', 'font_display', 'font_body'])
        ->pluck('value', 'key');
    $accountPrimaryColor = is_string($accountThemeSettings['primary_color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $accountThemeSettings['primary_color']) ? $accountThemeSettings['primary_color'] : '#0c5a49';
    $accountAccentColor = is_string($accountThemeSettings['accent_color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $accountThemeSettings['accent_color']) ? $accountThemeSettings['accent_color'] : '#f0b030';
    $accountDisplayFont = in_array($accountThemeSettings['font_display'] ?? '', ['Playfair Display', 'DM Serif Display'], true) ? $accountThemeSettings['font_display'] : 'Playfair Display';
    $accountBodyFont = in_array($accountThemeSettings['font_body'] ?? '', ['Inter', 'system-ui'], true) ? $accountThemeSettings['font_body'] : 'Inter';
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $currentPage }} — FindurSch</title>
    <meta name="description" content="Manage your FindurSch account.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" href="{{ asset('brand/findursch-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: {{ $accountPrimaryColor }};
            --brand-dark: color-mix(in srgb, {{ $accountPrimaryColor }} 78%, #000);
            --brand-deep: color-mix(in srgb, {{ $accountPrimaryColor }} 55%, #000);
            --gold: {{ $accountAccentColor }};
            --gold-dark: color-mix(in srgb, {{ $accountAccentColor }} 78%, #000);
            --accent: {{ $accountAccentColor }};
            --accent-dk: color-mix(in srgb, {{ $accountAccentColor }} 78%, #000);
            --display-font: '{{ $accountDisplayFont }}', Georgia, serif;
            --font-display: '{{ $accountDisplayFont }}', Georgia, serif;
            --font-body: '{{ $accountBodyFont }}', system-ui, sans-serif;
        }
        body { font-family: '{{ $accountBodyFont }}', system-ui, sans-serif; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/site-design-system.css') }}?v=5">
    <link rel="stylesheet" href="{{ asset('css/school-tile-cards.css') }}?v=1">
    @livewireStyles
</head>
<body class="h-full antialiased">
<div class="account-workspace" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <button type="button" class="account-workspace-backdrop" x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" aria-label="Close navigation"></button>

    <aside class="account-workspace-sidebar" :class="{ 'is-open': sidebarOpen }" aria-label="Account navigation">
        <div class="account-workspace-brand">
            <a href="{{ route('home') }}" class="account-workspace-brand-mark" aria-label="FindurSch home">
                <img src="{{ asset('brand/findursch-logo.png') }}" alt="FindurSch" width="184" height="41" style="display:block;width:184px;height:auto;max-height:40px;object-fit:contain;object-position:left center">
            </a>
            <span class="account-workspace-brand-caption" style="padding-left:0">YOUR ACCOUNT</span>
        </div>

        <div class="account-workspace-user">
            <div class="account-workspace-avatar">{{ mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1)) }}</div>
            <div class="account-workspace-user-copy">
                <p>{{ $user->name }}</p>
                <span>{{ $user->email }}</span>
            </div>
        </div>

        <nav class="account-workspace-nav">
            <p class="account-workspace-nav-label">MAIN MENU</p>
            @foreach ($navItems as $item)
                <a href="{{ $item['url'] }}"
                   @class(['account-workspace-nav-item', 'is-active' => request()->routeIs($item['active'])])
                   @click="sidebarOpen = false">
                    <span class="account-workspace-nav-icon" aria-hidden="true">
                        @switch($item['icon'])
                            @case('home')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"/></svg>
                                @break
                            @case('plus')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v8M8 12h8"/></svg>
                                @break
                            @case('inbox')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v12h-5l-2 3h-2l-2-3H4V4Z"/><path stroke-linecap="round" d="M8 9h8M8 12h5"/></svg>
                                @break
                            @case('star')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>
                                @break
                            @case('bookmark')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 4.5A1.5 1.5 0 0 1 7.5 3h9A1.5 1.5 0 0 1 18 4.5V21l-6-4-6 4V4.5Z"/></svg>
                                @break
                            @case('user')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 21a8 8 0 0 1 16 0"/></svg>
                                @break
                        @endswitch
                    </span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="account-workspace-sidebar-bottom">
            <a href="{{ route('home') }}" class="account-workspace-bottom-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9M18 13v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5"/></svg>
                View public website
            </a>
            <div class="account-workspace-version">FindurSch account</div>
        </div>
    </aside>

    <div class="account-workspace-main">
        <header class="account-workspace-topbar">
            <div class="account-workspace-topbar-left">
                <button type="button" class="account-workspace-menu-button" @click="sidebarOpen = true" aria-label="Open navigation">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="account-workspace-breadcrumb">
                    <span>My account</span>
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24a.75.75 0 0 1 0 1.06l-4.24 4.24a.75.75 0 0 1-1.08 0Z" clip-rule="evenodd"/></svg>
                    <strong>{{ $currentPage }}</strong>
                </div>
            </div>
            <div class="account-workspace-topbar-actions">
                <a href="{{ route('home') }}" class="account-workspace-public-link">Visit site</a>
                <span class="account-workspace-topbar-divider"></span>
                <div class="account-workspace-topbar-user">
                    <span class="account-workspace-topbar-avatar">{{ mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1)) }}</span>
                    <span>{{ $user->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="account-workspace-logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5M15 12H3m9-8h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5"/></svg>
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="account-workspace-content">
            {{ $slot ?? '' }}
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
