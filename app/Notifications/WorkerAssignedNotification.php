<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mail-only for phase 1 — workers don't have User accounts yet, so there's
 * no in-app/database channel to target. Add channels here (not at call
 * sites) once one exists.
 */
class WorkerAssignedNotification extends Notification
{
    public function __construct(public readonly Assignment $assignment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $shift = $this->assignment->shift;

        return (new MailMessage)
            ->subject('New shift assignment')
            ->line("You've been proposed for a shift on {$shift->shift_date->toFormattedDateString()}.")
            ->line("Time: {$shift->start_time} - {$shift->end_time}")
            ->line('Pickup: '.($shift->pickup_address_text ?? $shift->pickupAddress?->line1 ?? 'TBC'))
            ->line('Destination: '.($shift->destination_address_text ?? $shift->destinationAddress?->line1 ?? 'TBC'));
    }
}
