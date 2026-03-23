<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Delivery Server</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h1 style="color: #2563eb; margin-top: 0;">Verify Your Delivery Server</h1>
        <p>Hello,</p>
        <p>You have created a new delivery server in MailZen. To ensure email delivery works correctly, please verify this server by clicking the button below.</p>
        
        <div style="margin: 30px 0;">
            <table style="width: 100%; border-collapse: collapse; background-color: #fff; border: 1px solid #e5e7eb; border-radius: 8px;">
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;"><strong>Server Name:</strong></td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">{{ $deliveryServer->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;"><strong>Type:</strong></td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">{{ strtoupper($deliveryServer->type) }}</td>
                </tr>
                @if($deliveryServer->hostname)
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;"><strong>Hostname:</strong></td>
                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">{{ $deliveryServer->hostname }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 12px;"><strong>From Email:</strong></td>
                    <td style="padding: 12px;">{{ $deliveryServer->from_email }}</td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $verificationUrl }}" style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">Verify Delivery Server</a>
        </div>

        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            If the button doesn't work, copy and paste this link into your browser:<br>
            <a href="{{ $verificationUrl }}" style="color: #2563eb; word-break: break-all;">{{ $verificationUrl }}</a>
        </p>

        <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
            This verification link will expire in 7 days. If you didn't create this delivery server, please ignore this email.
        </p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        <p style="color: #9ca3af; font-size: 12px; text-align: center;">
            This is an automated message from MailZen. Please do not reply to this email.
        </p>
    </div>
</body>
</html>



