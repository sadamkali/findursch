<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('schools', 'is_demo')) {
            return;
        }

        DB::table('schools')
            ->where('is_demo', true)
            ->update([
                'status' => 'suspended',
                'verification_status' => 'unverified',
                'verified_at' => null,
                'verified_by' => null,
                'is_featured' => false,
                'featured_until' => null,
                'owner_id' => null,
                'tagline' => null,
                'description' => null,
                'phone' => null,
                'email' => null,
                'website' => null,
                'whatsapp' => null,
                'address' => null,
                'latitude' => null,
                'longitude' => null,
                'founded_year' => null,
                'student_count_approx' => null,
                'meta_title' => null,
                'meta_description' => null,
                'is_demo' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Archived records intentionally remain unpublished on rollback.
    }
};
