@extends('emails.layout')

@section('title', 'Xác nhận Đặt lịch - Beauty Clinic')

@section('content')
    <h2>✅ Đặt lịch thành công!</h2>

    <p>Xin chào <strong>{{ $booking->user ? $booking->user->name : $booking->guest_name }}</strong>,</p>

    <p>Cảm ơn bạn đã tin tưởng và đặt lịch tại <strong>Beauty Clinic</strong>. Chúng tôi đã nhận được yêu cầu đặt lịch của bạn.</p>

    <div class="info-box">
        <p><strong>📋 THÔNG TIN ĐẶT LỊCH</strong></p>
        <p><strong>Mã đặt lịch:</strong> {{ $booking->booking_code }}</p>
        <p><strong>Dịch vụ:</strong> {{ is_array($booking->service->name) ? ($booking->service->name['vi'] ?? $booking->service->name['en']) : $booking->service->name }}</p>
        <p><strong>Chi nhánh:</strong> {{ is_array($booking->branch->name) ? ($booking->branch->name['vi'] ?? $booking->branch->name['en']) : $booking->branch->name }}</p>
        <p><strong>Ngày hẹn:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}</p>
        <p><strong>Giờ hẹn:</strong> {{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</p>
        <p><strong>Thời gian dự kiến:</strong> {{ $booking->duration }} phút</p>
        @if($booking->staff)
            <p><strong>Nhân viên phục vụ:</strong> {{ $booking->staff->full_name }}</p>
        @endif
    </div>

    <div class="info-box" style="border-left-color: #28a745;">
        <p><strong>💰 THÔNG TIN THANH TOÁN</strong></p>
        <p><strong>Giá dịch vụ:</strong> {{ number_format($booking->service_price, 0, ',', '.') }} VNĐ</p>
        @if($booking->discount_amount > 0)
            <p><strong>Giảm giá:</strong> -{{ number_format($booking->discount_amount, 0, ',', '.') }} VNĐ</p>
        @endif
        <p style="font-size: 18px; color: #28a745;"><strong>Tổng thanh toán:</strong> {{ number_format($booking->total_amount, 0, ',', '.') }} VNĐ</p>
        <p><strong>Trạng thái:</strong> 
            @if($booking->payment_status === 'paid')
                <span style="color: #28a745;">✓ Đã thanh toán</span>
            @elseif($booking->payment_status === 'pending')
                <span style="color: #ffc107;">⏳ Chưa thanh toán</span>
            @else
                <span>{{ $booking->payment_status }}</span>
            @endif
        </p>
    </div>

    @if($booking->notes)
        <div class="info-box" style="border-left-color: #17a2b8;">
            <p><strong>📝 Ghi chú:</strong></p>
            <p>{{ $booking->notes }}</p>
        </div>
    @endif

    <div class="warning" style="background-color: #d1ecf1; border-left-color: #17a2b8;">
        <p style="color: #0c5460;">
            <strong>📌 Lưu ý quan trọng:</strong><br>
            • Vui lòng đến đúng giờ để đảm bảo chất lượng dịch vụ tốt nhất<br>
            • Nếu cần thay đổi lịch hẹn, vui lòng liên hệ trước ít nhất 4 giờ<br>
            • Mang theo mã đặt lịch <strong>{{ $booking->booking_code }}</strong> khi đến spa
        </p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <p style="margin-bottom: 15px;">Bạn có thể quản lý lịch hẹn của mình tại:</p>
        <a href="{{ config('app.frontend_url') }}/my-bookings" class="button">
            Xem Lịch Hẹn Của Tôi
        </a>
    </div>

    <p>
        Chúng tôi rất mong được phục vụ bạn! Nếu có bất kỳ thắc mắc nào, đừng ngần ngại liên hệ với chúng tôi.
    </p>

    <p style="margin-top: 20px;">
        Trân trọng,<br>
        <strong>Đội ngũ Beauty Clinic</strong> 💎
    </p>
@endsection
