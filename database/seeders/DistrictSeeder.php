<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $byRegion = [
            'Central' => [
                'Kampala', 'Wakiso', 'Mukono', 'Masaka', 'Luwero',
                'Kayunga', 'Mpigi', 'Mubende', 'Mityana', 'Nakasongola',
            ],
            'Eastern' => [
                'Jinja', 'Mbale', 'Soroti', 'Tororo', 'Busia',
                'Iganga', 'Kamuli', 'Pallisa', 'Bugiri', 'Mayuge',
            ],
            'Western' => [
                'Mbarara', 'Kabale', 'Ntungamo', 'Bushenyi', 'Rukungiri',
                'Fort Portal', 'Kasese', 'Masindi', 'Hoima', 'Kabarole',
                'Bundibugyo', 'Kanungu', 'Kisoro', 'Isingiro', 'Kiruhura',
            ],
            'Northern' => [
                'Gulu', 'Lira', 'Kitgum', 'Arua', 'Apac',
                'Nebbi', 'Moyo', 'Pader', 'Amuru', 'Adjumani',
            ],
        ];

        foreach ($byRegion as $regionName => $districts) {
            $region = Region::where('slug', Str::slug($regionName))->first();
            if (! $region) continue;

            foreach ($districts as $name) {
                District::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'region_id' => $region->id,
                        'name' => $name,
                        'school_count' => 0,
                        'is_featured' => in_array($name, ['Kampala', 'Wakiso', 'Mukono', 'Jinja', 'Mbarara', 'Gulu', 'Mbale', 'Fort Portal']),
                    ]
                );
            }
        }
    }
}
