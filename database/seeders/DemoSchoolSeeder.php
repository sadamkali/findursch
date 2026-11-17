<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\District;
use App\Models\Facility;
use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSchoolSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'staging'])) {
            return;
        }

        $premiumPlan = Plan::where('slug', 'premium')->first();
        $unebOl = Curriculum::where('slug', 'uneb-o-level')->first();
        $unebAl = Curriculum::where('slug', 'uneb-a-level')->first();
        $cambridge = Curriculum::where('slug', 'cambridge-igcse')->first();
        $ib = Curriculum::where('slug', 'international-baccalaureate-ib')->first();
        $stem = Curriculum::where('slug', 'stem-focus')->first();

        $facilities = Facility::all()->pluck('id')->toArray();

        $schools = [
            [
                'name' => 'Kampala Parents School',
                'owner_email' => 'john@example.com',
                'district' => 'Kampala',
                'type' => 'Secondary',
                'tagline' => 'Excellence in education since 1964',
                'description' => 'Kampala Parents School is one of Uganda\'s most prestigious schools, providing quality education with a focus on holistic development. Our students consistently rank among the top in national examinations.',
                'address' => 'Plot 14, Naguru Drive, Kampala',
                'phone' => '+256 414 250 101',
                'email' => 'info@kampalaparents.ac.ug',
                'website' => 'https://kampalaparents.ac.ug',
                'whatsapp' => '+256 700 250 101',
                'founded_year' => 1964,
                'boarding_type' => 'mixed',
                'gender_policy' => 'mixed',
                'student_count_approx' => 1800,
                'latitude' => 0.3476,
                'longitude' => 32.5825,
                'curricula' => [$unebOl?->id, $unebAl?->id],
                'plan' => 'premium',
                'is_featured' => true,
            ],
            [
                'name' => 'Makerere College School',
                'owner_email' => 'mary@example.com',
                'district' => 'Kampala',
                'type' => 'Secondary',
                'tagline' => 'Tradition of excellence',
                'description' => 'Makerere College School has been a leading boys\' school in Uganda for over 70 years, producing leaders in all fields of Ugandan society.',
                'address' => 'Makerere Hill, Kampala',
                'phone' => '+256 414 533 522',
                'email' => 'admin@makererecollege.ac.ug',
                'website' => 'https://makererecollege.ac.ug',
                'whatsapp' => '+256 700 533 522',
                'founded_year' => 1945,
                'boarding_type' => 'boarding',
                'gender_policy' => 'boys',
                'student_count_approx' => 1200,
                'latitude' => 0.3389,
                'longitude' => 32.5689,
                'curricula' => [$unebOl?->id, $unebAl?->id],
                'plan' => 'standard',
                'is_featured' => true,
            ],
            [
                'name' => 'Aga Khan Academy Kampala',
                'owner_email' => 'peter@example.com',
                'district' => 'Kampala',
                'type' => 'International',
                'tagline' => 'World-class education with a global perspective',
                'description' => 'Aga Khan Academy Kampala delivers an internationally recognised curriculum that develops students into critical thinkers and ethical leaders.',
                'address' => 'Kansanga, Ggaba Road, Kampala',
                'phone' => '+256 414 234 567',
                'email' => 'kampala@agakhanacademies.org',
                'website' => 'https://agakhanacademies.org/kampala',
                'whatsapp' => '+256 700 234 567',
                'founded_year' => 2003,
                'boarding_type' => 'mixed',
                'gender_policy' => 'mixed',
                'student_count_approx' => 850,
                'latitude' => 0.2601,
                'longitude' => 32.5956,
                'curricula' => [$ib?->id, $cambridge?->id],
                'plan' => 'premium',
                'is_featured' => true,
            ],
            [
                'name' => 'Loreto High School Jinja',
                'district' => 'Jinja',
                'type' => 'Secondary',
                'tagline' => 'Empowering girls through quality education',
                'description' => 'Loreto High School Jinja is one of Uganda\'s leading girls\' schools, founded by the Loreto Sisters in 1958 with a commitment to academic excellence and character formation.',
                'address' => 'Plot 2, Nalufenya Road, Jinja',
                'phone' => '+256 434 122 001',
                'email' => 'info@loretojinja.ac.ug',
                'website' => 'https://loretojinja.ac.ug',
                'whatsapp' => '+256 700 122 001',
                'founded_year' => 1958,
                'boarding_type' => 'boarding',
                'gender_policy' => 'girls',
                'student_count_approx' => 900,
                'latitude' => 0.4250,
                'longitude' => 33.2041,
                'curricula' => [$unebOl?->id, $unebAl?->id],
                'plan' => 'standard',
                'is_featured' => false,
            ],
            [
                'name' => 'Mbarara High School',
                'district' => 'Mbarara',
                'type' => 'Secondary',
                'tagline' => 'Where tradition meets excellence',
                'description' => 'Mbarara High School is one of the oldest and most respected schools in Western Uganda, known for producing well-rounded students.',
                'address' => 'Mbaguta Street, Mbarara',
                'phone' => '+256 485 420 011',
                'email' => 'admin@mbararahigh.ac.ug',
                'website' => null,
                'whatsapp' => '+256 700 420 011',
                'founded_year' => 1926,
                'boarding_type' => 'boarding',
                'gender_policy' => 'boys',
                'student_count_approx' => 750,
                'latitude' => -0.6057,
                'longitude' => 30.6486,
                'curricula' => [$unebOl?->id, $unebAl?->id],
                'plan' => 'standard',
                'is_featured' => true,
            ],
            [
                'name' => 'Greenhill Academy',
                'district' => 'Wakiso',
                'type' => 'Primary',
                'tagline' => 'Nurturing young minds',
                'description' => 'Greenhill Academy is a leading primary school in Kampala offering a balanced curriculum with strong emphasis on academic excellence and co-curricular activities.',
                'address' => 'Kira Road, Kampala',
                'phone' => '+256 414 567 890',
                'email' => 'info@greenhillacademy.ac.ug',
                'website' => 'https://greenhillacademy.ac.ug',
                'whatsapp' => '+256 700 567 890',
                'founded_year' => 1994,
                'boarding_type' => 'day',
                'gender_policy' => 'mixed',
                'student_count_approx' => 1500,
                'latitude' => 0.3556,
                'longitude' => 32.6092,
                'curricula' => [$cambridge?->id, $stem?->id],
                'plan' => 'premium',
                'is_featured' => false,
            ],
            [
                'name' => 'Gulu University',
                'district' => 'Gulu',
                'type' => 'University',
                'tagline' => 'Knowledge for development',
                'description' => 'Gulu University is a public university in Northern Uganda offering undergraduate and postgraduate programmes in various fields including medicine, agriculture, education and business.',
                'address' => 'Pece-Laroo Division, Gulu',
                'phone' => '+256 471 432 500',
                'email' => 'info@gu.ac.ug',
                'website' => 'https://gu.ac.ug',
                'whatsapp' => null,
                'founded_year' => 2002,
                'boarding_type' => 'mixed',
                'gender_policy' => 'mixed',
                'student_count_approx' => 4500,
                'latitude' => 2.7747,
                'longitude' => 32.2998,
                'curricula' => [],
                'plan' => 'standard',
                'is_featured' => false,
            ],
            [
                'name' => 'Buddo Junior School',
                'district' => 'Wakiso',
                'type' => 'Primary',
                'tagline' => 'A foundation for life',
                'description' => 'Buddo Junior School is one of the most prestigious primary schools in Uganda, providing quality education for children aged 5-12.',
                'address' => 'Buddo, Wakiso District',
                'phone' => '+256 414 200 411',
                'email' => 'info@buddojunior.ac.ug',
                'website' => null,
                'whatsapp' => '+256 700 200 411',
                'founded_year' => 1909,
                'boarding_type' => 'boarding',
                'gender_policy' => 'mixed',
                'student_count_approx' => 950,
                'latitude' => 0.2650,
                'longitude' => 32.5080,
                'curricula' => [$unebOl?->id, $cambridge?->id],
                'plan' => 'standard',
                'is_featured' => false,
            ],
        ];

        foreach ($schools as $data) {
            $owner = null;
            if (! empty($data['owner_email'])) {
                $owner = User::where('email', $data['owner_email'])->first();
            }
            unset($data['owner_email']);

            $district = District::where('slug', Str::slug($data['district']))->first();
            $type = SchoolType::where('slug', Str::slug($data['type']))->first();
            $curriculaIds = $data['curricula'];
            $planSlug = $data['plan'];
            unset($data['curricula'], $data['plan'], $data['district'], $data['type']);

            $data['district_id'] = $district?->id;
            $data['type_id'] = $type?->id;
            $data['owner_id'] = $owner?->id;
            $data['status'] = 'active';
            $data['verification_status'] = 'verified';
            $data['verified_at'] = now();
            $data['submitted_at'] = now()->subDays(rand(30, 365));

            $school = School::firstOrCreate(
                ['name' => $data['name']],
                $data
            );

            // Attach curricula
            if (! empty($curriculaIds)) {
                $school->curricula()->syncWithoutDetaching(array_filter($curriculaIds));
            }

            // Attach 3-5 random facilities
            if (! empty($facilities)) {
                $facilitySample = (array) array_rand(array_flip($facilities), min(5, count($facilities)));
                $school->facilities()->syncWithoutDetaching($facilitySample);
            }

            // Subscription
            $plan = Plan::where('slug', $planSlug)->first();
            if ($plan && $school->activeSubscription === null) {
                Subscription::firstOrCreate(
                    ['school_id' => $school->id, 'plan_id' => $plan->id, 'status' => 'active'],
                    [
                        'start_date' => now()->subDays(rand(1, 90)),
                        'end_date' => now()->addDays(rand(30, 365)),
                        'auto_renew' => true,
                    ]
                );
            }
        }
    }
}
