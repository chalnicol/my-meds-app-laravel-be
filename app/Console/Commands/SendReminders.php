<?php

namespace App\Console\Commands;

use App\Models\Reminder;
use App\Notifications\ReminderNotification;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Sends out pending reminders.';

    public function handle()
    {
        $now = now();

        $reminders = Reminder::where('remind_at', '<=', $now)
            ->whereNull('sent_at')
            ->get();

        foreach ($reminders as $reminder) {
            // Find the user to notify
            $user = $reminder->user;
            
            // Send the notification
            $user->notify(new ReminderNotification($reminder));

            // Update the reminder to mark it as sent
            $reminder->sent_at = $now;
            $reminder->save();
        }

        $this->info('Reminders sent successfully!');
    }
}
