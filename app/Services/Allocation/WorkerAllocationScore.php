<?php

namespace App\Services\Allocation;

/**
 * Result of scoring one worker against one shift. One shape shared by
 * AllocationController's JSON response and assignments.score_breakdown.
 */
final class WorkerAllocationScore
{
    /**
     * @param  array<int, array<string, mixed>>  $breakdown  one entry per evaluated rule
     */
    public function __construct(
        public readonly string $workerId,
        public readonly float $totalScore,
        public readonly array $breakdown,
    ) {}

    public function toArray(): array
    {
        return [
            'worker_id' => $this->workerId,
            'total_score' => $this->totalScore,
            'breakdown' => $this->breakdown,
        ];
    }
}
