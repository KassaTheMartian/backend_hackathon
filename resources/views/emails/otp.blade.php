@extends('emails.layout')

@section('title', 'Mã OTP - Beauty Clinic')

@section('content')
    <h2>
        @if($purpose === 'verify_email')
            ✉️ Xác thực Email của bạn
        @elseif($purpose === 'password_reset')
            🔐 Đặt lại Mật khẩu
        @elseif($purpose === 'guest_booking')
            📅 Xác nhận Đặt lịch
        @else
            🔒 Mã Xác thực OTP
        @endif
    </h2>

    <p>Xin chào,</p>

    @if($purpose === 'verify_email')
        <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>Beauty Clinic</strong>! Để hoàn tất quá trình đăng ký, vui lòng sử dụng mã OTP dưới đây để xác thực email của bạn.</p>
    @elseif($purpose === 'password_reset')
        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Vui lòng sử dụng mã OTP dưới đây để tiếp tục.</p>
    @elseif($purpose === 'guest_booking')
        <p>Để hoàn tất việc đặt lịch hoặc xem lịch sử đặt lịch của bạn, vui lòng sử dụng mã OTP dưới đây.</p>
    @else
        <p>Vui lòng sử dụng mã OTP dưới đây để xác thực.</p>
    @endif

    <div class="otp-box">
        <div class="otp-label">MÃ OTP CỦA BẠN</div>
        <div class="otp-code">{{ $otp }}</div>
        <div class="otp-expiry">⏰ Mã có hiệu lực trong {{ $expiryMinutes }} phút</div>
    </div>

    <div class="warning">
        <p>
            <strong>⚠️ Lưu ý bảo mật:</strong><br>
            Không chia sẻ mã OTP này với bất kỳ ai. Beauty Clinic sẽ không bao giờ yêu cầu mã OTP qua điện thoại hoặc email.
        </p>
    </div>

    @if($purpose === 'verify_email')
        <p>Sau khi xác thực email thành công, bạn sẽ có thể:</p>
        <ul style="color: #666; line-height: 1.8; margin-left: 20px;">
            <li>Đặt lịch hẹn dịch vụ làm đẹp</li>
            <li>Theo dõi lịch sử điều trị</li>
            <li>Nhận ưu đãi và khuyến mãi đặc biệt</li>
            <li>Tích điểm thành viên thân thiết</li>
        </ul>
    @endif

    <p style="margin-top: 30px;">
        Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi ngay.
    </p>

    <p style="margin-top: 20px;">
        Trân trọng,<br>
        <strong>Đội ngũ Beauty Clinic</strong>
    </p>
@endsection
