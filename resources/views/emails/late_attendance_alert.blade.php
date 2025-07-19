<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Late Attendance Alert</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">

<!-- Email content table -->
<table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px;">
    <tr>
        <td>
            <h2 style="color: #333;">Dear {{ $employeeName }},</h2>

            <p>
                This is to inform you that, as per our attendance records, you have been marked <strong>late</strong>
                on <strong>{{ $lateCount }}</strong> occasions this month.
            </p>

            <p style="color: #dc3545; font-weight: bold;">
                ⚠️ We respectfully urge you to improve your punctuality to avoid potential disciplinary measures in accordance with company HR policy.
            </p>

            <p>
                Consistent timeliness is vital to maintaining a productive and professional work environment.
                We appreciate your attention to this matter and your continued dedication.
            </p>

<hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">

<h3 style="color: #333;">📊 Attendance Summary</h3>
<ul style="line-height: 1.6;">
    <li><strong>Month:</strong> {{ now()->format('F Y') }}</li>
    <li><strong>Late Count:</strong> {{ $lateCount }}</li>
{{--    <li><strong>Last Late Date:</strong> {{ $lastLateDate ?? 'N/A' }}</li>--}}
</ul>

<p style="margin: 30px 0;">
    <a href="{{ $frontendUrl }}/add-self-attendance"
       style="background-color: #3490dc; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
        View Attendance
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
    If you're having trouble clicking the "View Attendance" button, copy and paste the URL below into your web browser:
    <br>
    <a href="{{ $frontendUrl }}/add-self-attendance">
        {{ $frontendUrl }}/add-self-attendance
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
