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

    // Google Maps / Places. Used by the app + marketing maps (Maps JavaScript API)
    // and server-side Places lookups. maps_id powers AdvancedMarkerElement styling;
    // defaults to Google's DEMO_MAP_ID so maps work without a cloud Map ID configured.
    'google' => [
        'maps_key' => env('GOOGLE_MAPS_API_KEY'),
        'maps_id' => env('GOOGLE_MAPS_MAP_ID', 'DEMO_MAP_ID'),
    ],

    // Web-push VAPID keypair (self-generated, no third-party account needed).
    'vapid' => [
        'public' => env('VAPID_PUBLIC_KEY'),
        'private' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT', env('APP_URL', 'mailto:hello@golocal.app')),
    ],

    // Stripe billing for paid plans (scaffolded — paste keys to go live).
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'prices' => [
            'featured' => env('STRIPE_PRICE_FEATURED'),
            'premium' => env('STRIPE_PRICE_PREMIUM'),
        ],
    ],

];
