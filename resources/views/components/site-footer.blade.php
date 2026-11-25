@php
    $footerSettings = \\App\\Models\\Setting::query()
        ->whereIn('key', [
            'site_name', 'site_description', 'site_footer_logo', 'site_horizontal_logo',
            'contact_address', 'support_phone', 'support_email', 'support_whatsapp',
            'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url',
            'site_footer_enabled', 'site_footer_explore_enabled', 'site_footer_resources_enabled',
            'site_footer_support_enabled', 'site_footer_social_enabled', 'site_footer_promo_enabled',
            'site_footer_copyright_text', 'primary_color', 'accent_color',
        ])
        ->pluck('value', 'key');

    $footerEnabled = filter_var($footerSettings['site_footer_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerExploreEnabled = filter_var($footerSettings['site_footer_explore_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerResourcesEnabled = filter_var($footerSettings['site_footer_resources_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerSupportEnabled = filter_var($footerSettings['site_footer_support_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerSocialEnabled = filter_var($footerSettings['site_footer_social_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerPromoEnabled = filter_var($footerSettings['site_footer_promo_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);

    $footerPrimaryColor = is_string($footerSettings['primary_color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $footerSettings['primary_color']) ? $footerSettings['primary_color'] : '#0c5a49';
    $footerAccentColor = is_string($footerSettings['accent_color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $footerSettings['accent_color']) ? $footerSettings['accent_color'] : '#f0b030';
    $footerName = ($footerSettings['site_name'] ?? null) ?: 'FindURSCH';
    $footerDescription = ($footerSettings['site_description'] ?? null) ?: 'Discover, compare and learn about schools across Uganda.';
    $footerLogo = ($footerSettings['site_footer_logo'] ?? null)
        ?: (($footerSettings['site_horizontal_logo'] ?? null) ?: 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_horizontal_logo/062d6754-5266-46a5-9f83-1a97bc393a19.png');
    $footerAddress = $footerSettings['contact_address'] ?? '';
    $footerPhone = $footerSettings['support_phone'] ?? '';
    $footerEmail = $footerSettings['support_email'] ?? '';
    $footerWhatsapp = preg_replace('/\\D+/', '', (string) ($footerSettings['support_whatsapp'] ?? ''));
    $footerCopyright = ($footerSettings['site_footer_copyright_text'] ?? null)
        ?: ('© '.date('Y').' '.$footerName.'. All rights reserved.');
@endphp

@if($footerEnabled)
<footer class="site-footer" aria-label="Website footer" style="--fd-brand: {{ $footerPrimaryColor }}; --fd-brand-deep: color-mix(in srgb, {{ $footerPrimaryColor }} 58%, #052a22); --fd-accent: {{ $footerAccentColor }}; --fd-accent-dark: color-mix(in srgb, {{ $footerAccentColor }} 78%, #000);">
    <div class="site-footer__accent" aria-hidden="true"></div>
    <div class="site-footer__inner">
        <div class="site-footer__main">
            <div class="site-footer__brand">
                <a href="{{ route('home') }}" class="site-footer__logo" aria-label="{{ $footerName }} home">
                    <img
                        src="{{ $footerLogo }}"
                        alt="{{ $footerName }}"
                        width="220"
                        height="56"
                        loading="lazy"
                        decoding="async"
                        onerror="this.onerror=null;this.src='{{ asset('brand/findursch-logo.png') }}';"
                    >
                </a>
                <p class="site-footer__description">{{ $footerDescription }}</p>

                @if($footerAddress)
                    <p class="site-footer__contact"><span class="site-footer__contact-label">Address</span>{{ $footerAddress }}</p>
                @endif
                @if($footerPhone)
                    <p class="site-footer__contact"><span class="site-footer__contact-label">Phone</span><a href="tel:{{ preg_replace('/[^0-9+]/', '', $footerPhone) }}">{{ $footerPhone }}</a></p>
                @endif
                @if($footerEmail)
                    <p class="site-footer__contact"><span class="site-footer__contact-label">Email</span><a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a></p>
                @endif
                @if($footerWhatsapp)
                    <a class="site-footer__whatsapp" href="https://wa.me/{{ $footerWhatsapp }}" target="_blank" rel="noopener noreferrer">Chat with us on WhatsApp</a>
                @endif

                @if($footerSocialEnabled)
                    <nav class="site-footer__social" aria-label="Social media">
                        @foreach([
                            'Facebook' => 'facebook_url',
                            'X' => 'twitter_url',
                            'Instagram' => 'instagram_url',
                            'YouTube' => 'youtube_url',
                        ] as $socialLabel => $socialKey)
                            @if(filled($footerSettings[$socialKey] ?? null))
                                <a href="{{ $footerSettings[$socialKey] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $socialLabel }}">{{ $socialLabel }}</a>
                            @endif
                        @endforeach
                    </nav>
                @endif
            </div>

            @if($footerExploreEnabled)
                <div class="site-footer__column">
                    <h2>Explore schools</h2>
                    <ul>
                        <li><a href="{{ route('schools.index') }}">Find a school</a></li>
                        <li><a href="{{ route('compare') }}">Compare schools</a></li>
                        <li><a href="{{ route('schools.index', ['sort' => 'newest']) }}">Recently added</a></li>
                        <li><a href="{{ route('schools.index', ['verified_only' => 1]) }}">Verified schools</a></li>
                    </ul>
                </div>
            @endif

            @if($footerResourcesEnabled)
                <div class="site-footer__column">
                    <h2>Resources</h2>
                    <ul>
                        <li><a href="{{ route('news.index') }}">Education news</a></li>
                        <li><a href="{{ route('articles.index') }}">Articles &amp; insights</a></li>
                        <li><a href="{{ route('scholarships.index') }}">Scholarships &amp; bursaries</a></li>
                        <li><a href="{{ route('jobs.index') }}">School jobs</a></li>
                        <li><a href="{{ route('events.index') }}">Events calendar</a></li>
                    </ul>
                </div>
            @endif

            @if($footerSupportEnabled)
                <div class="site-footer__column">
                    <h2>Support &amp; information</h2>
                    <ul>
                        <li><a href="{{ route('register') }}">Register your school</a></li>
                        <li><a href="{{ route('about') }}">About FindURSCH</a></li>
                        <li><a href="{{ route('contact') }}">Contact us</a></li>
                        <li><a href="{{ route('legal.terms') }}">Terms &amp; conditions</a></li>
                        <li><a href="{{ route('legal.privacy') }}">Privacy policy</a></li>
                    </ul>
                </div>
            @endif
        </div>

        @if($footerPromoEnabled)
            <div class="site-footer__cta">
                <div>
                    <span class="site-footer__eyebrow">Reach the right audience</span>
                    <strong>Connect your school or brand with families across Uganda.</strong>
                </div>
                <a href="{{ route('contact', ['subject' => 'Advertising enquiry']) }}">Advertise with us <span aria-hidden="true">→</span></a>
            </div>
        @endif

        <div class="site-footer__bottom">
            <span>{{ $footerCopyright }}</span>
            <div class="site-footer__bottom-links">
                <a href="{{ route('legal.privacy') }}">Privacy</a>
                <a href="{{ route('legal.terms') }}">Terms</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
            <span class="site-footer__credit">FindURSCH · Uganda</span>
        </div>
    </div>
</footer>
@endif
