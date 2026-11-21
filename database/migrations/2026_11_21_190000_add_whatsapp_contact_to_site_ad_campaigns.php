<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_ad_campaigns') && ! Schema::hasColumn('site_ad_campaigns', 'whatsapp_phone')) {
            Schema::table('site_ad_campaigns', function (Blueprint $table) {
                $table->string('whatsapp_phone', 20)->nullable()->after('destination_url');
            });
        }

        if (Schema::hasTable('site_ad_banners') && ! Schema::hasColumn('site_ad_banners', 'whatsapp_clicks')) {
            Schema::table('site_ad_banners', function (Blueprint $table) {
                $table->unsignedBigInteger('whatsapp_clicks')->default(0)->after('clicks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('site_ad_banners', 'whatsapp_clicks')) {
            Schema::table('site_ad_banners', function (Blueprint $table) {
                $table->dropColumn('whatsapp_clicks');
            });
        }

        if (Schema::hasColumn('site_ad_campaigns', 'whatsapp_phone')) {
            Schema::table('site_ad_campaigns', function (Blueprint $table) {
                $table->dropColumn('whatsapp_phone');
            });
        }
    }
};
