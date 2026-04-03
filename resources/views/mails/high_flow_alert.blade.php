<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: auto; }
        .header { background: #e53935; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .body { padding: 20px; }
        .stat { background: #fff3e0; border-left: 4px solid #e53935; padding: 12px; margin: 10px 0; border-radius: 4px; }
        .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>⚠️ High Water Flow Alert</h2>
            <p>MEEDMO Magallanes Water Billing System</p>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $clientName }}</strong>,</p>
            <p>Our system has detected an abnormally high water flow rate on your meter. Please check for possible leaks or unusual water usage.</p>

            <div class="stat">
                <strong>Current Flow Rate:</strong> {{ $flowRate }} L/min
            </div>
            <div class="stat">
                <strong>Total Volume (Cu.m):</strong> {{ $cubicMeter }} m³
            </div>
            <div class="stat">
                <strong>Alert Threshold:</strong> {{ $threshold }} L/min
            </div>

            <p>If you believe this is an error or need assistance, please contact us immediately.</p>
            <p>Thank you,<br><strong>MEEDMO Magallanes Water Billing Team</strong></p>
        </div>
        <div class="footer">
            This is an automated alert. Please do not reply to this email.
        </div>
    </div>
</body>
</html>