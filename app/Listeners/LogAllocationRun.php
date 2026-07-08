<?php

namespace App\Listeners;

use App\Events\ShiftAllocated;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Records that an allocation run happened, separately from the plain CRUD
 * audit trail the Auditable trait writes for the resulting Assignment —
 * this captures the *action* (who ran allocation, and the full ranked
 * candidate list), not just a row-level create/update diff.
 */
class LogAllocationRun
{
    public function handle(ShiftAllocated $event): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'allocated',
            'auditable_type' => $event->shift->getMorphClass(),
            'auditable_id' => $event->shift->id,
            'new_values' => [
                'ranked_worker_ids' => $event->rankedWorkers->pluck('workerId')->all(),
            ],
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
        ]);
    }
}
