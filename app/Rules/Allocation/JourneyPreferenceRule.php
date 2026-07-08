<?php

namespace App\Rules\Allocation;

use App\Enums\JourneyPreference;
use App\Models\Shift;
use App\Models\Worker;
use Illuminate\Support\Carbon;

class JourneyPreferenceRule extends SoftScoringRule
{
    public function score(Worker $worker, Shift $shift): float
    {
        return match ($worker->journey_preference) {
            null, JourneyPreference::Any => 0.5, // no stated preference / accepts anything — neutral
            JourneyPreference::Short => $this->isShortJourney($shift) ? 1.0 : 0.0,
            JourneyPreference::Long => $this->isShortJourney($shift) ? 0.0 : 1.0,
        };
    }

    public function explain(Worker $worker, Shift $shift): string
    {
        $length = $this->isShortJourney($shift) ? 'short' : 'long';
        $preference = $worker->journey_preference?->value ?? 'none';

        return sprintf('Shift is a %s journey; worker prefers %s (score %.2f).', $length, $preference, $this->score($worker, $shift));
    }

    private function isShortJourney(Shift $shift): bool
    {
        $durationHours = Carbon::parse($shift->start_time)->diffInMinutes(Carbon::parse($shift->end_time)) / 60;

        return $durationHours <= config('allocation.short_journey_max_hours');
    }
}
