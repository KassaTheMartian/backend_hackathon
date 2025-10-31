<?php

return [
    'tmn_code' => env('VNPAY_TMN_CODE', 'TDCER7JD'),
    'hash_secret' => env('VNPAY_HASH_SECRET', 'L308ZO12MJ2UQV63A61L7GDCS4VTIYS3'),
    'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'return_url' => env('VNPAY_RETURN_URL', 'http://localhost:3000/account/profile'),
    'api_url' => env('VNPAY_API_URL', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'),
];


