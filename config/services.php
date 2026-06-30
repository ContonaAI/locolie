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

    // Mailjet (Messaging Studio email provider). Sends via the Mailjet Send API
    // v3.1 over HTTPS (no SDK needed) - used for both transactional and campaign
    // email. All optional: the email channel logs + counts until a key/secret pair
    // is present, then delivery goes live with no caller change.
    'mailjet' => [
        'key' => env('MAILJET_KEY'),
        'secret' => env('MAILJET_SECRET'),
        'from' => env('MAILJET_FROM'),
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
    // gmail_* powers "Connect to Google" in the Messaging Studio (OAuth -> Gmail send).
    'google' => [
        'maps_key' => env('GOOGLE_MAPS_API_KEY'),
        'maps_id' => env('GOOGLE_MAPS_MAP_ID', 'DEMO_MAP_ID'),
        'gmail_client_id' => env('GOOGLE_GMAIL_CLIENT_ID'),
        'gmail_client_secret' => env('GOOGLE_GMAIL_CLIENT_SECRET'),
        'gmail_refresh_token' => env('GOOGLE_GMAIL_REFRESH_TOKEN'),
        'gmail_from' => env('GOOGLE_GMAIL_FROM'),
    ],

    // ── SMS providers (Messaging Studio). All optional - the SMS channel logs
    //    and counts sends until one is configured, then delivery goes live. ──
    'twilio' => [
        'sid' => env('TWILIO_ACCOUNT_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],
    'vonage' => [
        'key' => env('VONAGE_API_KEY'),
        'secret' => env('VONAGE_API_SECRET'),
        'from' => env('VONAGE_FROM'),
    ],
    'messagebird' => [
        'key' => env('MESSAGEBIRD_ACCESS_KEY'),
        'originator' => env('MESSAGEBIRD_ORIGINATOR'),
    ],
    'plivo' => [
        'auth_id' => env('PLIVO_AUTH_ID'),
        'auth_token' => env('PLIVO_AUTH_TOKEN'),
        'from' => env('PLIVO_FROM'),
    ],
    'sns' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'eu-west-1'),
    ],
    'clicksend' => [
        'username' => env('CLICKSEND_USERNAME'),
        'key' => env('CLICKSEND_API_KEY'),
        'from' => env('CLICKSEND_FROM'),
    ],

    // ── Native push for the future iOS / Android apps ──
    // FCM is the chosen push client (Android + web). Two ways to authenticate:
    //   - FCM HTTP v1 (preferred): project_id + a service-account JSON (path OR
    //     inline JSON). We mint a short-lived OAuth token from it server-side.
    //   - Legacy HTTP API (fallback): a single FCM server key.
    // All optional - the push channel logs + counts until creds exist.
    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID'),
        'credentials' => env('FCM_CREDENTIALS'), // path to (or inline) service-account JSON
        'server_key' => env('FCM_SERVER_KEY'),   // legacy HTTP API key (fallback)
    ],
    'apns' => [
        'key_id' => env('APNS_KEY_ID'),
        'team_id' => env('APNS_TEAM_ID'),
        'bundle_id' => env('APNS_BUNDLE_ID'),
        'auth_key' => env('APNS_AUTH_KEY'), // path to .p8
    ],

    // Web-push VAPID keypair (self-generated, no third-party account needed).
    'vapid' => [
        'public' => env('VAPID_PUBLIC_KEY'),
        'private' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT', env('APP_URL', 'mailto:hello@locolie.com')),
    ],

    // ── Social media control centre (/portal/social). All optional - posts can
    //    be drafted + scheduled today; direct API publishing goes live once each
    //    developer app is approved and its client id/secret + token are added.
    //    Each block holds the OAuth client credentials read by the connect flow;
    //    the per-account access token is captured at connect time and stored
    //    (encrypted) on the social_accounts row, never here. ──
    'social' => [
        'facebook' => [
            'client_id' => env('FB_APP_ID'),
            'client_secret' => env('FB_APP_SECRET'),
            'redirect' => env('FB_REDIRECT_URI'),
        ],
        'instagram' => [
            // Instagram publishing runs on the Facebook Graph app; these default
            // to the Facebook app credentials but can be overridden.
            'client_id' => env('INSTAGRAM_APP_ID', env('FB_APP_ID')),
            'client_secret' => env('INSTAGRAM_APP_SECRET', env('FB_APP_SECRET')),
            'redirect' => env('INSTAGRAM_REDIRECT_URI'),
        ],
        'tiktok' => [
            'client_id' => env('TIKTOK_CLIENT_KEY'),
            'client_secret' => env('TIKTOK_CLIENT_SECRET'),
            'redirect' => env('TIKTOK_REDIRECT_URI'),
        ],
        'linkedin' => [
            'client_id' => env('LINKEDIN_CLIENT_ID'),
            'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
            'redirect' => env('LINKEDIN_REDIRECT_URI'),
        ],
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
