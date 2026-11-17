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
            AdminUserSeeder::class,
        ]);

        if (app()->environment(['local', 'staging'])) {
            $this->call([
                DemoUserSeeder::class,
                DemoSchoolSeeder::class,
                DemoReviewSeeder::class,
            ]);
        }
    }
}
