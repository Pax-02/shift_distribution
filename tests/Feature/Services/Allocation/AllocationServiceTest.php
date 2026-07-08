<?php

use App\Enums\Gender;
use App\Models\Client;
use App\Models\Shift;
use App\Models\Worker;
use App\Models\WorkerAvailability;
use App\Services\Allocation\AllocationService;
use App\Services\Allocation\WorkerAllocationScore;

beforeEach(function () {
    $this->service = new AllocationService();
    $this->client = Client::factory()->create();

    // Monday, requires a female worker.
    $this->shift = Shift::factory()->create([
        'client_id' => $this->client->id,
        'shift_date' => '2026-08-10',
        'start_time' => '09:00',
        'end_time' => '11:00',
        'gender_requirement' => 'female',
    ]);
});

function givenFullDayAvailability(Worker $worker): void
{
    WorkerAvailability::factory()->create([
        'worker_id' => $worker->id,
        'type' => 'recurring',
        'day_of_week' => 1, // Monday
        'start_time' => '00:00',
        'end_time' => '23:59',
    ]);
}

it('excludes inactive workers, gender mismatches, and workers with no availability, ranking the rest by score', function () {
    // journey_preference pinned to "any" (neutral 0.5 in JourneyPreferenceRule)
    // on every worker below — otherwise WorkerFactory's random short/long
    // preference occasionally outweighs the ClientPreferenceRule delta this
    // test is isolating, making the ranking assertion flaky.
    $baseline = Worker::factory()->gender(Gender::Female)->create(['standby' => false, 'journey_preference' => 'any']);
    givenFullDayAvailability($baseline);

    $inactive = Worker::factory()->gender(Gender::Female)->inactive()->create();
    givenFullDayAvailability($inactive);

    $wrongGender = Worker::factory()->gender(Gender::Male)->create();
    givenFullDayAvailability($wrongGender);

    $noAvailability = Worker::factory()->gender(Gender::Female)->create();
    // deliberately no WorkerAvailability rows

    $preferredByClient = Worker::factory()->gender(Gender::Female)->create([
        'standby' => false,
        'journey_preference' => 'any',
        'preferred_client_ids' => [$this->client->id],
    ]);
    givenFullDayAvailability($preferredByClient);

    $results = $this->service->allocate($this->shift);

    $rankedWorkerIds = $results->pluck('workerId')->all();

    expect($rankedWorkerIds)
        ->toContain($baseline->id, $preferredByClient->id)
        ->not->toContain($inactive->id, $wrongGender->id, $noAvailability->id)
        ->and($rankedWorkerIds[0])->toBe($preferredByClient->id) // higher score: preferred client
        ->and($results)->each->toBeInstanceOf(WorkerAllocationScore::class);
});

it('returns a score breakdown with an entry per evaluated rule up to the first failed hard constraint', function () {
    $worker = Worker::factory()->gender(Gender::Female)->create();
    givenFullDayAvailability($worker);

    $results = $this->service->allocate($this->shift);
    $breakdown = $results->firstWhere('workerId', $worker->id)->breakdown;

    expect($breakdown)->not->toBeEmpty();

    foreach ($breakdown as $entry) {
        expect($entry)->toHaveKeys(['rule', 'type', 'explanation']);

        if ($entry['type'] === 'hard') {
            expect($entry)->toHaveKey('passed');
        } else {
            expect($entry)->toHaveKeys(['weight', 'raw_score', 'weighted_score']);
        }
    }
});
