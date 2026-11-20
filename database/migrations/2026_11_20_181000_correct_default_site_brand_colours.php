<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        // Only replace the old starter palette. Any custom colours already saved are preserved.
        DB::table('settings')
            ->where('key', 'primary_color')
            ->where('value', '#4F46E5')
            ->update(['value' => '#0c5a49', 'group' => 'branding', 'label' => 'Primary Color', 'updated_at' => now()]);

        DB::table('settings')
            ->where('key', 'accent_color')
            ->where('value', '#F59E0B')
            ->update(['value' => '#f0b030', 'group' => 'branding', 'label' => 'Accent Color', 'updated_at' => now()]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')
            ->where('key', 'primary_color')
            ->where('value', '#0c5a49')
            ->update(['value' => '#4F46E5', 'updated_at' => now()]);

        DB::table('settings')
            ->where('key', 'accent_color')
            ->where('value', '#f0b030')
            ->update(['value' => '#F59E0B', 'updated_at' => now()]);
    }
};
