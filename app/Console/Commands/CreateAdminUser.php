<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin {email : Email address for the initial administrator}';

    protected $description = 'Create or promote a verified FindurSch administrator using an interactive password prompt.';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Enter a valid email address.');

            return self::INVALID;
        }

        $name = trim((string) $this->ask('Administrator name'));

        if ($name === '') {
            $this->error('A name is required.');

            return self::INVALID;
        }

        $password = $this->secret('Choose a strong temporary password (12 characters minimum)');

        if (! is_string($password) || mb_strlen($password) < 12) {
            $this->error('The password must contain at least 12 characters.');

            return self::INVALID;
        }

        Role::findOrCreate('admin', 'web');

        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => $password,
            'user_type' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ])->save();

        $user->syncRoles(['admin']);

        $this->info("Administrator account provisioned for {$email}.");

        return self::SUCCESS;
    }
}
