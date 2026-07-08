<?php

namespace App\Events;

use App\Models\Shift;
use App\Services\Allocation\WorkerAllocationScore;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ShiftAllocated
{
    use Dispatchable, SerializesModels;

    /** @param  Collection<int, WorkerAllocationScore>  $rankedWorkers */
    public function __construct(
        public readonly Shift $shift,
        public readonly Collection $rankedWorkers,
    ) {}
}
