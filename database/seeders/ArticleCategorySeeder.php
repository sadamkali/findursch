<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Admissions Guide', 'description' => 'Guides for parents and students on school admissions'],
            ['name' => 'School News', 'description' => 'Latest news from schools across Uganda'],
            ['name' => 'Education Policy', 'description' => 'Updates on Ugandan education policy and curriculum'],
            ['name' => 'Parent Tips', 'description' => 'Helpful tips for parents navigating school choice'],
            ['name' => 'Rankings', 'description' => 'Top school rankings and league tables'],
        ];

        foreach ($categories as $c) {
            ArticleCategory::firstOrCreate(
                ['slug' => Str::slug($c['name'])],
                array_merge($c, ['is_active' => true])
            );
        }
    }
}
