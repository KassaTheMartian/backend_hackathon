<?php

return [
    'common' => [
        'greeting' => 'Hello,',
        'ignore_if_not_you' => 'If you did not make this request, please ignore this email or contact us immediately.',
        'regards' => 'Best regards,',
        'team_name' => 'Beauty Clinic Team',
    ],
    'otp' => [
        'subjects' => [
            'verify_email' => 'Verify Email - Beauty Clinic',
            'password_reset' => 'Reset Password - Beauty Clinic',
            'guest_booking' => 'Booking Confirmation - Beauty Clinic',
            'default' => 'Your OTP Code - Beauty Clinic',
        ],
        'headings' => [
            'verify_email' => 'Verify your email',
            'password_reset' => 'Reset your password',
            'guest_booking' => 'Confirm your booking',
            'default' => 'One-Time Password (OTP)',
        ],
        'intro' => [
            'verify_email' => 'Thank you for registering at <strong>Beauty Clinic</strong>! To complete your registration, please use the OTP below to verify your email.',
            'password_reset' => 'We received a request to reset your account password. Please use the OTP below to continue.',
            'guest_booking' => 'To complete your booking or view your booking history, please use the OTP below.',
            'default' => 'Please use the OTP below to verify.',
        ],
        'box' => [
            'label' => 'YOUR OTP CODE',
            'expiry' => '⏰ The code is valid for :minutes minutes',
        ],
        'security' => [
            'title' => 'Security notice',
            'note' => 'Do not share this OTP with anyone. Beauty Clinic will never ask for your OTP via phone or email.',
        ],
        'benefits' => [
            'title' => 'After verifying your email, you will be able to:',
            'item1' => 'Book beauty service appointments',
            'item2' => 'Track treatment history',
            'item3' => 'Receive special offers and promotions',
            'item4' => 'Accumulate loyalty points',
        ],
    ],
];


