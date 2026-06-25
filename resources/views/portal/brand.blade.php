@extends('portal.layout')
@section('title', 'Brand')

@section('content')
@include('portal._designnav')

@php
    // Reusable map-pin glyph (donut hole via even-odd) that scales with font-size.
    $pin = fn ($c, $h = '0.78em') => '<svg style="height:'.$h.';width:auto;display:inline-block;vertical-align:-0.05em;margin:0 -0.01em" viewBox="0 0 24 24" fill="'.$c.'" fill-rule="evenodd" clip-rule="evenodd" aria-hidden="true"><path d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>';

    // "locolie" wordmark with both o's rendered as pins of $pinColor; letters take $textClass.
    $wm = fn ($pinColor, $textClass = 'text-slate-900') => '<span class="font-extrabold lowercase tracking-tight '.$textClass.'">l'.$pin($pinColor).'c'.$pin($pinColor).'lie</span>';

    // A row of palette chips.
    $swatch = function ($cols) {
        $h = '';
        foreach ($cols as $c) {
            $h .= '<span class="inline-block h-6 w-6 rounded-md ring-1 ring-black/5" style="background:'.$c.'" title="'.$c.'"></span>';
        }
        return $h;
    };
@endphp

<div class="mb-8">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Brand &amp; Logos</h1>
    <p class="text-slate-500 mt-2 max-w-2xl">The name is <strong class="text-slate-700">locolie</strong> - lowercase, friendly, with the two o&rsquo;s doubling as map pins. Below: ten logo &amp; colour concepts, five app style directions, and the name rationale. Every mark is live SVG - resolution-independent and ready to drop into the app or portal.</p>
</div>

