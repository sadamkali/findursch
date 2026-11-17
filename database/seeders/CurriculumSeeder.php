<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $curricula = [
            ['name' => 'UNEB (O-Level)', 'abbreviation' => 'UNEB-OL', 'description' => 'Uganda National Examinations Board Ordinary Level curriculum (S1-S4)'],
            ['name' => 'UNEB (A-Level)', 'abbreviation' => 'UNEB-AL', 'description' => 'Uganda National Examinations Board Advanced Level curriculum (S5-S6)'],
            ['name' => 'Cambridge IGCSE', 'abbreviation' => 'CIE-IGCSE', 'description' => 'Cambridge International General Certificate of Secondary Education'],
            ['name' => 'Cambridge A-Level', 'abbreviation' => 'CIE-AL', 'description' => 'Cambridge International A-Level qualification'],
            ['name' => 'International Baccalaureate (IB)', 'abbreviation' => 'IB', 'description' => 'International Baccalaureate programme'],
            ['name' => 'STEM Focus', 'abbreviation' => 'STEM', 'description' => 'Science, Technology, Engineering and Mathematics focused curriculum'],
        ];

        foreach ($curricula as $c) {
            Curriculum::firstOrCreate(
                ['slug' => Str::slug($c['name'])],
                array_merge($c, ['is_active' => true])
            );
        }
    }
}
