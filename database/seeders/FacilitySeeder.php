<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['name' => 'Library', 'icon' => 'book-open', 'category' => 'academic'],
            ['name' => 'Science Labs', 'icon' => 'flask-conical', 'category' => 'academic'],
            ['name' => 'Computer Lab', 'icon' => 'monitor', 'category' => 'technology'],
            ['name' => 'Sports Field', 'icon' => 'trophy', 'category' => 'sports'],
            ['name' => 'Swimming Pool', 'icon' => 'waves', 'category' => 'sports'],
            ['name' => 'Dormitories', 'icon' => 'bed', 'category' => 'welfare'],
            ['name' => 'Chapel/Mosque', 'icon' => 'church', 'category' => 'welfare'],
            ['name' => 'Cafeteria', 'icon' => 'utensils', 'category' => 'welfare'],
            ['name' => 'WiFi Campus', 'icon' => 'wifi', 'category' => 'technology'],
            ['name' => 'Art Room', 'icon' => 'palette', 'category' => 'arts'],
            ['name' => 'Music Room', 'icon' => 'music', 'category' => 'arts'],
            ['name' => 'Drama Hall', 'icon' => 'theater', 'category' => 'arts'],
            ['name' => 'Sick Bay', 'icon' => 'first-aid', 'category' => 'welfare'],
        ];

        foreach ($facilities as $facility) {
            $model = Facility::query()
                ->whereRaw('LOWER(BTRIM(name)) = ?', [mb_strtolower(trim($facility['name']))])
                ->first();

            if (! $model) {
                $model = Facility::query()->where('slug', Str::slug($facility['name']))->first();
            }

            if (! $model) {
                $model = new Facility();
                $model->slug = Str::slug($facility['name']);
            }

            $model->fill(array_merge($facility, ['is_active' => true]));
            $model->save();
        }
    }
}
