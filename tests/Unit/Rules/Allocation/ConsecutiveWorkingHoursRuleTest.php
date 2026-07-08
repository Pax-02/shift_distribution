<?php

use App\Models\Assignment;
use App\Models\Shift;
use App\Models\Worker;
use App\Rules\Allocation\ConsecutiveWorkingHoursRule;

beforeEach(function () {
    $this->rule = new ConsecutiveWorkingHoursRule();
    config(['allocation.max_consecutive_hours' => 10]);
});

it('passes for a single isolated shift', function () {
    $worker = Worker::factory()->create();
    $shift = Shift::factory()->between('09:00', '11:00')->create();

    expect($this->rule->passes($worker->fresh(), $shift))->toBeTrue();
});

it('fails when back-to-back shifts push the consecutive run past the cap', function () {
    $worker = Worker::factory()->create();

    $earlier = Shift::factory()->on('2026-08-10')->between('06:00', '14:00')->create(); // 8h
    Assignment::factory()->create(['worker_id' => $worker->id, 'shift_id' => $earlier->id, 'status' => 'confirmed']);

    $newShift = Shift::factory()->on('2026-08-10')->between('14:00', '17:00')->create(); // touches earlier's end — 3h more = 11h run

    expect($this->rule->passes($worker->fresh(), $newShift))->toBeFalse();
});

it('passes when there is a gap between shifts even if combined hours are high', function () {
    $worker = Worker::factory()->create();

    $earlier = Shift::factory()->on('2026-08-10')->between('06:00', '10:00')->create(); // 4h
    Assignment::factory()->create(['worker_id' => $worker->id, 'shift_id' => $earlier->id, 'status' => 'confirmed']);

    $newShift = Shift::factory()->on('2026-08-10')->between('16:00', '20:00')->create(); // 4h, gap before it

    expect($this->rule->passes($worker->fresh(), $newShift))->toBeTrue();
});

it('ignores declined/cancelled assignments when computing the run', function () {
    $worker = Worker::factory()->create();

    $earlier = Shift::factory()->on('2026-08-10')->between('06:00', '14:00')->create();
    Assignment::factory()->create(['worker_id' => $worker->id, 'shift_id' => $earlier->id, 'status' => 'declined']);

    $newShift = Shift::factory()->on('2026-08-10')->between('14:00', '17:00')->create();

    expect($this->rule->passes($worker->fresh(), $newShift))->toBeTrue();
});
