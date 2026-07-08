<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['admin', 'dispatcher', 'viewer']),
            'description' => fake()->sentence(),
            'permissions' => [],
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['name' => 'admin', 'permissions' => self::MANAGE_PERMISSIONS]);
    }

    public function dispatcher(): static
    {
        return $this->state(fn () => ['name' => 'dispatcher', 'permissions' => self::MANAGE_PERMISSIONS]);
    }

    public function viewer(): static
    {
        return $this->state(fn () => [
            'name' => 'viewer',
            'permissions' => ['workers.view', 'clients.view', 'shifts.view', 'reports.view'],
        ]);
    }

    // admin and dispatcher currently have identical permission sets in phase
    // 1 (no feature distinguishes them yet); kept as separate roles since the
    // spec lists them separately and they're expected to diverge later.
    private const MANAGE_PERMISSIONS = [
        'workers.view', 'workers.manage',
        'clients.view', 'clients.manage',
        'shifts.view', 'shifts.manage',
        'allocation.run', 'reports.view',
    ];
}
