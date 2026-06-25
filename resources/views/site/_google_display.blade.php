{{-- ===================================================================
     "How you show up on Google" - sales/marketing mockup partial.
     A self-contained illustration (no external requests, all inline SVG)
     showing a retailer how locolie makes them appear across Google.
     Reusable: just @include('site._google_display').
==================================================================== --}}
@php
    $sample = \App\Models\Business::query()->whereNotNull('photos')->where('status', 'active')->inRandomOrder()->first()
        ?? \App\Models\Business::first();

    // Concrete, guarded sample values - fall back to sensible placeholder text.
    $bizName  = $sample->name ?? 'Your Business';
    $bizCat   = $sample?->category?->name ?? 'independent shop';
    $bizCatLc = \Illuminate\Support\Str::lower($bizCat);
    $bizCity  = $sample->city ?? $llCity;
    $bizRating  = $sample && $sample->rating ? number_format($sample->rating, 1) : '4.8';
    $bizReviews = $sample && $sample->reviews_count ? $sample->reviews_count : 120;
    $bizColor   = $sample ? $sample->brandColor() : '#059669';
    $bizInits   = $sample ? $sample->brandInitials() : 'YB';
    $bizLogo    = $sample ? $sample->logoUrl() : null;
    $bizSlug    = $sample->slug ?? 'your-business';

    // 4-colour Google "G", recreated inline (no hotlinking).
    $googleG = '<svg viewBox="0 0 48 48" class="h-full w-full" aria-hidden="true">'
        .'<path fill="#4285F4" d="M44.5 20H24v8.5h11.8C34.7 33.9 30 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.3 0 6.3 1.2 8.6 3.3l6-6C39 4.5 31.9 1.5 24 1.5 11.6 1.5 1.5 11.6 1.5 24S11.6 46.5 24 46.5c11.4 0 21.6-8.3 21.6-22.5 0-1.4-.2-2.7-.6-4z"/>'
        .'<path fill="#34A853" d="M6.3 14.7l7 5.1C15.2 16 19.2 13 24 13c3.3 0 6.3 1.2 8.6 3.3l6-6C34.9 6.5 29.7 4 24 4 15.9 4 8.9 8.6 6.3 14.7z" opacity="0"/>'
        .'<path fill="#FBBC05" d="M24 46.5c5.8 0 11-2 14.6-5.3l-6.7-5.5C29.9 37.5 27.1 38.5 24 38.5c-6 0-10.7-3-12.5-7.9l-7 5.4C7 42.4 14.8 46.5 24 46.5z" opacity="0"/>'
        .'<path fill="#EA4335" d="M11.4 28.9l-7 5.4" opacity="0"/></svg>';

    // Star row for rich-snippet ratings (gold filled to the rating).
    $starRow = function ($size = 'h-3.5 w-3.5') use ($bizRating) {
        $r = (float) $bizRating;
        $out = '';
        for ($i = 1; $i <= 5; $i++) {
            $fill = $i <= round($r) ? '#fbbc04' : '#dadce0';
            $out .= '<svg class="'.$size.'" viewBox="0 0 24 24" fill="'.$fill.'" aria-hidden="true"><path d="M12 2l2.9 6.3 6.8.7-5.1 4.6 1.5 6.7L12 17.8 5.9 20.6l1.5-6.7L2.3 9l6.8-.7z"/></svg>';
        }
        return $out;
    };

    // Faux road network for the map panel (purely decorative inline SVG).
    $mapSvg = '<svg viewBox="0 0 320 200" class="h-full w-full" preserveAspectRatio="xMidYMid slice" aria-hidden="true">'
        .'<rect width="320" height="200" fill="#e8eef0"/>'
        .'<g stroke="#ffffff" stroke-width="7" fill="none" stroke-linecap="round">'
        .'<path d="M-10 60 H330"/><path d="M-10 140 H330"/>'
        .'<path d="M70 -10 V210"/><path d="M210 -10 V210"/>'
        .'<path d="M-10 25 L120 100 L200 70 L340 150" stroke-width="5"/>'
        .'</g>'
        .'<g stroke="#dfe6e8" stroke-width="2" fill="none">'
        .'<path d="M0 100 H320"/><path d="M140 0 V200"/>'
        .'</g>'
        .'<g fill="#d6e8de" opacity="0.7"><rect x="14" y="74" width="44" height="52" rx="3"/><rect x="226" y="14" width="70" height="34" rx="3"/><rect x="84" y="150" width="48" height="40" rx="3"/></g>'
        .'</svg>';

    // A reusable emerald map pin (numbered).
    $mapPin = function ($n, $cls = '') {
        return '<span class="'.$cls.' relative flex h-7 w-7 items-center justify-center">'
            .'<svg viewBox="0 0 24 24" class="absolute inset-0 h-full w-full drop-shadow" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Z"/></svg>'
            .'<span class="relative -mt-1 text-[10px] font-extrabold text-white">'.$n.'</span></span>';
    };

    // The other two rows in the local 3-pack (sample sits at the top spot).
    $packExtra = [
        ['name' => 'High Street Trading Co.', 'rating' => '4.6', 'cat' => $bizCat],
        ['name' => 'The '.$bizCity.' Collective', 'rating' => '4.5', 'cat' => $bizCat],
    ];
