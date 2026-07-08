<?php

use App\Enums\JourneyPreference;
use App\Models\Shift;
use App\Models\Worker;
use App\Rules\Allocation\JourneyPreferenceRule;

beforeEach(function () {
    $this->rule = new JourneyPreferenceRule();
    config(['allocation.short_journey_max_hours' => 2]);
});

it('scores 1.0 when a short-preference worker gets a short shift', function () {
    $worker = Worker::factory()->create(['journey_preference' => JourneyPreference::Short]);
    $shift = Shift::factory()->between('09:00', '10:30')->create(); // 1.5h

    expect($this->rule->score($worker, $shift))->toBe(1.0);
});

it('scores 0.0 when a short-preference worker gets a long shift', function () {
    $worker = Worker::factory()->create(['journey_preference' => JourneyPreference::Short]);
    $shift = Shift::factory()->between('09:00', '13:00')->create(); // 4h

    expect($this->rule->score($worker, $shift))->toBe(0.0);
});

it('scores 1.0 when a long-preference worker gets a long shift', function () {
    $worker = Worker::factory()->create(['journey_preference' => JourneyPreference::Long]);
    $shift = Shift::factory()->between('09:00', '13:00')->create();

    expect($this->rule->score($worker, $shift))->toBe(1.0);
});

it('scores neutrally when the worker has no strong preference', function () {
    $worker = Worker::factory()->create(['journey_preference' => JourneyPreference::Any]);
    $shift = Shift::factory()->between('09:00', '13:00')->create();

    expect($this->rule->score($worker, $shift))->toBe(0.5);
});
