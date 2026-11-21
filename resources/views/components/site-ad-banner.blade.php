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
                'b.impressions',
            ]);

        if ($paidBanners->isNotEmpty()) {
            // Rotate fairly by campaign first, then cycle through the individual
            // poster images within that campaign using impression counts.
            $campaignGroups = $paidBanners->groupBy('campaign_id')->map(function ($items) {
                return [
                    'average_impressions' => (float) $items->avg('impressions'),
                    'banners' => $items->sortBy(['impressions', 'id'])->values(),
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
                ]);
        }

        if (! $siteAdBanner) {
            $houseBanners = (clone $siteAdQuery)
                ->where('c.is_house', true)
                ->get([
                    'b.id', 'b.image_url', 'b.image_alt',
                    'b.destination_url as banner_destination_url',
                    'c.destination_url as campaign_destination_url',
                    'b.impressions',
                ]);

            $siteAdBanner = $houseBanners->sortBy(['impressions', 'id'])->first();
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
        @if($siteAdPlacement->show_ad_label)
            <span class="site-ad-slot__label">AD</span>
        @endif
    </div>
@endif
