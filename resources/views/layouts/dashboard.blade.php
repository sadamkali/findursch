@php
    $user = auth()->user();
    $isSchoolOwner = $user?->hasRole('school_owner') ?? false;

    $navItems = $isSchoolOwner
        ? [
            ['label' => 'Dashboard', 'url' => route('owner.dashboard'), 'active' => 'owner.dashboard', 'icon' => 'home'],
            ['label' => 'Add a school', 'url' => route('owner.schools.create'), 'active' => 'owner.schools.create', 'icon' => 'plus'],
            ['label' => 'Inquiries', 'url' => route('owner.inquiries'), 'active' => 'owner.inquiries', 'icon' => 'inbox'],
            ['label' => 'Reviews', 'url' => route('owner.reviews'), 'active' => 'owner.reviews', 'icon' => 'star'],
        ]
        : [
            ['label' => 'Overview', 'url' => route('user.dashboard'), 'active' => 'user.dashboard', 'icon' => 'home'],
            ['label' => 'Saved schools', 'url' => route('user.saved-schools'), 'active' => 'user.saved-schools', 'icon' => 'bookmark'],
            ['label' => 'My inquiries', 'url' => route('user.inquiries'), 'active' => 'user.inquiries', 'icon' => 'inbox'],
            ['label' => 'My reviews', 'url' => route('user.reviews'), 'active' => 'user.reviews', 'icon' => 'star'],
            ['label' => 'Profile settings', 'url' => route('user.profile'), 'active' => 'user.profile', 'icon' => 'user'],
        ];

    $currentPage = match (request()->route()?->getName()) {
        'owner.dashboard', 'user.dashboard' => 'Dashboard',
        'owner.schools.create' => 'Add a school',
        'owner.schools.edit' => 'Edit school',
        'owner.schools.details' => 'School details',
        'owner.inquiries', 'user.inquiries' => 'Inquiries',
        'owner.reviews', 'user.reviews' => 'Reviews',
        'user.saved-schools' => 'Saved schools',
        'user.profile' => 'Profile settings',
        default => 'Workspace',
    };
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full antialiased">
<div class="fi-dashboard" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <button type="button" class="fi-dashboard-backdrop" x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" aria-label="Close navigation"></button>

    <aside class="fi-dashboard-sidebar" :class="{ 'is-open': sidebarOpen }" aria-label="Dashboard navigation">
        <div class="fi-dashboard-brand">
            <a href="{{ route('home') }}" class="fi-dashboard-brand-mark" aria-label="FindurSch home">
                <span class="fi-dashboard-brand-icon">F</span>
                <span>FindurSch</span>
            </a>
            <span class="fi-dashboard-brand-caption">WORKSPACE</span>
        </div>

        <div class="fi-dashboard-user">
            <div class="fi-dashboard-avatar">{{ mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1)) }}</div>
            <div class="fi-dashboard-user-copy">
                <p>{{ $user->name }}</p>
                <span>{{ $user->email }}</span>
            </div>
        </div>

        <nav class="fi-dashboard-nav">
            <p class="fi-dashboard-nav-label">MAIN MENU</p>
            @foreach ($navItems as $item)
                <a href="{{ $item['url'] }}"
                   @class(['fi-dashboard-nav-item', 'is-active' => request()->routeIs($item['active'])])
                   @click="sidebarOpen = false">
                    <span class="fi-dashboard-nav-icon" aria-hidden="true">
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

        <div class="fi-dashboard-sidebar-bottom">
            <a href="{{ route('home') }}" class="fi-dashboard-bottom-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9M18 13v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5"/></svg>
                View public website
            </a>
            <div class="fi-dashboard-version">FindurSch account workspace</div>
        </div>
    </aside>

    <div class="fi-dashboard-main">
        <header class="fi-dashboard-topbar">
            <div class="fi-dashboard-topbar-left">
                <button type="button" class="fi-dashboard-menu-button" @click="sidebarOpen = true" aria-label="Open navigation">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="fi-dashboard-breadcrumb">
                    <span>Workspace</span>
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24a.75.75 0 0 1 0 1.06l-4.24 4.24a.75.75 0 0 1-1.08 0Z" clip-rule="evenodd"/></svg>
                    <strong>{{ $currentPage }}</strong>
                </div>
            </div>
            <div class="fi-dashboard-topbar-actions">
                <a href="{{ route('home') }}" class="fi-dashboard-public-link">Visit site</a>
                <span class="fi-dashboard-topbar-divider"></span>
                <div class="fi-dashboard-topbar-user">
                    <span class="fi-dashboard-topbar-avatar">{{ mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1)) }}</span>
                    <span>{{ $user->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="fi-dashboard-logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5M15 12H3m9-8h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5"/></svg>
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="fi-dashboard-content">
            {{ $slot ?? '' }}
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
