<?php

use App\Models\Client;
use App\Models\Role;
use App\Models\User;

it('lists clients with their addresses', function () {
    $user = User::factory()->create(['role_id' => Role::factory()->dispatcher()->create()->id]);
    Client::factory()->has(\App\Models\ClientAddress::factory()->count(2), 'addresses')->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/clients');

    $response->assertOk()->assertJsonCount(2, 'data.0.addresses');
});

it('creates a client', function () {
    $user = User::factory()->create(['role_id' => Role::factory()->dispatcher()->create()->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/clients', ['name' => 'Acme Care']);

    $response->assertCreated()->assertJsonPath('data.name', 'Acme Care');
});

it('rejects client creation from a viewer', function () {
    $user = User::factory()->create(['role_id' => Role::factory()->viewer()->create()->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/clients', ['name' => 'Acme Care']);

    $response->assertForbidden();
});

it('soft deletes a client', function () {
    $user = User::factory()->create(['role_id' => Role::factory()->dispatcher()->create()->id]);
    $client = Client::factory()->create();

    $this->actingAs($user, 'sanctum')->deleteJson("/api/clients/{$client->id}")->assertNoContent();

    expect(Client::find($client->id))->toBeNull();
});