@endphp

<div class="mx-auto max-w-2xl text-center">
    <h2 class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald">
        <span class="inline-flex h-4 w-4 items-center justify-center">{!! $googleG !!}</span>
        Found on Google
    </h2>
    <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">How customers find you on Google.</p>
    <p class="mt-4 text-muted">locolie gives every listed business an SEO-optimised page for your area and category, rich snippets that earn star ratings, and marketing display - so independents show up on Google like the big chains do.</p>
    <p class="mt-2 text-xs font-medium text-muted/80">Illustration of how a listing can appear. Examples only.</p>
</div>

<div class="mt-14 grid items-stretch gap-6 lg:grid-cols-3">

    {{-- ============ 1. GOOGLE SEARCH RESULT SNIPPET ============ --}}
    <div class="flex flex-col rounded-card border border-hair bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted">
            <svg class="h-4 w-4 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            Google search result
        </div>
        {{-- faux search bar --}}
        <div class="mb-4 flex items-center gap-2 rounded-full border border-hair bg-[#f8f9fa] px-4 py-2">
            <span class="inline-flex h-4 w-4 shrink-0">{!! $googleG !!}</span>
            <span class="truncate text-sm text-ink/70">{{ $bizCatLc }} in {{ $bizCity }}</span>
            <svg class="ml-auto h-4 w-4 shrink-0 text-[#4285F4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </div>
        {{-- the organic result --}}
        <div class="rounded-xl bg-[#fafbfc] p-4">
            <div class="flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-full border border-hair bg-white">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>
                </span>
                <div class="leading-tight">
                    <div class="text-[13px] font-medium text-ink">locolie</div>
                    <div class="text-xs text-[#5f6368]">locolie.com &rsaquo; {{ \Illuminate\Support\Str::lower($bizCity) }}</div>
                </div>
            </div>
            <a href="/shop/{{ $bizSlug }}" class="mt-2 block text-lg font-medium leading-snug text-[#1a0dab] hover:underline">{{ $bizName }} - independent {{ $bizCatLc }} in {{ $bizCity }} | locolie</a>
            {{-- rich snippet rating row --}}
            <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[13px] text-[#5f6368]">
                <span class="font-semibold text-[#70757a]">{{ $bizRating }}</span>
                <span class="flex items-center gap-0.5">{!! $starRow() !!}</span>
                <span>{{ $bizReviews }} reviews</span>
            </div>
            <p class="mt-1.5 text-[13px] leading-relaxed text-[#4d5156]">A locally backed independent {{ $bizCatLc }} in {{ $bizCity }}. See opening hours, offers and reviews, and grab today's deal on locolie.</p>
        </div>
        <p class="mt-4 text-xs leading-relaxed text-muted">Your locolie page emits structured data, so Google can show star ratings right in the result.</p>
    </div>

    {{-- ============ 2. GOOGLE LOCAL MAP PACK (3-pack) ============ --}}
    <div class="flex flex-col rounded-card border border-hair bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted">
            <svg class="h-4 w-4 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            Google local map pack
        </div>
        {{-- faux map with 3 pins --}}
        <div class="relative overflow-hidden rounded-xl border border-hair">
            <div class="h-32 w-full">{!! $mapSvg !!}</div>
            <div class="absolute left-[18%] top-[30%]">{!! $mapPin(1) !!}</div>
            <div class="absolute left-[58%] top-[22%]">{!! $mapPin(2) !!}</div>
            <div class="absolute left-[44%] top-[62%]">{!! $mapPin(3) !!}</div>
        </div>
        {{-- the 3 stacked results, sample at the top --}}
        <div class="mt-3 divide-y divide-hair">
            {{-- top spot: the sample business --}}
            <div class="flex items-start gap-3 py-3">
                {!! $mapPin(1, 'mt-0.5 shrink-0') !!}
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-bold text-ink">{{ $bizName }}</div>
                    <div class="mt-0.5 flex items-center gap-1 text-xs text-[#5f6368]">
                        <span class="font-semibold text-[#70757a]">{{ $bizRating }}</span>
                        <span class="flex items-center gap-0.5">{!! $starRow('h-3 w-3') !!}</span>
                        <span>({{ $bizReviews }})</span>
                    </div>
                    <div class="mt-0.5 flex items-center gap-2 text-xs text-muted">
                        <span class="truncate">{{ $bizCat }} · {{ $bizCity }}</span>
                    </div>
                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-emerald-soft px-2 py-0.5 text-[10px] font-bold text-emerald">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald"></span> Open now
                    </span>
                </div>
            </div>
            @foreach ($packExtra as $i => $row)
                <div class="flex items-start gap-3 py-3">
                    {!! $mapPin($i + 2, 'mt-0.5 shrink-0 opacity-60') !!}
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold text-ink/80">{{ $row['name'] }}</div>
                        <div class="mt-0.5 flex items-center gap-1 text-xs text-[#5f6368]">
                            <span class="font-semibold text-[#70757a]">{{ $row['rating'] }}</span>
                            <span class="flex items-center gap-0.5">
                                @for ($s = 1; $s <= 5; $s++)
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="{{ $s <= round((float) $row['rating']) ? '#fbbc04' : '#dadce0' }}" aria-hidden="true"><path d="M12 2l2.9 6.3 6.8.7-5.1 4.6 1.5 6.7L12 17.8 5.9 20.6l1.5-6.7L2.3 9l6.8-.7z"/></svg>
                                @endfor
                            </span>
                        </div>
                        <div class="mt-0.5 truncate text-xs text-muted">{{ $row['cat'] }} · {{ $bizCity }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs leading-relaxed text-muted">Claim the top spot in the local "3-pack" when shoppers search nearby for what you sell.</p>
    </div>

    {{-- ============ 3. GOOGLE ADS DISPLAY BANNER ============ --}}
    <div class="flex flex-col rounded-card border border-hair bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted">
            <svg class="h-4 w-4 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 21h8M12 18v3"/></svg>
            Google Ads display
        </div>

        {{-- medium rectangle (300x250-ish) --}}
        <div class="relative overflow-hidden rounded-xl border border-hair bg-white">
            <span class="absolute left-2 top-2 z-10 rounded bg-black/55 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-white">Ad</span>
            {{-- coloured brand header --}}
            <div class="flex flex-col items-center justify-center gap-2 px-5 py-6 text-center" style="background: linear-gradient(135deg, {{ $bizColor }}, color-mix(in srgb, {{ $bizColor }} 70%, #000 30%));">
                @if ($bizLogo)
                    <img src="{{ $bizLogo }}" alt="{{ $bizName }}" class="h-12 w-12 rounded-xl bg-white object-contain p-1">
                @else
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/90 text-base font-extrabold" style="color: {{ $bizColor }};">{{ $bizInits }}</span>
                @endif
                <div class="text-sm font-extrabold leading-tight text-white drop-shadow-sm">{{ $bizName }}</div>
                <div class="text-[11px] font-medium text-white/85">{{ $bizCat }} · {{ $bizCity }}</div>
            </div>
            {{-- ad body --}}
            <div class="px-5 py-4 text-center">
                <div class="text-base font-extrabold leading-tight text-ink">20% off this week at {{ $bizName }}</div>
                <p class="mt-1 text-xs text-muted">Back your local independent. Show this on locolie in store.</p>
                <span class="mt-3 inline-flex items-center justify-center gap-1.5 rounded-full px-5 py-2 text-xs font-bold text-white" style="background: {{ $bizColor }};">
                    Visit
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </span>
            </div>
        </div>

        {{-- leaderboard strip --}}
        <div class="relative mt-3 flex items-center gap-3 overflow-hidden rounded-xl border border-hair px-3 py-2.5">
            <span class="absolute right-2 top-1.5 rounded bg-black/10 px-1 py-0.5 text-[8px] font-bold uppercase tracking-wider text-muted">Ad</span>
            @if ($bizLogo)
                <img src="{{ $bizLogo }}" alt="{{ $bizName }}" class="h-8 w-8 shrink-0 rounded-lg object-contain">
            @else
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-extrabold text-white" style="background: {{ $bizColor }};">{{ $bizInits }}</span>
            @endif
            <div class="min-w-0 flex-1">
                <div class="truncate text-xs font-bold text-ink">{{ $bizName }} · 20% off this week</div>
                <div class="truncate text-[11px] text-muted">Independent {{ $bizCatLc }} in {{ $bizCity }}</div>
            </div>
            <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-bold text-white" style="background: {{ $bizColor }};">Visit</span>
        </div>

        <p class="mt-4 text-xs leading-relaxed text-muted">Branded display and banner ads put your offers in front of locals across the web. Example creative.</p>
    </div>
</div>
