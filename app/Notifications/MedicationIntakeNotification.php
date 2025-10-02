<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User; // Assuming you have a User model

class MedicationIntakeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $intakeData;

    /**
     * Create a new notification instance.
     * @param array $intakeData An array containing 'medication' name and 'time'.
     */
    public function __construct(array $intakeData)
    {
        $this->intakeData = $intakeData;
    }

    /**
     * Get the notification's delivery channels.
     * We use 'database' for the in-app notification list.
     *
     * @param  User  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     * This data is saved to the 'data' column of the notifications table.
     *
     * @param  User  $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'medication_intake',
            'medication_name' => $this->intakeData['medication'],
            'scheduled_time' => $this->intakeData['time'],
            'message' => 'Your intake of ' . $this->intakeData['medication'] . ' is scheduled for ' . $this->intakeData['time'] . '.',
        ];
    }
}
