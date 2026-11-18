<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds only the reference data needed for the public site.
 * Deliberately excludes demo records and the development admin account.
 */
class ProductionReferenceDataSeeder extends Seeder
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
