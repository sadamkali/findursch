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
            ['name' => 'Parent & Family Guides', 'description' => 'Practical school-selection, enrolment and family education guides'],
            ['name' => 'Early Childhood & Daycare', 'description' => 'Daycare, kindergarten and early childhood development'],
            ['name' => 'Kindergarten', 'description' => 'Early learning, school readiness and kindergarten guidance'],
            ['name' => 'Primary Education', 'description' => 'Primary school discovery, learning foundations and parent guidance'],
            ['name' => 'Secondary Education', 'description' => 'Secondary school choices, boarding, day school and student support'],
            ['name' => 'Higher Education', 'description' => 'Universities, courses, tertiary institutions and career pathways'],
            ['name' => 'Student Development', 'description' => 'Study skills, wellbeing, extracurricular activities and life skills'],
            ['name' => 'Career Guidance', 'description' => 'Subject choices, career exploration and preparation for work'],
            ['name' => 'Teaching & Learning', 'description' => 'Classroom practice, learning approaches and educational ideas'],
            ['name' => 'Admissions & Enrolment', 'description' => 'Verified admission information, application guidance and enrolment updates'],
            ['name' => 'School Spotlight', 'description' => 'School profiles, facilities, programmes and community stories'],
            ['name' => 'School Events', 'description' => 'School activities, open days, competitions and community events'],
            ['name' => 'Student Opportunities', 'description' => 'Verified scholarships, bursaries, competitions and learning opportunities'],
            ['name' => 'Education News', 'description' => 'Verified education developments and announcements from Uganda'],
            ['name' => 'School News', 'description' => 'Verified updates and announcements from school communities'],
            ['name' => 'Education Policy', 'description' => 'Confirmed education policy, curriculum and sector updates'],
            ['name' => 'Rankings & Results', 'description' => 'Carefully sourced education results and comparisons with clear context'],
        ];

        foreach ($categories as $c) {
            ArticleCategory::firstOrCreate(
                ['slug' => Str::slug($c['name'])],
                array_merge($c, ['is_active' => true])
            );
        }
    }
}
