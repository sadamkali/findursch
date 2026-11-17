<?php

namespace Database\Seeders;

use App\Models\SchoolType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchoolTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Nursery', 'icon' => 'baby', 'description' => 'Early childhood education (3-5 years)', 'order' => 1],
            ['name' => 'Primary', 'icon' => 'book-open', 'description' => 'Lower primary and upper primary (P1-P7)', 'order' => 2],
            ['name' => 'Secondary', 'icon' => 'graduation-cap', 'description' => 'Lower secondary O-Level (S1-S4)', 'order' => 3],
            ['name' => 'A-Level', 'icon' => 'book-marked', 'description' => 'Upper secondary A-Level (S5-S6)', 'order' => 4],
            ['name' => 'University', 'icon' => 'university', 'description' => 'Higher education institutions', 'order' => 5],
            ['name' => 'Vocational', 'icon' => 'wrench', 'description' => 'Technical and vocational training', 'order' => 6],
            ['name' => 'International', 'icon' => 'globe', 'description' => 'International curriculum schools', 'order' => 7],
        ];

        foreach ($types as $t) {
            SchoolType::firstOrCreate(
                ['slug' => Str::slug($t['name'])],
                array_merge($t, ['is_active' => true])
            );
        }
    }
}
