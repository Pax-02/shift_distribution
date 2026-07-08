<?php

namespace App\Rules\Allocation\Concerns;

use App\Models\Shift;
use App\Models\Worker;
use App\Models\WorkerHours;
use Illuminate\Support\Carbon;

/**
 * Shared helpers for rules that reason about a worker's hours for the ISO
 * week containing the shift (MaximumWeeklyHoursRule, WeeklyContractHourFairnessRule).
 */
trait InteractsWithWeeklyHours
{
    protected function shiftDurationHours(Shift $shift): float
    {
        return Carbon::parse($shift->start_time)->diffInMinutes(Carbon::parse($shift->end_time)) / 60;
    }

    protected function weekStart(Shift $shift): string
    {
        return $shift->shift_date->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    /**
     * The worker's pre-aggregated hours row for the shift's week, if one has
     * been computed yet (see RecalculateWorkerHours listener). Falls back to
     * "zero allocated so far" when none exists — e.g. a brand-new worker.
     */
    protected function weeklyHoursFor(Worker $worker, Shift $shift): ?WorkerHours
    {
        return $worker->hours->firstWhere(
            fn (WorkerHours $hours) => $hours->week_start_date->toDateString() === $this->weekStart($shift)
        );
    }
}
