<?php

/*
 | Launch-market + brand identity. Single source of truth so every bit of
 | marketing copy, <meta>, JSON-LD schema and the geo-detection JS scales to a
 | new city by changing config/env only - no hunting through Blade for
 | "Newcastle". When you launch a second city, override these via env (or move
 | to a per-request resolver) and the whole public site follows.
 |
 | Per-business pages already use the business's own ->city, so those scale
 | automatically as you onboard businesses outside the launch city.
 */

$city = env('LOCOLIE_CITY', 'Newcastle');                  // short name, e.g. "Newcastle"
$cityFull = env('LOCOLIE_CITY_FULL', 'Newcastle upon Tyne'); // full/formal name for addresses + schema
$area = env('LOCOLIE_AREA', 'NE1');                        // headline district / outward code
$region = env('LOCOLIE_REGION', 'Tyne & Wear');

return [
    'brand' => env('LOCOLIE_BRAND', 'locolie'),

    /*
     | Public offers / discounts master switch. FALSE pre-launch: we list the real
     | independent businesses as a directory but show NO discount badges or
     | redemption (we have not signed those retailers up, so advertising their
     | "offer" would be misleading). The /demo page still shows the full offer +
     | redemption experience with sample data. Flip to TRUE (or extend
     | Business::showsPublicOffers() to go per-partner) once retailers opt in.
     */
    'offers_public' => filter_var(env('OFFERS_PUBLIC', false), FILTER_VALIDATE_BOOLEAN),

    'launch' => [
        'city' => $city,
        'city_full' => $cityFull,
        'area' => $area,
        'region' => $region,
        'country' => env('LOCOLIE_COUNTRY', 'GB'),

        // Composed conveniences used verbatim in copy/meta/schema.
        'place' => trim("$city $area"),          // "Newcastle NE1"
        'area_served' => trim("$cityFull, $area"), // "Newcastle upon Tyne, NE1"

        // Geo-detection: a visitor counts as "in the launch area" when their
        // outward postcode starts with this, or their town matches the city.
        'outward_prefix' => env('LOCOLIE_OUTWARD_PREFIX', 'NE'),
        'lat' => (float) env('LOCOLIE_LAT', 54.9733),
        'lng' => (float) env('LOCOLIE_LNG', -1.6139),
    ],
];
