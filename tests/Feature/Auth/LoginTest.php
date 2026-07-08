<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('logs in with valid credentials and returns a bearer token', function () {
    $user = User::factory()->create(['password' => Hash::make('correct-password')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'correct-password',
    ]);

    $response->assertOk()->assertJsonStructure(['token', 'user']);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create(['password' => Hash::make('correct-password')]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable();
});

it('logs out and revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/logout');

    $response->assertNoContent();
    expect($user->tokens()->count())->toBe(0);
});

it('blocks unauthenticated access to protected routes', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});
