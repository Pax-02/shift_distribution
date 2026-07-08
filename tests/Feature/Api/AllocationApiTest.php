<?php

use App\Enums\Gender;
use App\Models\Assignment;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerAvailability;

function dispatcherAllocationUser(): User
{
    return User::factory()->create(['role_id' => Role::factory()->dispatcher()->create()->id]);
}

it('runs allocation and proposes the top-ranked worker', function () {
    $user = dispatcherAllocationUser();
    $shift = Shift::factory()->create(['shift_date' => '2026-08-10', 'start_time' => '09:00', 'end_time' => '11:00']); // Monday

    $worker = Worker::factory()->gender(Gender::Male)->create();
    WorkerAvailability::factory()->create([
        'worker_id' => $worker->id, 'type' => 'recurring', 'day_of_week' => 1, 'start_time' => '00:00', 'end_time' => '23:59',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/allocation/run', ['shift_id' => $shift->id]);

    $response->assertOk()
        ->assertJsonPath('proposed_assignment.worker.id', $worker->id)
        ->assertJsonPath('shift_id', $shift->id);

    expect(Assignment::where('shift_id', $shift->id)->where('status', 'proposed')->exists())->toBeTrue()
        ->and($shift->fresh()->status->value)->toBe('allocated');
});

it('returns no proposed assignment when no worker is eligible', function () {
    $user = dispatcherAllocationUser();
    $shift = Shift::factory()->create(['shift_date' => '2026-08-10']); // no workers exist at all

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/allocation/run', ['shift_id' => $shift->id]);

    $response->assertOk()->assertJsonPath('proposed_assignment', null);
});

it('forbids running allocation for a viewer', function () {
    $user = User::factory()->create(['role_id' => Role::factory()->viewer()->create()->id]);
    $shift = Shift::factory()->create();

    $this->actingAs($user, 'sanctum')->postJson('/api/allocation/run', ['shift_id' => $shift->id])->assertForbidden();
});

it('lists allocation history filtered by shift', function () {
    $user = dispatcherAllocationUser();
    $shift = Shift::factory()->create();
    $otherShift = Shift::factory()->create();
    Assignment::factory()->create(['shift_id' => $shift->id]);
    Assignment::factory()->create(['shift_id' => $otherShift->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/allocation/history?shift_id={$shift->id}");

    $response->assertOk()->assertJsonCount(1, 'data');
});
