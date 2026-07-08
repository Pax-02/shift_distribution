<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reuse RoleFactory's states as the single source of truth for each
        // role's permission set, rather than duplicating the lists here.
        foreach (['admin', 'dispatcher', 'viewer'] as $roleName) {
            $made = Role::factory()->{$roleName}()->make();

            Role::updateOrCreate(
                ['name' => $roleName],
                ['description' => $made->description, 'permissions' => $made->permissions]
            );
        }
    }
}
