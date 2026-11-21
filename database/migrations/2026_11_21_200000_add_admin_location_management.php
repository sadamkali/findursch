<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['regions', 'districts', 'sub_counties'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'is_active')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->boolean('is_active')->default(true);
                });
            }
        }

        if (! Schema::hasTable('location_suggestions')) {
            Schema::create('location_suggestions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('district_id')->constrained()->restrictOnDelete();
                $table->string('suggested_name', 120);
                $table->string('normalized_name', 120);
                $table->foreignId('source_school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->foreignId('suggested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedInteger('school_count')->default(1);
                $table->string('status', 20)->default('pending');
                $table->foreignId('target_sub_county_id')->nullable()->constrained('sub_counties')->nullOnDelete();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
                $table->index('district_id');
            });
        }

        DB::statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS location_suggestions_pending_name_unique
             ON location_suggestions (district_id, normalized_name)
             WHERE status = \'pending\''
        );

        DB::statement('ALTER TABLE location_suggestions ENABLE ROW LEVEL SECURITY');
    }

    public function down(): void
    {
        Schema::dropIfExists('location_suggestions');

        foreach (['regions', 'districts', 'sub_counties'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'is_active')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('is_active');
                });
            }
        }
    }
};
