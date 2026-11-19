<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Support\\Facades\\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $facilities = [
            ['name' => 'School transport', 'slug' => 'school-transport', 'category' => 'welfare'],
            ['name' => 'Security & CCTV', 'slug' => 'security-cctv', 'category' => 'technology'],
            ['name' => 'Electricity / solar', 'slug' => 'electricity-solar', 'category' => 'technology'],
            ['name' => 'Clean water', 'slug' => 'clean-water', 'category' => 'welfare'],
            ['name' => 'Special needs support', 'slug' => 'special-needs-support', 'category' => 'academic'],
        ];

        foreach ($facilities as $facility) {
            DB::table('facilities')->updateOrInsert(
                ['slug' => $facility['slug']],
                [...$facility, 'is_active' => true, 'updated_at' => $now, 'created_at' => $now],
            );
        }

        DB::table('curricula')->updateOrInsert(
            ['slug' => 'other'],
            [
                'name' => 'Other',
                'abbreviation' => 'OTHER',
                'description' => 'Other curriculum or school programme',
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );
    }

    public function down(): void
    {
        // Keep these shared catalogue values during rollback so school selections remain valid.
    }
};
