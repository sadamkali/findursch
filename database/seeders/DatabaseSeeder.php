<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            DistrictSeeder::class,
            SchoolTypeSeeder::class,
            CurriculumSeeder::class,
            FacilitySeeder::class,
            PlanSeeder::class,
            ArticleCategorySeeder::class,
            SettingSeeder::class,
            AccessRoleSeeder::class,
        ]);
    }
}
