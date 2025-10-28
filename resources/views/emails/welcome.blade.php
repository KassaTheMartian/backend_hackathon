@extends('emails.layout')

@section('title', 'Chào mừng - Beauty Clinic')

@section('content')
    <h2>🎉 Chào mừng đến với Beauty Clinic!</h2>

    <p>Xin chào <strong>{{ $user->name }}</strong>,</p>

    <p>
        Chúng tôi rất vui mừng chào đón bạn đến với <strong>Beauty Clinic</strong> - nơi vẻ đẹp được tôn vinh! 
        Cảm ơn bạn đã tin tưởng và đăng ký tài khoản với chúng tôi.
    </p>

    <div class="info-box" style="text-align: center; border-left: none; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
        <p style="font-size: 18px; margin-bottom: 15px;">✨ <strong>Tài khoản của bạn đã sẵn sàng!</strong> ✨</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
    </div>

    <h3 style="color: #667eea; margin-top: 30px;">🌟 Những điều tuyệt vời đang chờ bạn:</h3>
    
    <div style="margin: 20px 0;">
        <div style="display: flex; align-items: start; margin-bottom: 15px;">
            <span style="font-size: 24px; margin-right: 10px;">💆</span>
            <div>
                <strong>Đặt lịch dễ dàng</strong><br>
                <span style="color: #666; font-size: 14px;">Chọn dịch vụ, thời gian và chuyên viên yêu thích chỉ với vài cú click</span>
            </div>
        </div>

        <div style="display: flex; align-items: start; margin-bottom: 15px;">
            <span style="font-size: 24px; margin-right: 10px;">🎁</span>
            <div>
                <strong>Ưu đãi độc quyền</strong><br>
                <span style="color: #666; font-size: 14px;">Nhận ngay voucher giảm giá 20% cho lần đặt lịch đầu tiên</span>
            </div>
        </div>

        <div style="display: flex; align-items: start; margin-bottom: 15px;">
            <span style="font-size: 24px; margin-right: 10px;">⭐</span>
            <div>
                <strong>Tích điểm thưởng</strong><br>
                <span style="color: #666; font-size: 14px;">Mỗi dịch vụ sử dụng sẽ được tích điểm để đổi quà hấp dẫn</span>
            </div>
        </div>

        <div style="display: flex; align-items: start; margin-bottom: 15px;">
            <span style="font-size: 24px; margin-right: 10px;">📱</span>
            <div>
                <strong>Quản lý lịch hẹn</strong><br>
                <span style="color: #666; font-size: 14px;">Theo dõi lịch sử điều trị và quản lý lịch hẹn mọi lúc mọi nơi</span>
            </div>
        </div>

        <div style="display: flex; align-items: start; margin-bottom: 15px;">
            <span style="font-size: 24px; margin-right: 10px;">🤖</span>
            <div>
                <strong>Tư vấn AI 24/7</strong><br>
                <span style="color: #666; font-size: 14px;">Chatbot thông minh sẵn sàng tư vấn và hỗ trợ bạn bất cứ lúc nào</span>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin: 40px 0;">
        <p style="margin-bottom: 15px; font-size: 16px;">Sẵn sàng để bắt đầu hành trình làm đẹp?</p>
        <a href="{{ config('app.frontend_url') }}/services" class="button">
            🌸 Khám phá Dịch vụ
        </a>
    </div>

    <div class="info-box" style="border-left-color: #28a745;">
        <p><strong>🎊 Ưu đãi chào mừng thành viên mới!</strong></p>
        <p>Sử dụng mã: <strong style="color: #667eea; font-size: 18px;">WELCOME20</strong></p>
        <p style="margin-bottom: 0;">Giảm ngay 20% cho lần đặt lịch đầu tiên (Áp dụng cho đơn hàng từ 500.000 VNĐ)</p>
    </div>

    <div class="divider"></div>

    <p style="font-size: 14px; color: #888;">
        <strong>💡 Mẹo nhỏ:</strong> Đăng nhập và hoàn thiện thông tin cá nhân để nhận được những tư vấn và chăm sóc phù hợp nhất với làn da của bạn!
    </p>

    <p style="margin-top: 30px;">
        Nếu bạn có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với chúng tôi. Chúng tôi luôn sẵn sàng hỗ trợ bạn!
    </p>

    <p style="margin-top: 20px;">
        Chúc bạn có những trải nghiệm tuyệt vời!<br>
        <strong>Đội ngũ Beauty Clinic</strong> 💎✨
    </p>
@endsection
