<?php

use App\Models\Shift;
use App\Models\Worker;
use App\Rules\Allocation\ActiveStatusRule;

beforeEach(fn () => $this->rule = new ActiveStatusRule());

it('passes for an active worker', function () {
    $worker = Worker::factory()->create(['is_active' => true]);
    $shift = Shift::factory()->create();

    expect($this->rule->passes($worker, $shift))->toBeTrue();
});

it('fails for an inactive worker', function () {
    $worker = Worker::factory()->inactive()->create();
    $shift = Shift::factory()->create();

    expect($this->rule->passes($worker, $shift))->toBeFalse();
});

it('is a hard constraint', function () {
    expect($this->rule->isHardConstraint())->toBeTrue();
});
