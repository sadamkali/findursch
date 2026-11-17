<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\SchoolView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_database_tables_are_available_after_migrations(): void
    {
        foreach ([
            'users',
            'regions',
            'districts',
            'sub_counties',
            'schools',
            'school_views',
            'saved_schools',
            'inquiries',
            'reviews',
            'article_categories',
            'articles',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected [{$table}] table to exist.");
        }

        $this->assertTrue(Schema::hasColumn('saved_schools', 'created_at'));
        $this->assertTrue(Schema::hasColumn('saved_schools', 'updated_at'));
    }

    public function test_user_school_relationships_and_school_view_records_work(): void
    {
        $region = Region::create([
            'name' => 'Central',
            'slug' => 'central',
        ]);

        $district = District::create([
            'region_id' => $region->id,
            'name' => 'Kampala',
            'slug' => 'kampala',
            'school_count' => 0,
            'is_featured' => true,
        ]);

        $schoolType = SchoolType::create([
            'name' => 'Primary',
            'slug' => 'primary',
            'icon' => 'book-open',
            'description' => 'Primary education',
            'order' => 1,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $school = School::create([
            'owner_id' => $user->id,
            'type_id' => $schoolType->id,
            'district_id' => $district->id,
            'name' => 'Central Test School',
            'slug' => 'central-test-school',
            'status' => 'active',
            'verification_status' => 'verified',
        ]);

        $user->savedSchools()->attach($school->id, ['notes' => 'Shortlisted']);
        $inquiry = $user->inquiries()->create([
            'school_id' => $school->id,
            'parent_name' => $user->name,
            'parent_email' => $user->email,
            'message' => 'Please share the admissions requirements.',
            'source' => 'web',
        ]);
        $review = $user->reviews()->create([
            'school_id' => $school->id,
            'reviewer_type' => 'parent',
            'rating' => 4,
            'title' => 'A good school',
            'body' => 'This is a test review.',
            'status' => 'pending',
        ]);

        $view = SchoolView::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'session_id' => 'test-session',
            'referrer' => 'https://example.test',
        ]);

        $this->assertTrue($user->savedSchools()->whereKey($school->id)->exists());
        $this->assertSame($inquiry->id, $user->inquiries()->firstOrFail()->id);
        $this->assertSame($review->id, $user->reviews()->firstOrFail()->id);
        $this->assertSame($school->id, $view->school()->value('id'));
        $this->assertSame($user->id, $view->user()->value('id'));
        $this->assertSame('Shortlisted', $user->savedSchools()->firstOrFail()->pivot->notes);
    }

    public function test_school_moderation_metadata_is_mass_assignable(): void
    {
        $school = new School();

        foreach (['verified_at', 'verified_by', 'submitted_at', 'rejection_reason'] as $metadataField) {
            $this->assertTrue($school->isFillable($metadataField), "[{$metadataField}] must be persistable by the school approval workflow.");
        }
    }

    public function test_account_privilege_and_verification_fields_are_not_mass_assignable(): void
    {
        $user = new User();

        foreach (['user_type', 'status', 'email_verified_at', 'last_login_at'] as $protectedField) {
            $this->assertFalse($user->isFillable($protectedField), "[{$protectedField}] must remain system-controlled.");
        }

        foreach (['phone', 'profile_photo', 'district_id'] as $profileField) {
            $this->assertTrue($user->isFillable($profileField), "[{$profileField}] should be available for profile updates.");
        }
    }
}
