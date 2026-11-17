<?php

namespace Tests\Feature;

use App\Livewire\Owner\SchoolEditor;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OwnerDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    private function owner(string $email): User
    {
        Role::findOrCreate('school_owner', 'web');

        $user = User::factory()->create(['email' => $email]);
        $user->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
            'user_type' => 'school_owner',
        ])->save();
        $user->assignRole('school_owner');

        return $user;
    }

    public function test_only_school_owner_accounts_can_open_the_owner_dashboard(): void
    {
        Role::findOrCreate('parent', 'web');

        $parent = User::factory()->create();
        $parent->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
        ])->save();
        $parent->assignRole('parent');

        $this->actingAs($parent)
            ->get('/owner')
            ->assertForbidden();

        $owner = $this->owner('owner@example.test');

        $this->actingAs($owner)
            ->get('/owner')
            ->assertOk();
    }

    public function test_school_owner_can_submit_a_new_school_for_admin_review(): void
    {
        $owner = $this->owner('school-owner@example.test');

        $region = Region::create([
            'name' => 'Central',
            'slug' => 'central',
        ]);

        $district = District::create([
            'region_id' => $region->id,
            'name' => 'Kampala',
            'slug' => 'kampala',
            'school_count' => 0,
            'is_featured' => false,
        ]);

        $type = SchoolType::create([
            'name' => 'Primary',
            'slug' => 'primary',
            'icon' => 'book-open',
            'description' => 'Primary education',
            'order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($owner);

        Livewire::test(SchoolEditor::class)
            ->set('name', 'Owner Submitted Primary School')
            ->set('type_id', (string) $type->id)
            ->set('district_id', (string) $district->id)
            ->set('boarding_type', 'day')
            ->set('gender_policy', 'mixed')
            ->call('submitForReview')
            ->assertRedirect(route('owner.dashboard'));

        $this->assertDatabaseHas('schools', [
            'owner_id' => $owner->id,
            'name' => 'Owner Submitted Primary School',
            'status' => 'pending',
            'verification_status' => 'pending',
        ]);
    }

    public function test_school_owner_cannot_open_another_owners_listing_editor(): void
    {
        $firstOwner = $this->owner('first-owner@example.test');
        $secondOwner = $this->owner('second-owner@example.test');

        $region = Region::create([
            'name' => 'Eastern',
            'slug' => 'eastern',
        ]);

        $district = District::create([
            'region_id' => $region->id,
            'name' => 'Jinja',
            'slug' => 'jinja',
            'school_count' => 0,
            'is_featured' => false,
        ]);

        $type = SchoolType::create([
            'name' => 'Secondary',
            'slug' => 'secondary',
            'icon' => 'graduation-cap',
            'description' => 'Secondary education',
            'order' => 2,
            'is_active' => true,
        ]);

        $school = School::create([
            'owner_id' => $firstOwner->id,
            'type_id' => $type->id,
            'district_id' => $district->id,
            'name' => 'Private Owner School',
            'slug' => 'private-owner-school',
            'status' => 'draft',
            'verification_status' => 'unverified',
        ]);

        $this->actingAs($secondOwner)
            ->get('/owner/schools/'.$school->id.'/edit')
            ->assertNotFound();
    }
}
