<?php

namespace App\Rules\Allocation;

use App\Enums\AssignmentStatus;
use App\Models\Assignment;
use App\Models\Shift;
use App\Models\Worker;
use Illuminate\Support\Carbon;

/**
 * Blocks a worker from being scheduled into a run of back-to-back/overlapping
 * shifts longer than config('allocation.max_consecutive_hours'), even if each
 * individual shift is short.
 */
class ConsecutiveWorkingHoursRule extends HardConstraintRule
{
    public function passes(Worker $worker, Shift $shift): bool
    {
        return $this->longestConsecutiveRunHours($worker, $shift) <= config('allocation.max_consecutive_hours');
    }

    public function explain(Worker $worker, Shift $shift): string
    {
        $hours = $this->longestConsecutiveRunHours($worker, $shift);
        $cap = config('allocation.max_consecutive_hours');

        return $this->passes($worker, $shift)
            ? sprintf('Longest consecutive run would be %.2fh, within the %sh cap.', $hours, $cap)
            : sprintf('Longest consecutive run would be %.2fh, exceeding the %sh cap.', $hours, $cap);
    }

    /**
     * Merges this shift's [start, end] interval with the worker's other
     * same-day proposed/confirmed assignments and returns the longest
     * contiguous (touching or overlapping) run, in hours.
     */
    private function longestConsecutiveRunHours(Worker $worker, Shift $shift): float
    {
        $intervals = $worker->assignments
            ->whereIn('status', [AssignmentStatus::Proposed, AssignmentStatus::Confirmed])
            ->map(fn (Assignment $assignment) => $assignment->shift)
            ->filter(fn (?Shift $other) => $other && $other->shift_date->isSameDay($shift->shift_date) && $other->id !== $shift->id)
            ->map(fn (Shift $other) => [
                Carbon::parse($other->start_time)->timestamp,
                Carbon::parse($other->end_time)->timestamp,
            ])
            ->push([Carbon::parse($shift->start_time)->timestamp, Carbon::parse($shift->end_time)->timestamp])
            ->sortBy(fn (array $interval) => $interval[0])
            ->values();

        $longestSeconds = 0;
        $runStart = null;
        $runEnd = null;

        foreach ($intervals as [$start, $end]) {
            if ($runStart === null) {
                [$runStart, $runEnd] = [$start, $end];
                continue;
            }

            if ($start <= $runEnd) { // touches or overlaps the current run — extend it
                $runEnd = max($runEnd, $end);
            } else {
                $longestSeconds = max($longestSeconds, $runEnd - $runStart);
                [$runStart, $runEnd] = [$start, $end];
            }
        }

        if ($runStart !== null) {
            $longestSeconds = max($longestSeconds, $runEnd - $runStart);
        }

        return $longestSeconds / 3600;
    }
}
