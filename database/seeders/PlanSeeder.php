<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'tagline' => 'Get your school listed for free',
                'description' => 'A basic listing to get your school on the map.',
                'price' => 0,
                'currency' => 'UGX',
                'duration_days' => 365,
                'billing_cycle' => 'yearly',
                'features' => ['Basic listing', 'Contact info display', '1 photo upload'],
                'includes_featured_slot' => false,
                'featured_slot_count' => 0,
                'order' => 1,
            ],
            [
                'name' => 'Standard',
                'tagline' => 'Grow your visibility',
                'description' => 'Reach more parents and showcase your school better.',
                'price' => 500000,
                'currency' => 'UGX',
                'duration_days' => 365,
                'billing_cycle' => 'yearly',
                'features' => [
                    'Everything in Free',
                    'Up to 20 gallery photos',
                    'Search visibility boost',
                    'Reviews management',
                    '1 featured slot/month',
                ],
                'includes_featured_slot' => true,
                'featured_slot_count' => 1,
                'order' => 2,
            ],
            [
                'name' => 'Premium',
                'tagline' => 'Maximum exposure for your school',
                'description' => 'Top placement, premium badge, and priority support.',
                'price' => 1500000,
                'currency' => 'UGX',
                'duration_days' => 365,
                'billing_cycle' => 'yearly',
                'features' => [
                    'Everything in Standard',
                    'Unlimited photos',
                    'Premium badge',
                    'Top search placement',
                    '3 featured slots/month',
                    'Priority support',
                    'Analytics dashboard',
                ],
                'includes_featured_slot' => true,
                'featured_slot_count' => 3,
                'order' => 3,
            ],
        ];

        foreach ($plans as $p) {
            Plan::firstOrCreate(
                ['slug' => Str::slug($p['name'])],
                array_merge($p, ['is_active' => true])
            );
        }
    }
}
