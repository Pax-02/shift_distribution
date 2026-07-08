<?php

namespace App\Listeners;

use App\Events\AssignmentCancelled;
use App\Notifications\AssignmentCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCancellationNotification implements ShouldQueue
{
    public function handle(AssignmentCancelled $event): void
    {
        $worker = $event->assignment->worker;

        if ($worker->email !== null) {
            $worker->notify(new AssignmentCancelledNotification($event->assignment));
        }
    }
}
