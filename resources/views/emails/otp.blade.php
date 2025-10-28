@extends('emails.layout')

@section('title', $purpose === 'verify_email'
    ? __('emails.otp.subjects.verify_email')
    : ($purpose === 'password_reset'
        ? __('emails.otp.subjects.password_reset')
        : ($purpose === 'guest_booking'
            ? __('emails.otp.subjects.guest_booking')
            : __('emails.otp.subjects.default'))))

@section('content')
    <h2>
        @if($purpose === 'verify_email')
            ✉️ {{ __('emails.otp.headings.verify_email') }}
        @elseif($purpose === 'password_reset')
            🔐 {{ __('emails.otp.headings.password_reset') }}
        @elseif($purpose === 'guest_booking')
            📅 {{ __('emails.otp.headings.guest_booking') }}
        @else
            🔒 {{ __('emails.otp.headings.default') }}
        @endif
    </h2>

    <p>{{ __('emails.common.greeting') }}</p>

    @if($purpose === 'verify_email')
        <p>{!! __('emails.otp.intro.verify_email') !!}</p>
    @elseif($purpose === 'password_reset')
        <p>{{ __('emails.otp.intro.password_reset') }}</p>
    @elseif($purpose === 'guest_booking')
        <p>{{ __('emails.otp.intro.guest_booking') }}</p>
    @else
        <p>{{ __('emails.otp.intro.default') }}</p>
    @endif

    <div class="otp-box">
        <div class="otp-label">{{ __('emails.otp.box.label') }}</div>
        <div class="otp-code">{{ $otp }}</div>
        <div class="otp-expiry">{!! __('emails.otp.box.expiry', ['minutes' => $expiryMinutes]) !!}</div>
    </div>

    <div class="warning">
        <p>
            <strong>⚠️ {{ __('emails.otp.security.title') }}</strong><br>
            {{ __('emails.otp.security.note') }}
        </p>
    </div>

    @if($purpose === 'verify_email')
        <p>{{ __('emails.otp.benefits.title') }}</p>
        <ul style="color: #666; line-height: 1.8; margin-left: 20px;">
            <li>{{ __('emails.otp.benefits.item1') }}</li>
            <li>{{ __('emails.otp.benefits.item2') }}</li>
            <li>{{ __('emails.otp.benefits.item3') }}</li>
            <li>{{ __('emails.otp.benefits.item4') }}</li>
        </ul>
    @endif

    <p style="margin-top: 30px;">{{ __('emails.common.ignore_if_not_you') }}</p>

    <p style="margin-top: 20px;">
        {{ __('emails.common.regards') }}<br>
        <strong>{{ __('emails.common.team_name') }}</strong>
    </p>
@endsection
