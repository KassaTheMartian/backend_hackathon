<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Beauty Clinic')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        
        .email-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .email-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .email-body {
            padding: 40px 30px;
        }
        
        .email-body h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .email-body p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
            font-size: 15px;
        }
        
        .otp-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-align: center;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .otp-code {
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 8px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }
        
        .otp-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .otp-expiry {
            font-size: 13px;
            opacity: 0.8;
            margin-top: 10px;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .info-box p {
            margin-bottom: 10px;
            color: #555;
        }
        
        .info-box strong {
            color: #333;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 14px 30px;
            text-decoration: none;
            border-radius: 25px;
            margin: 20px 0;
            font-weight: bold;
            text-align: center;
        }
        
        .button:hover {
            opacity: 0.9;
        }
        
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .warning p {
            color: #856404;
            margin-bottom: 0;
        }
        
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        
        .email-footer p {
            margin-bottom: 10px;
        }
        
        .social-links {
            margin: 20px 0;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #667eea;
            text-decoration: none;
        }
        
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 30px 0;
        }
        
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            
            .otp-code {
                font-size: 36px;
                letter-spacing: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>💎 Beauty Clinic</h1>
            <p>Nơi Vẻ Đẹp Được Tôn Vinh - Where Beauty Is Celebrated</p>
        </div>
        
        <div class="email-body">
            @yield('content')
        </div>
        
        <div class="email-footer">
            <p><strong>Beauty Clinic - FPT Software</strong></p>
            <p>📍 Hoàng Tử Gió, Nha Trang, Khánh Hòa</p>
            <p>📞 Hotline: 1900-xxxx | 📧 Email: support@beautyclinic.vn</p>
            
            <div class="divider"></div>
            
            <p style="color: #999; font-size: 12px;">
                Email này được gửi tự động. Vui lòng không trả lời email này.<br>
                © {{ date('Y') }} Beauty Clinic - FPT Software Nha Trang. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
