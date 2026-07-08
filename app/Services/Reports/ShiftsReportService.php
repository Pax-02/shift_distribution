<?php

namespace App\Services\Reports;

use App\Models\Shift;
use Illuminate\Support\Carbon;

class ShiftsReportService
{
    /** Shift counts by status over a date range, plus a confirmed-allocation rate. */
    public function summary(?string $from = null, ?string $to = null): array
    {
        $query = Shift::query()
            ->when($from, fn ($q) => $q->where('shift_date', '>=', Carbon::parse($from)->toDateString()))
            ->when($to, fn ($q) => $q->where('shift_date', '<=', Carbon::parse($to)->toDateString()));

        $total = (clone $query)->count();

        // ->toBase(): see WorkersReportService — avoids enum-keyed pluck().
        // count(*) also needs an explicit (int) cast for the same reason.
        $byStatus = (clone $query)->toBase()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->map(fn ($count) => (int) $count);

        $confirmed = (clone $query)->where('status', 'confirmed')->count();

        return [
            'total_shifts' => $total,
            'by_status' => $byStatus,
            'confirmed_allocation_rate' => $total > 0 ? round($confirmed / $total, 2) : null,
        ];
    }
}
