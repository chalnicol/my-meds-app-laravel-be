<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
    <style>
        /* Add your custom CSS here for styling and responsiveness */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .header { background-color: #1f2937; color: #ffffff; padding: 10px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px 0; line-height: 1.6; color: #333; }
        .button-container { text-align: center; margin: 25px 0; }
        .button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #1f2937;
            color: #ffffff !important; /* !important to override client styles */
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #888; }
        a { color: #1f2937; text-decoration: none; }
       
        .url-wrapper {
            word-wrap: break-word; /* Older browsers */
            overflow-wrap: break-word;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        <div class="content">
            <p>Hello {{ $fullname }},</p>
            
            <p>This is an automated notification to inform you that the password for your account has been changed</p>

            <div class="button-container">
                <a href="{{ $url }}" class="button">View Profile</a>
            </div>

            <p>If you are having trouble clicking the "View Profile" button, copy and paste the URL below into your web browser:</p>
            
            <p class="url-wrapper"><a href="{{ $url }}">{{ $url }}</a></p>

            <p>If you performed this action, no further steps are required.</p>

            <p>If you did not authorize this change, please contact us immediately to secure your account.</p>


            <p>Thanks,<br>{{ config('app.name') }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>