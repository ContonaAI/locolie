<?php

/*
 | Messaging Studio catalogue.
 |
 | Drives the channel + provider pickers in the portal. Secrets are read from
 | env via config/services.php; this file is just the non-secret catalogue of
 | what can be connected, what fields each provider needs, and how to label it.
 |
 | Every channel follows the established "demo-able now, live when keys added"
 | pattern: with no provider configured, sends are logged and counted so the
 | full flow is demoable; the moment env keys exist, delivery goes live with no
 | change to the calling code.
 */

return [

    // The brand-neutral default accent + sender used when a business has none.
    'defaults' => [
        'brand_color' => '#059669',
        'from_name' => 'locolie',
        'from_address' => env('MAIL_FROM_ADDRESS', 'hello@locolie.com'),
    ],

    'channels' => [

        'email' => [
            'label' => 'Email',
            'icon' => 'envelope',
            'blurb' => 'Branded, responsive emails - newsletters, offer drops and receipts.',
            'providers' => [
                'google' => [
                    'label' => 'Google Workspace / Gmail',
                    'blurb' => 'Connect a Google account to send from your own domain.',
                    'recommended' => true,
                    'fields' => ['client_id', 'client_secret', 'from_address'],
                    'oauth' => true,
                ],
                'smtp' => [
                    'label' => 'Custom SMTP',
                    'blurb' => 'Any SMTP host (Mailgun, Postmark, SES relay, your own server).',
                    'fields' => ['host', 'port', 'username', 'password', 'from_address'],
                ],
                'resend' => [
                    'label' => 'Resend',
                    'blurb' => 'Modern transactional email API.',
                    'fields' => ['api_key', 'from_address'],
                ],
            ],
        ],

        'sms' => [
            'label' => 'SMS',
            'icon' => 'chat',
            'blurb' => 'Text offers straight to a customer\'s phone - the highest open rate there is.',
            'providers' => [
                'twilio' => [
                    'label' => 'Twilio',
                    'blurb' => 'Global SMS leader. Per-message pricing, great deliverability.',
                    'recommended' => true,
                    'fields' => ['account_sid', 'auth_token', 'from'],
                ],
                'vonage' => [
                    'label' => 'Vonage (Nexmo)',
                    'blurb' => 'Competitive UK + EU routes, alphanumeric sender ids.',
                    'fields' => ['api_key', 'api_secret', 'from'],
                ],
                'messagebird' => [
                    'label' => 'MessageBird / Bird',
                    'blurb' => 'Strong European coverage and Omnichannel APIs.',
                    'fields' => ['access_key', 'originator'],
                ],
                'plivo' => [
                    'label' => 'Plivo',
                    'blurb' => 'Low-cost high-volume SMS.',
                    'fields' => ['auth_id', 'auth_token', 'from'],
                ],
                'aws_sns' => [
                    'label' => 'AWS SNS',
                    'blurb' => 'Pay-as-you-go SMS on AWS infrastructure.',
                    'fields' => ['key', 'secret', 'region'],
                ],
                'clicksend' => [
                    'label' => 'ClickSend',
                    'blurb' => 'UK-friendly, simple per-message billing.',
                    'fields' => ['username', 'api_key', 'from'],
                ],
            ],
        ],

        'push' => [
            'label' => 'Push',
            'icon' => 'bell',
            'blurb' => 'Instant notifications to web browsers and the iOS + Android apps.',
            'providers' => [
                'web_push' => [
                    'label' => 'Web Push (VAPID)',
                    'blurb' => 'Browser notifications. Self-generated keys, no third party.',
                    'recommended' => true,
                    'platforms' => ['web'],
                    'fields' => ['public_key', 'private_key', 'subject'],
                ],
                'fcm' => [
                    'label' => 'Firebase Cloud Messaging',
                    'blurb' => 'Android (and web) native push via Firebase.',
                    'platforms' => ['android', 'web'],
                    'fields' => ['project_id', 'service_account'],
                ],
                'apns' => [
                    'label' => 'Apple Push (APNs)',
                    'blurb' => 'Native iOS push notifications.',
                    'platforms' => ['ios'],
                    'fields' => ['key_id', 'team_id', 'bundle_id', 'auth_key'],
                ],
            ],
        ],
    ],
];
