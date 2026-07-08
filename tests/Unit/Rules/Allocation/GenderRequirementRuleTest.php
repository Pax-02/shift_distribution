<?php

use App\Enums\Gender;
use App\Models\Shift;
use App\Models\Worker;
use App\Rules\Allocation\GenderRequirementRule;

beforeEach(fn () => $this->rule = new GenderRequirementRule());

it('passes when the shift has no gender requirement', function () {
    $worker = Worker::factory()->gender(Gender::Male)->create();
    $shift = Shift::factory()->genderRequirement('any')->create();

    expect($this->rule->passes($worker, $shift))->toBeTrue();
});

it('passes when worker gender matches the requirement', function () {
    $worker = Worker::factory()->gender(Gender::Female)->create();
    $shift = Shift::factory()->genderRequirement('female')->create();

    expect($this->rule->passes($worker, $shift))->toBeTrue();
});

it('fails when worker gender does not match the requirement', function () {
    $worker = Worker::factory()->gender(Gender::Male)->create();
    $shift = Shift::factory()->genderRequirement('female')->create();

    expect($this->rule->passes($worker, $shift))->toBeFalse();
});
