<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medication Intake Reminder</title>
    <style>
        /* Add your custom CSS here for styling and responsiveness */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .header { background-color: #1f2937; color: #ffffff; padding: 10px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px 0; line-height: 1.6; color: #333; }
        .schedule-list { margin: 25px 0; padding: 15px; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; }
        .schedule-item { margin-bottom: 10px; padding: 8px; border-bottom: 1px solid #eee; }
        .schedule-item:last-child { border-bottom: none; }
        .time { font-weight: bold; color: #059669; } /* A green color for time */
        .medication-name { font-weight: bold; color: #1f2937; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #888; }
        a { color: #1f2937; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        <div class="content">
            {{-- NOTE: Assuming you can pass $userName from the Mail class if needed, but it's often omitted in automated reminders --}}
            <p>Hello! This is a reminder that you have medication intakes scheduled soon.</p>

            <p>Please prepare for the following doses coming up in the next **30 minutes**:</p>

            {{-- LOOP THROUGH SCHEDULES --}}
            <div class="schedule-list">
                @foreach ($schedules as $schedule)
                    <div class="schedule-item">
                        <span class="medication-name">{{ $schedule['medication'] }}</span>
                        is due at
                        <span class="time">{{ $schedule['time'] }}</span>
                    </div>
                @endforeach
            </div>
            {{-- END LOOP --}}

            <p>This is an automated reminder. Please ensure you take your medication on time.</p>

            {{-- Removed the button-container as it's not needed for a reminder --}}
            
            <p>Thanks,<br>{{ config('app.name') }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>