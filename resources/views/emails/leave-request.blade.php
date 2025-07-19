<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Leave Request</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;">

<!-- Email content table -->
<table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: white; border: 1px solid #ddd; padding: 20px;">
    <tr>
        <td>
            <h2>Hello {{ $adminName }},</h2>

            <p><strong>{{ $leaveData['employee_name'] }}</strong> has submitted a leave request.</p>

            <hr>

            <h3>🗓️ Leave Request Details:</h3>
            <ul>
                <li><strong>From:</strong> {{ $leaveData['from_date'] }}</li>
                <li><strong>To:</strong> {{ $leaveData['to_date'] }}</li>
                <li><strong>Reason:</strong> {{ $leaveData['leave_reason'] ?? 'N/A' }}</li>
            </ul>

            <p style="margin: 20px 0;">
                <a href="{{ $frontendUrl }}/leave-requests-list"
                   style="background-color: #3490dc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    View Request
                </a>
            </p>

            <p>Thank you for using WeTechHubHRM system!</p>

            <p>Regards,<br>{{ config('app.name') }}</p>

            <hr>
            <p style="font-size: 12px; color: #666;">
                If you're having trouble clicking the "View Request" button, copy and paste the URL below into your web browser:
                <br>
                <a href="{{ $frontendUrl }}/leave-requests-list">
                    {{ $frontendUrl }}/leave-requests-list
                </a>
            </p>
        </td>
    </tr>
</table>

<!-- Copyright footer below the table -->
<p style="text-align: center; font-size: 12px; color: #999; margin-top: 20px;">
    © 2025 WeTechHubHRMS. All rights reserved.
</p>

</body>
</html>


