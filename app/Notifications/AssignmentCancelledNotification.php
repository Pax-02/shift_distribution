<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentCancelledNotification extends Notification
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
            ->subject('Shift assignment cancelled')
            ->line("Your assignment for the shift on {$shift->shift_date->toFormattedDateString()} has been cancelled.")
            ->when($this->assignment->cancellation_reason, fn ($message) => $message->line("Reason: {$this->assignment->cancellation_reason}"));
    }
}
