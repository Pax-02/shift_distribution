<?php

use App\Rules\Allocation\ActiveStatusRule;
use App\Rules\Allocation\AvailabilityRule;
use App\Rules\Allocation\ClientPreferenceRule;
use App\Rules\Allocation\ConsecutiveWorkingHoursRule;
use App\Rules\Allocation\DistanceFromPreviousShiftRule;
use App\Rules\Allocation\GenderRequirementRule;
use App\Rules\Allocation\JourneyPreferenceRule;
use App\Rules\Allocation\MaximumWeeklyHoursRule;
use App\Rules\Allocation\StandbyRule;
use App\Rules\Allocation\WeeklyContractHourFairnessRule;

return [

    /*
    |--------------------------------------------------------------------------
    | Allocation Rules
    |--------------------------------------------------------------------------
    |
    | Every rule AllocationService considers, keyed by class. Hard-constraint
    | rules only need "enabled" — they gate eligibility, not score. Soft
    | rules also need "weight", which AllocationService multiplies against
    | the rule's 0.0-1.0 score to get its contribution to the total.
    |
    | Rule weights live here (not a DB table) because the rule set is small,
    | developer-tunable, and benefits from being version-controlled alongside
    | the rule classes themselves. A DB-editable table is a natural extension
    | once an admin UI exists to justify it.
    |
    */

    'rules' => [
        // Hard constraints (order = evaluation order; cheapest/most-exclusionary first)
        ActiveStatusRule::class => ['enabled' => true],
        GenderRequirementRule::class => ['enabled' => true],
        AvailabilityRule::class => ['enabled' => true],
        MaximumWeeklyHoursRule::class => ['enabled' => true],
        ConsecutiveWorkingHoursRule::class => ['enabled' => true],

        // Soft, weighted scoring rules
        WeeklyContractHourFairnessRule::class => ['enabled' => true, 'weight' => 20],
        ClientPreferenceRule::class => ['enabled' => true, 'weight' => 15],
        DistanceFromPreviousShiftRule::class => ['enabled' => true, 'weight' => 12],
        StandbyRule::class => ['enabled' => true, 'weight' => 10],
        JourneyPreferenceRule::class => ['enabled' => true, 'weight' => 8],
    ],

    // ConsecutiveWorkingHoursRule: max contiguous working span, in hours.
    'max_consecutive_hours' => 10,

    // StandbyRule: a shift starting within this many hours is "urgent".
    'urgent_within_hours' => 24,

    // JourneyPreferenceRule: shifts at or under this duration count as "short".
    'short_journey_max_hours' => 2,

    // DistanceFromPreviousShiftRule: distance (km) at/beyond which the score bottoms out at 0.
    'preferred_distance_km' => 15,
];
