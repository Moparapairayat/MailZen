Verify Your Delivery Server

Hello,

You have created a new delivery server in MailZen. To ensure email delivery works correctly, please verify this server by visiting the link below.

Server Details:
- Server Name: {{ $deliveryServer->name }}
- Type: {{ strtoupper($deliveryServer->type) }}
@if($deliveryServer->hostname)
- Hostname: {{ $deliveryServer->hostname }}
@endif
- From Email: {{ $deliveryServer->from_email }}

Verify your delivery server:
{{ $verificationUrl }}

This verification link will expire in 7 days. If you didn't create this delivery server, please ignore this email.

---
This is an automated message from MailZen. Please do not reply to this email.



