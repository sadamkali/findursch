<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'Central', 'description' => 'Central region of Uganda including Kampala'],
            ['name' => 'Eastern', 'description' => 'Eastern region of Uganda'],
            ['name' => 'Western', 'description' => 'Western region of Uganda'],
            ['name' => 'Northern', 'description' => 'Northern region of Uganda'],
        ];

        foreach ($regions as $r) {
            Region::firstOrCreate(
                ['slug' => Str::slug($r['name'])],
                ['name' => $r['name']]
            );
        }
    }
}
