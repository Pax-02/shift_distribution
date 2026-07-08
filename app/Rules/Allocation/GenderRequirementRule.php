<?php

namespace App\Rules\Allocation;

use App\Models\Shift;
use App\Models\Worker;

class GenderRequirementRule extends HardConstraintRule
{
    public function passes(Worker $worker, Shift $shift): bool
    {
        return $shift->gender_requirement->satisfiedBy($worker->gender);
    }

    public function explain(Worker $worker, Shift $shift): string
    {
        return $this->passes($worker, $shift)
            ? "Worker gender ({$worker->gender->value}) satisfies shift requirement ({$shift->gender_requirement->value})."
            : "Shift requires gender {$shift->gender_requirement->value}, worker is {$worker->gender->value}.";
    }
}
