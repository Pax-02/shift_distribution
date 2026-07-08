<?php

use App\Models\Client;
use App\Models\Shift;
use App\Models\Worker;
use App\Rules\Allocation\ClientPreferenceRule;

beforeEach(fn () => $this->rule = new ClientPreferenceRule());

it('scores 1.0 when the client is preferred', function () {
    $client = Client::factory()->create();
    $worker = Worker::factory()->create(['preferred_client_ids' => [$client->id]]);
    $shift = Shift::factory()->create(['client_id' => $client->id]);

    expect($this->rule->score($worker, $shift))->toBe(1.0);
});

it('scores 0.0 when the client is avoided', function () {
    $client = Client::factory()->create();
    $worker = Worker::factory()->create(['avoided_client_ids' => [$client->id]]);
    $shift = Shift::factory()->create(['client_id' => $client->id]);

    expect($this->rule->score($worker, $shift))->toBe(0.0);
});

it('scores neutrally with no stated preference', function () {
    $client = Client::factory()->create();
    $worker = Worker::factory()->create();
    $shift = Shift::factory()->create(['client_id' => $client->id]);

    expect($this->rule->score($worker, $shift))->toBe(0.5);
});
