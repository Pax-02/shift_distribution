<?php

namespace App\Listeners;

use App\Events\AssignmentCreated;
use App\Notifications\WorkerAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAssignmentNotification implements ShouldQueue
{
    public function handle(AssignmentCreated $event): void
    {
        $worker = $event->assignment->worker;

        // Notifiable's default mail route reads $worker->email — skip
        // silently rather than let the mailer fail on a null recipient.
        if ($worker->email !== null) {
            $worker->notify(new WorkerAssignedNotification($event->assignment));
        }
    }
}
