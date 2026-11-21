<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_ad_placements')) {
            Schema::create('site_ad_placements', function (Blueprint $table) {
                $table->string('id', 40)->primary();
                $table->string('name', 100);
                $table->unsignedInteger('width');
                $table->unsignedInteger('height');
                $table->string('description', 255);
                $table->unsignedBigInteger('price_ugx')->default(0);
                $table->unsignedSmallInteger('capacity')->default(1);
                $table->boolean('show_ad_label')->default(true);
                $table->unsignedBigInteger('fallback_banner_id')->nullable();
                $table->index('fallback_banner_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('site_ad_campaigns')) {
            Schema::create('site_ad_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('campaign_name', 180);
                $table->string('advertiser_name', 180)->nullable();
                $table->boolean('is_house')->default(false);
                $table->boolean('is_paid')->default(false);
                $table->string('destination_url', 2048)->nullable();
                $table->string('payment_reference', 120)->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['is_house', 'is_paid']);
                $table->index('created_by');
            });
        }

        if (! Schema::hasTable('site_ad_banners')) {
            Schema::create('site_ad_banners', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('site_ad_campaigns')->cascadeOnDelete();
                $table->string('placement_id', 40);
                $table->foreign('placement_id')->references('id')->on('site_ad_placements')->cascadeOnDelete();
                $table->text('image_url');
                $table->string('image_alt', 255);
                $table->string('destination_url', 2048)->nullable();
                $table->unsignedInteger('width');
                $table->unsignedInteger('height');
                $table->unsignedInteger('file_size_kb')->default(0);
                $table->string('status', 20)->default('draft');
                $table->string('rejection_reason', 500)->nullable();
                $table->boolean('is_fallback')->default(false);
                $table->unsignedBigInteger('impressions')->default(0);
                $table->unsignedBigInteger('clicks')->default(0);
                $table->unsignedInteger('rotation_order')->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();
                $table->index(['placement_id', 'status', 'starts_at', 'ends_at']);
                $table->index(['campaign_id', 'status']);
            });
        }

        $fallbackConstraint = DB::selectOne(
            "SELECT EXISTS (
                SELECT 1 FROM pg_constraint
                WHERE conname = 'site_ad_placements_fallback_banner_id_foreign'
            ) AS present"
        );
        if (! $fallbackConstraint?->present) {
            Schema::table('site_ad_placements', function (Blueprint $table) {
                $table->foreign('fallback_banner_id')
                    ->references('id')
                    ->on('site_ad_banners')
                    ->nullOnDelete();
            });
        }

        foreach (['site_ad_placements', 'site_ad_campaigns', 'site_ad_banners'] as $table) {
            DB::statement('ALTER TABLE '.$table.' ENABLE ROW LEVEL SECURITY');
        }

        $now = now();
        $placements = [
            ['id' => 'top', 'name' => 'Top banner', 'width' => 728, 'height' => 90, 'description' => 'Under the header on every page', 'price_ugx' => 500000, 'capacity' => 1, 'show_ad_label' => true],
            ['id' => 'inlist', 'name' => 'In-list banner', 'width' => 728, 'height' => 90, 'description' => 'Between rows in school, news and opportunity lists', 'price_ugx' => 250000, 'capacity' => 2, 'show_ad_label' => true],
            ['id' => 'side', 'name' => 'Sidebar rectangle', 'width' => 300, 'height' => 250, 'description' => 'Homepage and content sidebars', 'price_ugx' => 300000, 'capacity' => 3, 'show_ad_label' => true],
            ['id' => 'art', 'name' => 'Article banner', 'width' => 728, 'height' => 90, 'description' => 'Inside news and blog articles', 'price_ugx' => 150000, 'capacity' => 2, 'show_ad_label' => true],
            ['id' => 'foot1', 'name' => 'Footer banner, large', 'width' => 728, 'height' => 200, 'description' => 'Above the footer, left', 'price_ugx' => 250000, 'capacity' => 2, 'show_ad_label' => true],
            ['id' => 'foot2', 'name' => 'Footer banner, small', 'width' => 468, 'height' => 200, 'description' => 'Above the footer, right', 'price_ugx' => 200000, 'capacity' => 2, 'show_ad_label' => true],
        ];

        foreach ($placements as $placement) {
            if (! DB::table('site_ad_placements')->where('id', $placement['id'])->exists()) {
                DB::table('site_ad_placements')->insert([
                    ...$placement,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('site_ad_placements', function (Blueprint $table) {
            $table->dropForeign(['fallback_banner_id']);
        });
        Schema::dropIfExists('site_ad_banners');
        Schema::dropIfExists('site_ad_campaigns');
        Schema::dropIfExists('site_ad_placements');
    }
};
