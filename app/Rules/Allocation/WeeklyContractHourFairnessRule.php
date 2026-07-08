<?php

namespace App\Rules\Allocation;

use App\Models\Shift;
use App\Models\Worker;
use App\Rules\Allocation\Concerns\InteractsWithWeeklyHours;

/**
 * Favours workers furthest below their contracted hours this week, so
 * allocation spreads shifts evenly rather than repeatedly picking whoever
 * scores highest on other rules.
 */
class WeeklyContractHourFairnessRule extends SoftScoringRule
{
    use InteractsWithWeeklyHours;

    public function score(Worker $worker, Shift $shift): float
    {
        $contracted = (float) $worker->contracted_weekly_hours;

        if ($contracted <= 0) {
            return 0.0; // no contracted-hours signal to apply
        }

        $allocatedSoFar = (float) ($this->weeklyHoursFor($worker, $shift)?->allocated_hours ?? 0);
        $deficit = max(0.0, $contracted - $allocatedSoFar);

        return min(1.0, $deficit / $contracted);
    }

    public function explain(Worker $worker, Shift $shift): string
    {
        $allocatedSoFar = (float) ($this->weeklyHoursFor($worker, $shift)?->allocated_hours ?? 0);

        return sprintf(
            'Worker has %.2f of %.2f contracted hours allocated this week (score %.2f).',
            $allocatedSoFar,
            (float) $worker->contracted_weekly_hours,
            $this->score($worker, $shift)
        );
    }
}
