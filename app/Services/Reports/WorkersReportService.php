<?php

namespace App\Services\Reports;

use App\Models\Worker;

class WorkersReportService
{
    /** Roster-level counts — active/inactive, standby, and a gender breakdown. */
    public function summary(): array
    {
        $total = Worker::count();
        $active = Worker::where('is_active', true)->count();
        $standby = Worker::where('standby', true)->count();

        // ->toBase() bypasses Eloquent model hydration/casts — otherwise
        // pluck() would try to use the casted Gender enum as an array key,
        // which isn't legal (array keys must be int|string).
        // count(*) comes back from PDO as a numeric string, not an int —
        // cast explicitly so the JSON response has real numbers.
        $byGender = Worker::query()
            ->toBase()
            ->selectRaw('gender, count(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->map(fn ($count) => (int) $count);

        return [
            'total_workers' => $total,
            'active_workers' => $active,
            'inactive_workers' => $total - $active,
            'standby_workers' => $standby,
            'by_gender' => $byGender,
        ];
    }
}
