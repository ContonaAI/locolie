{{-- Pixel-perfect, self-contained STATIC mock of the app home screen inside a
     phone frame. Renders identically everywhere (no iframe clipping, no network),
     with a looping "redeemed" toast so it feels live. Tapping opens the REAL
     full-screen app at $src.  Props: $src, $class, $dark (bool) --}}
@include('site._appchrome')
@php
    $dark = $dark ?? false;
    $cats = [
        ['All', '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>', true],
        ['Food', '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4Z"/>', false],
        ['Pubs', '<path d="M8 22h8M12 11v11M5 3h14l-1 8a6 6 0 0 1-12 0Z"/>', false],
        ['Retail', '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/>', false],
        ['Beauty', '<path d="m12 3 1.9 5.8H20l-5 3.6 1.9 5.8L12 14.6 6.1 18.2 8 12.4l-5-3.6h6.1Z"/>', false],
    ];
    // Real businesses (with photos) when the includer passes $cards; else gradient fallback.
    $realCards = (isset($cards) && $cards && count($cards)) ? collect($cards)->take(2) : null;
    $demoCards = [
        ['Bones Club Barbershop', 'Hairdressers', 'Free fringe trim', 'from-[#3a2f2a] to-[#1a1410]'],
        ['Newcastle Fitness Club', 'Fitness', 'Free class taster', 'from-[#1f2937] to-[#0b1220]'],
    ];
    $tabs = [
        ['Home', '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>', true],
        ['Map', '<polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/>', false],
        ['Scan', '<path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><line x1="3" y1="12" x2="21" y2="12"/>', false],
        ['Saved', '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>', false],
    ];
