@php
    use App\Models\Setting;

    $headerSettings = Setting::query()
        ->whereIn('key', [
            'site_name', 'site_navigation', 'site_topbar_enabled',
            'site_navigation_enabled', 'site_account_actions_enabled',
            'site_header_cta_label', 'primary_color', 'accent_color',
        ])
        ->pluck('value', 'key');

    $headerSiteName = ($headerSettings['site_name'] ?? null) ?: 'FindURSCH';
    $headerTopbarEnabled = filter_var($headerSettings['site_topbar_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $headerNavigationEnabled = filter_var($headerSettings['site_navigation_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $headerAccountActionsEnabled = filter_var($headerSettings['site_account_actions_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $headerCtaLabel = ($headerSettings['site_header_cta_label'] ?? null) ?: 'List your school';

    $headerPrimaryColor = is_string($headerSettings['primary_color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $headerSettings['primary_color'])
        ? $headerSettings['primary_color'] : '#0c5a49';
    $headerAccentColor = is_string($headerSettings['accent_color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $headerSettings['accent_color'])
        ? $headerSettings['accent_color'] : '#f0b030';
    $headerColorsStyle = '--brand: '.$headerPrimaryColor.'; --brand-dk: color-mix(in srgb, '.$headerPrimaryColor.' 78%, #000); --brand-xdk: color-mix(in srgb, '.$headerPrimaryColor.' 55%, #000); --accent: '.$headerAccentColor.'; --accent-dk: color-mix(in srgb, '.$headerAccentColor.' 78%, #000);';

    $savedNavigation = json_decode((string) ($headerSettings['site_navigation'] ?? '[]'), true);
    $savedNavigation = is_array($savedNavigation) ? collect($savedNavigation) : collect();
    $navigationFallback = [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Schools', 'href' => route('schools.index')],
        ['label' => 'Compare', 'href' => route('compare')],
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
@endphp

@if($headerTopbarEnabled)
<div class="topbar" role="region" aria-label="Quick links" style="{{ $headerColorsStyle }}">
    <div class="wrap">
        <a href="{{ route('scholarships.index') }}">Scholarships</a>
        <a href="{{ route('events.index') }}">Events</a>
        <a href="{{ route('articles.index') }}">Articles</a>
        <a href="{{ route('contact') }}">Contact</a>
        @if($headerAccountActionsEnabled)
            <a href="{{ route('login') }}">Log in</a>
            <a href="{{ route('register') }}">Sign up</a>
        @endif
    </div>
</div>
@endif

<header style="{{ $headerColorsStyle }}">
    <div class="wrap">
        <a class="logo" href="{{ route('home') }}" aria-label="{{ $headerSiteName }} home">Find<b>UR</b>SCH</a>
        @if($headerNavigationEnabled)
            <nav aria-label="Main navigation">
                @foreach($primaryNavigation as $item)
                    <a href="{{ $item['href'] }}" @class(['on' => $item['active']])>{{ $item['label'] }}</a>
                @endforeach
            </nav>
        @endif
        <a class="btn btn-ghost" href="{{ route('register') }}">{{ $headerCtaLabel }}</a>
        @if($headerNavigationEnabled)
            <button class="burger" id="burger" type="button" aria-label="Menu" aria-expanded="false">☰</button>
        @endif
    </div>
</header>

<a class="rtab" href="{{ route('register') }}" style="{{ $headerColorsStyle }}">REGISTER YOUR SCHOOL</a>