<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'staging'])) {
            return;
        }

        $kampala = District::where('slug', 'kampala')->first();
        $wakiso = District::where('slug', 'wakiso')->first();
        $mbarara = District::where('slug', 'mbarara')->first();
        $gulu = District::where('slug', 'gulu')->first();

        $owners = [
            ['name' => 'John Mukasa', 'email' => 'john@example.com', 'phone' => '+256 700 100 001', 'district_id' => $kampala?->id],
            ['name' => 'Mary Nakato', 'email' => 'mary@example.com', 'phone' => '+256 700 100 002', 'district_id' => $kampala?->id],
            ['name' => 'Peter Ochieng', 'email' => 'peter@example.com', 'phone' => '+256 700 100 003', 'district_id' => $wakiso?->id],
        ];

        foreach ($owners as $owner) {
            $this->upsertDemoUser($owner, 'school_owner');
        }

        $parents = [
            ['name' => 'Sarah Achieng', 'email' => 'parent1@example.com', 'phone' => '+256 700 200 001', 'district_id' => $kampala?->id],
            ['name' => 'David Ssali', 'email' => 'parent2@example.com', 'phone' => '+256 700 200 002', 'district_id' => $wakiso?->id],
            ['name' => 'Grace Akello', 'email' => 'parent3@example.com', 'phone' => '+256 700 200 003', 'district_id' => $gulu?->id],
            ['name' => 'Brian Tushabe', 'email' => 'parent4@example.com', 'phone' => '+256 700 200 004', 'district_id' => $mbarara?->id],
            ['name' => 'Esther Namugga', 'email' => 'parent5@example.com', 'phone' => '+256 700 200 005', 'district_id' => $kampala?->id],
        ];

        foreach ($parents as $parent) {
            $this->upsertDemoUser($parent, 'parent');
        }
    }

    /**
     * These values are deliberately assigned from trusted development seed data.
     * Public registration cannot mass-assign account type, status or verification.
     */
    private function upsertDemoUser(array $attributes, string $userType): void
    {
        $user = User::firstOrNew(['email' => $attributes['email']]);

        $user->forceFill([
            ...$attributes,
            'password' => Hash::make('password'),
            'user_type' => $userType,
            'status' => 'active',
            'email_verified_at' => now(),
        ])->save();

        Role::findOrCreate($userType, 'web');
        $user->syncRoles([$userType]);
    }
}
