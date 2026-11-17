<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\District;
use App\Models\School;
use App\Models\SchoolType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DemoSchoolSeeder extends Seeder
{
    /**
     * Fictional sample records for the public browsing experience.
     * These have no owner or contact details and always remain unverified.
     */
    public function run(): void
    {
        $districts = District::all()->keyBy('slug');
        $types = SchoolType::all()->keyBy('slug');
        $curricula = Curriculum::all()->keyBy('slug');

        $samples = [
            ['name' => 'Demo — Acacia Grove Primary School', 'district' => 'kampala', 'type' => 'primary', 'tagline' => 'A sample primary-school profile', 'boarding' => 'day', 'curricula' => ['uneb-o-level', 'stem-focus'], 'featured' => true],
            ['name' => 'Demo — Lakeview Nursery and Kindergarten', 'district' => 'wakiso', 'type' => 'nursery', 'tagline' => 'A sample early-learning profile', 'boarding' => 'day', 'curricula' => [], 'featured' => true],
            ['name' => 'Demo — Cedar Ridge Secondary School', 'district' => 'mukono', 'type' => 'secondary', 'tagline' => 'A sample lower-secondary profile', 'boarding' => 'mixed', 'curricula' => ['uneb-o-level'], 'featured' => true],
            ['name' => 'Demo — Nilebridge International Academy', 'district' => 'jinja', 'type' => 'international', 'tagline' => 'A sample international-curriculum profile', 'boarding' => 'day', 'curricula' => ['cambridge-igcse', 'international-baccalaureate-ib'], 'featured' => false],
            ['name' => 'Demo — Rwenzori Horizon A-Level College', 'district' => 'mbarara', 'type' => 'a-level', 'tagline' => 'A sample advanced-level profile', 'boarding' => 'boarding', 'curricula' => ['uneb-a-level', 'cambridge-a-level'], 'featured' => false],
            ['name' => 'Demo — Northern Star Community School', 'district' => 'gulu', 'type' => 'primary', 'tagline' => 'A sample community-school profile', 'boarding' => 'day', 'curricula' => ['uneb-o-level'], 'featured' => false],
            ['name' => 'Demo — Eastern Skills and Technology Institute', 'district' => 'mbale', 'type' => 'vocational', 'tagline' => 'A sample technical-training profile', 'boarding' => 'mixed', 'curricula' => ['stem-focus'], 'featured' => false],
            ['name' => 'Demo — Masaka Greenfield Academy', 'district' => 'masaka', 'type' => 'secondary', 'tagline' => 'A sample secondary-school profile', 'boarding' => 'boarding', 'curricula' => ['uneb-o-level', 'uneb-a-level'], 'featured' => false],
            ['name' => 'Demo — Westgate Early Learning Centre', 'district' => 'arua', 'type' => 'nursery', 'tagline' => 'A sample early-years profile', 'boarding' => 'day', 'curricula' => [], 'featured' => false],
            ['name' => 'Demo — Lira University of Applied Studies', 'district' => 'lira', 'type' => 'university', 'tagline' => 'A sample higher-education profile', 'boarding' => 'day', 'curricula' => ['stem-focus'], 'featured' => false],
        ];

        foreach ($samples as $sample) {
            $district = $districts->get($sample['district']);
            $type = $types->get($sample['type']);

            if (! $district || ! $type) {
                throw new RuntimeException('FindurSch demo school reference data is incomplete.');
            }

            $slug = Str::slug($sample['name']);
            $description = 'DEMO LISTING — This is fictional sample content created to demonstrate FindurSch. '
                .$sample['name'].' is not a real institution. Its name and profile details must not be used '
                .'for admissions or school-selection decisions.';

            $school = School::updateOrCreate(
                ['slug' => $slug],
                [
                    'owner_id' => null,
                    'type_id' => $type->id,
                    'district_id' => $district->id,
                    'name' => $sample['name'],
                    'slug' => $slug,
                    'tagline' => $sample['tagline'],
                    'description' => $description,
                    'boarding_type' => $sample['boarding'],
                    'gender_policy' => 'mixed',
                    'status' => 'active',
                    'verification_status' => 'unverified',
                    'verified_at' => null,
                    'verified_by' => null,
                    'is_demo' => true,
                    'is_featured' => $sample['featured'],
                    'meta_title' => $sample['name'].' | FindurSch Demo Listing',
                    'meta_description' => 'Fictional demonstration listing. Not a real school and not verified.',
                ]
            );

            // Insert-only pivot rows avoid duplicate-key races during simultaneous Vercel cold starts.
            $curriculumIds = collect($sample['curricula'])
                ->map(fn (string $curriculumSlug) => $curricula->get($curriculumSlug)?->id)
                ->filter()
                ->values()
                ->all();

            if ($curriculumIds !== []) {
                DB::table('school_curriculum')->insertOrIgnore(
                    array_map(
                        fn (int $curriculumId) => [
                            'school_id' => $school->id,
                            'curriculum_id' => $curriculumId,
                        ],
                        $curriculumIds
                    )
                );
            }
        }
    }
}
