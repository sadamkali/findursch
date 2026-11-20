<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Branding
            ['key' => 'site_name', 'value' => 'FindURSCH', 'type' => 'string', 'group' => 'branding', 'label' => 'Site Name'],
            ['key' => 'site_tagline', 'value' => "Uganda's School Discovery Platform", 'type' => 'string', 'group' => 'branding', 'label' => 'Site Tagline'],
            ['key' => 'site_description', 'value' => 'Find the best schools in Uganda. Discover, compare, and apply to top schools across the country.', 'type' => 'string', 'group' => 'branding', 'label' => 'Site Description'],
            ['key' => 'site_logo', 'value' => null, 'type' => 'string', 'group' => 'branding', 'label' => 'Site Logo URL'],
            ['key' => 'site_favicon', 'value' => null, 'type' => 'string', 'group' => 'branding', 'label' => 'Site Favicon URL'],
            ['key' => 'primary_color', 'value' => '#0c5a49', 'type' => 'string', 'group' => 'branding', 'label' => 'Primary Color'],
            ['key' => 'accent_color', 'value' => '#f0b030', 'type' => 'string', 'group' => 'branding', 'label' => 'Accent Color'],
            ['key' => 'success_color', 'value' => '#10B981', 'type' => 'string', 'group' => 'branding', 'label' => 'Success Color'],
            ['key' => 'danger_color', 'value' => '#EF4444', 'type' => 'string', 'group' => 'branding', 'label' => 'Danger Color'],
            ['key' => 'font_display', 'value' => 'DM Serif Display', 'type' => 'string', 'group' => 'branding', 'label' => 'Display Font'],
            ['key' => 'font_body', 'value' => 'Inter', 'type' => 'string', 'group' => 'branding', 'label' => 'Body Font'],

            // SEO
            ['key' => 'meta_title', 'value' => "FindURSCH - Discover Uganda's Best Schools", 'type' => 'string', 'group' => 'seo', 'label' => 'Default Meta Title'],
            ['key' => 'meta_description', 'value' => 'FindURSCH helps parents discover, compare, and apply to the best schools in Uganda. Find nurseries, primary, secondary and universities near you.', 'type' => 'string', 'group' => 'seo', 'label' => 'Default Meta Description'],
            ['key' => 'meta_keywords', 'value' => 'schools uganda, education, admissions, kampala schools, primary schools, secondary schools, international schools', 'type' => 'string', 'group' => 'seo', 'label' => 'Default Meta Keywords'],
            ['key' => 'google_maps_key', 'value' => null, 'type' => 'string', 'group' => 'seo', 'label' => 'Google Maps API Key'],

            // Contact
            ['key' => 'support_email', 'value' => 'hello@findursch.ug', 'type' => 'string', 'group' => 'contact', 'label' => 'Support Email'],
            ['key' => 'support_phone', 'value' => '+256 700 000000', 'type' => 'string', 'group' => 'contact', 'label' => 'Support Phone'],
            ['key' => 'contact_address', 'value' => 'Kampala, Uganda', 'type' => 'string', 'group' => 'contact', 'label' => 'Contact Address'],

            // Social
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/findursch', 'type' => 'string', 'group' => 'social', 'label' => 'Facebook URL'],
            ['key' => 'twitter_url', 'value' => 'https://twitter.com/findursch', 'type' => 'string', 'group' => 'social', 'label' => 'Twitter URL'],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/findursch', 'type' => 'string', 'group' => 'social', 'label' => 'Instagram URL'],
            ['key' => 'youtube_url', 'value' => null, 'type' => 'string', 'group' => 'social', 'label' => 'YouTube URL'],

            // Payment
            ['key' => 'currency', 'value' => 'UGX', 'type' => 'string', 'group' => 'payment', 'label' => 'Default Currency'],
            ['key' => 'payment_provider', 'value' => 'flutterwave', 'type' => 'string', 'group' => 'payment', 'label' => 'Default Payment Provider'],
        ];

        foreach ($settings as $s) {
            Setting::firstOrCreate(
                ['key' => $s['key']],
                $s
            );
        }
    }
}
