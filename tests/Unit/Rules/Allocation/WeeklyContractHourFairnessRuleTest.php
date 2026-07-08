<?php

use App\Models\Shift;
use App\Models\Worker;
use App\Models\WorkerHours;
use App\Rules\Allocation\WeeklyContractHourFairnessRule;
use Illuminate\Support\Carbon;

beforeEach(fn () => $this->rule = new WeeklyContractHourFairnessRule());

it('scores 1.0 when nothing has been allocated yet', function () {
    $worker = Worker::factory()->create(['contracted_weekly_hours' => 20]);
    $shift = Shift::factory()->create();

    expect($this->rule->score($worker, $shift))->toBe(1.0);
});

it('scores lower as more of the contracted hours are already allocated', function () {
    $worker = Worker::factory()->create(['contracted_weekly_hours' => 20]);
    $shift = Shift::factory()->on('2026-08-12')->create();

    WorkerHours::factory()->create([
        'worker_id' => $worker->id,
        'week_start_date' => Carbon::parse('2026-08-12')->startOfWeek(Carbon::MONDAY)->toDateString(),
        'contracted_hours' => 20,
        'allocated_hours' => 15,
    ]);

    expect($this->rule->score($worker->fresh(), $shift))->toEqual(0.25); // (20-15)/20
});

it('scores 0.0 when the worker has no contracted hours to compare against', function () {
    $worker = Worker::factory()->create(['contracted_weekly_hours' => 0]);
    $shift = Shift::factory()->create();

    expect($this->rule->score($worker, $shift))->toBe(0.0);
});

it('never scores below 0 when allocated hours exceed the contract', function () {
    $worker = Worker::factory()->create(['contracted_weekly_hours' => 20]);
    $shift = Shift::factory()->on('2026-08-12')->create();

    WorkerHours::factory()->create([
        'worker_id' => $worker->id,
        'week_start_date' => Carbon::parse('2026-08-12')->startOfWeek(Carbon::MONDAY)->toDateString(),
        'contracted_hours' => 20,
        'allocated_hours' => 25,
    ]);

    expect($this->rule->score($worker->fresh(), $shift))->toBe(0.0);
});
