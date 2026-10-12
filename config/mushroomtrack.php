<?php

/*
| MushroomTrack / MushFungAI settings. Read with config('mushroomtrack.key').
*/
return [

    // Reasons a worker can pick when reporting lost bags (key => label)
    'loss_reasons' => [
        'contamination' => 'Contamination (mold / bacteria)',
        'pests'         => 'Pests (flies, mites)',
        'dryness'       => 'Too dry',
        'overwatering'  => 'Overwatering / rot',
        'damage'        => 'Physical damage',
        'other'         => 'Other',
    ],

    // Extra key used by the system when an admin edits the lost-bags number by hand
    'admin_adjustment_reason' => 'admin_adjustment',

    /*
    | AI review of the daily-check photo (Claude vision). Needs ANTHROPIC_API_KEY.
    | The model only ever makes a result worse (healthy -> attention / contaminated), never better.
    */
    'ai' => [
        'enabled'           => (bool) env('AI_ENABLED', true),
        'api_key'           => env('ANTHROPIC_API_KEY'),
        'gemini_api_key'    => env('GEMINI_API_KEY'),
        'model'             => env('AI_MODEL', 'gemini-1.5-flash'), // or claude-3-5-sonnet-20241022

        'timeout'           => (int) env('AI_TIMEOUT', 20),             // seconds; keep under PHP's 30s max_execution_time
        'auto_harvest_date' => (bool) env('AI_AUTO_HARVEST_DATE', true),
        'min_date_shift'    => (int) env('AI_MIN_DATE_SHIFT', 2),       // only move the harvest date when the estimate differs by this many days
    ],

    // How customers can pay. Cash on pickup / delivery only. Online payments removed.
    'payment_methods' => [
        'cash' => 'Cash on Pickup / Delivery',
    ],

    // Maximum number of harvest flushes before a bag should be disposed (system default; can be overridden per batch)
    'max_flushes' => (int) env('MAX_FLUSHES', 5),

    'paymongo' => [
        'secret_key'     => env('PAYMONGO_SECRET_KEY'),      // sk_test_... or sk_live_...
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),  // whsk_...
        'api_version'    => env('PAYMONGO_API_VERSION', 'v1'),
        'timeout'        => 20,
        'min_amount'     => (float) env('PAYMONGO_MIN_AMOUNT', 20),   // online payments below this are refused by PayMongo
    ],

    // Delivery fee is charged once per checkout. free_over = 0 means the fee is never waived.
    'delivery' => [
        'fee'       => (float) env('DELIVERY_FEE', 50),
        'free_over' => (float) env('DELIVERY_FREE_OVER', 0),
    ],

    // SMS to Philippine numbers. Drivers: semaphore (real), log (writes to storage/logs/laravel.log), null (off)
    'sms' => [
        'driver'        => env('SMS_DRIVER', 'log'),
        'semaphore_key' => env('SEMAPHORE_API_KEY'),
        'sender'        => env('SEMAPHORE_SENDER_NAME'),
    ],

    // false = emails/SMS/AI run during the request (fine for XAMPP). true = use `php artisan queue:work`.
    'notify' => [
        'queue' => (bool) env('NOTIFY_QUEUE', false),
    ],
];
