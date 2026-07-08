<?php

namespace App\Listeners;

use App\Enums\AssignmentStatus;
use App\Events\AssignmentCancelled;
use App\Events\AssignmentCreated;
use App\Models\WorkerHours;
use Illuminate\Support\Carbon;

/**
 * Keeps worker_hours.allocated_hours in sync with the assignments table, so
 * WeeklyContractHourFairnessRule/MaximumWeeklyHoursRule can read a
 * precomputed weekly total instead of re-summing assignments on every
 * allocation run. Recomputes from scratch rather than incrementing/
 * decrementing, so it self-corrects regardless of which event fired.
 */
class RecalculateWorkerHours
{
    public function handle(AssignmentCreated|AssignmentCancelled $event): void
    {
        $assignment = $event->assignment;
        $worker = $assignment->worker;
        $weekStart = $assignment->shift->shift_date->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $allocatedHours = $worker->assignments()
            ->whereIn('status', [AssignmentStatus::Proposed, AssignmentStatus::Confirmed])
            ->whereHas('shift', fn ($query) => $query->whereBetween('shift_date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
            ->with('shift')
            ->get()
            ->sum(fn ($a) => Carbon::parse($a->shift->start_time)->diffInMinutes(Carbon::parse($a->shift->end_time)) / 60);

        WorkerHours::updateOrCreate(
            ['worker_id' => $worker->id, 'week_start_date' => $weekStart->toDateString()],
            ['contracted_hours' => $worker->contracted_weekly_hours, 'allocated_hours' => $allocatedHours]
        );
    }
}
