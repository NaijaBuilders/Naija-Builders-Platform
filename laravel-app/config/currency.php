<?php

return [
    'base' => 'NGN',

    /*
    |--------------------------------------------------------------------------
    | Currency exchange rates
    |--------------------------------------------------------------------------
    |
    | Product prices and backend threshold logic stay in NGN. These values
    | represent how many NGN equal one unit of the display currency.
    |
    | Example:
    | CURRENCY_NGN_PER_GBP=1850
    | CURRENCY_NGN_PER_USD=1500
    |
    */
    'ngn_per_unit' => [
        'NGN' => 1.0,
        'GBP' => env('CURRENCY_NGN_PER_GBP'),
        'USD' => env('CURRENCY_NGN_PER_USD'),
        'EUR' => env('CURRENCY_NGN_PER_EUR'),
        'CAD' => env('CURRENCY_NGN_PER_CAD'),
        'AUD' => env('CURRENCY_NGN_PER_AUD'),
        'ZAR' => env('CURRENCY_NGN_PER_ZAR'),
        'GHS' => env('CURRENCY_NGN_PER_GHS'),
        'KES' => env('CURRENCY_NGN_PER_KES'),
        'CNY' => env('CURRENCY_NGN_PER_CNY'),
        'JPY' => env('CURRENCY_NGN_PER_JPY'),
        'INR' => env('CURRENCY_NGN_PER_INR'),
        'AED' => env('CURRENCY_NGN_PER_AED'),
        'SAR' => env('CURRENCY_NGN_PER_SAR'),
        'CHF' => env('CURRENCY_NGN_PER_CHF'),
        'SEK' => env('CURRENCY_NGN_PER_SEK'),
        'NOK' => env('CURRENCY_NGN_PER_NOK'),
        'DKK' => env('CURRENCY_NGN_PER_DKK'),
        'NZD' => env('CURRENCY_NGN_PER_NZD'),
        'SGD' => env('CURRENCY_NGN_PER_SGD'),
        'HKD' => env('CURRENCY_NGN_PER_HKD'),
        'MXN' => env('CURRENCY_NGN_PER_MXN'),
        'BRL' => env('CURRENCY_NGN_PER_BRL'),
    ],
];
