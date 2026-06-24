<?php

return [

    /*
    | Shared secret that authorises local -> production data sync requests.
    | Set SYNC_TOKEN in .env on BOTH ends (local + server). Never commit it —
    | this endpoint mutates production data, so the token is its only guard.
    */
    'token' => env('SYNC_TOKEN'),

    /*
    | Where `php artisan sync:push` sends data to. Defaults to production.
    | Override with SYNC_TARGET in .env (e.g. http://localhost:8000 to test).
    */
    'target' => rtrim(env('SYNC_TARGET', 'https://locolie.com'), '/'),

];
