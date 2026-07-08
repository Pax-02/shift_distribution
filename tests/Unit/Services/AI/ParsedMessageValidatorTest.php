<?php

use App\Services\AI\ParsedMessageValidator;
use App\Services\AI\ParsedShiftMessageDTO;

beforeEach(fn () => $this->validator = new ParsedMessageValidator());

function validDto(array $overrides = []): ParsedShiftMessageDTO
{
    $fields = array_merge([
        'client' => 'Jane Doe',
        'date' => '2026-08-12',
        'pickup' => '12 High Street',
        'destination' => 'Willow Day Centre',
        'startTime' => '09:00',
        'endTime' => '11:00',
        'genderRequirement' => 'any',
        'confidence' => 1.0,
        'raw' => 'raw message',
    ], $overrides);

    return new ParsedShiftMessageDTO(...$fields);
}

it('passes for a fully populated, well-formed payload', function () {
    expect($this->validator->passes(validDto()))->toBeTrue();
});

it('fails when a required field is missing', function () {
    $errors = $this->validator->validate(validDto(['client' => null]));

    expect($errors->has('client'))->toBeTrue();
});

it('fails when end_time is not after start_time', function () {
    $errors = $this->validator->validate(validDto(['startTime' => '11:00', 'endTime' => '09:00']));

    expect($errors->has('end_time'))->toBeTrue();
});

it('fails when gender_requirement is not one of any/male/female', function () {
    $errors = $this->validator->validate(validDto(['genderRequirement' => 'unknown']));

    expect($errors->has('gender_requirement'))->toBeTrue();
});

it('fails when the date is not a valid date', function () {
    $errors = $this->validator->validate(validDto(['date' => 'not-a-date']));

    expect($errors->has('date'))->toBeTrue();
});
