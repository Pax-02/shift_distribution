<?php

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;

it('belongs to a role', function () {
    $role = Role::factory()->admin()->create();
    $user = User::factory()->create(['role_id' => $role->id]);

    expect($user->role)->toBeInstanceOf(Role::class)
        ->and($user->role->name)->toBe('admin');
});

it('generates a uuid primary key', function () {
    $user = User::factory()->create();

    expect($user->id)->toBeString()->toHaveLength(36);
});

it('hides password and remember_token when serialized', function () {
    $user = User::factory()->create();

    $array = $user->toArray();

    expect($array)->not->toHaveKeys(['password', 'remember_token']);
});

it('writes an audit log entry when created and updated', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    $createdLog = AuditLog::where('auditable_type', $user->getMorphClass())
        ->where('auditable_id', $user->id)
        ->where('action', 'created')
        ->first();
    expect($createdLog)->not->toBeNull();

    $user->update(['name' => 'Updated Name']);

    $updatedLog = AuditLog::where('auditable_type', $user->getMorphClass())
        ->where('auditable_id', $user->id)
        ->where('action', 'updated')
        ->first();

    expect($updatedLog)->not->toBeNull()
        ->and($updatedLog->old_values['name'])->toBe('Original Name')
        ->and($updatedLog->new_values['name'])->toBe('Updated Name')
        ->and($updatedLog->new_values)->not->toHaveKey('password');
});
