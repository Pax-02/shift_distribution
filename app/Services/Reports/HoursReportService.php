<?php

namespace App\Services\Reports;

use App\Models\WorkerHours;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HoursReportService
{
    /**
     * Per-worker contracted vs. allocated (vs. worked, once timesheets
     * exist) hours for one ISO week. Reads the precomputed worker_hours
     * table (kept in sync by RecalculateWorkerHours), not a live sum over
     * assignments.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function forWeek(?string $weekStart = null): Collection
    {
        $weekStart = $weekStart
            ? Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY)->toDateString()
            : Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();

        return WorkerHours::query()
            ->with('worker:id,first_name,last_name')
            ->where('week_start_date', $weekStart)
            ->get()
            ->map(fn (WorkerHours $hours) => [
                'worker_id' => $hours->worker_id,
                'worker_name' => $hours->worker->fullName(),
                'week_start_date' => $hours->week_start_date->toDateString(),
                'contracted_hours' => (float) $hours->contracted_hours,
                'allocated_hours' => (float) $hours->allocated_hours,
                'worked_hours' => $hours->worked_hours !== null ? (float) $hours->worked_hours : null,
                'utilization' => $hours->contracted_hours > 0
                    ? round((float) $hours->allocated_hours / (float) $hours->contracted_hours, 2)
                    : null,
            ])
            ->values();
    }
}
