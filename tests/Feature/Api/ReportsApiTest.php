<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Worker;

it('allows a viewer to read reports', function () {
    $user = User::factory()->create(['role_id' => Role::factory()->viewer()->create()->id]);
    Worker::factory()->create();

    $this->actingAs($user, 'sanctum')->getJson('/api/reports/workers')
        ->assertOk()
        ->assertJsonPath('data.total_workers', 1);
});

it('serves the hours report', function () {
    $user = User::factory()->create(['role_id' => Role::factory()->dispatcher()->create()->id]);

    $this->actingAs($user, 'sanctum')->getJson('/api/reports/hours')->assertOk()->assertJsonStructure(['data']);
});

it('serves the shifts report with a date range', function () {
    $user = User::factory()->create(['role_id' => Role::factory()->dispatcher()->create()->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/reports/shifts?from=2026-08-01&to=2026-08-31')
        ->assertOk()
        ->assertJsonStructure(['data' => ['total_shifts', 'by_status', 'confirmed_allocation_rate']]);
});

it('rejects reports access for a role without reports.view', function () {
    $role = Role::factory()->create(['name' => 'no-permissions', 'permissions' => []]);
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum')->getJson('/api/reports/workers')->assertForbidden();
});
