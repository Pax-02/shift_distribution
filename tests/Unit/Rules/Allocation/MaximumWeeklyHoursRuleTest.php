<?php

use App\Models\Shift;
use App\Models\Worker;
use App\Models\WorkerHours;
use App\Rules\Allocation\MaximumWeeklyHoursRule;
use Illuminate\Support\Carbon;

beforeEach(fn () => $this->rule = new MaximumWeeklyHoursRule());

it('passes when no hours have been allocated yet this week', function () {
    $worker = Worker::factory()->create(['contracted_weekly_hours' => 20]);
    $shift = Shift::factory()->between('09:00', '11:00')->create(); // 2h shift

    expect($this->rule->passes($worker, $shift))->toBeTrue();
});

it('fails when this shift would push allocated hours over the cap', function () {
    $worker = Worker::factory()->create(['contracted_weekly_hours' => 20, 'max_weekly_hours' => null]);
    $shift = Shift::factory()->on('2026-08-12')->between('09:00', '12:00')->create(); // 3h shift, Wed

    WorkerHours::factory()->create([
        'worker_id' => $worker->id,
        'week_start_date' => Carbon::parse('2026-08-12')->startOfWeek(Carbon::MONDAY)->toDateString(),
        'contracted_hours' => 20,
        'allocated_hours' => 19,
    ]);

    expect($this->rule->passes($worker->fresh(), $shift))->toBeFalse();
});

it('uses max_weekly_hours override instead of contracted hours when set', function () {
    $worker = Worker::factory()->create(['contracted_weekly_hours' => 20, 'max_weekly_hours' => 30]);
    $shift = Shift::factory()->on('2026-08-12')->between('09:00', '12:00')->create();

    WorkerHours::factory()->create([
        'worker_id' => $worker->id,
        'week_start_date' => Carbon::parse('2026-08-12')->startOfWeek(Carbon::MONDAY)->toDateString(),
        'contracted_hours' => 20,
        'allocated_hours' => 25,
    ]);

    expect($this->rule->passes($worker->fresh(), $shift))->toBeTrue();
});
