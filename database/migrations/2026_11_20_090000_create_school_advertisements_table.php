<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('school_advertisements')) {
            Schema::create('school_advertisements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title', 180);
                $table->string('placement', 40)->default('school_spotlight');
                $table->string('destination_url', 500)->nullable();
                $table->text('description');
                $table->string('status', 20)->default('draft');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->text('image_url')->nullable();
                $table->string('image_alt', 255)->nullable();
                $table->string('cta_label', 80)->nullable();
                $table->boolean('is_platform_ad')->default(false);
                $table->timestamps();
                $table->index(['status', 'placement', 'starts_at', 'ends_at']);
                $table->index(['school_id', 'status']);
            });
        }

        DB::table('settings')->updateOrInsert(
            ['key' => 'homepage_hero_image_url'],
            [
                'value' => null,
                'type' => 'string',
                'group' => 'media',
                'label' => 'Homepage hero background image',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('school_advertisements');
        DB::table('settings')->where('key', 'homepage_hero_image_url')->delete();
    }
};
