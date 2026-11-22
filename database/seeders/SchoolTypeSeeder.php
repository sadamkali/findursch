<?php

namespace Database\Seeders;

use App\Models\SchoolType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchoolTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Secondary schools may offer both O-Level and A-Level.
        // Retain historical school references, but no longer present A-Level as a separate school type.
        SchoolType::query()->where('slug', 'a-level')->update(['is_active' => false]);

        $types = [
            ['name' => 'Nursery', 'icon' => 'baby', 'description' => 'Early childhood education (3-5 years)', 'order' => 1],
            ['name' => 'Primary', 'icon' => 'book-open', 'description' => 'Lower primary and upper primary (P1-P7)', 'order' => 2],
            ['name' => 'Secondary', 'icon' => 'graduation-cap', 'description' => 'Secondary education, including O-Level and A-Level', 'order' => 3],
            ['name' => 'University', 'icon' => 'university', 'description' => 'Higher education institutions', 'order' => 5],
            ['name' => 'Vocational', 'icon' => 'wrench', 'description' => 'Technical and vocational training', 'order' => 6],
            ['name' => 'International', 'icon' => 'globe', 'description' => 'International curriculum schools', 'order' => 7],
        ];

        foreach ($types as $type) {
            $model = SchoolType::query()
                ->whereRaw('LOWER(BTRIM(name)) = ?', [mb_strtolower(trim($type['name']))])
                ->first();

            if (! $model) {
                $model = SchoolType::query()->where('slug', Str::slug($type['name']))->first();
            }

            if (! $model) {
                $model = new SchoolType();
                $model->slug = Str::slug($type['name']);
            }

            $model->fill(array_merge($type, ['is_active' => true]));
            $model->save();
        }
    }
}
