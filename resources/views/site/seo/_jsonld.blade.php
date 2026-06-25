{{--
    Rich snippets for the programmatic SEO pages. Emits schema.org JSON-LD:
      - BreadcrumbList  (always, from $breadcrumbs = [['name'=>,'url'=>], ...])
      - ItemList of LocalBusiness (from $businesses, optional)
      - FAQPage         (from $faqs = [['q'=>,'a'=>], ...], optional)
    All values are escaped for safe embedding in a <script> JSON block.
--}}
@php
    $esc = fn ($v) => trim((string) $v);
    $graph = [];

    if (! empty($breadcrumbs)) {
        $graph[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)->values()->map(fn ($b, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $esc($b['name']),
                'item' => $b['url'],
            ])->all(),
        ];
    }

    if (! empty($businesses) && count($businesses)) {
        $graph[] = [
            '@type' => 'ItemList',
            'itemListElement' => collect($businesses)->take(20)->values()->map(function ($b, $i) use ($esc) {
                $biz = [
                    '@type' => 'LocalBusiness',
                    'name' => $esc($b->name),
                    'url' => url('/shop/'.$b->slug),
                ];
                if ($b->address) $biz['address'] = $esc($b->address);
                if ($b->phone) $biz['telephone'] = $esc($b->phone);
                if (! empty($b->photos[0])) $biz['image'] = url($b->photos[0]);
                if ($b->rating) {
                    $biz['aggregateRating'] = [
                        '@type' => 'AggregateRating',
                        'ratingValue' => (float) $b->rating,
                        'reviewCount' => (int) ($b->reviews_count ?: 1),
                    ];
                }

                return ['@type' => 'ListItem', 'position' => $i + 1, 'item' => $biz];
            })->all(),
        ];
    }

    if (! empty($faqs)) {
        $graph[] = [
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($f) => [
                '@type' => 'Question',
                'name' => $esc($f['q']),
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $esc($f['a'])],
            ])->all(),
        ];
    }

    $ld = ['@context' => 'https://schema.org', '@graph' => $graph];
@endphp
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
