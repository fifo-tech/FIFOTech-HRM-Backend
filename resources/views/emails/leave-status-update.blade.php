<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Status Update</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">

<!-- Email content table -->
<table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px;">
    <tr>
        <td>
            <h2 style="color: #333;">Hello {{ $employeeName }},</h2>

            <p>Your leave request from <strong>{{ $leaveRequest->start_date }}</strong> to <strong>{{ $leaveRequest->end_date }}</strong> has been
                <span style="color: {{ $status == 'Approved' ? '#28a745' : '#dc3545' }}; font-weight: bold;">
                    {{ $status }}
                </span>.
            </p>

            <p><strong>Approved/Rejected By:</strong> {{ $hrName }}</p>

            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">

            <h3 style="color: #333;">🗓️ Leave Request Details</h3>
            <ul style="line-height: 1.6;">
                <li><strong>From:</strong> {{ $leaveRequest->start_date }}</li>
                <li><strong>To:</strong> {{ $leaveRequest->end_date }}</li>
                <li><strong>Reason:</strong> {{ $leaveRequest->leave_reason ?? 'N/A' }}</li>
            </ul>

            <p style="margin: 30px 0;">
                <a href="{{ $frontendUrl }}/self-leave-requests-list"
                   style="background-color: #3490dc; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    View Request
                </a>
            </p>

            <p style="font-size: 14px;">Thank you for using the <strong>WeTechHubHRM</strong> system.</p>

            <p style="font-size: 14px;">
                Regards,<br>
                <strong>HR Department</strong><br>
                WeTechHub
            </p>

            <hr style="border: none; border-top: 1px solid #ddd; margin-top: 40px;">
            <p style="font-size: 12px; color: #666;">
                If you're having trouble clicking the "View Request" button, copy and paste the URL below into your web browser:
                <br>
                <a href="{{ $frontendUrl }}/self-leave-requests-list">
                    {{ $frontendUrl }}/self-leave-requests-list
                </a>
            </p>
        </td>
    </tr>
</table>

<!-- Footer -->
<p style="text-align: center; font-size: 12px; color: #999; margin-top: 20px;">
    © {{ date('Y') }} WeTechHubHRMS. All rights reserved.
</p>

</body>
</html>
