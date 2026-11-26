@php
    $footerSettings = \App\Models\Setting::publicSiteValues();

    $footerEnabled = filter_var($footerSettings['site_footer_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerExploreEnabled = filter_var($footerSettings['site_footer_explore_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerResourcesEnabled = filter_var($footerSettings['site_footer_resources_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerSupportEnabled = filter_var($footerSettings['site_footer_support_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerPromoEnabled = filter_var($footerSettings['site_footer_promo_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);

    $footerPrimaryColor = is_string($footerSettings['primary_color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $footerSettings['primary_color'])
        ? $footerSettings['primary_color'] : '#0c5a49';
    $footerAccentColor = is_string($footerSettings['accent_color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $footerSettings['accent_color'])
        ? $footerSettings['accent_color'] : '#f0b030';
    $footerColorsStyle = '--brand: '.$footerPrimaryColor.'; --brand-dk: color-mix(in srgb, '.$footerPrimaryColor.' 78%, #000); --brand-xdk: color-mix(in srgb, '.$footerPrimaryColor.' 55%, #000); --accent: '.$footerAccentColor.'; --accent-dk: color-mix(in srgb, '.$footerAccentColor.' 78%, #000);';

    $footerName = ($footerSettings['site_name'] ?? null) ?: 'FindURSCH';
    $footerDescription = ($footerSettings['site_description'] ?? null) ?: 'Uganda’s school discovery, comparison and admissions platform.';
    $footerAddress = ($footerSettings['contact_address'] ?? null) ?: 'Kampala, Uganda';
    $footerPhone = $footerSettings['support_phone'] ?? '';
    $footerEmail = $footerSettings['support_email'] ?? '';
    $footerCopyright = ($footerSettings['site_footer_copyright_text'] ?? null)
        ?: ('© '.date('Y').' '.$footerName.'. All rights reserved.');

    $footerSchoolTypes = \App\Models\SchoolType::query()
        ->where('is_active', true)
        ->get(['id', 'name']);
    $footerSchoolTypeUrl = function (array $terms) use ($footerSchoolTypes) {
        $type = $footerSchoolTypes->first(function ($item) use ($terms) {
            $name = mb_strtolower((string) $item->name);
            foreach ($terms as $term) {
                if (str_contains($name, $term)) {
                    return true;
                }
            }
            return false;
        });

        return route('schools.index', $type ? ['type' => $type->id] : []);
    };
@endphp

@if($footerEnabled)
<footer style="{{ $footerColorsStyle }}">
    <div class="wrap">
        <div class="fgrid">
            <div>
                <a class="logo" href="{{ route('home') }}" aria-label="{{ $footerName }} home">Find<b>UR</b>SCH</a>
                <p>{{ $footerDescription }}</p>
                <p>
                    {{ $footerAddress }}
                    @if($footerPhone)<br><a href="tel:{{ preg_replace('/[^0-9+]/', '', $footerPhone) }}">{{ $footerPhone }}</a>@endif
                    @if($footerEmail)<br><a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a>@endif
                </p>
            </div>

            @if($footerExploreEnabled)
                <div>
                    <h6>Explore schools</h6>
                    <ul>
                        <li><a href="{{ $footerSchoolTypeUrl(['primary']) }}">Primary schools</a></li>
                        <li><a href="{{ $footerSchoolTypeUrl(['secondary']) }}">Secondary schools</a></li>
                        <li><a href="{{ $footerSchoolTypeUrl(['universit']) }}">Universities</a></li>
                        <li><a href="{{ $footerSchoolTypeUrl(['vocational']) }}">Vocational schools</a></li>
                        <li><a href="{{ route('schools.index') }}">Boarding schools</a></li>
                        <li><a href="{{ $footerSchoolTypeUrl(['international']) }}">International schools</a></li>
                    </ul>
                </div>
            @endif

            @if($footerResourcesEnabled)
                <div>
                    <h6>Resources</h6>
                    <ul>
                        <li><a href="{{ route('news.index') }}">Latest news</a></li>
                        <li><a href="{{ route('articles.index') }}">Blogs</a></li>
                        <li><a href="{{ route('scholarships.index') }}">Scholarships</a></li>
                        <li><a href="{{ route('jobs.index') }}">School jobs</a></li>
                        <li><a href="{{ route('events.index') }}">Events calendar</a></li>
                        <li><a href="{{ route('compare') }}">Compare schools</a></li>
                    </ul>
                </div>
            @endif

            @if($footerSupportEnabled)
                <div>
                    <h6>Support</h6>
                    <ul>
                        <li><a href="{{ route('register') }}">List your school</a></li>
                        <li><a href="{{ route('contact', ['subject' => 'Advertising enquiry']) }}">Advertise with us</a></li>
                        <li><a href="{{ route('about') }}">About</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                        <li><a href="{{ route('legal.terms') }}">Terms and conditions</a></li>
                        <li><a href="{{ route('legal.privacy') }}">Privacy policy</a></li>
                    </ul>
                </div>
            @endif
        </div>

        <div class="fbar">
            <span>{{ $footerCopyright }}</span>
            @if($footerPromoEnabled)
                <div class="box2">
                    <span>Want to reach parents and students every month?</span>
                    <a class="btn btn-ghost" href="{{ route('contact', ['subject' => 'Advertising enquiry']) }}">Advertise with us</a>
                </div>
            @endif
        </div>
    </div>
</footer>
@endif