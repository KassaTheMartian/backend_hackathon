@extends('emails.layout')

@section('title', 'Test Email - Beauty Clinic')

@section('content')
    <h2>🎉 Email Template Test</h2>

    <p>Xin chào,</p>

    <p>
        Đây là email test để kiểm tra template email của <strong>Beauty Clinic</strong>. 
        Nếu bạn nhận được email này, nghĩa là hệ thống email đang hoạt động tốt!
    </p>

    <div class="otp-box">
        <div class="otp-label">MÃ TEST</div>
        <div class="otp-code">123456</div>
        <div class="otp-expiry">⏰ Email template đã sẵn sàng sử dụng!</div>
    </div>

    <div class="info-box">
        <p><strong>✅ Các tính năng của email template:</strong></p>
        <p>• Responsive design - hiển thị đẹp trên mọi thiết bị</p>
        <p>• Màu sắc gradient hiện đại</p>
        <p>• Typography dễ đọc</p>
        <p>• Icons và emojis sinh động</p>
        <p>• Layout chuyên nghiệp</p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="#" class="button">
            Nút Call-to-Action
        </a>
    </div>

    <div class="warning">
        <p>
            <strong>⚠️ Đây là ví dụ về warning box</strong><br>
            Sử dụng để hiển thị thông tin quan trọng hoặc cảnh báo.
        </p>
    </div>

    <p>
        Template này có thể được sử dụng cho:
    </p>
    <ul style="color: #666; line-height: 1.8; margin-left: 20px;">
        <li>OTP verification emails</li>
        <li>Password reset emails</li>
        <li>Booking confirmation emails</li>
        <li>Welcome emails</li>
        <li>Promotional emails</li>
    </ul>

    <p style="margin-top: 30px;">
        Trân trọng,<br>
        <strong>Đội ngũ Beauty Clinic</strong> 💎
    </p>
@endsection
