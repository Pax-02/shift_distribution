<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Worker;

function actingAsUserWithRole(string $roleState = 'dispatcher'): User
{
    $role = Role::factory()->{$roleState}()->create();

    return User::factory()->create(['role_id' => $role->id]);
}

it('lists workers for an authorized user', function () {
    $user = actingAsUserWithRole('dispatcher');
    Worker::factory()->count(3)->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/workers');

    $response->assertOk()->assertJsonCount(3, 'data');
});

it('rejects an unauthenticated request', function () {
    $this->getJson('/api/workers')->assertUnauthorized();
});

it('creates a worker with valid data', function () {
    $user = actingAsUserWithRole('dispatcher');

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/workers', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'gender' => 'female',
        'contracted_weekly_hours' => 20,
    ]);

    $response->assertCreated()->assertJsonPath('data.first_name', 'Jane');
    expect(Worker::count())->toBe(1);
});

it('rejects worker creation from a viewer who lacks workers.manage', function () {
    $user = actingAsUserWithRole('viewer');

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/workers', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'gender' => 'female',
        'contracted_weekly_hours' => 20,
    ]);

    $response->assertForbidden();
});

it('validates required fields when creating a worker', function () {
    $user = actingAsUserWithRole('dispatcher');

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/workers', []);

    $response->assertUnprocessable()->assertJsonValidationErrors(['first_name', 'last_name', 'gender', 'contracted_weekly_hours']);
});

it('updates a worker', function () {
    $user = actingAsUserWithRole('dispatcher');
    $worker = Worker::factory()->create(['first_name' => 'Old']);

    $response = $this->actingAs($user, 'sanctum')->putJson("/api/workers/{$worker->id}", ['first_name' => 'New']);

    $response->assertOk()->assertJsonPath('data.first_name', 'New');
});

it('soft deletes a worker', function () {
    $user = actingAsUserWithRole('dispatcher');
    $worker = Worker::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/workers/{$worker->id}");

    $response->assertNoContent();
    expect(Worker::find($worker->id))->toBeNull();
});
