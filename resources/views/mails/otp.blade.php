<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>One-Time Password – Magallanes Water Billing</title>
    <style>
        body{ font-family:Arial,Helvetica,sans-serif; font-size:14px; color:#333; margin:0; padding:20px; background-color:#f6f6f6; }
        .wrapper{ max-width:600px; margin:auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.1); }
        .header{ background-color:#0C6170; color:#ffffff; padding:24px; text-align:center; }
        .header h1{ margin:0; font-size:22px; font-weight:bold; }
        .content{ padding:30px; }
        .otp-box{ background-color:#f1f8ff; border-left:5px solid #0C6170; padding:20px; margin:20px 0; font-size:18px; text-align:center; }
        .otp-code{ font-size:28px; font-weight:bold; letter-spacing:4px; color:#0C6170; }
        .footer{ background-color:#f6f6f6; font-size:12px; color:#666; padding:20px; text-align:center; }
        .footer a{ color:#0C6170; text-decoration:none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Magallanes Water Billing System</h1>
    </div>

    <div class="content">
        <p>Dear {{ $recepient->first_name }},</p>

        <p>You recently requested to reset your password. Please use the one-time password below to complete the process:</p>

        <div class="otp-box">
            <strong>One-Time Password (OTP)</strong><br>
            <span class="otp-code">{{ $otp }}</span>
        </div>

        <p><strong>Important:</strong></p>
        <ul>
            <li>This code expires in <strong>15 minutes</strong>.</li>
            <li>Do not share this code with anyone; our staff will never ask for it.</li>
            <li>If you did not request this, please disregard this e-mail or contact support immediately.</li>
        </ul>

        <p>Need help? Reply to this e-mail or call our support desk:</p>
        <p>
            📞 <a href="tel:{{ config('app.support_phone', '(085) 123-4567') }}">{{ config('app.support_phone', '(085) 123-4567') }}</a><br>
            ✉️ <a href="mailto:{{ config('app.support_email', 'support@magallaneswater.gov.ph') }}">{{ config('app.support_email', 'support@magallaneswater.gov.ph') }}</a>
        </p>

        <p>Thank you for choosing Magallanes Water Provider.</p>
        <p>Respectfully,<br>Magallanes Water Billing System</p>
    </div>

    <div class="footer">
        This is an automated message. Please do not reply to this address. Visit our website for further assistance.
    </div>
</div>
</body>
</html>