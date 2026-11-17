<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use App\Notifications\SchoolApprovedNotification;
use App\Notifications\SchoolRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SchoolService
{
    public function createDraft(User $owner, array $data): School
    {
        return DB::transaction(function () use ($owner, $data) {
            $data['owner_id'] = $owner->id;
            $data['status'] = 'draft';

            return School::create($data);
        });
    }

    public function submit(School $school): void
    {
        $school->update([
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    }

    public function approve(School $school, User $admin): void
    {
        $school->update([
            'status' => 'active',
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);
    }

    public function reject(School $school, User $admin, string $reason): void
    {
        $school->update([
            'status' => 'rejected',
            'verification_status' => 'unverified',
            'rejection_reason' => $reason,
        ]);
    }

    public function suspend(School $school): void
    {
        $school->update(['status' => 'suspended']);
    }

    public function toggleFeatured(School $school, ?string $until = null): void
    {
        $school->update([
            'is_featured' => ! $school->is_featured,
            'featured_until' => $until,
        ]);
    }
}
