<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Changed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        p {
            color: #555;
        }
        .info {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Changed Confirmation</h2>
        <p>Hello <strong>{{$user->name}}</strong>,</p>
        <p>Your password has been successfully changed. Please use the credentials below to log in:</p>
        <div class="info">
            <p><strong>Email/Username:</strong> {{$user->email}}</p>
            <p><strong>New Password:</strong> {{$new_password}}</p>
        </div>
        <p>If you did not request this change, please contact our support team immediately.</p>
        <div class="footer">
            <p>&copy; {{date('Y')}}Blog System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