{{-- ============================ LOGO & COLOUR CONCEPTS ============================ --}}
<h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-4">01 · Ten logo &amp; colour concepts</h2>
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

    {{-- 01 Twin Pins (PRIMARY) --}}
    <div class="rounded-2xl border border-emerald-300 bg-white p-6 shadow-sm ring-1 ring-emerald-100">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:linear-gradient(135deg,#059669,#0d9488)">
                <svg width="34" height="34" viewBox="0 0 48 24" fill="#fff" fill-rule="evenodd" clip-rule="evenodd"><path d="M12 0C7.58 0 4 3.58 4 8c0 5.25 8 12 8 12s8-6.75 8-12c0-4.42-3.58-8-8-8Zm0 5.5A2.5 2.5 0 1 0 12 10.5 2.5 2.5 0 0 0 12 5.5Z"/><path d="M36 0c-4.42 0-8 3.58-8 8 0 5.25 8 12 8 12s8-6.75 8-12c0-4.42-3.58-8-8-8Zm0 5.5A2.5 2.5 0 1 0 36 10.5 2.5 2.5 0 0 0 36 5.5Z"/></svg>
            </div>
            <div>
                <div class="text-2xl">{!! $wm('#059669') !!}</div>
                <div class="text-xs text-slate-400">Twin Pins · Emerald</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">The two o&rsquo;s become map pins - two places, two people meeting locally. Reads tiny on a map and clean as a favicon. <span class="text-emerald-700 font-medium">Recommended primary.</span></p>
        <div class="flex items-center gap-1.5 mt-4">{!! $swatch(['#059669','#0d9488','#ecfdf5','#064e3b']) !!}</div>
    </div>

    {{-- 02 Loco Loop --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:linear-gradient(135deg,#06b6d4,#0891b2)">
                <svg width="30" height="30" viewBox="0 0 56 56" fill="none"><circle cx="28" cy="28" r="17" stroke="#fff" stroke-width="5" stroke-dasharray="74 26" stroke-linecap="round"/><circle cx="28" cy="28" r="6" fill="#fff"/></svg>
            </div>
            <div>
                <div class="text-2xl">{!! $wm('#0891b2') !!}</div>
                <div class="text-xs text-slate-400">Loco Loop · Cyan</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">A geo-ring orbiting a centre point - &ldquo;stay in the loop with your town.&rdquo; Retention baked into the mark.</p>
        <div class="flex items-center gap-1.5 mt-4">{!! $swatch(['#06b6d4','#0891b2','#cffafe','#164e63']) !!}</div>
    </div>

    {{-- 03 Pin Drop --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:linear-gradient(135deg,#6366f1,#4f46e5)">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="#fff" fill-rule="evenodd" clip-rule="evenodd"><path d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>
            </div>
            <div>
                <div class="text-2xl">{!! $wm('#4f46e5') !!}</div>
                <div class="text-xs text-slate-400">Pin Drop · Indigo</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">One confident pin, modern and techy. Reads as a serious software product - App-Store ready.</p>
        <div class="flex items-center gap-1.5 mt-4">{!! $swatch(['#6366f1','#4f46e5','#eef2ff','#312e81']) !!}</div>
    </div>

    {{-- 04 High Street --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:linear-gradient(135deg,#f59e0b,#b45309)">
                <svg width="32" height="32" viewBox="0 0 56 56"><path d="M14 30v12h7V34h6v8h7v-8h6v8h2V30" stroke="#fff" stroke-width="2.5" fill="none" stroke-linejoin="round"/><path d="M12 30l4-10h24l4 10" stroke="#fff" stroke-width="2.5" fill="none" stroke-linejoin="round"/></svg>
            </div>
            <div>
                <div class="text-2xl">{!! $wm('#b45309') !!}</div>
                <div class="text-xs text-slate-400">High Street · Amber</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">A row of shop rooftops - explicitly indie. Warm and inviting, with a strong &ldquo;save the high street&rdquo; PR angle.</p>
        <div class="flex items-center gap-1.5 mt-4">{!! $swatch(['#f59e0b','#b45309','#fffbeb','#78350f']) !!}</div>
    </div>

    {{-- 05 Coral Pop --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl flex items-center justify-center" style="background:#f43f5e;box-shadow:3px 3px 0 #881337">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="#fff" fill-rule="evenodd" clip-rule="evenodd"><path d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>
            </div>
            <div>
                <div class="text-2xl">{!! $wm('#e11d48') !!}</div>
                <div class="text-xs text-slate-400">Coral Pop · Rose</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">Chunky, neo-brutalist, full of energy. The most &ldquo;consumer brand&rdquo; option - youthful and very memorable.</p>
        <div class="flex items-center gap-1.5 mt-4">{!! $swatch(['#f43f5e','#e11d48','#fff1f2','#881337']) !!}</div>
    </div>

    {{-- 06 Sunset --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:linear-gradient(135deg,#f97316,#ec4899)">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="#fff" fill-rule="evenodd" clip-rule="evenodd"><path d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>
            </div>
            <div>
                <div class="text-2xl">{!! $wm('#ea580c') !!}</div>
                <div class="text-xs text-slate-400">Sunset · Orange &rarr; Pink</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">A warm gradient pin - friendly, lifestyle, Instagram-native. Feels like evenings out and weekend treats.</p>
        <div class="flex items-center gap-1.5 mt-4">{!! $swatch(['#f97316','#ec4899','#fff7ed','#831843']) !!}</div>
    </div>

    {{-- 07 Midnight --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center ring-1 ring-white/10" style="background:#0b1220">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a3e635" stroke-width="2" stroke-linejoin="round"><path d="M12 21s7-6.75 7-11a7 7 0 1 0-14 0c0 4.25 7 11 7 11Z"/><circle cx="12" cy="10" r="2.4"/></svg>
            </div>
            <div>
                <div class="text-2xl">{!! $wm('#65a30d') !!}</div>
                <div class="text-xs text-slate-400">Midnight · Lime on charcoal</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">Dark canvas, electric-lime accent. Sleek and premium - perfect for a night mode or a future paid &ldquo;pro&rdquo; tier.</p>
        <div class="flex items-center gap-1.5 mt-4">{!! $swatch(['#84cc16','#a3e635','#0b1220','#1a2740']) !!}</div>
    </div>

    {{-- 08 Berry --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:linear-gradient(135deg,#7c3aed,#a855f7)">
                <svg width="36" height="36" viewBox="0 0 48 24" fill="#fff" fill-rule="evenodd" clip-rule="evenodd"><path opacity=".55" d="M16 0c-4.42 0-8 3.58-8 8 0 5.25 8 12 8 12s8-6.75 8-12c0-4.42-3.58-8-8-8Zm0 5.5A2.5 2.5 0 1 0 16 10.5 2.5 2.5 0 0 0 16 5.5Z"/><path d="M30 0c-4.42 0-8 3.58-8 8 0 5.25 8 12 8 12s8-6.75 8-12c0-4.42-3.58-8-8-8Zm0 5.5A2.5 2.5 0 1 0 30 10.5 2.5 2.5 0 0 0 30 5.5Z"/></svg>
            </div>
            <div>
                <div class="text-2xl">{!! $wm('#7c3aed') !!}</div>
                <div class="text-xs text-slate-400">Berry · Violet</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">Two overlapping pins - community and connection. Distinctive in a sea of green-and-blue local apps.</p>
        <div class="flex items-center gap-1.5 mt-4">{!! $swatch(['#7c3aed','#a855f7','#f5f3ff','#4c1d95']) !!}</div>
    </div>

    {{-- 09 Ocean --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:linear-gradient(135deg,#2563eb,#0ea5e9)">
                <svg width="34" height="34" viewBox="0 0 56 56"><path fill="#fff" fill-rule="evenodd" clip-rule="evenodd" d="M28 6c-7 0-12.5 5.5-12.5 12.5C15.5 27 28 40 28 40s12.5-13 12.5-21.5C40.5 11.5 35 6 28 6Zm0 8.5a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/><path d="M14 44c4 3 24 3 28 0" stroke="#fff" stroke-width="2" fill="none" opacity=".6" stroke-linecap="round"/><path d="M18 49c3 2 17 2 20 0" stroke="#fff" stroke-width="2" fill="none" opacity=".33" stroke-linecap="round"/></svg>
            </div>
            <div>
                <div class="text-2xl">{!! $wm('#2563eb') !!}</div>
                <div class="text-xs text-slate-400">Ocean · Blue</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">A pin rippling over water - calm, trustworthy, dependable. Plays well for coastal and riverside towns.</p>
        <div class="flex items-center gap-1.5 mt-4">{!! $swatch(['#2563eb','#0ea5e9','#eff6ff','#1e3a8a']) !!}</div>
    </div>

    {{-- 10 Terracotta --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:linear-gradient(135deg,#ea580c,#c2410c)">
                <svg width="32" height="32" viewBox="0 0 56 56"><circle cx="39" cy="16" r="6" fill="#fff" opacity=".85"/><path fill="#fff" fill-rule="evenodd" clip-rule="evenodd" d="M24 14c-7 0-12.5 5.5-12.5 12.5C11.5 35 24 48 24 48s12.5-13 12.5-21.5C36.5 19.5 31 14 24 14Zm0 8.5a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/></svg>
            </div>
            <div>
                <div class="text-2xl">{!! $wm('#c2410c') !!}</div>
                <div class="text-xs text-slate-400">Terracotta · Warm sand</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">A pin meeting the sun - earthy, human, market-day warmth. Heritage feel without going fully retro.</p>
        <div class="flex items-center gap-1.5 mt-4">{!! $swatch(['#ea580c','#c2410c','#fef3c7','#7c2d12']) !!}</div>
    </div>

</div>

{{-- App icon row --}}
<div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white/60 p-6 shadow-sm">
    <div class="text-xs text-slate-400 mb-3">App icon - the pin-with-tick mark ("verified local") across ink, colour and light</div>
    <div class="flex items-center gap-3 flex-wrap">
        <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:#0a0a0a">
            <svg width="32" height="32" viewBox="0 0 24 24"><path d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Z" fill="#059669"/><path d="M8.4 10 11 12.6 15.7 7.2" fill="none" stroke="#fff" stroke-width="2.05" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:#059669">
            <svg width="32" height="32" viewBox="0 0 24 24"><path d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Z" fill="#fff"/><path d="M8.4 10 11 12.6 15.7 7.2" fill="none" stroke="#059669" stroke-width="2.05" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="h-14 w-14 rounded-2xl shadow-md bg-white border border-slate-200 flex items-center justify-center">
            <svg width="32" height="32" viewBox="0 0 24 24"><path d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Z" fill="#059669"/><path d="M8.4 10 11 12.6 15.7 7.2" fill="none" stroke="#fff" stroke-width="2.05" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
    </div>
</div>

{{-- ============================ THE SEAL ============================ --}}
<h2 id="seal" class="scroll-mt-24 text-xs font-semibold uppercase tracking-wider text-emerald-700 mt-12 mb-4">The seal - our trust-mark</h2>
<p class="text-slate-500 -mt-2 mb-5 max-w-2xl text-sm">A map pin says <em>local</em>; a tick inside it says <em>approved</em> - one ownable mark, the job the Red Tractor does for farms but built sharp and modern. Every verified shop gets it for the window and the till. Reusable as <code class="rounded bg-slate-100 px-1.5 py-0.5 text-[11px]">&lt;x-seal&gt;</code> in any view.</p>
<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-seal class="h-36 w-36" />
        <span class="text-xs font-semibold text-slate-500">primary · the canonical mark</span>
        <code class="text-[11px] text-slate-400">&lt;x-seal /&gt;</code>
    </div>
    <div class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200 p-6 shadow-sm" style="background:#f1f0ea">
        <x-seal variant="light" class="h-36 w-36" />
        <span class="text-xs font-semibold text-slate-500">inverse · light surfaces</span>
        <code class="text-[11px] text-slate-400">variant="light"</code>
    </div>
    <div class="flex flex-col items-center gap-3 rounded-2xl border border-slate-800 p-6 shadow-sm" style="background:#0a0a0a">
        <x-seal variant="mono" class="h-36 w-36" />
        <span class="text-xs font-semibold text-slate-400">one-colour · emboss / etch</span>
        <code class="text-[11px] text-slate-500">variant="mono"</code>
    </div>
</div>

{{-- ============================ 20 STAMP EXPLORATIONS ============================ --}}
@php
    // One pin glyph for every stamp - the SAME silhouette as the wordmark's "o"s, so the
    // logo and the trust-mark finally share a single geometry. Solid pin holds a tick.
    $pinD  = 'M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Z';
    $tickD = 'M8.4 10 11 12.6 15.7 7.2';

    // pin+tick mark, centred at (cx,cy), $h tall in viewBox units.
    $mark = function ($cx, $cy, $h, $pinC, $tickC, $sw = 2.05) use ($pinD, $tickD) {
        $s = $h / 24;
        $tx = round($cx - 12 * $s, 2);
        $ty = round($cy - 12 * $s, 2);
        return '<g transform="translate('.$tx.' '.$ty.') scale('.round($s, 4).')">'
            .'<path d="'.$pinD.'" fill="'.$pinC.'"/>'
            .'<path d="'.$tickD.'" fill="none" stroke="'.$tickC.'" stroke-width="'.$sw.'" stroke-linecap="round" stroke-linejoin="round"/>'
            .'</g>';
    };
    // pin as a clean outline (for mono / etched marks).
    $markLine = function ($cx, $cy, $h, $c, $sw = 1.6) use ($pinD, $tickD) {
        $s = $h / 24;
        $tx = round($cx - 12 * $s, 2);
        $ty = round($cy - 12 * $s, 2);
        return '<g transform="translate('.$tx.' '.$ty.') scale('.round($s, 4).')" fill="none" stroke="'.$c.'" stroke-width="'.$sw.'" stroke-linecap="round" stroke-linejoin="round">'
            .'<path d="'.$pinD.'"/><path d="'.$tickD.'"/></g>';
    };

    $E = '#059669'; $D = '#0a0a0a'; $W = '#ffffff'; $M = '#6ee7b7'; $G = '#d97706';

    // Each stamp: [name, note, card background class/style, svg].
    $stamps = [];

    // 01 Classic ring (refined current) ---------------------------------------
    $stamps[] = ['Classic ring', 'Refined version of today\'s mark - thinner rings, tighter type.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><defs><path id="s1" d="M18 60a42 42 0 0 1 84 0" fill="none"/></defs>'
        .'<circle cx="60" cy="60" r="55" fill="none" stroke="'.$E.'" stroke-width="1.4"/>'
        .'<circle cx="60" cy="60" r="47" fill="none" stroke="'.$E.'" stroke-opacity=".3" stroke-width="1"/>'
        .'<text font-family="Inter" font-size="8" font-weight="700" letter-spacing="2.4" fill="'.$D.'"><textPath href="#s1" startOffset="50%" text-anchor="middle">VERIFIED LOCAL</textPath></text>'
        .$mark(60, 50, 32, $E, $W)
        .'<text x="60" y="80" text-anchor="middle" font-family="Inter" font-size="14" font-weight="800" letter-spacing="-.5" fill="'.$D.'">locolie</text></svg>'];

    // 02 Solid disc ----------------------------------------------------------
    $stamps[] = ['Solid disc', 'Filled emerald coin, knockout mark. Boldest at small sizes.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><defs><path id="s2" d="M20 60a40 40 0 0 1 80 0" fill="none"/></defs>'
        .'<circle cx="60" cy="60" r="55" fill="'.$E.'"/>'
        .'<circle cx="60" cy="60" r="47" fill="none" stroke="'.$W.'" stroke-opacity=".4" stroke-width="1"/>'
        .'<text font-family="Inter" font-size="8" font-weight="700" letter-spacing="2.4" fill="'.$W.'"><textPath href="#s2" startOffset="50%" text-anchor="middle">VERIFIED LOCAL</textPath></text>'
        .$mark(60, 50, 32, $W, $E)
        .'<text x="60" y="80" text-anchor="middle" font-family="Inter" font-size="14" font-weight="800" letter-spacing="-.5" fill="'.$W.'">locolie</text></svg>'];

    // 03 Ink disc (current default) ------------------------------------------
    $stamps[] = ['Ink disc', 'Black coin, emerald pin. The current default - premium, app-icon ready.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><defs><path id="s3" d="M20 60a40 40 0 0 1 80 0" fill="none"/></defs>'
        .'<circle cx="60" cy="60" r="55" fill="'.$D.'"/>'
        .'<circle cx="60" cy="60" r="47" fill="none" stroke="'.$W.'" stroke-opacity=".14" stroke-width="1"/>'
        .'<text font-family="Inter" font-size="8" font-weight="700" letter-spacing="2.4" fill="'.$W.'"><textPath href="#s3" startOffset="50%" text-anchor="middle">VERIFIED LOCAL</textPath></text>'
        .$mark(60, 50, 32, $E, $W)
        .'<text x="60" y="80" text-anchor="middle" font-family="Inter" font-size="14" font-weight="800" letter-spacing="-.5" fill="'.$W.'">locolie</text></svg>'];

    // 04 Rosette (award) -----------------------------------------------------
        $ros = ''; for ($i = 0; $i < 24; $i++) { $a = deg2rad($i * 15); $rr = ($i % 2 === 0) ? 56 : 50; $ros .= round(60 + $rr * cos($a), 2).','.round(60 + $rr * sin($a), 2).' '; }
    $stamps[] = ['Rosette', 'Scalloped award edge - "best of local" energy. Feels earned.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full">'
        .'<polygon points="'.$ros.'" fill="'.$E.'"/>'
        .'<circle cx="60" cy="60" r="44" fill="'.$W.'"/>'
        .'<circle cx="60" cy="60" r="44" fill="none" stroke="'.$E.'" stroke-opacity=".25" stroke-width="1"/>'
        .$mark(60, 54, 38, $E, $W)
        .'<text x="60" y="84" text-anchor="middle" font-family="Inter" font-size="9" font-weight="800" letter-spacing="1" fill="'.$E.'">LOCAL</text></svg>'];

    // 05 Sunburst ------------------------------------------------------------
        $rays = ''; for ($i = 0; $i < 36; $i++) { $a = deg2rad($i * 10); $rays .= '<line x1="'.round(60 + 44 * cos($a), 2).'" y1="'.round(60 + 44 * sin($a), 2).'" x2="'.round(60 + 55 * cos($a), 2).'" y2="'.round(60 + 55 * sin($a), 2).'" stroke="'.$E.'" stroke-width="2.4" stroke-linecap="round"/>'; }
    $stamps[] = ['Sunburst', 'Radiating rays - a stamp that pops off the page. Energetic.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full">'.$rays
        .'<circle cx="60" cy="60" r="40" fill="'.$D.'"/>'
        .$mark(60, 54, 34, $E, $W)
        .'<text x="60" y="82" text-anchor="middle" font-family="Inter" font-size="8.5" font-weight="800" letter-spacing=".5" fill="'.$W.'">locolie</text></svg>'];

    // 06 Hexagon -------------------------------------------------------------
        $hex = ''; for ($i = 0; $i < 6; $i++) { $a = deg2rad(60 * $i - 90); $hex .= round(60 + 54 * cos($a), 2).','.round(60 + 54 * sin($a), 2).' '; }
    $stamps[] = ['Hexagon', 'Geometric, techy, modern-software. Tiles and scales cleanly.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><polygon points="'.$hex.'" fill="none" stroke="'.$E.'" stroke-width="2.4" stroke-linejoin="round"/>'
        .$mark(60, 52, 38, $E, $W)
        .'<text x="60" y="84" text-anchor="middle" font-family="Inter" font-size="11" font-weight="800" letter-spacing="-.4" fill="'.$D.'">locolie</text></svg>'];

    // 07 Shield --------------------------------------------------------------
    $stamps[] = ['Shield', 'Protection + trust. Heritage badge, "officially backed".', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><path d="M60 8 18 22v34c0 28 22 44 42 56 20-12 42-28 42-56V22Z" fill="'.$E.'"/>'
        .'<path d="M60 8 18 22v34c0 28 22 44 42 56 20-12 42-28 42-56V22Z" fill="none" stroke="'.$D.'" stroke-opacity=".15" stroke-width="1.5"/>'
        .$mark(60, 52, 40, $W, $E)
        .'<text x="60" y="92" text-anchor="middle" font-family="Inter" font-size="10.5" font-weight="800" letter-spacing="-.3" fill="'.$W.'">locolie</text></svg>'];

    // 08 Postage stamp -------------------------------------------------------
        $perf = ''; $n = 11; for ($i = 0; $i <= $n; $i++) { $p = round(14 + $i * (92 / $n), 2); $perf .= '<circle cx="'.$p.'" cy="14" r="2.4" fill="#fff"/><circle cx="'.$p.'" cy="106" r="2.4" fill="#fff"/><circle cx="14" cy="'.$p.'" r="2.4" fill="#fff"/><circle cx="106" cy="'.$p.'" r="2.4" fill="#fff"/>'; }
    $stamps[] = ['Postage stamp', 'Perforated edge - collectible, "limited" feel. Playful but premium.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><rect x="14" y="14" width="92" height="92" rx="3" fill="'.$E.'"/>'
        .'<rect x="22" y="22" width="76" height="76" rx="2" fill="none" stroke="'.$W.'" stroke-opacity=".4" stroke-width="1"/>'
        .$mark(60, 50, 34, $W, $E)
        .'<text x="60" y="84" text-anchor="middle" font-family="Inter" font-size="12" font-weight="800" letter-spacing="-.4" fill="'.$W.'">locolie</text>'
        .$perf.'</svg>'];

    // 09 Rubber stamp (rough, rotated) ---------------------------------------
    $stamps[] = ['Rubber stamp', 'Inky, hand-stamped, slightly off-kilter. Honest and indie.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><defs><path id="s9" d="M22 60a38 38 0 0 1 76 0" fill="none"/><path id="s9b" d="M24 60a36 36 0 0 0 72 0" fill="none"/></defs>'
        .'<g transform="rotate(-7 60 60)" opacity=".92">'
        .'<circle cx="60" cy="60" r="52" fill="none" stroke="'.$E.'" stroke-width="3.5"/>'
        .'<circle cx="60" cy="60" r="44" fill="none" stroke="'.$E.'" stroke-width="1.4"/>'
        .'<text font-family="Inter" font-size="8" font-weight="800" letter-spacing="3" fill="'.$E.'"><textPath href="#s9" startOffset="50%" text-anchor="middle">VERIFIED LOCAL</textPath></text>'
        .'<text font-family="Inter" font-size="7" font-weight="700" letter-spacing="2" fill="'.$E.'"><textPath href="#s9b" startOffset="50%" text-anchor="middle">LOCOLIE</textPath></text>'
        .$markLine(60, 58, 40, $E, 2.4).'</g></svg>'];

    // 10 Postmark ------------------------------------------------------------
    $stamps[] = ['Postmark', 'Date-stamp cancellation lines. Quietly official.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><defs><path id="s10" d="M24 60a36 36 0 0 1 72 0" fill="none"/><path id="s10b" d="M26 60a34 34 0 0 0 68 0" fill="none"/></defs>'
        .'<circle cx="60" cy="60" r="50" fill="none" stroke="'.$D.'" stroke-width="2"/>'
        .'<circle cx="60" cy="60" r="42" fill="none" stroke="'.$D.'" stroke-width="1"/>'
        .'<text font-family="Inter" font-size="7" font-weight="700" letter-spacing="2.5" fill="'.$D.'"><textPath href="#s10" startOffset="50%" text-anchor="middle">LOCOLIE</textPath></text>'
        .'<text font-family="Inter" font-size="6.5" font-weight="600" letter-spacing="2" fill="'.$D.'"><textPath href="#s10b" startOffset="50%" text-anchor="middle">VERIFIED LOCAL</textPath></text>'
        .'<line x1="14" y1="54" x2="44" y2="54" stroke="'.$D.'" stroke-width="2"/><line x1="14" y1="60" x2="44" y2="60" stroke="'.$D.'" stroke-width="2"/><line x1="14" y1="66" x2="44" y2="66" stroke="'.$D.'" stroke-width="2"/>'
        .'<line x1="76" y1="54" x2="106" y2="54" stroke="'.$D.'" stroke-width="2"/><line x1="76" y1="60" x2="106" y2="60" stroke="'.$D.'" stroke-width="2"/><line x1="76" y1="66" x2="106" y2="66" stroke="'.$D.'" stroke-width="2"/>'
        .$mark(60, 60, 30, $E, $W).'</svg>'];

    // 11 Banner ribbon -------------------------------------------------------
    $stamps[] = ['Banner ribbon', 'Circle + ribbon to drop the town name in. Localisable.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full">'
        .'<circle cx="60" cy="52" r="40" fill="'.$E.'"/>'
        .$mark(60, 48, 36, $W, $E)
        .'<path d="M16 84 H104 L96 104 H24 Z" fill="'.$D.'"/>'
        .'<path d="M16 84 L8 96 L24 104 Z" fill="'.$D.'" opacity=".6"/><path d="M104 84 L112 96 L96 104 Z" fill="'.$D.'" opacity=".6"/>'
        .'<text x="60" y="98" text-anchor="middle" font-family="Inter" font-size="9.5" font-weight="800" letter-spacing="1.5" fill="'.$W.'">LOCOLIE</text></svg>'];

    // 12 Cog / notched -------------------------------------------------------
        $cog = ''; for ($i = 0; $i < 48; $i++) { $a = deg2rad($i * 7.5); $rr = ($i % 2 === 0) ? 55 : 49; $cog .= round(60 + $rr * cos($a), 2).','.round(60 + $rr * sin($a), 2).' '; }
    $stamps[] = ['Notched coin', 'Fine milled edge like a minted coin. Tactile, valuable.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><polygon points="'.$cog.'" fill="'.$D.'"/>'
        .'<circle cx="60" cy="60" r="44" fill="none" stroke="'.$E.'" stroke-width="1.4"/>'
        .$mark(60, 52, 34, $E, $W)
        .'<text x="60" y="82" text-anchor="middle" font-family="Inter" font-size="9" font-weight="800" letter-spacing=".5" fill="'.$W.'">locolie</text></svg>'];

    // 13 Octagon -------------------------------------------------------------
        $oct = ''; for ($i = 0; $i < 8; $i++) { $a = deg2rad(45 * $i - 22.5); $oct .= round(60 + 54 * cos($a), 2).','.round(60 + 54 * sin($a), 2).' '; }
    $stamps[] = ['Octagon', 'Confident, sign-like. Strong silhouette from across the street.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><polygon points="'.$oct.'" fill="'.$E.'"/>'
        .'<polygon points="'.$oct.'" fill="none" stroke="'.$D.'" stroke-opacity=".12" stroke-width="6"/>'
        .$mark(60, 52, 38, $W, $E)
        .'<text x="60" y="86" text-anchor="middle" font-family="Inter" font-size="11" font-weight="800" letter-spacing="-.4" fill="'.$W.'">locolie</text></svg>'];

    // 14 Medallion (bevelled rings) ------------------------------------------
    $stamps[] = ['Medallion', 'Concentric bevels - a struck medal. Most "trophy" of the set.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full">'
        .'<circle cx="60" cy="60" r="55" fill="'.$E.'"/>'
        .'<circle cx="60" cy="60" r="49" fill="none" stroke="'.$D.'" stroke-opacity=".12" stroke-width="2"/>'
        .'<circle cx="60" cy="60" r="42" fill="'.$D.'"/>'
        .'<circle cx="60" cy="60" r="36" fill="none" stroke="'.$M.'" stroke-opacity=".5" stroke-width="1"/>'
        .$mark(60, 54, 32, $E, $W)
        .'<text x="60" y="82" text-anchor="middle" font-family="Inter" font-size="8.5" font-weight="800" letter-spacing=".5" fill="'.$W.'">locolie</text></svg>'];

    // 15 Laurel --------------------------------------------------------------
    $stamps[] = ['Laurel', 'Two laurel branches - classic "award winner". Warm, celebratory.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full">'
        .'<g fill="none" stroke="'.$E.'" stroke-width="2.2" stroke-linecap="round">'
        .'<path d="M40 98 C24 84 22 56 34 30"/><path d="M80 98 C96 84 98 56 86 30"/>'
        .'<path d="M34 44l-9-3M33 54l-9-2M34 64l-8-1M37 74l-8 0M41 84l-7 2"/>'
        .'<path d="M86 44l9-3M87 54l9-2M86 64l8-1M83 74l8 0M79 84l7 2"/></g>'
        .$mark(60, 52, 38, $E, $W)
        .'<text x="60" y="86" text-anchor="middle" font-family="Inter" font-size="10" font-weight="800" letter-spacing="-.3" fill="'.$D.'">locolie</text></svg>'];

    // 16 Wax seal ------------------------------------------------------------
        $wax = ''; for ($i = 0; $i < 40; $i++) { $a = deg2rad($i * 9); $rr = 50 + 4 * sin($i * 1.7) + 2 * cos($i * 0.9); $wax .= round(60 + $rr * cos($a), 2).','.round(60 + $rr * sin($a), 2).' '; }
    $stamps[] = ['Wax seal', 'Pressed-wax blob, embossed pin. Crafted, ceremonial.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><polygon points="'.$wax.'" fill="'.$E.'"/>'
        .'<polygon points="'.$wax.'" fill="none" stroke="'.$D.'" stroke-opacity=".1" stroke-width="3"/>'
        .'<circle cx="60" cy="60" r="40" fill="none" stroke="#047857" stroke-width="1.5"/>'
        .$markLine(60, 56, 38, '#065f46', 2.2)
        .'<text x="60" y="88" text-anchor="middle" font-family="Inter" font-size="9" font-weight="800" letter-spacing=".5" fill="#065f46">locolie</text></svg>'];

    // 17 Minimal lockup (no frame) -------------------------------------------
    $stamps[] = ['Minimal lockup', 'No frame at all - mark + word. Cleanest, most "app".', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full">'
        .$mark(60, 44, 44, $E, $W)
        .'<text x="60" y="92" text-anchor="middle" font-family="Inter" font-size="15" font-weight="800" letter-spacing="-.6" fill="'.$D.'">locolie</text>'
        .'<text x="60" y="106" text-anchor="middle" font-family="Inter" font-size="7" font-weight="700" letter-spacing="3" fill="'.$E.'">VERIFIED LOCAL</text></svg>'];

    // 18 Tag / label ---------------------------------------------------------
    $stamps[] = ['Swing tag', 'A shop tag with a hole - retail-native, hangs anywhere.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><path d="M30 24 H92 a6 6 0 0 1 6 6 V90 a6 6 0 0 1-6 6 H30 a6 6 0 0 1-6-6 V46 Z" fill="'.$E.'" transform="rotate(-4 60 60)"/>'
        .'<circle cx="40" cy="42" r="5" fill="'.$W.'" transform="rotate(-4 60 60)"/>'
        .$mark(63, 56, 34, $W, $E)
        .'<text x="62" y="88" text-anchor="middle" font-family="Inter" font-size="11" font-weight="800" letter-spacing="-.4" fill="'.$W.'" transform="rotate(-4 60 60)">locolie</text></svg>'];

    // 19 Twin pins (ties to logo) --------------------------------------------
    $stamps[] = ['Twin pins', 'The two-pin motif from the wordmark, ringed. Most on-brand.', 'bg-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><circle cx="60" cy="60" r="55" fill="none" stroke="'.$E.'" stroke-width="1.4"/>'
        .'<circle cx="60" cy="60" r="47" fill="'.$E.'" fill-opacity=".06"/>'
        .$mark(46, 54, 34, $E, $W).$mark(74, 54, 34, '#047857', $W)
        .'<text x="60" y="86" text-anchor="middle" font-family="Inter" font-size="12" font-weight="800" letter-spacing="-.5" fill="'.$D.'">locolie</text></svg>'];

    // 20 Mono / etched -------------------------------------------------------
    $stamps[] = ['Mono etch', 'Single-weight outline for one-colour print, foil and laser etch.', 'bg-[#0a0a0a] text-white',
        '<svg viewBox="0 0 120 120" class="h-full w-full"><defs><path id="s20" d="M20 60a40 40 0 0 1 80 0" fill="none"/></defs>'
        .'<circle cx="60" cy="60" r="55" fill="none" stroke="'.$W.'" stroke-width="1.2"/>'
        .'<circle cx="60" cy="60" r="48" fill="none" stroke="'.$W.'" stroke-opacity=".4" stroke-width="1"/>'
        .'<text font-family="Inter" font-size="8" font-weight="700" letter-spacing="2.4" fill="'.$W.'"><textPath href="#s20" startOffset="50%" text-anchor="middle">VERIFIED LOCAL</textPath></text>'
        .$markLine(60, 52, 32, $W, 1.6)
        .'<text x="60" y="82" text-anchor="middle" font-family="Inter" font-size="13" font-weight="800" letter-spacing="-.5" fill="'.$W.'">locolie</text></svg>'];
@endphp

<h2 id="stamps" class="scroll-mt-24 text-xs font-semibold uppercase tracking-wider text-emerald-700 mt-12 mb-2">The stamp - 20 explorations</h2>
<p class="text-slate-500 mb-5 max-w-2xl text-sm">The current seal is a bit flat, so here are twenty directions. <strong class="text-slate-700">Every one is built on the exact same map-pin glyph as the wordmark&rsquo;s &ldquo;o&rdquo;s</strong> - so whichever we pick, the logo and the stamp finally share one geometry. Pick a favourite and it becomes the canonical <code class="rounded bg-slate-100 px-1.5 py-0.5 text-[11px]">&lt;x-seal&gt;</code>.</p>
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
    @foreach ($stamps as $i => $st)
        <div class="rounded-2xl border {{ str_contains($st[2], '#0a0a0a') ? 'border-slate-800' : 'border-slate-200' }} {{ $st[2] }} p-5 shadow-sm flex flex-col items-center text-center">
            <div class="h-28 w-28">{!! $st[3] !!}</div>
            <div class="mt-3 flex items-center gap-1.5">
                <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-600 text-[9px] font-bold text-white">{{ $i + 1 }}</span>
                <span class="text-sm font-bold {{ str_contains($st[2], '#0a0a0a') ? 'text-white' : 'text-slate-900' }}">{{ $st[0] }}</span>
            </div>
            <p class="mt-1 text-[11px] leading-snug {{ str_contains($st[2], '#0a0a0a') ? 'text-slate-400' : 'text-slate-500' }}">{{ $st[1] }}</p>
        </div>
    @endforeach
</div>
<div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6">
    <div class="font-semibold text-emerald-900">Recommendation</div>
    <p class="text-sm text-emerald-900/80 mt-1">For the trust-mark, <strong>19 · Twin pins</strong> or <strong>01 · Classic ring</strong> tie most tightly to the wordmark. For the app icon and small sizes, <strong>02 · Solid disc</strong> / <strong>03 · Ink disc</strong> stay legible. Tell me the number and I&rsquo;ll wire it into <code class="rounded bg-emerald-100 px-1.5 py-0.5 text-[11px]">&lt;x-seal&gt;</code> everywhere (window decal, till card, footer).</p>
</div>

{{-- ============================ STYLE DIRECTIONS ============================ --}}
<h2 id="styles" class="scroll-mt-24 text-xs font-semibold uppercase tracking-wider text-emerald-700 mt-12 mb-4">02 · App style directions</h2>
<p class="text-slate-500 -mt-2 mb-5 max-w-2xl text-sm">Each direction is shown as a live &ldquo;offer card&rdquo; - what a deal would actually look like in the app.</p>
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

    {{-- Fresh Mint --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white overflow-hidden shadow-sm">
        <div class="p-5 bg-slate-50">
            <div class="rounded-xl bg-white border border-slate-200 shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">25% OFF</span>
                    <span class="text-xs text-slate-400">0.3 mi</span>
                </div>
                <div class="mt-3 font-bold text-slate-900">The Corner Café</div>
                <div class="text-sm text-slate-500">Any breakfast, all week</div>
                <button class="mt-3 w-full rounded-lg bg-emerald-600 text-white text-sm font-medium py-2">Redeem</button>
            </div>
        </div>
        <div class="p-5"><div class="font-semibold text-slate-900">Fresh Mint</div><p class="text-sm text-slate-500 mt-1">Light, clean, trustworthy. The safe, scalable default. <span class="text-emerald-700">#059669</span></p></div>
    </div>

    {{-- Liquid Glass --}}
    <div class="rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm">
        <div class="p-5 relative" style="background:linear-gradient(135deg,#0ea5e9,#10b981 60%,#6366f1)">
            <div class="absolute inset-0 opacity-30" style="background-image:radial-gradient(circle at 30% 20%,#fff 1px,transparent 1px);background-size:18px 18px"></div>
            <div class="relative rounded-xl p-4 border border-white/40" style="background:rgba(255,255,255,.18);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)">
                <div class="flex items-center justify-between text-white">
                    <span class="text-xs font-semibold bg-white/25 px-2 py-0.5 rounded-full backdrop-blur">25% OFF</span>
                    <span class="text-xs text-white/80">0.3 mi</span>
                </div>
                <div class="mt-3 font-bold text-white">The Corner Café</div>
                <div class="text-sm text-white/80">Any breakfast, all week</div>
                <button class="mt-3 w-full rounded-lg bg-white/90 text-slate-900 text-sm font-semibold py-2">Redeem</button>
            </div>
        </div>
        <div class="p-5"><div class="font-semibold text-slate-900">Liquid Glass</div><p class="text-sm text-slate-500 mt-1">Frosted, translucent, very &ldquo;iOS 2025&rdquo;. Premium and fresh - heaviest on polish.</p></div>
    </div>

    {{-- Premium Dark --}}
    <div class="rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm">
        <div class="p-5" style="background:#0b1220">
            <div class="rounded-xl p-4 border border-white/10" style="background:#111a2b">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-900 px-2 py-0.5 rounded-full" style="background:#fbbf24">25% OFF</span>
                    <span class="text-xs text-slate-400">0.3 mi</span>
                </div>
                <div class="mt-3 font-bold text-white">The Corner Café</div>
                <div class="text-sm text-slate-400">Any breakfast, all week</div>
                <button class="mt-3 w-full rounded-lg text-slate-900 text-sm font-semibold py-2" style="background:#fbbf24">Redeem</button>
            </div>
        </div>
        <div class="p-5"><div class="font-semibold text-slate-900">Premium Dark</div><p class="text-sm text-slate-500 mt-1">Charcoal + gold accent. Feels exclusive and high-end; great for a &ldquo;pro&rdquo; tier.</p></div>
    </div>

    {{-- Editorial / High Street --}}
    <div class="rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm">
        <div class="p-5" style="background:#faf6ef">
            <div class="rounded-xl p-4 border border-amber-900/10 bg-white">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-amber-900 border border-amber-900/30 px-2 py-0.5 rounded-full">25% OFF</span>
                    <span class="text-xs text-stone-400">0.3 mi</span>
                </div>
                <div class="mt-3 font-bold text-stone-900" style="font-family:Georgia,serif">The Corner Café</div>
                <div class="text-sm text-stone-500">Any breakfast, all week</div>
                <button class="mt-3 w-full rounded-lg text-white text-sm font-medium py-2" style="background:#7c2d12">Redeem</button>
            </div>
        </div>
        <div class="p-5"><div class="font-semibold text-slate-900">Editorial / High Street</div><p class="text-sm text-slate-500 mt-1">Warm cream, serif headers, heritage feel. Leans into &ldquo;support local&rdquo; storytelling.</p></div>
    </div>

    {{-- Bold Pop --}}
    <div class="rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm">
        <div class="p-5" style="background:linear-gradient(135deg,#fef08a,#fb923c)">
            <div class="rounded-2xl p-4 bg-white" style="box-shadow:4px 4px 0 #111">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-extrabold text-white px-2 py-0.5 rounded-full" style="background:#111">25% OFF</span>
                    <span class="text-xs text-slate-400">0.3 mi</span>
                </div>
                <div class="mt-3 font-extrabold text-slate-900">The Corner Café</div>
                <div class="text-sm text-slate-500">Any breakfast, all week</div>
                <button class="mt-3 w-full rounded-xl text-white text-sm font-bold py-2" style="background:#111">Redeem</button>
            </div>
        </div>
        <div class="p-5"><div class="font-semibold text-slate-900">Bold Pop</div><p class="text-sm text-slate-500 mt-1">Energetic, chunky, fun. Most &ldquo;consumer brand&rdquo; - memorable but harder to keep premium.</p></div>
    </div>

    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/60 p-6 shadow-sm flex flex-col justify-center">
        <div class="font-semibold text-slate-900">Recommendation</div>
        <p class="text-sm text-slate-500 mt-1">Ship <strong class="text-emerald-700">Fresh Mint</strong> as the base for trust and speed, with <strong>Liquid Glass</strong> treatments on hero/redemption moments for the premium feel. Keep <strong>Premium Dark</strong> in the back pocket for a future paid tier.</p>
    </div>
</div>

{{-- ============================ THE NAME ============================ --}}
<h2 id="names" class="scroll-mt-24 text-xs font-semibold uppercase tracking-wider text-emerald-700 mt-12 mb-4">03 · The name - locolie</h2>
<div class="grid gap-5 lg:grid-cols-3">
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6 lg:col-span-2">
        <div class="text-2xl mb-2">{!! $wm('#059669') !!}</div>
        <p class="text-sm text-emerald-900/80">Reads <em>lo-co-lie</em>. It hides &ldquo;local&rdquo; without saying it outright, the two o&rsquo;s give us a built-in pin motif, and it&rsquo;s short, lowercase and friendly. <strong>locolie.com</strong> is live. The wordmark works as plain text or with the pins dropped in - so it always degrades gracefully.</p>
    </div>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="text-sm font-semibold text-slate-900">Still to lock down</div>
        <ul class="mt-3 space-y-2 text-sm text-slate-500 list-disc pl-4">
            <li>UK IPO trademark check on the wordmark</li>
            <li>Secure <code>.co.uk</code> + key social handles</li>
            <li>Pick the primary mark (recommend <strong class="text-emerald-700">Twin Pins</strong>)</li>
            <li>Gut-check with a few Wokingham merchants</li>
        </ul>
    </div>
</div>
<div class="mt-5 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
    <div class="font-semibold text-slate-900">Alternates kept on file</div>
    <p class="text-sm text-slate-500 mt-1">If locolie ever hits a trademark wall: <strong>Patch</strong> (&ldquo;what&rsquo;s in your patch&rdquo;), <strong>TownLoop</strong> (retention in the name), and the brandable wildcard <strong>Mooch</strong> (&ldquo;have a mooch&rdquo;). Full write-up in Appendix C of the <a href="{{ route('portal.plan') }}" class="underline">business plan</a>.</p>
</div>
@endsection
