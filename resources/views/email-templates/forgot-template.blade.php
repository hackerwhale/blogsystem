<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 15px;
        }
        p {
            font-size: 16px;
            color: #555;
            line-height: 1.5;
        }
        .btn {
            display: inline-block;
            padding: 12px 20px;
            margin: 20px 0;
            background: #007bff;
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Your Password</h2>
        <p>Hello, {{ $user->name}}</p>
        <p>We received a request to reset your password. Click the button below to proceed.</p>
        <a href="{{ $actionlink }}" target="_blank" class="btn">Reset Password</a>
        <p>
            This link is valid for 15 minutes.
        </p>
        <p>If you did not request this, please ignore this email.</p>
        <p class="footer">© {{ date('Y')}} Blog System. All Rights Reserved.</p>
    </div>
</body>
</html>
