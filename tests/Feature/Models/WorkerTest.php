<?php

use App\Enums\Gender;
use App\Models\Worker;
use App\Models\WorkerAvailability;
use App\Models\WorkerHours;

it('casts gender and journey_preference to enums', function () {
    $worker = Worker::factory()->gender(Gender::Female)->create();

    expect($worker->gender)->toBe(Gender::Female);
});

it('has many availability windows', function () {
    $worker = Worker::factory()->create();
    WorkerAvailability::factory()->count(3)->create(['worker_id' => $worker->id]);

    expect($worker->availability)->toHaveCount(3);
});

it('has many weekly hours records', function () {
    $worker = Worker::factory()->create();
    WorkerHours::factory()->create(['worker_id' => $worker->id]);

    expect($worker->hours)->toHaveCount(1);
});

it('soft deletes rather than removing the row', function () {
    $worker = Worker::factory()->create();
    $worker->delete();

    expect(Worker::find($worker->id))->toBeNull()
        ->and(Worker::withTrashed()->find($worker->id))->not->toBeNull();
});

it('scopes inactive and standby via factory states', function () {
    $inactive = Worker::factory()->inactive()->create();
    $standby = Worker::factory()->standby()->create();

    expect($inactive->is_active)->toBeFalse()
        ->and($standby->standby)->toBeTrue();
});
