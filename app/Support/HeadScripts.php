<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Custom <head> scripts (analytics, pixels, verification snippets) managed by
 * locolie admins from the Settings page. Stored in the database-backed cache so
 * edits take effect live with no redeploy, then injected into every public page.
 *
 * Pre-seeded with the Google Analytics (gtag.js) tag so tracking works out of
 * the box; admins can edit or replace it.
 *
 * SECURITY: this injects raw HTML/JS into every visitor's page, so the editor
 * must stay behind the portal password gate - never expose it publicly.
 */
class HeadScripts
{
    private const KEY = 'scripts.head';

    /** Pre-pasted default: Google Analytics 4 (gtag.js). */
    private const DEFAULT = <<<'HTML'
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-5HM9PN3TND"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-5HM9PN3TND');
</script>
HTML;

    /** Raw HTML injected into <head>. Falls back to the default until an admin saves. */
    public static function head(): string
    {
        $v = Cache::get(self::KEY);

        return $v === null ? self::DEFAULT : (string) $v;
    }

    /** Replace the head scripts from the admin textarea (empty string is honoured). */
    public static function set(?string $raw): void
    {
        Cache::forever(self::KEY, (string) $raw);
    }

    /** True when there is something to inject. */
    public static function isActive(): bool
    {
        return trim(self::head()) !== '';
    }
}
