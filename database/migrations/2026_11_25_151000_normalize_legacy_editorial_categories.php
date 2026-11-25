<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryMap = [
            'admissions-guide' => 'admissions-enrolment',
            'parent-tips' => 'parent-family-guides',
            'rankings' => 'rankings-results',
        ];

        foreach ($categoryMap as $legacySlug => $currentSlug) {
            $legacy = DB::table('article_categories')->where('slug', $legacySlug)->first(['id']);
            $current = DB::table('article_categories')->where('slug', $currentSlug)->first(['id']);

            if ($legacy && $current) {
                DB::table('articles')
                    ->where('category_id', $legacy->id)
                    ->update(['category_id' => $current->id]);
            }
        }

        DB::table('article_categories')
            ->whereIn('slug', array_keys($categoryMap))
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('article_categories')
            ->whereIn('slug', ['admissions-guide', 'parent-tips', 'rankings'])
            ->update(['is_active' => true, 'updated_at' => now()]);
    }
};
