<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Medication; 
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\MedicationIntakeMail; 
use App\Notifications\MedicationIntakeNotification; 

class CheckMedicationSchedules extends Command
{
    protected $signature = 'app:meds-notify';
    protected $description = 'Checks for upcoming medication intake schedules and sends a single summary email.';

    public function handle()
    {
        $this->info('Starting medication schedule check...');

        $now = Carbon::now();
        $future = $now->copy()->addMinutes(30);
        $this->comment("Checking schedules between {$now->format('g:i A')} and {$future->format('g:i A')}...");

        // Array to collect all schedules, grouped by user ID, for the summary email
        $notificationsByUser = [];

        // 1. Fetch all Medications with associated users and time schedules
        $medications = Medication::with(['user', 'timeSchedules'])->whereHas('user')->get();

        // 2. Loop through all schedules, dispatch DB notification, and collect email data
        foreach ($medications as $medication) {
            $user = $medication->user;
            
            if (!$user || !$user->timezone) {
                continue; // Skip if user or timezone is not set
            }

            foreach ($medication->timeSchedules as $schedule) {
                try {
                    // Create a Carbon instance from the schedule time, using the user's timezone.
                    $scheduledIntake = Carbon::parse($schedule->schedule_time, $user->timezone);
                } catch (\Exception $e) {
                    $this->error("Could not parse time: {$schedule->schedule_time} for user {$user->id}. Error: {$e->getMessage()}");
                    continue; // Skip invalid time formats
                }
                
                // Check if the time is now or in the future and within the next 30 minutes
                if ($scheduledIntake->greaterThanOrEqualTo($now) && $scheduledIntake->lessThanOrEqualTo($future)) {
                    
                    $notificationData = [
                        'time' => $scheduledIntake->format('g:i A'),
                        'medication' => $medication->brand_name,
                        'user_id' => $user->id,
                    ];

                    // Send Database Notification (one per intake for clear in-app logs)
                    Notification::send($user, new MedicationIntakeNotification($notificationData));
                    
                    // Collect data for the single summary email
                    $notificationsByUser[$user->id]['user'] = $user;
                    $notificationsByUser[$user->id]['schedules'][] = [
                        'time' => $scheduledIntake->format('g:i A'),
                        'medication' => $medication->brand_name,
                    ];
                }
            }
        }

        // 3. Loop through grouped users and dispatch the single summary email
        $emailCount = 0;
        foreach ($notificationsByUser as $userData) {
            $user = $userData['user'];
            $schedules = $userData['schedules'];

            $this->line("Dispatching summary email for User {$user->id} with " . count($schedules) . " intakes.");

            // Mailer now receives an array of all scheduled intakes
            Mail::to($user->email)->send(new MedicationIntakeMail($schedules));
            $emailCount++;
        }

        $this->info("Medication schedule check complete. Dispatched {$emailCount} summary emails.");
        return Command::SUCCESS;
    }
}