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
            ['key' => 'dashboard_logo', 'value' => 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_horizontal_logo/694ca722-e686-401e-ab6a-17e4d593137e.png', 'type' => 'string', 'group' => 'dashboard-branding', 'label' => 'Dashboard Login and Sidebar Logo'],
            ['key' => 'dashboard_mark', 'value' => 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_mobile_logo/e527a232-1bf6-4574-b9af-34c3f6ccc81f.png', 'type' => 'string', 'group' => 'dashboard-branding', 'label' => 'Dashboard Compact Mark'],
            ['key' => 'dashboard_favicon', 'value' => 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_mobile_logo/e527a232-1bf6-4574-b9af-34c3f6ccc81f.png', 'type' => 'string', 'group' => 'dashboard-branding', 'label' => 'Dashboard Browser Icon'],
            ['key' => 'site_navigation', 'value' => '[{"label":"Home","href":"/","enabled":true},{"label":"Schools","href":"/schools","enabled":true},{"label":"Compare","href":"/compare","enabled":true},{"label":"Scholarships","href":"/scholarships","enabled":true},{"label":"News","href":"/news","enabled":true},{"label":"Events","href":"/events","enabled":true},{"label":"Blogs","href":"/articles","enabled":true}]', 'type' => 'json', 'group' => 'navigation', 'label' => 'Primary Navigation'],
            ['key' => 'site_topbar_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'header', 'label' => 'Show Topbar'],
            ['key' => 'site_navigation_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'header', 'label' => 'Show Primary Navigation'],
            ['key' => 'site_account_actions_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'header', 'label' => 'Show Account Actions'],
            ['key' => 'site_header_cta_label', 'value' => 'List your school', 'type' => 'string', 'group' => 'header', 'label' => 'Header CTA Label'],
            ['key' => 'site_footer_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'footer', 'label' => 'Show Footer'],
            ['key' => 'site_footer_explore_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'footer', 'label' => 'Show Explore Column'],
            ['key' => 'site_footer_resources_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'footer', 'label' => 'Show Resources Column'],
            ['key' => 'site_footer_support_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'footer', 'label' => 'Show Support Column'],
            ['key' => 'site_footer_social_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'footer', 'label' => 'Show Social Links'],
            ['key' => 'site_footer_promo_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'footer', 'label' => 'Show Footer Promotion'],
            ['key' => 'site_footer_copyright_text', 'value' => null, 'type' => 'string', 'group' => 'footer', 'label' => 'Footer Copyright Text'],
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
