<?php

/*
 | Social handles / profile URLs and Trustpilot review links used across the
 | public site (footer social row, Contact page, etc.).
 |
 | These are simple placeholders for now and are easy to edit later. A CMS or
 | admin screen may make them editable down the line - keep this a plain config
 | array. Leave a value blank ('') to hide that icon/link in the UI.
 */

return [
    // Social profiles. Blank ('') values are hidden wherever they are rendered.
    'facebook' => env('SOCIAL_FACEBOOK', 'https://facebook.com/locolie'),
    'instagram' => env('SOCIAL_INSTAGRAM', 'https://instagram.com/locolie'),
    'tiktok' => env('SOCIAL_TIKTOK', 'https://tiktok.com/@locolie'),
    'linkedin' => env('SOCIAL_LINKEDIN', 'https://linkedin.com/company/locolie'),

    // Optional extras - left blank by default, ready to switch on later.
    'twitter' => env('SOCIAL_TWITTER', ''),   // X / Twitter
    'youtube' => env('SOCIAL_YOUTUBE', ''),

    // Trustpilot. The public review/profile URL plus the Business Unit ID used
    // by Trustpilot's widgets if we wire those up later.
    'trustpilot_url' => env('SOCIAL_TRUSTPILOT_URL', 'https://uk.trustpilot.com/review/locolie.com'),
    'trustpilot_business_unit_id' => env('SOCIAL_TRUSTPILOT_BUID', ''),
];
