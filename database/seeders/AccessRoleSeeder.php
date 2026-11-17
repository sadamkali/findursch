<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AccessRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'school_owner', 'parent', 'student'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
