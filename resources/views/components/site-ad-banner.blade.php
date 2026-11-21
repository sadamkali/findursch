@props(['placement'])

@php
    $siteAdPlacement = \Illuminate\Support\Facades\DB::table('site_ad_placements')
        ->where('id', $placement)
        ->first();

    $siteAdBanner = null;

    if ($siteAdPlacement) {
        $now = now();

        $siteAdQuery = \Illuminate\Support\Facades\DB::table('site_ad_banners as b')
            ->join('site_ad_campaigns as c', 'c.id', '=', 'b.campaign_id')
            ->where('b.placement_id', $placement)
            ->where('b.status', 'approved')
            ->where(fn ($query) => $query->whereNull('b.starts_at')->orWhere('b.starts_at', '<=', $now))
            ->where(fn ($query) => $query->whereNull('b.ends_at')->orWhere('b.ends_at', '>=', $now))
            ->where(fn ($query) => $query->where('c.is_house', true)->orWhere('c.is_paid', true));

        $paidBanners = (clone $siteAdQuery)
            ->where('c.is_house', false)
            ->where('c.is_paid', true)
            ->get([
                'b.id', 'b.campaign_id', 'b.image_url', 'b.image_alt',
                'b.destination_url as banner_destination_url',
                'c.destination_url as campaign_destination_url',
                'c.whatsapp_phone',
                'b.impressions',
            ]);

        if ($paidBanners->isNotEmpty()) {
            // Rotate fairly by campaign first, then cycle through the individual
            // poster images within that campaign using impression counts.
            $campaignGroups = $paidBanners->groupBy('campaign_id')->map(function ($items) {
                return [
                    'average_impressions' => (float) $items->avg('impressions'),
                    'banners' => $items->sortBy(fn ($banner) => [(int) $banner->impressions, (int) $banner->id])->values(),
                ];
            });

            $siteAdCampaignGroup = $campaignGroups
                ->sortBy('average_impressions')
                ->first();

            $siteAdBanner = $siteAdCampaignGroup['banners']->first();
        }

        // If there is no eligible paid advertiser campaign, use the selected
        // house fallback. If none is configured, rotate through approved
        // FindUrSch house banners in this placement.
        if (! $siteAdBanner && $siteAdPlacement->fallback_banner_id) {
            $siteAdBanner = (clone $siteAdQuery)
                ->where('c.is_house', true)
                ->where('b.id', $siteAdPlacement->fallback_banner_id)
                ->first([
                    'b.id', 'b.image_url', 'b.image_alt',
                    'b.destination_url as banner_destination_url',
                    'c.destination_url as campaign_destination_url',
                    'c.whatsapp_phone',
                ]);
        }

        if (! $siteAdBanner) {
            $houseBanners = (clone $siteAdQuery)
                ->where('c.is_house', true)
                ->get([
                    'b.id', 'b.image_url', 'b.image_alt',
                    'b.destination_url as banner_destination_url',
                    'c.destination_url as campaign_destination_url',
                    'c.whatsapp_phone',
                    'b.impressions',
                ]);

            $siteAdBanner = $houseBanners->sortBy(fn ($banner) => [(int) $banner->impressions, (int) $banner->id])->first();
        }

        if ($siteAdBanner) {
            \Illuminate\Support\Facades\DB::table('site_ad_banners')
                ->where('id', $siteAdBanner->id)
                ->increment('impressions', 1, ['updated_at' => $now]);
        }
    }

    $siteAdDestination = $siteAdBanner
        ? ($siteAdBanner->banner_destination_url ?: $siteAdBanner->campaign_destination_url)
        : null;

    $siteAdWhatsappPhone = $siteAdBanner && preg_match('/^2567[0-9]{8}$/', (string) ($siteAdBanner->whatsapp_phone ?? ''))
        ? $siteAdBanner->whatsapp_phone
        : null;
@endphp

@if($siteAdPlacement && $siteAdBanner)
    <div class="site-ad-slot site-ad-slot--{{ $placement }}">
        @if($siteAdDestination)
            <a class="site-ad-slot__link" href="{{ route('site-ad-banner.click', ['banner' => $siteAdBanner->id]) }}" aria-label="Advertisement">
                <img src="{{ $siteAdBanner->image_url }}" alt="{{ $siteAdBanner->image_alt }}" loading="lazy" decoding="async">
            </a>
        @else
            <div class="site-ad-slot__image">
                <img src="{{ $siteAdBanner->image_url }}" alt="{{ $siteAdBanner->image_alt }}" loading="lazy" decoding="async">
            </div>
        @endif
        @if($siteAdWhatsappPhone)
            <a
                class="site-ad-slot__whatsapp"
                href="{{ route('site-ad-banner.click', ['banner' => $siteAdBanner->id, 'contact' => 'whatsapp']) }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Chat with the advertiser on WhatsApp"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M20.52 3.48A11.86 11.86 0 0 0 12.05 0C5.5 0 .17 5.33.17 11.88c0 2.09.55 4.14 1.59 5.95L.07 24l6.34-1.66a11.89 11.89 0 0 0 5.64 1.43h.01c6.55 0 11.88-5.33 11.88-11.88 0-3.17-1.23-6.15-3.42-8.41ZM12.06 21.8a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.76.99 1-3.67-.24-.38a9.88 9.88 0 1 1 8.4 4.65Zm5.42-7.39c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.48-1.74-1.65-2.04-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.03 1.01-1.03 2.47s1.06 2.87 1.21 3.07c.15.2 2.09 3.19 5.06 4.47.71.31 1.26.5 1.69.64.71.23 1.35.2 1.86.12.57-.09 1.76-.72 2.01-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"/>
                </svg>
                <span>Chat on WhatsApp</span>
            </a>
        @endif
        @if($siteAdPlacement->show_ad_label)
            <span class="site-ad-slot__label">AD</span>
        @endif
    </div>
@endif
