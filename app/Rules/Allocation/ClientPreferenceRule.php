<?php

namespace App\Rules\Allocation;

use App\Models\Shift;
use App\Models\Worker;

class ClientPreferenceRule extends SoftScoringRule
{
    public function score(Worker $worker, Shift $shift): float
    {
        if (in_array($shift->client_id, $worker->preferred_client_ids ?? [], strict: true)) {
            return 1.0;
        }

        if (in_array($shift->client_id, $worker->avoided_client_ids ?? [], strict: true)) {
            return 0.0;
        }

        return 0.5; // no stated preference either way
    }

    public function explain(Worker $worker, Shift $shift): string
    {
        return match ($this->score($worker, $shift)) {
            1.0 => 'Worker has previously preferred this client.',
            0.0 => 'Worker has asked to avoid this client.',
            default => 'Worker has no stated preference for this client.',
        };
    }
}
