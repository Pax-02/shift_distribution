<?php

use App\Services\AI\MockAIParser;
use Illuminate\Support\Carbon;

beforeEach(fn () => $this->parser = new MockAIParser());

it('extracts all fields from a well-formed message', function () {
    Carbon::setTestNow('2026-01-01');

    $result = $this->parser->parseMessage(
        'Need a driver for Jane Doe on 12/08/2026 from 09:00 to 11:00, pickup 12 High Street, dropoff Willow Day Centre. Female driver required.'
    );

    expect($result->client)->toBe('Jane Doe')
        ->and($result->date)->toBe('2026-08-12')
        ->and($result->startTime)->toBe('09:00')
        ->and($result->endTime)->toBe('11:00')
        ->and($result->pickup)->toBe('12 High Street')
        ->and($result->destination)->toBe('Willow Day Centre')
        ->and($result->genderRequirement)->toBe('female')
        ->and($result->confidence)->toBe(1.0);

    Carbon::setTestNow();
});

it('rolls a dateless year forward when the bare day/month has already passed this year', function () {
    Carbon::setTestNow('2026-08-20');

    $result = $this->parser->parseMessage('Shift for John Smith on 01/01, pickup Home, dropoff Clinic. 08:00 to 09:00.');

    expect($result->date)->toBe('2027-01-01');

    Carbon::setTestNow();
});

it('defaults gender_requirement to any when unmentioned', function () {
    $result = $this->parser->parseMessage('Shift for John Smith on 12/08/2026, pickup Home, dropoff Clinic. 08:00 to 09:00.');

    expect($result->genderRequirement)->toBe('any');
});

it('returns partial results with reduced confidence for an ambiguous message', function () {
    $result = $this->parser->parseMessage('call me about a shift sometime');

    expect($result->client)->toBeNull()
        ->and($result->date)->toBeNull()
        ->and($result->confidence)->toBeLessThan(1.0);
});

it('reports its model identifier', function () {
    expect($this->parser->modelIdentifier())->toBe('mock-v1');
});
