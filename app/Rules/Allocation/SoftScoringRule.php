<?php

namespace App\Rules\Allocation;

use App\Models\Shift;
use App\Models\Worker;

/**
 * Base for weighted scoring signals: they never exclude a worker, so
 * passes() is always true and subclasses only implement score()/explain().
 */
abstract class SoftScoringRule implements AllocationRuleInterface
{
    public function isHardConstraint(): bool
    {
        return false;
    }

    public function passes(Worker $worker, Shift $shift): bool
    {
        return true;
    }
}
