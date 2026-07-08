<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /** One login per role, for local testing. Password is "password" for all three. */
    public function run(): void
    {
        foreach (['admin', 'dispatcher', 'viewer'] as $roleName) {
            $role = Role::where('name', $roleName)->firstOrFail();

            User::updateOrCreate(
                ['email' => "{$roleName}@example.com"],
                ['name' => ucfirst($roleName), 'role_id' => $role->id, 'password' => Hash::make('password')]
            );
        }
    }
}
