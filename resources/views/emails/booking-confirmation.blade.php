@extends('emails.layout')

@section('title', __('emails.booking.subject'))

@section('content')
    <h2>✅ {{ __('emails.booking.heading_success') }}</h2>

    <p>{{ __('emails.common.greeting') }} <strong>{{ $booking->user ? $booking->user->name : $booking->guest_name }}</strong>,</p>

    <p>{!! __('emails.booking.intro') !!}</p>

    <div class="info-box">
        <p><strong>📋 {{ __('emails.booking.box_title') }}</strong></p>
        <p><strong>{{ __('emails.booking.code') }}:</strong> {{ $booking->booking_code }}</p>
        <p><strong>{{ __('emails.booking.service') }}:</strong> {{ is_array($booking->service->name) ? ($booking->service->name[app()->getLocale()] ?? ($booking->service->name['vi'] ?? $booking->service->name['en'])) : $booking->service->name }}</p>
        <p><strong>{{ __('emails.booking.branch') }}:</strong> {{ is_array($booking->branch->name) ? ($booking->branch->name[app()->getLocale()] ?? ($booking->branch->name['vi'] ?? $booking->branch->name['en'])) : $booking->branch->name }}</p>
        <p><strong>{{ __('emails.booking.date') }}:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}</p>
        <p><strong>{{ __('emails.booking.time') }}:</strong> {{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}</p>
        <p><strong>{{ __('emails.booking.duration') }}:</strong> {{ $booking->duration }} {{ __('emails.booking.minutes') }}</p>
        @if($booking->staff)
            <p><strong>{{ __('emails.booking.staff') }}:</strong> {{ $booking->staff->full_name }}</p>
        @endif
    </div>

    <div class="info-box" style="border-left-color: #28a745;">
        <p><strong>💰 {{ __('emails.booking.payment_title') }}</strong></p>
        <p><strong>{{ __('emails.booking.service_price') }}:</strong> {{ number_format($booking->service_price, 0, ',', '.') }} {{ __('emails.booking.vnd') }}</p>
        @if($booking->discount_amount > 0)
            <p><strong>{{ __('emails.booking.discount') }}:</strong> -{{ number_format($booking->discount_amount, 0, ',', '.') }} {{ __('emails.booking.vnd') }}</p>
        @endif
        <p style="font-size: 18px; color: #28a745;"><strong>{{ __('emails.booking.total') }}:</strong> {{ number_format($booking->total_amount, 0, ',', '.') }} {{ __('emails.booking.vnd') }}</p>
        <p><strong>{{ __('emails.booking.status') }}:</strong> 
            @if($booking->payment_status === 'paid')
                <span style="color: #28a745;">✓ {{ __('emails.booking.status_paid') }}</span>
            @elseif($booking->payment_status === 'pending')
                <span style="color: #ffc107;">⏳ {{ __('emails.booking.status_pending') }}</span>
            @else
                <span>{{ $booking->payment_status }}</span>
            @endif
        </p>
    </div>

    @if($booking->notes)
        <div class="info-box" style="border-left-color: #17a2b8;">
            <p><strong>📝 {{ __('emails.booking.notes') }}:</strong></p>
            <p>{{ $booking->notes }}</p>
        </div>
    @endif

    <div class="warning" style="background-color: #d1ecf1; border-left-color: #17a2b8;">
        <p style="color: #0c5460;">
            <strong>📌 {{ __('emails.booking.notice_title') }}:</strong><br>
            • {{ __('emails.booking.notice_on_time') }}<br>
            • {{ __('emails.booking.notice_reschedule') }}<br>
            • {{ __('emails.booking.notice_bring_code', ['code' => $booking->booking_code]) }}
        </p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <p style="margin-bottom: 15px;">{{ __('emails.booking.manage_at') }}</p>
        <a href="{{ config('app.frontend_url') }}/my-bookings" class="button">
            {{ __('emails.booking.cta_my_bookings') }}
        </a>
    </div>

    <p>{{ __('emails.booking.outro') }}</p>

    <p style="margin-top: 20px;">
        {{ __('emails.common.regards') }}<br>
        <strong>{{ __('emails.common.team_name') }}</strong> 💎
    </p>
@endsection
