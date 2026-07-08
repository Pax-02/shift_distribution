<?php

use App\Enums\GenderRequirement;
use App\Enums\ShiftStatus;
use App\Models\Assignment;
use App\Models\Client;
use App\Models\Shift;

it('casts status and gender_requirement to enums', function () {
    $shift = Shift::factory()->create();

    expect($shift->status)->toBe(ShiftStatus::Pending)
        ->and($shift->gender_requirement)->toBe(GenderRequirement::Any);
});

it('belongs to a client', function () {
    $client = Client::factory()->create();
    $shift = Shift::factory()->create(['client_id' => $client->id]);

    expect($shift->client->is($client))->toBeTrue();
});

it('has many assignments', function () {
    $shift = Shift::factory()->create();
    Assignment::factory()->count(2)->create(['shift_id' => $shift->id]);

    expect($shift->assignments)->toHaveCount(2);
});

it('allows only one confirmed assignment per shift at the database level', function () {
    $shift = Shift::factory()->create();
    Assignment::factory()->confirmed()->create(['shift_id' => $shift->id]);

    Assignment::factory()->confirmed()->create(['shift_id' => $shift->id]);
})->throws(\Illuminate\Database\UniqueConstraintViolationException::class);

it('allows multiple non-confirmed assignments for the same shift', function () {
    $shift = Shift::factory()->create();
    Assignment::factory()->count(3)->create(['shift_id' => $shift->id, 'status' => 'declined']);

    expect($shift->assignments()->count())->toBe(3);
});
