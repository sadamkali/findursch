<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['district_id', 'name']);
        });
        DB::statement('ALTER TABLE counties ENABLE ROW LEVEL SECURITY');

        Schema::table('sub_counties', function (Blueprint $table) {
            $table->foreignId('county_id')->nullable()->after('district_id')
                ->constrained('counties')->nullOnDelete();
            $table->index(['county_id', 'is_active']);
        });

        Schema::create('parishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_county_id')->constrained('sub_counties')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['sub_county_id', 'name']);
        });
        DB::statement('ALTER TABLE parishes ENABLE ROW LEVEL SECURITY');

        Schema::table('schools', function (Blueprint $table) {
            $table->foreignId('county_id')->nullable()->after('district_id')
                ->constrained('counties')->nullOnDelete();
            $table->foreignId('parish_id')->nullable()->after('sub_county_id')
                ->constrained('parishes')->nullOnDelete();
            $table->index(['county_id', 'parish_id']);
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['parish_id']);
            $table->dropForeign(['county_id']);
            $table->dropIndex(['county_id', 'parish_id']);
            $table->dropColumn(['county_id', 'parish_id']);
        });

        Schema::dropIfExists('parishes');

        Schema::table('sub_counties', function (Blueprint $table) {
            $table->dropForeign(['county_id']);
            $table->dropIndex(['county_id', 'is_active']);
            $table->dropColumn('county_id');
        });

        Schema::dropIfExists('counties');
    }
};
