<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role?->hasPermission('shifts.view') ?? false;
    }

    public function view(User $user, Shift $shift): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->role?->hasPermission('shifts.manage') ?? false;
    }

    public function update(User $user, Shift $shift): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $this->create($user);
    }
}
