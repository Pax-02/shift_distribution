<?php

namespace App\Rules\Allocation;

use App\Models\Shift;
use App\Models\Worker;
use Illuminate\Support\Carbon;

/**
 * Prioritizes standby workers for last-minute shifts; has little effect when
 * a shift is scheduled well in advance since standby availability isn't the
 * deciding factor at that point.
 */
class StandbyRule extends SoftScoringRule
{
    public function score(Worker $worker, Shift $shift): float
    {
        return match (true) {
            $worker->standby && $this->isUrgent($shift) => 1.0,
            $worker->standby => 0.6,
            $this->isUrgent($shift) => 0.0,
            default => 0.5,
        };
    }

    public function explain(Worker $worker, Shift $shift): string
    {
        $urgency = $this->isUrgent($shift) ? 'urgent' : 'not urgent';

        return sprintf(
            'Worker standby=%s, shift is %s (score %.2f).',
            $worker->standby ? 'yes' : 'no',
            $urgency,
            $this->score($worker, $shift)
        );
    }

    private function isUrgent(Shift $shift): bool
    {
        $shiftStart = Carbon::parse($shift->shift_date->toDateString().' '.$shift->start_time);

        // Signed hours-until-shift, computed from raw timestamps rather than
        // diffInHours(), whose sign convention is easy to get backwards.
        $hoursUntilShift = ($shiftStart->timestamp - now()->timestamp) / 3600;

        return $hoursUntilShift >= 0 && $hoursUntilShift <= config('allocation.urgent_within_hours');
    }
}
