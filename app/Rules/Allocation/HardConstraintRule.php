<?php

namespace App\Rules\Allocation;

use App\Models\Shift;
use App\Models\Worker;

/**
 * Base for eligibility gates: only passes() matters, so subclasses only
 * implement that (plus explain()). score() is never consulted for a hard
 * rule — AllocationService excludes the worker before scoring runs.
 */
abstract class HardConstraintRule implements AllocationRuleInterface
{
    public function isHardConstraint(): bool
    {
        return true;
    }

    public function score(Worker $worker, Shift $shift): float
    {
        return 1.0;
    }
}
