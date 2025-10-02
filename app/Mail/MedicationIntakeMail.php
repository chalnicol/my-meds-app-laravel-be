<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MedicationIntakeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The array of upcoming schedules for the user.
     * @var array
     */
    public $schedules;

    public function __construct(array $schedules)
    {
        $this->schedules = $schedules;
    }

    public function envelope(): Envelope
    {
        $intakeCount = count($this->schedules);
        $subject = ($intakeCount > 1) 
            ? "Reminder: {$intakeCount} Upcoming Medication Intakes!"
            : "Medication Reminder: Upcoming Intake!";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            // markdown: 'emails.password-reset', // Use a Blade Markdown view
            view: 'emails.custom-med-intake-reminder',
             with: [
                'schedules' => $this->schedules,
            ]
        );

    }
}