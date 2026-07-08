<?php

namespace App\Rules\Allocation;

use App\Models\Shift;
use App\Models\Worker;
use App\Rules\Allocation\Concerns\InteractsWithWeeklyHours;

class MaximumWeeklyHoursRule extends HardConstraintRule
{
    use InteractsWithWeeklyHours;

    public function passes(Worker $worker, Shift $shift): bool
    {
        $cap = (float) ($worker->max_weekly_hours ?? $worker->contracted_weekly_hours);
        $allocatedSoFar = (float) ($this->weeklyHoursFor($worker, $shift)?->allocated_hours ?? 0);

        return ($allocatedSoFar + $this->shiftDurationHours($shift)) <= $cap;
    }

    public function explain(Worker $worker, Shift $shift): string
    {
        $cap = (float) ($worker->max_weekly_hours ?? $worker->contracted_weekly_hours);
        $allocatedSoFar = (float) ($this->weeklyHoursFor($worker, $shift)?->allocated_hours ?? 0);
        $projected = $allocatedSoFar + $this->shiftDurationHours($shift);

        return $this->passes($worker, $shift)
            ? sprintf('Projected weekly hours %.2f within cap %.2f.', $projected, $cap)
            : sprintf('Projected weekly hours %.2f would exceed cap %.2f.', $projected, $cap);
    }
}
