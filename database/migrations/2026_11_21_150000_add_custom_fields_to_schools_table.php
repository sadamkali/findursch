<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('schools', 'custom_fields')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->jsonb('custom_fields')->default('{}');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('schools', 'custom_fields')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('custom_fields');
            });
        }
    }
};
