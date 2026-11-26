<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
    ];

    private const PUBLIC_SITE_SETTING_CACHE_KEY = 'findursch:public-site-settings:v1';

    /**
     * Public presentation settings only. Credentials and private admin settings
     * are deliberately excluded from this shared cache.
     */
    private const PUBLIC_SITE_SETTING_KEYS = [
        'site_name',
        'site_tagline',
        'site_description',
        'site_logo',
        'site_horizontal_logo',
        'site_homepage_logo',
        'site_footer_logo',
        'site_mobile_logo',
        'site_favicon',
        'site_apple_touch_icon',
        'site_social_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'support_email',
        'support_phone',
        'support_whatsapp',
        'contact_address',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'youtube_url',
        'site_navigation',
        'site_topbar_enabled',
        'site_navigation_enabled',
        'site_account_actions_enabled',
        'site_header_cta_label',
        'site_footer_enabled',
        'site_footer_explore_enabled',
        'site_footer_resources_enabled',
        'site_footer_support_enabled',
        'site_footer_social_enabled',
        'site_footer_promo_enabled',
        'site_footer_copyright_text',
        'primary_color',
        'accent_color',
        'font_display',
        'font_body',
    ];

    protected static function booted(): void
    {
        $forgetPublicSiteSettings = static function (): void {
            Cache::forget(self::PUBLIC_SITE_SETTING_CACHE_KEY);
            Cache::forget('findursch:homepage-public-content:v1');
        };

        static::saved($forgetPublicSiteSettings);
        static::deleted($forgetPublicSiteSettings);
    }

    /**
     * Return the allow-listed public settings as one shared, short-lived cache entry.
     */
    public static function publicSiteValues(): array
    {
        return Cache::remember(
            self::PUBLIC_SITE_SETTING_CACHE_KEY,
            now()->addSeconds(60),
            static fn (): array => static::query()
                ->whereIn('key', self::PUBLIC_SITE_SETTING_KEYS)
                ->pluck('value', 'key')
                ->all()
        );
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if ($setting === null) {
            return $default;
        }

        return $setting->value;
    }

    public static function set(string $key, mixed $value): bool
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        ) ? true : false;
    }
}
