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
        'mode' => env('KYC_MODE', 'local'),
        'face_match_threshold' => (int) env('KYC_FACE_MATCH_THRESHOLD', 80),
        'prembly' => [
            'base_url' => env('PREMBLY_BASE_URL', 'https://api.prembly.com'),
            'api_key' => env('PREMBLY_API_KEY'),
            'app_id' => env('PREMBLY_APP_ID'),
            'webhook_secret' => env('PREMBLY_WEBHOOK_SECRET'),
            'timeout' => (int) env('PREMBLY_TIMEOUT_SECONDS', 30),
            // Prembly docs currently show customer_name in the bank comparison sample,
            // but the body table says customer. Keep it deploy-configurable.
            'bank_customer_name_field' => env('PREMBLY_BANK_CUSTOMER_NAME_FIELD', 'customer_name'),
            'endpoints' => [
                'cac' => env('PREMBLY_ENDPOINT_CAC', '/verification/cac'),
                'bvn_basic' => env('PREMBLY_ENDPOINT_BVN_BASIC', '/verification/bvn_validation'),
                'nin' => env('PREMBLY_ENDPOINT_NIN', '/verification/vnin'),
                'bank_basic' => env('PREMBLY_ENDPOINT_BANK_BASIC', '/verification/bank_account/basic'),
                'bank_comparison' => env('PREMBLY_ENDPOINT_BANK_COMPARISON', '/verification/bank_account/comparism'),
                'email_company_search' => env('PREMBLY_ENDPOINT_EMAIL_COMPANY_SEARCH', '/identitypass/verification/global/company/search_with_email'),
                'document_with_face' => env('PREMBLY_ENDPOINT_DOCUMENT_WITH_FACE', '/verification/document_w_face'),
                // Confirm the required AML payload with Prembly before enabling in production.
                'aml_pep' => env('PREMBLY_ENDPOINT_AML_PEP'),
            ],
        ],
        'dojah' => [
            'base_url' => env('DOJAH_BASE_URL', 'https://sandbox.dojah.example'),
            'app_id' => env('DOJAH_APP_ID'),
            'secret_key' => env('DOJAH_SECRET_KEY'),
        ],
    ],

];
