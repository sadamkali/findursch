<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use Illuminate\Database\Seeder;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
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

        Curriculum::query()
            ->whereNotIn('slug', array_keys($curricula))
            ->update(['is_active' => false]);

        foreach ($curricula as $slug => $attributes) {
            $curriculum = Curriculum::query()->firstOrNew(['slug' => $slug]);
            $curriculum->name = $attributes['name'];
            $curriculum->slug = $slug;
            $curriculum->abbreviation = $attributes['abbreviation'];
            $curriculum->description = $attributes['description'];
            $curriculum->is_active = true;
            $curriculum->save();
        }
    }
}
