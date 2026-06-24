<?php

/*
 | Legal / company identity used across the Terms, Privacy and Cookie pages and
 | in consent records.
 |
 | NOTE: company_number, registered_address and ico_registration are left blank
 | until you have the real, verified values (these are legal facts and must not
 | be guessed). Pages omit any line that is blank, so nothing unfinished shows.
 | Fill them here or via the matching env vars, and have a solicitor review the
 | final copy before relying on it.
 */

return [
    // The legal entity that operates locolie (the "data controller").
    'company' => env('LEGAL_COMPANY', 'Locolie Limited'),
    'trading_name' => env('LEGAL_TRADING_NAME', 'locolie'),
    'company_number' => env('LEGAL_COMPANY_NUMBER', ''),
    'registered_address' => env('LEGAL_ADDRESS', ''),
    'ico_registration' => env('LEGAL_ICO_REG', ''),

    // Contact points for legal / privacy matters.
    'contact_email' => env('LEGAL_CONTACT_EMAIL', 'hello@locolie.com'),
    'privacy_email' => env('LEGAL_PRIVACY_EMAIL', 'privacy@locolie.com'),
    'dpo_email' => env('LEGAL_DPO_EMAIL', 'privacy@locolie.com'),

    'jurisdiction' => env('LEGAL_JURISDICTION', 'England and Wales'),

    // Bump these when you materially change the wording, so we can record which
    // version each person accepted.
    'terms_version' => '2026-06-24',
    'privacy_version' => '2026-06-24',
    'cookies_version' => '2026-06-24',

    'effective_date' => '24 June 2026',
];
