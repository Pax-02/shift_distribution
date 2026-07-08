<?php

use App\Models\Client;
use App\Models\Role;
use App\Models\ShiftMessage;
use App\Models\User;
use Illuminate\Support\Carbon;

function dispatcherApiUser(): User
{
    return User::factory()->create(['role_id' => Role::factory()->dispatcher()->create()->id]);
}

// QUEUE_CONNECTION=sync in phpunit.xml, so ParseShiftMessageJob runs inline —
// these assert the end-to-end result rather than just "a job was dispatched".

it('parses a well-formed message and creates the shift', function () {
    Carbon::setTestNow('2026-01-01');
    $user = dispatcherApiUser();
    Client::factory()->create(['name' => 'Jane Doe']);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/shift-messages', [
        'raw_message' => 'Need a driver for Jane Doe on 12/08/2026 from 09:00 to 11:00, pickup 12 High Street, dropoff Willow Day Centre.',
    ]);

    // Asserted against the response body, not just the DB row — regression
    // check for the response reflecting stale pre-job data (channel/
    // parse_status came back null even though the job had already run).
    $response->assertCreated()
        ->assertJsonPath('data.parse_status', 'validated')
        ->assertJsonPath('data.channel', 'manual');

    $message = ShiftMessage::first();
    expect($message->parse_status->value)->toBe('validated')
        ->and($message->shift_id)->not->toBeNull()
        ->and($message->shift->client->name)->toBe('Jane Doe');

    Carbon::setTestNow();
});

it('marks the message failed when the parse is unusable', function () {
    $user = dispatcherApiUser();

    $this->actingAs($user, 'sanctum')->postJson('/api/shift-messages', [
        'raw_message' => 'call me about a shift sometime',
    ])->assertCreated();

    $message = ShiftMessage::first();
    expect($message->parse_status->value)->toBe('failed')
        ->and($message->shift_id)->toBeNull();
});

it('leaves the message validated but unlinked when the client name does not match a known client', function () {
    $user = dispatcherApiUser();

    $this->actingAs($user, 'sanctum')->postJson('/api/shift-messages', [
        'raw_message' => 'Need a driver for Unknown Person on 12/08/2026 from 09:00 to 11:00, pickup Home, dropoff Clinic.',
    ])->assertCreated();

    $message = ShiftMessage::first();
    expect($message->parse_status->value)->toBe('validated')
        ->and($message->shift_id)->toBeNull();
});
