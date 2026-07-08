<?php

namespace App\Rules\Allocation;

use App\Enums\AssignmentStatus;
use App\Models\Assignment;
use App\Models\Shift;
use App\Models\Worker;
use App\Services\Allocation\GeoDistance;
use Illuminate\Support\Carbon;

/**
 * Prefers workers whose previous same-day shift finishes close to this
 * shift's pickup, minimizing dead-mileage between jobs. Falls back to the
 * worker's home address for their first shift of the day.
 */
class DistanceFromPreviousShiftRule extends SoftScoringRule
{
    public function score(Worker $worker, Shift $shift): float
    {
        $from = $this->previousLocation($worker, $shift);
        $to = $this->pickupLocation($shift);

        if ($from === null || $to === null) {
            return 0.5; // no coordinates to compare — neutral, not a penalty
        }

        $distanceKm = GeoDistance::kilometers($from[0], $from[1], $to[0], $to[1]);
        $preferredMaxKm = (float) config('allocation.preferred_distance_km');

        return max(0.0, 1 - min($distanceKm / $preferredMaxKm, 1.0));
    }

    public function explain(Worker $worker, Shift $shift): string
    {
        $from = $this->previousLocation($worker, $shift);
        $to = $this->pickupLocation($shift);

        if ($from === null || $to === null) {
            return 'No coordinates available to compare distance.';
        }

        $distanceKm = GeoDistance::kilometers($from[0], $from[1], $to[0], $to[1]);

        return sprintf('%.1fkm from previous location (score %.2f).', $distanceKm, $this->score($worker, $shift));
    }

    /** @return array{0: float, 1: float}|null [lat, lng] */
    private function pickupLocation(Shift $shift): ?array
    {
        $address = $shift->pickupAddress;

        return $address?->latitude !== null && $address?->longitude !== null
            ? [(float) $address->latitude, (float) $address->longitude]
            : null;
    }

    /** @return array{0: float, 1: float}|null [lat, lng] */
    private function previousLocation(Worker $worker, Shift $shift): ?array
    {
        $previousShift = $worker->assignments
            ->whereIn('status', [AssignmentStatus::Proposed, AssignmentStatus::Confirmed])
            ->map(fn (Assignment $assignment) => $assignment->shift)
            ->filter(fn (?Shift $other) => $other
                && $other->id !== $shift->id
                && $other->shift_date->isSameDay($shift->shift_date)
                && Carbon::parse($other->end_time)->lte(Carbon::parse($shift->start_time))
            )
            ->sortByDesc(fn (Shift $other) => Carbon::parse($other->end_time)->timestamp)
            ->first();

        if ($previousShift) {
            $address = $previousShift->destinationAddress;

            if ($address?->latitude !== null && $address?->longitude !== null) {
                return [(float) $address->latitude, (float) $address->longitude];
            }
        }

        return $worker->home_latitude !== null && $worker->home_longitude !== null
            ? [(float) $worker->home_latitude, (float) $worker->home_longitude]
            : null;
    }
}
