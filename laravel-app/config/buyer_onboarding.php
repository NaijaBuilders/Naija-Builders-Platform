<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Buyer onboarding platform settings
    |--------------------------------------------------------------------------
    |
    | All order tiering, first-transaction controls, fraud triggers, payment
    | routing, OTP, and dispute windows read from this file so production can
    | tune behavior without code changes.
    |
    */

    'exchange_rates' => [
        'gbp_to_ngn' => (float) env('BUYER_GBP_TO_NGN_RATE', env('CURRENCY_NGN_PER_GBP', 1900)),
    ],

    'tiers' => [
        'tier_1_upper_ngn' => (float) env('BUYER_TIER_1_UPPER_NGN', 1000000),
        'tier_2_upper_ngn' => (float) env('BUYER_TIER_2_UPPER_NGN', 5000000),
    ],

    'first_transaction' => [
        'max_order_ngn' => (float) env('BUYER_FIRST_ORDER_MAX_NGN', 500000),
        'velocity_window_hours' => (int) env('BUYER_FIRST_TX_WINDOW_HOURS', 24),
        'max_orders' => (int) env('BUYER_FIRST_TX_MAX_ORDERS', 2),
    ],

    'fraud' => [
        'hard_trigger_score' => (int) env('BUYER_FRAUD_SCORE_HARD_TRIGGER', 65),
        'medium_review_score' => (int) env('BUYER_FRAUD_SCORE_MEDIUM_TRIGGER', 40),
        'value_spike_multiplier' => (float) env('BUYER_ORDER_VALUE_SPIKE_MULTIPLIER', 3),
        'rapid_account_age_hours' => (int) env('BUYER_RAPID_ACCOUNT_AGE_HOURS', 24),
        'daily_cumulative_review_ngn' => (float) env('BUYER_DAILY_CUMULATIVE_REVIEW_NGN', 5000000),
        'order_velocity_review_count' => (int) env('BUYER_ORDER_VELOCITY_REVIEW_COUNT', 3),
        'weights' => [
            'gateway_medium' => (int) env('BUYER_FRAUD_WEIGHT_GATEWAY_MEDIUM', 20),
            'gateway_high' => (int) env('BUYER_FRAUD_WEIGHT_GATEWAY_HIGH', 70),
            'device_change' => (int) env('BUYER_FRAUD_WEIGHT_DEVICE_CHANGE', 15),
            'ip_inconsistency' => (int) env('BUYER_FRAUD_WEIGHT_IP_INCONSISTENCY', 10),
            'order_velocity' => (int) env('BUYER_FRAUD_WEIGHT_ORDER_VELOCITY', 20),
            'daily_cumulative_value' => (int) env('BUYER_FRAUD_WEIGHT_DAILY_CUMULATIVE', 15),
            'order_value_spike' => (int) env('BUYER_FRAUD_WEIGHT_VALUE_SPIKE', 20),
            'rapid_account_creation' => (int) env('BUYER_FRAUD_WEIGHT_RAPID_ACCOUNT', 10),
            'billing_name_mismatch' => (int) env('BUYER_FRAUD_WEIGHT_BILLING_NAME_MISMATCH', 25),
        ],
    ],

    'payment' => [
        'stripe_currencies' => [
            'GB' => 'GBP',
            'UK' => 'GBP',
            'US' => 'USD',
        ],
        'eu_countries' => [
            'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
            'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
            'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
        ],
    ],

    'delivery' => [
        'minimum_photos' => (int) env('BUYER_DELIVERY_MIN_PHOTOS', 2),
        'otp_ttl_minutes' => (int) env('BUYER_DELIVERY_OTP_TTL_MINUTES', 30),
    ],

    'dispute_window_hours' => (int) env('BUYER_DISPUTE_WINDOW_HOURS', 72),

    'otp' => [
        'ttl_minutes' => (int) env('BUYER_OTP_TTL_MINUTES', 15),
        'expose_in_local' => (bool) env('BUYER_OTP_EXPOSE_IN_LOCAL', true),
    ],

    'manual_review' => [
        'more_info_deadline_hours' => (int) env('BUYER_MORE_INFO_DEADLINE_HOURS', 24),
    ],

    'kyc' => [
        'document_types' => [
            'nin_slip',
            'international_passport',
            'drivers_licence',
        ],
    ],
];
