<?php

use App\Models\Shift;
use App\Models\Worker;
use App\Models\WorkerAvailability;
use App\Rules\Allocation\AvailabilityRule;
use Illuminate\Support\Carbon;

beforeEach(fn () => $this->rule = new AvailabilityRule());

it('fails when the worker has no availability at all', function () {
    $worker = Worker::factory()->create();
    $shift = Shift::factory()->on(Carbon::parse('2026-08-10')->toDateString())->between('09:00', '11:00')->create(); // Monday

    expect($this->rule->passes($worker, $shift))->toBeFalse();
});

it('passes when a recurring window covers the shift', function () {
    $worker = Worker::factory()->create();
    $shift = Shift::factory()->on('2026-08-10')->between('09:00', '11:00')->create(); // Monday = day_of_week 1

    WorkerAvailability::factory()->create([
        'worker_id' => $worker->id,
        'type' => 'recurring',
        'day_of_week' => 1,
        'start_time' => '08:00',
        'end_time' => '17:00',
        'is_available' => true,
    ]);

    expect($this->rule->passes($worker->fresh(), $shift))->toBeTrue();
});

it('fails when the recurring window does not fully cover the shift', function () {
    $worker = Worker::factory()->create();
    $shift = Shift::factory()->on('2026-08-10')->between('09:00', '11:00')->create();

    WorkerAvailability::factory()->create([
        'worker_id' => $worker->id,
        'type' => 'recurring',
        'day_of_week' => 1,
        'start_time' => '10:00', // starts after the shift starts
        'end_time' => '17:00',
        'is_available' => true,
    ]);

    expect($this->rule->passes($worker->fresh(), $shift))->toBeFalse();
});

it('fails when an explicit block overlaps an otherwise-available window', function () {
    $worker = Worker::factory()->create();
    $shift = Shift::factory()->on('2026-08-10')->between('09:00', '11:00')->create();

    WorkerAvailability::factory()->create([
        'worker_id' => $worker->id, 'type' => 'recurring', 'day_of_week' => 1,
        'start_time' => '08:00', 'end_time' => '17:00', 'is_available' => true,
    ]);
    WorkerAvailability::factory()->create([
        'worker_id' => $worker->id, 'type' => 'date_specific', 'specific_date' => '2026-08-10',
        'start_time' => '10:00', 'end_time' => '10:30', 'is_available' => false,
    ]);

    expect($this->rule->passes($worker->fresh(), $shift))->toBeFalse();
});
