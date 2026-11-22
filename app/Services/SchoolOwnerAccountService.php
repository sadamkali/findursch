<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class SchoolOwnerAccountService
{
    /**
     * @return array{user: User, created: bool, invitation_pending: bool}
     */
    public function resolveOrCreate(array $details, int $districtId): array
    {
        $owner = null;

        if (filled($details['user_id'] ?? null)) {
            $owner = User::query()->findOrFail((int) $details['user_id']);
        } else {
            $email = Str::lower(trim((string) ($details['email'] ?? '')));
            if ($email === '') {
                throw ValidationException::withMessages([
                    'owner_email' => ['Enter the school representative’s email address.'],
                ]);
            }

            $owner = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        }

        if ($owner) {
            if ($owner->user_type !== 'school_owner') {
                throw ValidationException::withMessages([
                    'owner_email' => ['This email already belongs to a different type of FindUrSch account. Use an existing school-owner account or another email address.'],
                    'owner_user_id' => ['Select an existing school-owner account.'],
                ]);
            }

            if (in_array($owner->status, ['suspended', 'banned'], true) ||
                ($owner->status === 'inactive' && ! $owner->owner_invitation_pending)) {
                throw ValidationException::withMessages([
                    'owner_user_id' => ['This school-owner account is inactive or restricted. Reactivate it from account management before assigning it.'],
                    'owner_email' => ['This school-owner account is inactive or restricted. Reactivate it from account management before assigning it.'],
                ]);
            }

            Role::findOrCreate('school_owner', 'web');
            if (! $owner->hasRole('school_owner')) {
                $owner->assignRole('school_owner');
            }

            return [
                'user' => $owner,
                'created' => false,
                'invitation_pending' => (bool) $owner->owner_invitation_pending,
            ];
        }

        $name = trim((string) ($details['name'] ?? ''));
        if (mb_strlen($name) < 2) {
            throw ValidationException::withMessages([
                'owner_name' => ['Enter the school representative’s full name.'],
            ]);
        }

        $email = Str::lower(trim((string) ($details['email'] ?? '')));
        $owner = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Str::random(64),
            'phone' => filled($details['phone'] ?? null) ? trim((string) $details['phone']) : null,
            'school_role' => 'School representative',
            'district_id' => $districtId,
        ]);

        $owner->forceFill([
            'user_type' => 'school_owner',
            'status' => 'inactive',
            'owner_invitation_pending' => true,
        ])->save();

        Role::findOrCreate('school_owner', 'web');
        $owner->assignRole('school_owner');

        return [
            'user' => $owner,
            'created' => true,
            'invitation_pending' => true,
        ];
    }

    public function sendInvitation(User $owner): string
    {
        return Password::broker()->sendResetLink(['email' => $owner->email]);
    }
}
