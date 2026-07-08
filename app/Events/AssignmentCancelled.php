<?php

namespace App\Events;

use App\Models\Assignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Assignment $assignment) {}
}
