<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdManagerController extends Controller
{
    private function authorizeManage(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('admin') || $user->can('admin.ads.manage')), 403, 'Your administrator role cannot manage website banners.');
    }

    private function effectiveStatus(object $banner): string
    {
        if ($banner->status !== 'approved') {
            return $banner->status;
        }

        $now = now();
        if ($banner->starts_at && Carbon::parse($banner->starts_at)->isFuture()) {
            return 'scheduled';
        }
        if ($banner->ends_at && Carbon::parse($banner->ends_at)->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Store Uganda WhatsApp mobile numbers in international format for wa.me.
     * A national 07XXXXXXXX number is converted to 2567XXXXXXXX.
     */
    private function normalizeWhatsappPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\\D+/', '', trim((string) $phone)) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = '256'.substr($digits, 1);
        }

        abort_unless(
            preg_match('/^2567[0-9]{8}$/', $digits) === 1,
            422,
            'Enter a valid Uganda WhatsApp mobile number, e.g. 07XXXXXXXX or +2567XXXXXXXX.'
        );

        return $digits;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        $placements = DB::table('site_ad_placements')->orderBy('name')->get();

        $campaigns = DB::table('site_ad_campaigns as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.created_by')
            ->leftJoinSub(
                DB::table('site_ad_banners')
                    ->selectRaw('campaign_id, COUNT(*) as banners_count, COUNT(DISTINCT placement_id) as placements_count')
                    ->groupBy('campaign_id'),
                'banner_counts',
                'banner_counts.campaign_id',
                '=',
                'c.id'
            )
            ->orderByDesc('c.created_at')
            ->get([
                'c.id', 'c.campaign_name', 'c.advertiser_name', 'c.is_house', 'c.is_paid',
                'c.destination_url', 'c.whatsapp_phone', 'c.payment_reference', 'c.starts_at', 'c.ends_at',
                'c.created_at', 'u.name as created_by_name',
                'banner_counts.banners_count', 'banner_counts.placements_count',
            ]);

        $banners = DB::table('site_ad_banners as b')
            ->join('site_ad_campaigns as c', 'c.id', '=', 'b.campaign_id')
            ->join('site_ad_placements as p', 'p.id', '=', 'b.placement_id')
            ->orderByDesc('b.created_at')
            ->get([
                'b.id', 'b.campaign_id', 'b.placement_id', 'b.image_url', 'b.image_alt',
                'b.destination_url as banner_destination_url', 'b.width', 'b.height',
                'b.file_size_kb', 'b.status', 'b.whatsapp_clicks', 'b.rejection_reason', 'b.is_fallback',
                'b.impressions', 'b.clicks', 'b.rotation_order', 'b.starts_at', 'b.ends_at',
                'b.created_at', 'c.campaign_name', 'c.advertiser_name', 'c.is_house',
                'c.is_paid', 'c.destination_url as campaign_destination_url', 'c.whatsapp_phone',
                'p.name as placement_name', 'p.width as placement_width', 'p.height as placement_height',
            ])
            ->map(function ($banner) {
                $banner->effective_status = $this->effectiveStatus($banner);
                $banner->destination_url = $banner->banner_destination_url ?: $banner->campaign_destination_url;
                return $banner;
            });

        $active = $banners->where('effective_status', 'active');
        $pending = $banners->where('effective_status', 'pending');
        $endingSoon = $active->filter(fn ($banner) => $banner->ends_at && Carbon::parse($banner->ends_at)->between(now(), now()->addDays(7)));

        $liveBannersByPlacement = $active->groupBy('placement_id');

        // A live FindUrSch house banner is a fallback, not an empty placement.
        $emptyPlacements = $placements->filter(
            fn ($placement) => ($liveBannersByPlacement[$placement->id] ?? collect())->isEmpty()
        )->count();

        return response()->json([
            'data' => [
                'placements' => $placements,
                'campaigns' => $campaigns,
                'banners' => $banners->values(),
                'summary' => [
                    'active_banners' => $active->count(),
                    'pending_approval' => $pending->count(),
                    'ending_soon' => $endingSoon->count(),
                    'empty_placements' => $emptyPlacements,
                ],
            ],
        ]);
    }

    public function storeCampaign(Request $request): JsonResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'campaign_name' => ['required', 'string', 'min:2', 'max:180'],
            'advertiser_name' => ['nullable', 'string', 'max:180'],
            'is_house' => ['required', 'boolean'],
            'destination_url' => ['nullable', 'url', 'max:2048'],
            'whatsapp_phone' => ['nullable', 'string', 'max:32'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        if (! $data['is_house'] && blank($data['advertiser_name'] ?? null)) {
            return response()->json(['message' => 'Enter the advertiser name for a paid campaign.'], 422);
        }
        if (! $data['is_house'] && empty($data['ends_at'])) {
            return response()->json(['message' => 'Paid advertiser campaigns need an end date.'], 422);
        }

        $whatsappPhone = $this->normalizeWhatsappPhone($data['whatsapp_phone'] ?? null);

        $id = DB::table('site_ad_campaigns')->insertGetId([
            'campaign_name' => trim($data['campaign_name']),
            'advertiser_name' => $data['is_house'] ? 'FindUrSch' : trim($data['advertiser_name']),
            'is_house' => (bool) $data['is_house'],
            'is_paid' => (bool) $data['is_house'],
            'destination_url' => $data['destination_url'] ?? null,
            'whatsapp_phone' => $whatsappPhone,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'created_by' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Campaign created.',
            'data' => DB::table('site_ad_campaigns')->where('id', $id)->first(),
        ], 201);
    }

    public function storeBanner(Request $request, int $campaignId): JsonResponse
    {
        $this->authorizeManage($request);
        $campaign = DB::table('site_ad_campaigns')->where('id', $campaignId)->first();
        abort_unless($campaign, 404, 'Campaign not found.');

        $data = $request->validate([
            'placement_id' => ['required', 'string', 'exists:site_ad_placements,id'],
            'image_url' => ['required', 'url', 'max:2048'],
            'image_alt' => ['required', 'string', 'min:5', 'max:255'],
            'destination_url' => ['nullable', 'url', 'max:2048'],
            'whatsapp_phone' => ['nullable', 'string', 'max:32'],
            'width' => ['required', 'integer', 'min:1', 'max:3000'],
            'height' => ['required', 'integer', 'min:1', 'max:3000'],
            'file_size_kb' => ['required', 'integer', 'min:1', 'max:1536'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_fallback' => ['sometimes', 'boolean'],
            'publish' => ['sometimes', 'boolean'],
        ]);

        $whatsappPhone = $this->normalizeWhatsappPhone($data['whatsapp_phone'] ?? null);
        if (array_key_exists('whatsapp_phone', $data)) {
            DB::table('site_ad_campaigns')->where('id', $campaignId)->update([
                'whatsapp_phone' => $whatsappPhone,
                'updated_at' => now(),
            ]);
        }

        $placement = DB::table('site_ad_placements')->where('id', $data['placement_id'])->first();
        $correctDimensions = (
            ((int) $data['width'] === (int) $placement->width && (int) $data['height'] === (int) $placement->height) ||
            ((int) $data['width'] === ((int) $placement->width * 2) && (int) $data['height'] === ((int) $placement->height * 2))
        );
        abort_unless($correctDimensions, 422, 'The uploaded banner dimensions do not match this placement.');

        $startsAt = $data['starts_at'] ?? $campaign->starts_at;
        $endsAt = array_key_exists('ends_at', $data) ? $data['ends_at'] : $campaign->ends_at;
        if (! $campaign->is_house && empty($endsAt)) {
            return response()->json(['message' => 'Paid advertiser banners need an end date.'], 422);
        }

        $isFallback = (bool) ($data['is_fallback'] ?? false);
        abort_if($isFallback && ! $campaign->is_house, 422, 'Only FindUrSch house banners can be used as a fallback.');

        $publish = (bool) ($data['publish'] ?? false);
        $status = ! $publish ? 'draft' : ($campaign->is_house ? 'approved' : 'pending');

        $id = DB::table('site_ad_banners')->insertGetId([
            'campaign_id' => $campaignId,
            'placement_id' => $data['placement_id'],
            'image_url' => $data['image_url'],
            'image_alt' => trim($data['image_alt']),
            'destination_url' => $data['destination_url'] ?? null,
            'width' => $data['width'],
            'height' => $data['height'],
            'file_size_kb' => $data['file_size_kb'],
            'status' => $status,
            'is_fallback' => $isFallback,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($isFallback && $status === 'approved') {
            DB::table('site_ad_placements')->where('id', $data['placement_id'])->update([
                'fallback_banner_id' => $id,
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'message' => $status === 'pending' ? 'Banner submitted for payment confirmation and approval.' : ($status === 'approved' ? 'House banner published.' : 'Banner saved as a draft.'),
            'data' => DB::table('site_ad_banners')->where('id', $id)->first(),
        ], 201);
    }

    private function ensurePlacementCapacity(object $banner): void
    {
        $placement = DB::table('site_ad_placements')->where('id', $banner->placement_id)->first();
        abort_unless($placement, 404, 'Banner placement not found.');

        $campaign = DB::table('site_ad_campaigns')->where('id', $banner->campaign_id)->first();
        abort_unless($campaign, 404, 'Campaign not found.');
        if ($campaign->is_house) {
            return;
        }

        $sameCampaignAlreadyUsesSlot = DB::table('site_ad_banners as b')
            ->join('site_ad_campaigns as c', 'c.id', '=', 'b.campaign_id')
            ->where('b.placement_id', $banner->placement_id)
            ->where('b.campaign_id', $banner->campaign_id)
            ->where('b.status', 'approved')
            ->where('c.is_paid', true)
            ->where(fn ($query) => $query->whereNull('b.ends_at')->orWhere('b.ends_at', '>=', now()))
            ->exists();
        if ($sameCampaignAlreadyUsesSlot) {
            return;
        }

        $usedCampaignCount = DB::table('site_ad_banners as b')
            ->join('site_ad_campaigns as c', 'c.id', '=', 'b.campaign_id')
            ->where('b.placement_id', $banner->placement_id)
            ->where('b.status', 'approved')
            ->where('c.is_house', false)
            ->where('c.is_paid', true)
            ->where(fn ($query) => $query->whereNull('b.ends_at')->orWhere('b.ends_at', '>=', now()))
            ->distinct()
            ->count('b.campaign_id');

        abort_if($usedCampaignCount >= (int) $placement->capacity, 409, 'This placement has reached its campaign capacity. Increase the capacity or end another campaign first.');
    }

    public function updateBanner(Request $request, int $bannerId): JsonResponse
    {
        $this->authorizeManage($request);
        $banner = DB::table('site_ad_banners')->where('id', $bannerId)->first();
        abort_unless($banner, 404, 'Banner not found.');
        $campaign = DB::table('site_ad_campaigns')->where('id', $banner->campaign_id)->first();
        abort_unless($campaign, 404, 'Campaign not found.');

        $data = $request->validate([
            'action' => ['required', 'in:approve,reject,pause,resume,end,mark_paid,dates,replace'],
            'reason' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image_alt' => ['nullable', 'string', 'min:5', 'max:255'],
            'destination_url' => ['nullable', 'url', 'max:2048'],
            'width' => ['nullable', 'integer', 'min:1', 'max:3000'],
            'height' => ['nullable', 'integer', 'min:1', 'max:3000'],
            'file_size_kb' => ['nullable', 'integer', 'min:1', 'max:1536'],
        ]);

        $now = now();
        switch ($data['action']) {
            case 'mark_paid':
                abort_if($campaign->is_house, 422, 'FindUrSch house banners do not need payment.');
                DB::table('site_ad_campaigns')->where('id', $campaign->id)->update([
                    'is_paid' => true,
                    'updated_at' => $now,
                ]);
                $message = 'Payment marked as received.';
                break;

            case 'approve':
                abort_unless($campaign->is_house || $campaign->is_paid, 422, 'Confirm payment before approving this advertiser banner.');
                $this->ensurePlacementCapacity($banner);
                DB::table('site_ad_banners')->where('id', $bannerId)->update([
                    'status' => 'approved',
                    'rejection_reason' => null,
                    'updated_at' => $now,
                ]);
                $message = 'Banner approved.';
                break;

            case 'reject':
                DB::table('site_ad_banners')->where('id', $bannerId)->update([
                    'status' => 'rejected',
                    'rejection_reason' => $data['reason'] ?? 'Rejected by an administrator.',
                    'updated_at' => $now,
                ]);
                $message = 'Banner rejected.';
                break;

            case 'pause':
                DB::table('site_ad_banners')->where('id', $bannerId)->update(['status' => 'paused', 'updated_at' => $now]);
                $message = 'Banner paused.';
                break;

            case 'resume':
                abort_unless($campaign->is_house || $campaign->is_paid, 422, 'Confirm payment before resuming this advertiser banner.');
                $this->ensurePlacementCapacity($banner);
                DB::table('site_ad_banners')->where('id', $bannerId)->update(['status' => 'approved', 'updated_at' => $now]);
                $message = 'Banner resumed.';
                break;

            case 'end':
                DB::table('site_ad_banners')->where('id', $bannerId)->update([
                    'status' => 'expired',
                    'ends_at' => $now,
                    'updated_at' => $now,
                ]);
                DB::table('site_ad_placements')->where('fallback_banner_id', $bannerId)->update([
                    'fallback_banner_id' => null,
                    'updated_at' => $now,
                ]);
                $message = 'Banner ended.';
                break;

            case 'dates':
                if (! $campaign->is_house && empty($data['ends_at'])) {
                    return response()->json(['message' => 'Paid advertiser banners need an end date.'], 422);
                }
                DB::table('site_ad_banners')->where('id', $bannerId)->update([
                    'starts_at' => $data['starts_at'] ?? $banner->starts_at,
                    'ends_at' => array_key_exists('ends_at', $data) ? $data['ends_at'] : $banner->ends_at,
                    'updated_at' => $now,
                ]);
                $message = 'Banner schedule saved.';
                break;

            case 'replace':
                abort_unless(! empty($data['image_url']) && ! empty($data['width']) && ! empty($data['height']) && ! empty($data['file_size_kb']), 422, 'Upload a replacement banner image first.');
                $placement = DB::table('site_ad_placements')->where('id', $banner->placement_id)->first();
                $correctDimensions = (
                    ((int) $data['width'] === (int) $placement->width && (int) $data['height'] === (int) $placement->height) ||
                    ((int) $data['width'] === ((int) $placement->width * 2) && (int) $data['height'] === ((int) $placement->height * 2))
                );
                abort_unless($correctDimensions, 422, 'The replacement image dimensions do not match this placement.');
                DB::table('site_ad_banners')->where('id', $bannerId)->update([
                    'image_url' => $data['image_url'],
                    'image_alt' => $data['image_alt'] ?? $banner->image_alt,
                    'destination_url' => $data['destination_url'] ?? $banner->destination_url,
                    'width' => $data['width'],
                    'height' => $data['height'],
                    'file_size_kb' => $data['file_size_kb'],
                    'status' => $campaign->is_house ? 'approved' : 'pending',
                    'updated_at' => $now,
                ]);
                $message = $campaign->is_house ? 'House banner image replaced.' : 'Replacement submitted for approval.';
                break;
        }

        return response()->json([
            'message' => $message,
            'data' => DB::table('site_ad_banners')->where('id', $bannerId)->first(),
        ]);
    }

    public function updatePlacements(Request $request): JsonResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'placements' => ['required', 'array', 'min:1', 'max:6'],
            'placements.*.id' => ['required', 'string', 'exists:site_ad_placements,id'],
            'placements.*.price_ugx' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'placements.*.capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'placements.*.show_ad_label' => ['required', 'boolean'],
            'placements.*.fallback_banner_id' => ['nullable', 'integer', 'exists:site_ad_banners,id'],
        ]);

        foreach ($data['placements'] as $item) {
            if (! empty($item['fallback_banner_id'])) {
                $fallback = DB::table('site_ad_banners as b')
                    ->join('site_ad_campaigns as c', 'c.id', '=', 'b.campaign_id')
                    ->where('b.id', $item['fallback_banner_id'])
                    ->where('b.placement_id', $item['id'])
                    ->where('b.status', 'approved')
                    ->where('c.is_house', true)
                    ->first();
                abort_unless($fallback, 422, 'Fallback banners must be approved FindUrSch house banners in the same placement.');
            }
        }

        DB::transaction(function () use ($data) {
            foreach ($data['placements'] as $item) {
                DB::table('site_ad_placements')->where('id', $item['id'])->update([
                    'price_ugx' => $item['price_ugx'],
                    'capacity' => $item['capacity'],
                    'show_ad_label' => $item['show_ad_label'],
                    'fallback_banner_id' => $item['fallback_banner_id'] ?? null,
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json(['message' => 'Placement settings saved.']);
    }

    public function deleteBanner(Request $request, int $bannerId): JsonResponse
    {
        $this->authorizeManage($request);
        $banner = DB::table('site_ad_banners')->where('id', $bannerId)->first();
        abort_unless($banner, 404, 'Banner not found.');

        DB::transaction(function () use ($bannerId) {
            DB::table('site_ad_placements')->where('fallback_banner_id', $bannerId)->update([
                'fallback_banner_id' => null,
                'updated_at' => now(),
            ]);
            DB::table('site_ad_banners')->where('id', $bannerId)->delete();
        });

        return response()->json(['message' => 'Banner deleted.']);
    }

    public function click(Request $request, int $bannerId): RedirectResponse
    {
        $banner = DB::table('site_ad_banners as b')
            ->join('site_ad_campaigns as c', 'c.id', '=', 'b.campaign_id')
            ->where('b.id', $bannerId)
            ->where('b.status', 'approved')
            ->where(fn ($query) => $query->where('c.is_house', true)->orWhere('c.is_paid', true))
            ->where(fn ($query) => $query->whereNull('b.starts_at')->orWhere('b.starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('b.ends_at')->orWhere('b.ends_at', '>=', now()))
            ->first([
                'b.id',
                'b.destination_url as banner_destination_url',
                'c.destination_url as campaign_destination_url',
                'c.whatsapp_phone',
            ]);

        abort_unless($banner, 404);

        if ($request->query('contact') === 'whatsapp') {
            $phone = $this->normalizeWhatsappPhone($banner->whatsapp_phone);
            abort_unless($phone, 404);

            DB::table('site_ad_banners')->where('id', $bannerId)->increment('whatsapp_clicks', 1, ['updated_at' => now()]);
            $message = rawurlencode('Hello, I saw your advertisement on FindUrSch. Please share more information.');
            $url = "https://wa.me/{$phone}?text={$message}";
        } else {
            $url = $banner->banner_destination_url ?: $banner->campaign_destination_url;
            abort_unless($url && str_starts_with($url, 'https://'), 404);

            DB::table('site_ad_banners')->where('id', $bannerId)->increment('clicks', 1, ['updated_at' => now()]);
        }

        return redirect()->away($url)->header('Cache-Control', 'no-store');
    }
}
