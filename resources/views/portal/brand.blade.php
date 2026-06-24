@extends('portal.layout')
@section('title', 'Brand')

@section('content')
@include('portal._designnav')
<div class="mb-8">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Brand &amp; Logos</h1>
    <p class="text-slate-500 mt-2 max-w-2xl">Five logo concepts, five app style directions, and the name exploration. All logos are live SVG - resolution-independent and ready to drop into the app or portal.</p>
</div>

{{-- ============================ LOGOS ============================ --}}
<h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-4">01 · Logo concepts</h2>
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

    {{-- Concept 1: The Pin (primary) --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <svg width="56" height="56" viewBox="0 0 56 56" fill="none" aria-hidden="true">
                <defs><linearGradient id="g1" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#10b981"/><stop offset="1" stop-color="#0d9488"/></linearGradient></defs>
                <path d="M28 4C18.6 4 11 11.6 11 21c0 12 17 31 17 31s17-19 17-31C45 11.6 37.4 4 28 4Z" fill="url(#g1)"/>
                <circle cx="28" cy="21" r="7.5" fill="#fff"/>
            </svg>
            <div>
                <div class="text-2xl font-extrabold tracking-tight text-slate-900">Go<span class="text-emerald-600">Local</span></div>
                <div class="text-xs text-slate-400">The Pin</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">The classic map pin = "what's near me." Clean, instantly readable, works tiny on a map. <span class="text-slate-700 font-medium">Recommended primary.</span></p>
    </div>

    {{-- Concept 2: High Street tile --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <svg width="56" height="56" viewBox="0 0 56 56" aria-hidden="true">
                <defs><linearGradient id="g2" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#0f766e"/><stop offset="1" stop-color="#065f46"/></linearGradient></defs>
                <rect width="56" height="56" rx="14" fill="url(#g2)"/>
                <path d="M14 30v12h7V34h6v8h7v-8h6v8h2V30" stroke="#fff" stroke-width="2.5" fill="none" stroke-linejoin="round"/>
                <path d="M12 30l4-10h24l4 10" stroke="#fff" stroke-width="2.5" fill="none" stroke-linejoin="round"/>
                <circle cx="28" cy="16" r="3.5" fill="#a7f3d0"/>
            </svg>
            <div>
                <div class="text-2xl font-extrabold tracking-tight text-slate-900">High<span class="text-teal-600">street</span></div>
                <div class="text-xs text-slate-400">Rooftops</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">A row of shop rooftops in an app tile - explicitly indie / high-street, strong PR angle ("save the high street").</p>
    </div>

    {{-- Concept 3: The Loop --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <svg width="56" height="56" viewBox="0 0 56 56" fill="none" aria-hidden="true">
                <defs><linearGradient id="g3" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#34d399"/><stop offset="1" stop-color="#0891b2"/></linearGradient></defs>
                <circle cx="28" cy="28" r="18" stroke="url(#g3)" stroke-width="6" fill="none" stroke-dasharray="78 24" stroke-linecap="round"/>
                <circle cx="28" cy="28" r="6.5" fill="url(#g3)"/>
            </svg>
            <div>
                <div class="text-2xl font-extrabold tracking-tight text-slate-900">Town<span class="text-cyan-600">Loop</span></div>
                <div class="text-xs text-slate-400">The Loop</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">A geo-ring orbiting a centre point - "stay in the loop with your town." Retention baked into the mark.</p>
    </div>

    {{-- Concept 4: Price-tag pin --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <svg width="56" height="56" viewBox="0 0 56 56" fill="none" aria-hidden="true">
                <defs><linearGradient id="g4" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#059669"/><stop offset="1" stop-color="#047857"/></linearGradient></defs>
                <path d="M27 6 46 6 46 25 27 44 8 25 27 6Z" transform="rotate(45 28 25)" fill="url(#g4)"/>
                <circle cx="34" cy="18" r="3.6" fill="#fff"/>
            </svg>
            <div>
                <div class="text-2xl font-extrabold tracking-tight text-slate-900">Go<span class="text-emerald-600">Local</span></div>
                <div class="text-xs text-slate-400">Tag + Pin</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">A price tag shaped like a location marker - fuses "offer" and "nearby" into one symbol. Great as a standalone favicon.</p>
    </div>

    {{-- Concept 5: Friendly wordmark (Mooch) --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <svg width="56" height="56" viewBox="0 0 56 56" fill="none" aria-hidden="true">
                <rect width="56" height="56" rx="18" fill="#ecfdf5"/>
                <circle cx="28" cy="20" r="5.5" fill="#10b981"/>
                <path d="M28 25c-7 0-12 5-12 12v3h24v-3c0-7-5-12-12-12Z" fill="#10b981" opacity=".25"/>
            </svg>
            <div>
                <div class="text-2xl font-extrabold tracking-tight lowercase text-slate-900">mooch<span class="text-emerald-500">.</span></div>
                <div class="text-xs text-slate-400">Friendly wordmark</div>
            </div>
        </div>
        <p class="text-sm text-slate-500 mt-4">Lowercase, warm, the dot doubling as a pin. Pairs with the playful "have a mooch" name - younger, more consumer-loved.</p>
    </div>

    {{-- App icon row --}}
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/60 p-6 shadow-sm flex flex-col">
        <div class="text-xs text-slate-400 mb-3">App icon - at a glance</div>
        <div class="flex items-center gap-3 flex-wrap">
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center" style="background:linear-gradient(135deg,#10b981,#0d9488)">
                <svg width="26" height="26" viewBox="0 0 56 56"><path d="M28 4C18.6 4 11 11.6 11 21c0 12 17 31 17 31s17-19 17-31C45 11.6 37.4 4 28 4Z" fill="#fff"/><circle cx="28" cy="21" r="7.5" fill="#10b981"/></svg>
            </div>
            <div class="h-14 w-14 rounded-2xl shadow-md flex items-center justify-center text-white font-extrabold text-2xl" style="background:linear-gradient(135deg,#0f766e,#065f46)">G</div>
            <div class="h-14 w-14 rounded-2xl shadow-md bg-white flex items-center justify-center text-emerald-600 font-extrabold text-2xl border border-slate-200">G</div>
        </div>
        <p class="text-sm text-slate-500 mt-4">The same pin reads cleanly as a home-screen icon in colour, knockout and light variants.</p>
    </div>
</div>

{{-- ============================ STYLE DIRECTIONS ============================ --}}
<h2 id="styles" class="scroll-mt-24 text-xs font-semibold uppercase tracking-wider text-emerald-700 mt-12 mb-4">02 · App style directions</h2>
<p class="text-slate-500 -mt-2 mb-5 max-w-2xl text-sm">Each direction is shown as a live "offer card" - what a deal would actually look like in the app.</p>
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
        <div class="p-5"><div class="font-semibold text-slate-900">Liquid Glass</div><p class="text-sm text-slate-500 mt-1">Frosted, translucent, very "iOS 2025". Premium and fresh - heaviest on polish.</p></div>
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
        <div class="p-5"><div class="font-semibold text-slate-900">Premium Dark</div><p class="text-sm text-slate-500 mt-1">Charcoal + gold accent. Feels exclusive and high-end; great for a "pro" tier.</p></div>
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
        <div class="p-5"><div class="font-semibold text-slate-900">Editorial / High Street</div><p class="text-sm text-slate-500 mt-1">Warm cream, serif headers, heritage feel. Leans into "support local" storytelling.</p></div>
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
        <div class="p-5"><div class="font-semibold text-slate-900">Bold Pop</div><p class="text-sm text-slate-500 mt-1">Energetic, chunky, fun. Most "consumer brand" - memorable but harder to keep premium.</p></div>
    </div>

    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/60 p-6 shadow-sm flex flex-col justify-center">
        <div class="font-semibold text-slate-900">Recommendation</div>
        <p class="text-sm text-slate-500 mt-1">Ship <strong class="text-emerald-700">Fresh Mint</strong> as the base for trust and speed, with <strong>Liquid Glass</strong> treatments on hero/redemption moments for the premium feel. Keep <strong>Premium Dark</strong> in the back pocket for a future paid tier.</p>
    </div>
</div>

{{-- ============================ NAMES ============================ --}}
<h2 id="names" class="scroll-mt-24 text-xs font-semibold uppercase tracking-wider text-emerald-700 mt-12 mb-4">03 · Name exploration</h2>
<div class="grid gap-5 lg:grid-cols-3">
    @php
        $nameGroups = [
            ['Local / proximity', ['locolie - current, clear', 'Vicinity - premium', 'Neara - brandable "nearby"', 'Hereabouts - characterful', 'Patch - "what\'s in your patch"']],
            ['High street / community', ['Highstreet - strong PR angle', 'TownLoop - retention in the name', 'LocalLoop - clearer locality', 'Doorstep - hyperlocal & homely', 'Cornershop - nostalgic, British']],
            ['Discovery / brandable', ['Mooch - "have a mooch", fun', 'Yonder - calm, premium', 'Pop - "pop in", punchy', 'Tucked - curated gems']],
        ];
    @endphp
    @foreach ($nameGroups as [$group, $names])
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <div class="text-sm font-semibold text-slate-900">{{ $group }}</div>
            <ul class="mt-3 space-y-2">
                @foreach ($names as $n)
                    @php([$head, $tail] = array_pad(explode(' - ', $n, 2), 2, ''))
                    <li class="text-sm"><span class="font-medium text-slate-800">{{ $head }}</span><span class="text-slate-400"> - {{ $tail }}</span></li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
<div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-6">
    <div class="font-semibold text-emerald-900">Shortlist to test</div>
    <p class="text-sm text-emerald-800/80 mt-1">Carry three forward: a clear one (<strong>locolie</strong> / <strong>Vicinity</strong>), a community one (<strong>Patch</strong> / <strong>TownLoop</strong>), and a brandable wildcard (<strong>Mooch</strong>). Check <code>.com</code>/<code>.co.uk</code> + UK IPO trademark, then gut-check with a few Wokingham merchants. Full write-up in Appendix C of the <a href="{{ route('portal.plan') }}" class="underline">business plan</a>.</p>
</div>
@endsection
