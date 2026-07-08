<?php

namespace App\Policies;

use App\Models\User;

/**
 * Not tied to a single Eloquent model, so — like AllocationPolicy — its
 * ability is registered explicitly via Gate::define() in AppServiceProvider.
 */
class ReportPolicy
{
    public function view(User $user): bool
    {
        return $user->role?->hasPermission('reports.view') ?? false;
    }
}
