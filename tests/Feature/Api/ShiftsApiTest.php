<?php

use App\Models\Client;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;

function dispatcherUser(): User
{
    return User::factory()->create(['role_id' => Role::factory()->dispatcher()->create()->id]);
}

it('lists shifts ordered by date and time', function () {
    $user = dispatcherUser();
    Shift::factory()->count(2)->create();

    $this->actingAs($user, 'sanctum')->getJson('/api/shifts')->assertOk()->assertJsonCount(2, 'data');
});

it('creates a shift and stamps created_by from the authenticated user', function () {
    $user = dispatcherUser();
    $client = Client::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/shifts', [
        'client_id' => $client->id,
        'pickup_address_text' => '12 High St',
        'destination_address_text' => 'Willow Centre',
        'shift_date' => '2026-08-12',
        'start_time' => '09:00',
        'end_time' => '11:00',
    ]);

    $response->assertCreated();
    expect(Shift::first()->created_by)->toBe($user->id);
});

it('rejects a shift where end_time is not after start_time', function () {
    $user = dispatcherUser();
    $client = Client::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/shifts', [
        'client_id' => $client->id,
        'pickup_address_text' => '12 High St',
        'destination_address_text' => 'Willow Centre',
        'shift_date' => '2026-08-12',
        'start_time' => '11:00',
        'end_time' => '09:00',
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors(['end_time']);
});

it('updates a shift status', function () {
    $user = dispatcherUser();
    $shift = Shift::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->putJson("/api/shifts/{$shift->id}", ['status' => 'cancelled']);

    $response->assertOk()->assertJsonPath('data.status', 'cancelled');
});
