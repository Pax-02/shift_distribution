<?php

use App\Events\ShiftMessageReceived;
use App\Jobs\ParseShiftMessageJob;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

it('dispatches ParseShiftMessageJob and fires ShiftMessageReceived when a message is ingested', function () {
    Queue::fake();
    Event::fake([ShiftMessageReceived::class]);

    $user = User::factory()->create(['role_id' => Role::factory()->dispatcher()->create()->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/shift-messages', ['raw_message' => 'Shift for Someone on 12/08/2026, pickup Home, dropoff Clinic. 09:00 to 11:00.'])
        ->assertCreated();

    Queue::assertPushed(ParseShiftMessageJob::class);
    Event::assertDispatched(ShiftMessageReceived::class);
});
