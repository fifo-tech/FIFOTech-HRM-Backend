<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f8fa;
            margin: 0;
            padding: 40px;
        }
        .email-container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.05);
            text-align: center;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
            letter-spacing: 5px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
<div class="email-container">
    <h2>Hello, {{ $userName }}</h2>
    <p>Use the following OTP to complete your verification:</p>
    <div class="otp-code">{{ $code }}</div>
    <p>This OTP is valid for 5 minutes.</p>
    <div class="footer">
        &copy; {{ date('Y') }} Wetech HRM. All rights reserved.
    </div>
</div>
</body>
</html>
