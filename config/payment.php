<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment driver that will be used when
    | processing donations. You may set this to any of the drivers defined
    | in the "drivers" array below.
    |
    */

    'default_driver' => env('PAYMENT_DRIVER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Payment Processing Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure various settings for payment processing
    |
    */

    'settings' => [
        'min_amount_cents' => 100, // 1.00 eur minimum
        'max_amount_cents' => 1000000, // 10,000.00 eur maximum
        'currency' => 'EUR',
        'timeout_seconds' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Drivers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each payment driver
    |
    */

    'drivers' => [

        'mock' => [
            'name' => 'Mock Payment Service',
            'enabled' => true,
            'test_mode' => true,
        ],

        'stripe' => [
            'name' => 'Stripe',
            'enabled' => env('STRIPE_ENABLED', false),
            'public_key' => env('STRIPE_PUBLIC_KEY'),
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'test_mode' => env('STRIPE_TEST_MODE', true),
        ],

        'paypal' => [
            'name' => 'PayPal',
            'enabled' => env('PAYPAL_ENABLED', false),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'test_mode' => env('PAYPAL_TEST_MODE', true),
        ],

        'square' => [
            'name' => 'Square',
            'enabled' => env('SQUARE_ENABLED', false),
            'application_id' => env('SQUARE_APPLICATION_ID'),
            'access_token' => env('SQUARE_ACCESS_TOKEN'),
            'test_mode' => env('SQUARE_TEST_MODE', true),
        ],

    ],

];
