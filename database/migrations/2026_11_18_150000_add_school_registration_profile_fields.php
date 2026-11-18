<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('school_role', 80)->nullable();
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->string('ownership', 60)->nullable();
            $table->string('religious_affiliation', 100)->nullable();
            $table->string('town', 120)->nullable();
            $table->string('po_box', 120)->nullable();
            $table->string('nearest_landmark', 255)->nullable();
            $table->string('maps_url', 500)->nullable();
            $table->string('facebook_url', 500)->nullable();
            $table->string('instagram_url', 500)->nullable();
            $table->decimal('fees_from', 12, 2)->nullable();
            $table->decimal('fees_to', 12, 2)->nullable();
            $table->text('activities')->nullable();
            $table->string('referral_source', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'ownership', 'religious_affiliation', 'town', 'po_box',
                'nearest_landmark', 'maps_url', 'facebook_url', 'instagram_url',
                'fees_from', 'fees_to', 'activities', 'referral_source',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('school_role');
        });
    }
};
