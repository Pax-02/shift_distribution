<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function confirm(User $user, Assignment $assignment): bool
    {
        return $user->role?->hasPermission('shifts.manage') ?? false;
    }

    public function cancel(User $user, Assignment $assignment): bool
    {
        return $this->confirm($user, $assignment);
    }
}
