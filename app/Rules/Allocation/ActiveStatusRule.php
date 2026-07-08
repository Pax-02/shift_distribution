<?php

namespace App\Rules\Allocation;

use App\Models\Shift;
use App\Models\Worker;

class ActiveStatusRule extends HardConstraintRule
{
    public function passes(Worker $worker, Shift $shift): bool
    {
        return $worker->is_active;
    }

    public function explain(Worker $worker, Shift $shift): string
    {
        return $worker->is_active ? 'Worker is active.' : 'Worker is inactive.';
    }
}
