<?php

use App\Models\Shift;
use App\Models\Worker;
use App\Rules\Allocation\StandbyRule;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->rule = new StandbyRule();
    config(['allocation.urgent_within_hours' => 24]);
});

it('scores 1.0 for a standby worker on an urgent shift', function () {
    $worker = Worker::factory()->standby()->create();
    $shift = Shift::factory()->on(now()->addHours(2)->toDateString())->between(
        now()->addHours(2)->format('H:i'),
        now()->addHours(4)->format('H:i')
    )->create();

    expect($this->rule->score($worker, $shift))->toBe(1.0);
});

it('scores 0.0 for a non-standby worker on an urgent shift', function () {
    $worker = Worker::factory()->create(['standby' => false]);
    $shift = Shift::factory()->on(now()->addHours(2)->toDateString())->between(
        now()->addHours(2)->format('H:i'),
        now()->addHours(4)->format('H:i')
    )->create();

    expect($this->rule->score($worker, $shift))->toBe(0.0);
});

it('scores neutrally for a non-standby worker on a non-urgent shift', function () {
    $worker = Worker::factory()->create(['standby' => false]);
    $shift = Shift::factory()->on(now()->addDays(10)->toDateString())->between('09:00', '11:00')->create();

    expect($this->rule->score($worker, $shift))->toBe(0.5);
});
