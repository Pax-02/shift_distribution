<?php

namespace App\Rules\Allocation;

use App\Models\Shift;
use App\Models\Worker;
use App\Models\WorkerAvailability;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AvailabilityRule extends HardConstraintRule
{
    public function passes(Worker $worker, Shift $shift): bool
    {
        $windows = $this->matchingWindows($worker, $shift);

        $coveredByAnAvailableWindow = $windows
            ->where('is_available', true)
            ->contains(fn (WorkerAvailability $window) => $this->windowCovers($window, $shift));

        $blockedByAnUnavailableWindow = $windows
            ->where('is_available', false)
            ->contains(fn (WorkerAvailability $window) => $this->windowOverlaps($window, $shift));

        return $coveredByAnAvailableWindow && ! $blockedByAnUnavailableWindow;
    }

    public function explain(Worker $worker, Shift $shift): string
    {
        return $this->passes($worker, $shift)
            ? 'Worker has an availability window covering the shift.'
            : 'Worker has no availability window covering the shift, or an explicit block overlaps it.';
    }

    /** Availability rows for the shift's day-of-week (recurring) or exact date (date_specific). */
    private function matchingWindows(Worker $worker, Shift $shift): Collection
    {
        $dayOfWeek = $shift->shift_date->dayOfWeek; // Carbon: 0=Sun..6=Sat, matches our schema
        $date = $shift->shift_date->toDateString();

        return $worker->availability
            ->filter(fn (WorkerAvailability $window) => ($window->type === 'recurring' && $window->day_of_week === $dayOfWeek)
                || ($window->type === 'date_specific' && $window->specific_date?->toDateString() === $date)
            );
    }

    private function windowCovers(WorkerAvailability $window, Shift $shift): bool
    {
        return Carbon::parse($window->start_time)->lte(Carbon::parse($shift->start_time))
            && Carbon::parse($window->end_time)->gte(Carbon::parse($shift->end_time));
    }

    /** Any overlap at all — a partial block still makes the worker unavailable for the shift. */
    private function windowOverlaps(WorkerAvailability $window, Shift $shift): bool
    {
        return Carbon::parse($window->start_time)->lt(Carbon::parse($shift->end_time))
            && Carbon::parse($window->end_time)->gt(Carbon::parse($shift->start_time));
    }
}
