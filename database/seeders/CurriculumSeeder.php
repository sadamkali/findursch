<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        // UNEB O-Level and A-Level belong under one secondary curriculum choice.
        // Keep the old rows for schools already linked to them, but hide them from new registrations.
        Curriculum::query()
            ->whereIn('slug', ['uneb-o-level', 'uneb-a-level'])
            ->update(['is_active' => false]);

        $curricula = [
            [
                'name' => 'Uganda National Curriculum (Primary)',
                'abbreviation' => 'UG-PRI',
                'description' => 'Uganda national curriculum for primary education.',
            ],
            [
                'name' => 'Uganda National Curriculum (UNEB)',
                'abbreviation' => 'UG-UNEB',
                'description' => 'Uganda national curriculum for secondary education, covering both O-Level and A-Level.',
            ],
            [
                'name' => 'Cambridge IGCSE',
                'abbreviation' => 'CIE-IGCSE',
                'description' => 'Cambridge International General Certificate of Secondary Education',
            ],
            [
                'name' => 'Cambridge A-Level',
                'abbreviation' => 'CIE-AL',
                'description' => 'Cambridge International A-Level qualification',
            ],
            [
                'name' => 'International Baccalaureate (IB)',
                'abbreviation' => 'IB',
                'description' => 'International Baccalaureate programme',
            ],
            [
                'name' => 'STEM Focus',
                'abbreviation' => 'STEM',
                'description' => 'Science, Technology, Engineering and Mathematics focused curriculum',
            ],
        ];

        foreach ($curricula as $curriculum) {
            Curriculum::updateOrCreate(
                ['slug' => Str::slug($curriculum['name'])],
                array_merge($curriculum, ['is_active' => true])
            );
        }
    }
}
