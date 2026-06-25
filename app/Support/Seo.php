<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Search Console / SEO verification settings.
 *
 * Stored in the (database-backed) cache so they can be set from the admin UI and
 * take effect live with no redeploy. Supports the two ownership-verification
 * methods Google offers:
 *   - meta tag : <meta name="google-site-verification" content="TOKEN">
 *   - HTML file: a googleXXXX.html file served at the site root
 * Multiple tokens/files are allowed (Google permits several owners per property).
 */
class Seo
{
    private const META_KEY = 'seo.google_verification';

    private const FILE_KEY = 'seo.google_html_files';

    /** @return array<int,string> meta-tag content tokens. */
    public static function verificationTags(): array
    {
        $tokens = (array) Cache::get(self::META_KEY, []);

        // Optional env fallback for infra-managed setups.
        if ($env = config('services.google.site_verification')) {
            $tokens[] = $env;
        }

        return array_values(array_unique(array_filter(array_map('trim', $tokens))));
    }

    /** @return array<int,string> admin-set meta tokens only (no env fallback), for the form. */
    public static function storedTags(): array
    {
        return array_values(array_filter((array) Cache::get(self::META_KEY, [])));
    }

    /** @return array<int,string> google HTML verification filenames. */
    public static function htmlFiles(): array
    {
        return array_values(array_unique(array_filter((array) Cache::get(self::FILE_KEY, []))));
    }

    /** True when the given googleXXXX.html filename is an approved verification file. */
    public static function isHtmlFile(string $filename): bool
    {
        return in_array($filename, self::htmlFiles(), true);
    }

    /** Replace the meta-tag verification tokens from a free-text textarea. */
    public static function setVerificationTags(?string $raw): void
    {
        Cache::forever(self::META_KEY, self::parseList($raw, fn ($t) => self::cleanToken($t)));
    }

    /** Replace the HTML verification filenames from a free-text textarea. */
    public static function setHtmlFiles(?string $raw): void
    {
        Cache::forever(self::FILE_KEY, self::parseList($raw, function ($t) {
            $t = trim($t);
            // Accept a pasted filename or a full URL; keep just the googleXXXX.html.
            $t = Str::afterLast($t, '/');

            return preg_match('/^google[A-Za-z0-9_]+\.html$/', $t) ? $t : null;
        }));
    }

    /** The body Google expects inside a verification HTML file. */
    public static function htmlFileBody(string $filename): string
    {
        return "google-site-verification: {$filename}";
    }

    /** Split a textarea (newlines/commas) and map+filter each entry. */
    private static function parseList(?string $raw, callable $map): array
    {
        return collect(preg_split('/[\r\n,]+/', (string) $raw))
            ->map($map)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** Strip a pasted full meta tag down to just the content token. */
    private static function cleanToken(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        // If they pasted the whole <meta ... content="X">, extract X.
        if (preg_match('/content=["\']([^"\']+)["\']/', $raw, $m)) {
            return $m[1];
        }

        return $raw;
    }
}
