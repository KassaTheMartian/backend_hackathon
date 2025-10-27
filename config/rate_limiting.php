<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the rate limiting settings for your application.
    | These settings control how many requests a user can make within a given
    | time period.
    |
    */

    'limits' => [
        // General API limits
        'api' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],

        // Authentication endpoints
        'auth' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],

        // Demo endpoints
        'demo' => [
            'max_attempts' => 100,
            'decay_minutes' => 1,
        ],

        // Read operations (GET requests)
        'read' => [
            'max_attempts' => 200,
            'decay_minutes' => 1,
        ],

        // Write operations (POST, PUT, DELETE)
        'write' => [
            'max_attempts' => 30,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Keys
    |--------------------------------------------------------------------------
    |
    | Define how rate limiting keys are generated for different scenarios.
    |
    */

    'keys' => [
        'user' => 'user_id',
        'ip' => 'ip_address',
        'combined' => 'user_id_and_ip',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Headers
    |--------------------------------------------------------------------------
    |
    | Configure which headers are included in rate limit responses.
    |
    */

    'headers' => [
        'limit' => 'X-RateLimit-Limit',
        'remaining' => 'X-RateLimit-Remaining',
        'reset' => 'X-RateLimit-Reset',
        'retry_after' => 'Retry-After',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Messages
    |--------------------------------------------------------------------------
    |
    | Customize the error messages returned when rate limits are exceeded.
    |
    */

    'messages' => [
        'default' => 'Too many requests. Please try again later.',
        'auth' => 'Too many authentication attempts. Please try again later.',
        'api' => 'API rate limit exceeded. Please try again later.',
    ],
];
