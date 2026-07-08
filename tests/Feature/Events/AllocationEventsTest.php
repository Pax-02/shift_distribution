<?php

use App\Enums\Gender;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerAvailability;
use App\Models\WorkerHours;
use App\Notifications\AssignmentCancelledNotification;
use App\Notifications\WorkerAssignedNotification;
use Illuminate\Support\Facades\Notification;

function allocationDispatcher(): User
{
    return User::factory()->create(['role_id' => Role::factory()->dispatcher()->create()->id]);
}

function eligibleWorkerFor(Shift $shift): Worker
{
    $worker = Worker::factory()->gender(Gender::Male)->create();
    WorkerAvailability::factory()->create([
        'worker_id' => $worker->id, 'type' => 'recurring',
        'day_of_week' => $shift->shift_date->dayOfWeek, 'start_time' => '00:00', 'end_time' => '23:59',
    ]);

    return $worker;
}

it('notifies the worker and updates worker_hours when an assignment is created via allocation/run', function () {
    Notification::fake();

    $user = allocationDispatcher();
    $shift = Shift::factory()->create(['shift_date' => '2026-08-10', 'start_time' => '09:00', 'end_time' => '12:00']); // 3h
    $worker = eligibleWorkerFor($shift);

    $this->actingAs($user, 'sanctum')->postJson('/api/allocation/run', ['shift_id' => $shift->id])->assertOk();

    Notification::assertSentTo($worker, WorkerAssignedNotification::class);

    $hours = WorkerHours::where('worker_id', $worker->id)->first();
    expect($hours)->not->toBeNull()
        ->and((float) $hours->allocated_hours)->toBe(3.0);
});

it('logs an audit_logs "allocated" entry with the ranked worker ids', function () {
    $user = allocationDispatcher();
    $shift = Shift::factory()->create(['shift_date' => '2026-08-10']);
    eligibleWorkerFor($shift);

    $this->actingAs($user, 'sanctum')->postJson('/api/allocation/run', ['shift_id' => $shift->id])->assertOk();

    $log = AuditLog::where('action', 'allocated')->where('auditable_id', $shift->id)->first();

    expect($log)->not->toBeNull()
        ->and($log->new_values['ranked_worker_ids'])->toBeArray()->not->toBeEmpty();
});

it('notifies the worker and recalculates worker_hours when an assignment is cancelled', function () {
    $user = allocationDispatcher();
    $shift = Shift::factory()->create(['shift_date' => '2026-08-10', 'start_time' => '09:00', 'end_time' => '12:00']);
    $worker = eligibleWorkerFor($shift);

    $this->actingAs($user, 'sanctum')->postJson('/api/allocation/run', ['shift_id' => $shift->id])->assertOk();
    $assignment = $worker->assignments()->first();

    Notification::fake();

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/assignments/{$assignment->id}/cancel", ['reason' => 'Worker unavailable'])
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');

    Notification::assertSentTo($worker, AssignmentCancelledNotification::class);

    $hours = WorkerHours::where('worker_id', $worker->id)->first();
    expect((float) $hours->allocated_hours)->toBe(0.0); // cancelled no longer counts
});

it('confirms an assignment and updates the shift status', function () {
    $user = allocationDispatcher();
    $shift = Shift::factory()->create(['shift_date' => '2026-08-10']);
    $worker = eligibleWorkerFor($shift);

    $this->actingAs($user, 'sanctum')->postJson('/api/allocation/run', ['shift_id' => $shift->id])->assertOk();
    $assignment = $worker->assignments()->first();

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/assignments/{$assignment->id}/confirm")
        ->assertOk()
        ->assertJsonPath('data.status', 'confirmed');

    expect($shift->fresh()->status->value)->toBe('confirmed');
});

it('rejects confirming a second assignment when one is already confirmed for the shift', function () {
    $user = allocationDispatcher();
    $shift = Shift::factory()->create(['shift_date' => '2026-08-10']);
    $firstWorker = eligibleWorkerFor($shift);
    $secondWorker = eligibleWorkerFor($shift);

    $firstAssignment = \App\Models\Assignment::factory()->confirmed()->create(['shift_id' => $shift->id, 'worker_id' => $firstWorker->id]);
    $secondAssignment = \App\Models\Assignment::factory()->create(['shift_id' => $shift->id, 'worker_id' => $secondWorker->id, 'status' => 'proposed']);

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/assignments/{$secondAssignment->id}/confirm")
        ->assertStatus(409);
});
