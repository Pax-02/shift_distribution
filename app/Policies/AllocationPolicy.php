<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

/**
 * Not auto-discovered like WorkerPolicy/ClientPolicy/ShiftPolicy — it doesn't
 * map 1:1 to a single Eloquent model, so its abilities are registered
 * explicitly via Gate::define() in AppServiceProvider.
 */
class AllocationPolicy
{
    public function run(User $user, Shift $shift): bool
    {
        return $user->role?->hasPermission('allocation.run') ?? false;
    }

    public function viewHistory(User $user): bool
    {
        return $user->role?->hasPermission('allocation.run') ?? false;
    }
}
