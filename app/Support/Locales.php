<?php

namespace App\Support;

/**
 * The languages + region the public site targets, used for hreflang annotations
 * (in both the page <head> and the XML sitemap) and the ?hl= language switcher.
 *
 * locolie serves UK English at the canonical URL, with on-the-fly translation
 * into the community languages common in its launch areas. So the canonical /
 * default / x-default is en-GB, and the other languages are exposed as ?hl=
 * alternates that the front-end auto-translates into.
 */
class Locales
{
    /** hreflang region code for the default (canonical) version. */
    public const DEFAULT = 'en-GB';

    /**
     * Alternate languages: hreflang code => ['hl' => switcher code, 'label' => ...].
     * The ?hl value matches window.flTranslate() in the layout.
     */
    public const ALTERNATES = [
        'pl' => ['hl' => 'pl', 'label' => 'Polski'],
        'es' => ['hl' => 'es', 'label' => 'Español'],
        'fr' => ['hl' => 'fr', 'label' => 'Français'],
        'ur' => ['hl' => 'ur', 'label' => 'اردو'],
        'zh-CN' => ['hl' => 'zh', 'label' => '中文'],
    ];

    /** Valid ?hl= switcher codes (for guarding input). */
    public static function switcherCodes(): array
    {
        return collect(self::ALTERNATES)->pluck('hl')->push('en')->all();
    }

    /**
     * hreflang alternates for a canonical URL, ready for <link>/<xhtml:link>.
     * Returns [['hreflang'=>, 'href'=>], ...] including en-GB, x-default and each
     * community language as a ?hl= variant.
     *
     * @return array<int,array{hreflang:string,href:string}>
     */
    public static function alternatesFor(string $canonicalUrl): array
    {
        $sep = str_contains($canonicalUrl, '?') ? '&' : '?';

        $links = [
            ['hreflang' => self::DEFAULT, 'href' => $canonicalUrl],
            ['hreflang' => 'x-default', 'href' => $canonicalUrl],
        ];

        foreach (self::ALTERNATES as $hreflang => $meta) {
            $links[] = ['hreflang' => $hreflang, 'href' => $canonicalUrl.$sep.'hl='.$meta['hl']];
        }

        return $links;
    }
}
