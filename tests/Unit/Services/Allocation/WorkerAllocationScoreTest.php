<?php

use App\Services\Allocation\WorkerAllocationScore;

it('serializes to an array with worker_id, total_score and breakdown', function () {
    $score = new WorkerAllocationScore(
        workerId: 'abc-123',
        totalScore: 42.5,
        breakdown: [['rule' => 'SomeRule', 'raw_score' => 1.0]]
    );

    expect($score->toArray())->toBe([
        'worker_id' => 'abc-123',
        'total_score' => 42.5,
        'breakdown' => [['rule' => 'SomeRule', 'raw_score' => 1.0]],
    ]);
});
