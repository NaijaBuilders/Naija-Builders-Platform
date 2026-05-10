<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'kyc' => [
        'provider' => env('KYC_PROVIDER', 'fake'),
        'mode' => env('KYC_MODE', 'sandbox'),
        'face_match_threshold' => (int) env('KYC_FACE_MATCH_THRESHOLD', 80),
        'prembly' => [
            'base_url' => env('PREMBLY_BASE_URL', 'https://sandbox.prembly.example'),
            'api_key' => env('PREMBLY_API_KEY'),
        ],
        'dojah' => [
            'base_url' => env('DOJAH_BASE_URL', 'https://sandbox.dojah.example'),
            'app_id' => env('DOJAH_APP_ID'),
            'secret_key' => env('DOJAH_SECRET_KEY'),
        ],
    ],

];
