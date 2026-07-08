<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Worker;

class WorkerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role?->hasPermission('workers.view') ?? false;
    }

    public function view(User $user, Worker $worker): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->role?->hasPermission('workers.manage') ?? false;
    }

    public function update(User $user, Worker $worker): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Worker $worker): bool
    {
        return $this->create($user);
    }
}
