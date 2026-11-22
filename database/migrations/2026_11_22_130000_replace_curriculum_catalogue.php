<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $curricula = [
            'uneb-uganda' => [
                'name' => 'UNEB (Uganda)',
                'abbreviation' => 'UNEB',
                'description' => 'Uganda national curriculum pathway. National PLE, UCE and UACE examinations are administered by UNEB.',
            ],
            'cambridge' => [
                'name' => 'Cambridge (IGCSE / A-Level)',
                'abbreviation' => 'CAMBRIDGE',
                'description' => 'Cambridge International pathway for schools offering IGCSE and/or International A-Level.',
            ],
            'american-curriculum' => [
                'name' => 'American Curriculum',
                'abbreviation' => 'AMERICAN',
                'description' => 'United States-style school curriculum pathway used by American-curriculum schools.',
            ],
        ];

        DB::table('curricula')
            ->whereNotIn('slug', array_keys($curricula))
            ->update(['is_active' => false, 'updated_at' => $now]);

        foreach ($curricula as $slug => $attributes) {
            $existing = DB::table('curricula')->where('slug', $slug)->first(['id']);
            $values = [
                ...$attributes,
                'slug' => $slug,
                'is_active' => true,
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('curricula')->where('id', $existing->id)->update($values);
            } else {
                DB::table('curricula')->insert([
                    ...$values,
                    'created_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $now = now();

        DB::table('curricula')
            ->whereIn('slug', ['uneb-uganda', 'cambridge', 'american-curriculum'])
            ->update(['is_active' => false, 'updated_at' => $now]);

        DB::table('curricula')
            ->whereIn('slug', [
                'cambridge-igcse',
                'cambridge-a-level',
                'international-baccalaureate-ib',
                'stem-focus',
                'other',
                'uganda-national-curriculum-primary',
                'uganda-national-curriculum-uneb',
            ])
            ->update(['is_active' => true, 'updated_at' => $now]);
    }
};
