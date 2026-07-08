<?php

use App\Enums\AssignmentStatus;
use App\Models\Assignment;
use App\Models\Shift;
use App\Models\Worker;

it('belongs to a shift and a worker', function () {
    $shift = Shift::factory()->create();
    $worker = Worker::factory()->create();
    $assignment = Assignment::factory()->create(['shift_id' => $shift->id, 'worker_id' => $worker->id]);

    expect($assignment->shift->is($shift))->toBeTrue()
        ->and($assignment->worker->is($worker))->toBeTrue()
        ->and($assignment->status)->toBe(AssignmentStatus::Proposed);
});

it('stores the score breakdown as an array', function () {
    $assignment = Assignment::factory()->withScore(87.5, [
        ['rule' => 'ActiveStatusRule', 'raw_score' => 1.0, 'weighted_score' => 1.0],
    ])->create();

    expect($assignment->allocation_score)->toEqual('87.5000')
        ->and($assignment->score_breakdown)->toBeArray()->toHaveCount(1);
});