@endphp
<a href="{{ $src }}" target="_blank" rel="noopener"
   class="{{ $class ?? '' }} group relative block overflow-hidden rounded-[2.7rem] border-[12px] {{ $dark ? 'border-[#1c1c1c] ring-1 ring-white/10' : 'border-[#111]' }} bg-[#111] shadow-[0_30px_70px_-25px_rgba(0,0,0,0.55)]"
   style="width:300px;max-width:82vw;" aria-label="Open the locolie app">
    <span class="absolute -left-[14px] top-24 z-30 h-12 w-[3px] rounded-l bg-[#222]"></span>
    <span class="absolute -right-[14px] top-28 z-30 h-16 w-[3px] rounded-r bg-[#222]"></span>

    <div class="flex items-center justify-center bg-black" style="height:24px;"><span class="h-[16px] w-20 rounded-full bg-[#0a0a0a] ring-1 ring-white/10"></span></div>

    <div class="relative flex flex-col bg-[#eef1f4]" style="height:600px;">
        {{-- header - mirrors the real app (location · wordmark · bell + search) --}}
        @php $pinGlyph = '<svg class="dm-pin" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>'; @endphp
        <div class="dm-head">
            <div class="dm-head-row">
                <span class="dm-loc"><svg class="dm-loc-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg><strong>{{ $llCity }}</strong><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:rgba(255,255,255,.5)"><polyline points="6 9 12 15 18 9"/></svg></span>
                <span class="dm-wm" style="font-size:19px">L{!! $pinGlyph !!}colie</span>
                <svg class="dm-head-bell" style="height:16px;width:16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
            </div>
            <div class="dm-search">
                <div class="dm-search-input"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="height:14px;width:14px"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg> Search shops &amp; offers</div>
                <div class="dm-search-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="height:15px;width:15px"><line x1="4" y1="6" x2="20" y2="6"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="10" y1="18" x2="14" y2="18"/></svg></div>
            </div>
        </div>

        <div class="flex-1 overflow-hidden px-3 pt-3">
            <div class="flex gap-3 pb-3 text-[9px] font-semibold text-ink/70">
                @foreach ($cats as $c)
                    <div class="flex flex-col items-center gap-1">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $c[2] ? 'bg-ink text-white' : 'bg-white text-ink/70 shadow-sm' }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $c[1] !!}</svg></span>
                        {{ $c[0] }}
                    </div>
                @endforeach
            </div>

            <div class="mb-2 text-[13px] font-extrabold text-ink">Featured today</div>
            <div class="flex gap-2.5">
                @if ($realCards)
                    @foreach ($realCards as $b)
                        @php $o = $b->activeOffers->first(); @endphp
                        <div class="w-1/2 overflow-hidden rounded-2xl bg-white shadow-sm">
                            <div class="relative h-20 bg-[#e2e8f0]">
                                <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-emerald-500 to-emerald-800 text-xl font-extrabold text-white/90">{{ mb_strtoupper(mb_substr($b->name, 0, 1)) }}</div>
                                <img src="{{ $b->photos[0] ?? '' }}" alt="{{ $b->name }}" loading="lazy" decoding="async" onerror="this.remove()" class="relative h-full w-full object-cover">
                                @if ($o)<span class="absolute left-2 top-2 rounded-md bg-emerald px-1.5 py-0.5 text-[8px] font-extrabold text-white">{{ $o->badge }}</span>@endif
                                @if ($b->plan !== 'free')<span class="absolute right-2 top-2 rounded-md bg-black/70 px-1.5 py-0.5 text-[7px] font-bold uppercase text-white">Spon</span>@endif
                            </div>
                            <div class="p-2">
                                <div class="truncate text-[11px] font-bold text-ink">{{ $b->name }}</div>
                                <div class="mt-0.5 flex items-center gap-1 text-[9px] text-muted"><span class="text-amber-500">★</span>{{ number_format((float) $b->rating, 1) }} · {{ $b->category?->name }}</div>
                                @if ($o)<div class="mt-1.5 truncate rounded-md bg-emerald-soft px-2 py-1 text-[9px] font-bold text-emerald">{{ $o->title }}</div>@endif
                            </div>
                        </div>
                    @endforeach
                @else
                    @foreach ($demoCards as $card)
                        <div class="w-1/2 overflow-hidden rounded-2xl bg-white shadow-sm">
                            <div class="relative h-20 bg-gradient-to-br {{ $card[3] }}">
                                <span class="absolute left-2 top-2 rounded-md bg-emerald px-1.5 py-0.5 text-[8px] font-extrabold text-white">FREE</span>
                                <span class="absolute right-2 top-2 rounded-md bg-black/70 px-1.5 py-0.5 text-[7px] font-bold uppercase text-white">Sponsored</span>
                            </div>
                            <div class="p-2">
                                <div class="truncate text-[11px] font-bold text-ink">{{ $card[0] }}</div>
                                <div class="mt-0.5 flex items-center gap-1 text-[9px] text-muted"><span class="text-amber-500">★</span>5 · {{ $card[1] }}</div>
                                <div class="mt-1.5 rounded-md bg-emerald-soft px-2 py-1 text-[9px] font-bold text-emerald">{{ $card[2] }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="mt-3 overflow-hidden rounded-2xl bg-gradient-to-r from-[#2563eb] to-[#1e40af] p-3 text-white">
                <div class="text-[8px] font-bold uppercase tracking-wider text-white/60">Sponsored</div>
                <div class="text-[12px] font-extrabold">Back your high street</div>
                <div class="mt-0.5 text-[9px] text-white/70">List free and bring shoppers through your door.</div>
                <div class="mt-2 inline-block rounded-md bg-white px-2.5 py-1 text-[9px] font-bold text-ink">List free</div>
            </div>
        </div>

        <div class="dm-tabbar">
            @foreach ($tabs as $t)
                <div class="dm-tab {{ $t[2] ? 'on' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $t[1] !!}</svg>{{ $t[0] }}
                </div>
            @endforeach
        </div>

        {{-- looping "redeemed" toast --}}
        <div class="pointer-events-none absolute bottom-20 left-1/2 -translate-x-1/2" style="animation: toastpop 5s ease-in-out infinite;">
            <div class="flex items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-xl ring-1 ring-black/5">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald text-white"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                <div class="text-[10px] leading-tight"><div class="font-bold text-ink">Code redeemed</div><div class="text-muted">at the till ✓</div></div>
            </div>
        </div>

        <div class="pointer-events-none absolute inset-x-0 bottom-0 flex justify-center pb-2 opacity-0 transition group-hover:opacity-100">
            <span class="rounded-full bg-ink/85 px-3 py-1.5 text-[10px] font-semibold text-white">Open the live app ↗</span>
        </div>
    </div>
</a>
@once
@push('head')
<style>@keyframes toastpop{0%,12%{opacity:0;transform:translate(-50%,14px)}20%,80%{opacity:1;transform:translate(-50%,0)}92%,100%{opacity:0;transform:translate(-50%,14px)}}</style>
@endpush
@endonce
