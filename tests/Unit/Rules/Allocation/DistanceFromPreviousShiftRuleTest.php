<?php

use App\Models\Assignment;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Shift;
use App\Models\Worker;
use App\Rules\Allocation\DistanceFromPreviousShiftRule;
use App\Services\Allocation\GeoDistance;

beforeEach(function () {
    $this->rule = new DistanceFromPreviousShiftRule();
    config(['allocation.preferred_distance_km' => 15]);
});

it('scores neutrally when the pickup address has no coordinates', function () {
    $worker = Worker::factory()->create(['home_latitude' => 51.5, 'home_longitude' => -0.1]);
    $shift = Shift::factory()->create(); // no pickup_address_id set — just free-text

    expect($this->rule->score($worker, $shift))->toBe(0.5);
});

it('falls back to the worker home address for the first shift of the day', function () {
    $worker = Worker::factory()->create(['home_latitude' => 51.500, 'home_longitude' => -0.100]);
    $client = Client::factory()->create();
    $pickup = ClientAddress::factory()->create(['client_id' => $client->id, 'latitude' => 51.505, 'longitude' => -0.110]);
    $shift = Shift::factory()->create(['client_id' => $client->id, 'pickup_address_id' => $pickup->id]);

    $expectedDistance = GeoDistance::kilometers(51.500, -0.100, 51.505, -0.110);
    $expectedScore = max(0.0, 1 - min($expectedDistance / 15, 1.0));

    expect($this->rule->score($worker->fresh(), $shift))->toEqualWithDelta($expectedScore, 0.0001);
});

it('uses the previous same-day shift destination instead of home when one exists', function () {
    $worker = Worker::factory()->create(['home_latitude' => 51.000, 'home_longitude' => -1.000]); // far away, irrelevant
    $client = Client::factory()->create();

    $previousDestination = ClientAddress::factory()->create(['client_id' => $client->id, 'latitude' => 51.500, 'longitude' => -0.100]);
    $previousShift = Shift::factory()->create([
        'client_id' => $client->id,
        'shift_date' => '2026-08-10',
        'start_time' => '08:00',
        'end_time' => '09:00',
        'destination_address_id' => $previousDestination->id,
    ]);
    Assignment::factory()->create(['worker_id' => $worker->id, 'shift_id' => $previousShift->id, 'status' => 'confirmed']);

    $pickup = ClientAddress::factory()->create(['client_id' => $client->id, 'latitude' => 51.501, 'longitude' => -0.101]);
    $nextShift = Shift::factory()->create([
        'client_id' => $client->id,
        'shift_date' => '2026-08-10',
        'start_time' => '09:30',
        'end_time' => '11:00',
        'pickup_address_id' => $pickup->id,
    ]);

    $expectedDistance = GeoDistance::kilometers(51.500, -0.100, 51.501, -0.101);
    $expectedScore = max(0.0, 1 - min($expectedDistance / 15, 1.0));

    expect($this->rule->score($worker->fresh(), $nextShift))->toEqualWithDelta($expectedScore, 0.0001);
});

it('scores 0.0 when the previous location is far beyond the preferred distance', function () {
    $worker = Worker::factory()->create(['home_latitude' => 51.500, 'home_longitude' => -0.100]);
    $client = Client::factory()->create();
    $pickup = ClientAddress::factory()->create(['client_id' => $client->id, 'latitude' => 53.800, 'longitude' => -1.550]); // Leeds, ~250km away
    $shift = Shift::factory()->create(['client_id' => $client->id, 'pickup_address_id' => $pickup->id]);

    expect($this->rule->score($worker->fresh(), $shift))->toBe(0.0);
});
