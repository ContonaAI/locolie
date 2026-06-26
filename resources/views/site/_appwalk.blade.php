{{-- Animated, self-contained phone that walks through the app being USED:
     Discover -> Offer -> Scan -> Savings, on a loop. Built to MIRROR the real
     app - same dark header, same bottom tab bar (persistent), real business
     photos - and to move with the brand's motion language (smooth
     cubic-bezier(.16,.8,.3,1) easing + the map-pin sonar ping from the logo).
     Tapping the frame opens the real app at $src.
     Props: $src, $class, $dark (bool), $cards (collection|null). --}}
@include('site._appchrome')
@php
    $dark = $dark ?? false;
    // The real app wordmark: "L" + map-pin (first o) + "colie" (mirrors markFor()).
    $pinGlyph = '<svg class="dm-pin" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>';
    $awWordmark = 'L'.$pinGlyph.'colie';
    $list = (isset($cards) && $cards && count($cards)) ? collect($cards)->values() : collect();
    $primary = $list->first();
    $grid = $list->slice(1, 2)->values();

    // A branded gradient + initial always sits behind the photo, so a missing or
    // broken file degrades gracefully (onerror removes the img, revealing it) -
    // no broken-image icons, ever. Parent element must be position:relative.
    $photo = function ($biz) {
        $initial = e(mb_strtoupper(mb_substr($biz?->name ?? 'L', 0, 1)));
        $fallback = '<div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-emerald-500 to-emerald-800 font-extrabold text-white/90" style="font-size:1.6rem">'.$initial.'</div>';
        $p = $biz?->photos[0] ?? null;
        $img = $p ? '<img src="'.e($p).'" alt="'.e($biz?->name ?? '').'" loading="lazy" decoding="async" onerror="this.remove()" class="relative h-full w-full object-cover">' : '';
        return $fallback.$img;
    };

    $pName = $primary?->name ?? 'The Corner Café';
    $pCat = $primary?->category?->name ?? 'Coffee';
    $pRating = $primary ? number_format((float) $primary->rating, 1) : '4.9';
    $pOffer = $primary?->activeOffers?->first();
    $pBadge = $pOffer?->badge ?? '25% OFF';
    $pTitle = $pOffer?->title ?? 'Any breakfast, all week';

    // Deterministic decorative QR so the "scan" screen looks the part.
    $qn = 17; $qc = 100 / $qn; $qmods = '';
    for ($r = 0; $r < $qn; $r++) {
        for ($k = 0; $k < $qn; $k++) {
            if (($r < 6 && $k < 6) || ($r < 6 && $k >= $qn - 6) || ($r >= $qn - 6 && $k < 6)) continue;
            if (((($r + 1) * ($k + 3) + $r * 5 + $k * 2) % 5) < 2) {
                $qmods .= '<rect x="'.round($k * $qc, 2).'" y="'.round($r * $qc, 2).'" width="'.round($qc + .3, 2).'" height="'.round($qc + .3, 2).'"/>';
            }
        }
    }
    $qfind = function ($gr, $gk) use ($qc) {
        $x = $gk * $qc; $y = $gr * $qc; $s = 6 * $qc;
        return '<rect x="'.round($x, 2).'" y="'.round($y, 2).'" width="'.round($s, 2).'" height="'.round($s, 2).'" rx="3" fill="#0a0a0a"/>'
            .'<rect x="'.round($x + $qc, 2).'" y="'.round($y + $qc, 2).'" width="'.round($s - 2 * $qc, 2).'" height="'.round($s - 2 * $qc, 2).'" rx="2" fill="#fff"/>'
            .'<rect x="'.round($x + 2 * $qc, 2).'" y="'.round($y + 2 * $qc, 2).'" width="'.round($s - 4 * $qc, 2).'" height="'.round($s - 4 * $qc, 2).'" rx="1.5" fill="#059669"/>';
    };
    $qr = '<svg viewBox="0 0 100 100" class="h-full w-full"><g fill="#0a0a0a">'.$qmods.'</g>'.$qfind(0, 0).$qfind(0, $qn - 6).$qfind($qn - 6, 0).'</svg>';

    $mpin = '<span class="inline-block h-2 w-2 bg-emerald align-middle" style="border-radius:50% 50% 50% 0;transform:rotate(-45deg)"></span>';
    $steps = ['Discover offers near you', 'Reveal your code', 'Scan it at the till', 'Watch your savings add up'];
    // Which bottom-tab lights up on each step.
    $activeTab = [0, 0, 2, 3];
@endphp

<div class="{{ $class ?? '' }} relative"
     x-data="{ i: 0, n: 4, t: null, paused: false,
               start() { this.t = setInterval(() => { if (!this.paused) this.i = (this.i + 1) % this.n; }, 3200); },
               go(k) { this.i = k; clearInterval(this.t); this.start(); } }"
     x-init="start()" @mouseenter="paused = true" @mouseleave="paused = false">

  {{-- caption that tracks the step --}}
  <div class="absolute -top-9 left-1/2 z-20 hidden -translate-x-1/2 sm:block">
    <template x-for="(s, k) in {{ \Illuminate\Support\Js::from($steps) }}" :key="k">
      <span x-show="i === k" x-transition:enter="aw-cap" x-transition:enter-start="aw-cap-0" x-transition:enter-end="aw-cap-1"
            class="absolute left-1/2 top-0 -translate-x-1/2 whitespace-nowrap rounded-full {{ $dark ? 'bg-white/10 text-white ring-1 ring-white/15' : 'bg-ink/90 text-white' }} px-3.5 py-1.5 text-xs font-semibold shadow-lg backdrop-blur" x-text="s"></span>
    </template>
  </div>

  <a href="{{ $src }}" target="_blank" rel="noopener"
     class="group relative block overflow-hidden rounded-[2.7rem] border-[12px] {{ $dark ? 'border-[#1c1c1c] ring-1 ring-white/10' : 'border-[#111]' }} bg-[#111] shadow-[0_30px_70px_-25px_rgba(0,0,0,0.55)]"
     style="width:300px;max-width:82vw;" aria-label="Open the locolie app">
    <span class="absolute -left-[14px] top-24 z-30 h-12 w-[3px] rounded-l bg-[#222]"></span>
    <span class="absolute -right-[14px] top-28 z-30 h-16 w-[3px] rounded-r bg-[#222]"></span>
    <div class="flex items-center justify-center bg-black" style="height:24px;"><span class="h-[16px] w-20 rounded-full bg-[#0a0a0a] ring-1 ring-white/10"></span></div>

    {{-- The app body: a fixed content stage with a persistent header + bottom tab bar, exactly like the app. --}}
    <div class="relative flex flex-col bg-[#eef1f4]" style="height:600px;">
      {{-- PERSISTENT header (location · wordmark · bell) - on every screen, for consistency. --}}
      <div class="dm-head" style="padding-bottom:12px">
        <div class="dm-head-row">
          <span class="dm-loc"><svg class="dm-loc-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg><strong>{{ $llCity }}</strong><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:rgba(255,255,255,.5)"><polyline points="6 9 12 15 18 9"/></svg></span>
          <span class="dm-wm" style="font-size:19px">{!! $awWordmark !!}</span>
          <svg class="dm-head-bell" style="height:16px;width:16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
        </div>
      </div>
      <div class="relative flex-1 overflow-hidden">

        {{-- ============ SCREEN 0 · DISCOVER ============ --}}
        <div x-show="i === 0" x-transition:enter="aw-tr" x-transition:enter-start="aw-from" x-transition:enter-end="aw-to" x-transition:leave="aw-tr" x-transition:leave-start="aw-to" x-transition:leave-end="aw-leave" class="absolute inset-0 flex flex-col">
          {{-- search bar continues seamlessly from the persistent dark header (home only) --}}
          <div style="background:#0a0a0a;padding:0 14px 13px">
            <div class="dm-search" style="margin-top:0">
              <div class="dm-search-input"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="height:14px;width:14px"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg> Search shops &amp; offers</div>
              <div class="dm-search-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="height:15px;width:15px"><line x1="4" y1="6" x2="20" y2="6"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="10" y1="18" x2="14" y2="18"/></svg></div>
            </div>
          </div>
          <div class="flex-1 overflow-hidden px-3 pt-3">
            <div class="mb-2 text-[13px] font-extrabold text-ink">Featured today</div>
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-sm">
              <div class="relative h-28 bg-[#e2e8f0]">
                {!! $photo($primary) !!}
                <span class="absolute left-2 top-2 rounded-md bg-emerald px-1.5 py-0.5 text-[9px] font-extrabold text-white">{{ $pBadge }}</span>
                {{-- brand sonar ping: nudges the eye to the featured card --}}
                <span class="aw-ping absolute right-3 top-3" x-show="i===0"></span>
              </div>
              <div class="p-2.5">
                <div class="truncate text-[12px] font-bold text-ink">{{ $pName }}</div>
                <div class="mt-0.5 flex items-center gap-1 text-[9px] text-muted"><span class="text-amber-500">★</span>{{ $pRating }} · {{ $pCat }}</div>
                <div class="mt-1.5 truncate rounded-md bg-emerald-soft px-2 py-1 text-[10px] font-bold text-emerald">{{ $pTitle }}</div>
              </div>
            </div>
            <div class="mt-2.5 grid grid-cols-2 gap-2.5">
              @forelse ($grid as $g)
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                  <div class="relative h-14 bg-[#e2e8f0]">{!! $photo($g) !!}</div>
                  <div class="p-2"><div class="truncate text-[10px] font-bold text-ink">{{ $g->name }}</div><div class="mt-1 truncate rounded bg-emerald-soft px-1.5 py-0.5 text-[8px] font-bold text-emerald">{{ $g->activeOffers->first()?->badge ?? 'Offer' }}</div></div>
                </div>
              @empty
                @foreach (['Newcastle Fitness','Bones Barbers'] as $idx => $nm)
                  <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <div class="h-14 bg-gradient-to-br {{ $idx ? 'from-[#1f2937] to-[#0b1220]' : 'from-[#0e7490] to-[#155e75]' }}"></div>
                    <div class="p-2"><div class="truncate text-[10px] font-bold text-ink">{{ $nm }}</div><div class="mt-1 rounded bg-emerald-soft px-1.5 py-0.5 text-[8px] font-bold text-emerald">Free taster</div></div>
                  </div>
                @endforeach
              @endforelse
            </div>
          </div>
        </div>

        {{-- ============ SCREEN 1 · OFFER DETAIL ============ --}}
        <div x-show="i === 1" x-transition:enter="aw-tr" x-transition:enter-start="aw-from" x-transition:enter-end="aw-to" x-transition:leave="aw-tr" x-transition:leave-start="aw-to" x-transition:leave-end="aw-leave" class="absolute inset-0 flex flex-col bg-white">
          <div class="relative h-40 bg-[#e2e8f0]">
            {!! $photo($primary) !!}
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
            <div class="absolute left-3 top-3 flex h-7 w-7 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></div>
            <span class="absolute left-3 bottom-3 rounded-lg bg-emerald px-2 py-1 text-[11px] font-extrabold text-white shadow">{{ $pBadge }}</span>
          </div>
          <div class="flex-1 px-4 pt-3.5">
            <div class="text-[15px] font-extrabold text-ink">{{ $pName }}</div>
            <div class="mt-0.5 flex items-center gap-1 text-[10px] text-muted"><span class="text-amber-500">★</span> {{ $pRating }} · {{ $pCat }} · 0.3 mi</div>
            <div class="mt-3 rounded-xl border border-emerald-soft bg-emerald-soft/40 p-3">
              <div class="text-[12px] font-bold text-ink">{{ $pTitle }}</div>
              <div class="mt-0.5 text-[10px] text-muted">Show the code at the till. One per visit.</div>
            </div>
            <div class="mt-3 rounded-xl border-2 border-dashed border-emerald/40 bg-white p-3 text-center">
              <div class="text-[9px] font-bold uppercase tracking-widest text-muted">Your code</div>
              <div class="mt-1 font-mono text-xl font-extrabold tracking-[0.2em] text-emerald">LOCO-7K2</div>
            </div>
          </div>
          <div class="px-4 pb-4">
            <div class="relative rounded-xl bg-emerald py-3 text-center text-[13px] font-bold text-white shadow-lg shadow-emerald/30">
              Reveal &amp; redeem
              <span class="aw-ping aw-ping-light absolute right-4 top-1/2 -translate-y-1/2" x-show="i===1"></span>
            </div>
          </div>
        </div>

        {{-- ============ SCREEN 2 · SCAN AT TILL ============ --}}
        <div x-show="i === 2" x-transition:enter="aw-tr" x-transition:enter-start="aw-from" x-transition:enter-end="aw-to" x-transition:leave="aw-tr" x-transition:leave-start="aw-to" x-transition:leave-end="aw-leave" class="absolute inset-0 flex flex-col items-center justify-center bg-[#0a0a0a] px-6 text-center text-white">
          <div class="text-[13px] font-bold uppercase tracking-[0.18em] text-emerald-soft">Show this at the till</div>
          <div class="relative mt-5 h-44 w-44">
            {{-- brand sonar rings radiating from the code, echoing the logo pin ping --}}
            <span class="aw-ping aw-ping-xl absolute inset-0 m-auto" x-show="i===2"></span>
            <span class="aw-ping aw-ping-xl aw-ping-d2 absolute inset-0 m-auto" x-show="i===2"></span>
            <div class="relative h-44 w-44 overflow-hidden rounded-2xl bg-white p-3">
              {!! $qr !!}
              <div class="pointer-events-none absolute inset-x-0 h-0.5 bg-emerald/80 shadow-[0_0_12px_4px_rgba(5,150,105,.7)]" style="animation: scanline 2.4s cubic-bezier(.45,0,.55,1) infinite;"></div>
            </div>
          </div>
          <div class="mt-5 font-mono text-lg font-extrabold tracking-[0.2em]">LOCO-7K2</div>
          <div class="mt-1 text-[11px] text-white/50">{{ \Illuminate\Support\Str::limit($pName, 22) }} · {{ $pBadge }}</div>
        </div>

        {{-- ============ SCREEN 3 · SAVINGS ============ --}}
        <div x-show="i === 3" x-transition:enter="aw-tr" x-transition:enter-start="aw-from" x-transition:enter-end="aw-to" x-transition:leave="aw-tr" x-transition:leave-start="aw-to" x-transition:leave-end="aw-leave" class="absolute inset-0 flex flex-col bg-[#eef1f4]">
          <div class="bg-[#0a0a0a] px-4 pb-4 pt-3 text-white">
            <div class="text-[11px] font-semibold text-white/60">Your locolie</div>
            <div class="mt-1 flex items-end gap-1.5"><span class="text-3xl font-extrabold">£42.50</span><span class="mb-1 text-[11px] text-emerald-soft">saved this month</span></div>
          </div>
          <div class="flex-1 px-3 pt-3">
            <div class="mb-2 text-[12px] font-extrabold text-ink">Recently redeemed</div>
            @php
              $recent = $list->take(3)->map(fn ($b) => [$b->name, $b->activeOffers->first()?->title ?? 'Offer redeemed']);
              if ($recent->isEmpty()) $recent = collect([['The Corner Café','25% off breakfast'],['Bones Barbers','Free fringe trim'],['Newcastle Fitness','Free class taster']]);
              $amounts = ['£4.20','£8.00','£12.00'];
            @endphp
            @foreach ($recent as $idx => $row)
              <div class="mb-2 flex items-center justify-between rounded-xl bg-white p-2.5 shadow-sm">
                <div class="flex min-w-0 items-center gap-2.5">
                  <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-soft text-emerald"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                  <div class="min-w-0"><div class="truncate text-[11px] font-bold text-ink">{{ $row[0] }}</div><div class="truncate text-[9px] text-muted">{{ $row[1] }}</div></div>
                </div>
                <span class="shrink-0 text-[11px] font-extrabold text-emerald">{{ $amounts[$idx] ?? '£5.00' }}</span>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- PERSISTENT bottom tab bar - the exact app nav (icons copied from the live app). --}}
      @php $tabs = [
        ['Home','<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>'],
        ['Map','<polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/>'],
        ['Scan','<path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><line x1="3" y1="12" x2="21" y2="12"/>'],
        ['Saved','<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>'],
      ]; @endphp
      <div class="dm-tabbar">
        @foreach ($tabs as $ti => $t)
          <div class="dm-tab" :class="{{ \Illuminate\Support\Js::from($activeTab) }}[i] === {{ $ti }} && 'on'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $t[1] !!}</svg>{{ $t[0] }}
          </div>
        @endforeach
      </div>

      <div class="pointer-events-none absolute inset-x-0 bottom-0 flex justify-center pb-2 opacity-0 transition group-hover:opacity-100">
        <span class="rounded-full bg-ink/85 px-3 py-1.5 text-[10px] font-semibold text-white backdrop-blur">Open the live app ↗</span>
      </div>
    </div>
  </a>

  {{-- step dots --}}
  <div class="mt-5 flex items-center justify-center gap-2">
    <template x-for="k in [0,1,2,3]" :key="k">
      <button type="button" @click="go(k)" :aria-label="'Step ' + (k+1)"
              :class="i === k ? 'w-6 bg-emerald' : 'w-2 {{ $dark ? 'bg-white/30' : 'bg-ink/15' }}'"
              class="aw-dot h-2 rounded-full"></button>
    </template>
  </div>
</div>

@once
@push('head')
<style>
  /* Brand motion: the same smooth easing as the logo + reveals. */
  .aw-tr   { transition: opacity .55s cubic-bezier(.16,.8,.3,1), transform .55s cubic-bezier(.16,.8,.3,1); will-change: opacity, transform; }
  .aw-from { opacity: 0; transform: translateX(20px) scale(.985); }
  .aw-to   { opacity: 1; transform: translateX(0) scale(1); }
  .aw-leave{ opacity: 0; transform: translateX(-20px) scale(.985); }
  .aw-cap   { transition: opacity .45s cubic-bezier(.16,.8,.3,1), transform .45s cubic-bezier(.16,.8,.3,1); }
  .aw-cap-0 { opacity: 0; transform: translate(-50%, 6px); }
  .aw-cap-1 { opacity: 1; transform: translate(-50%, 0); }
  .aw-dot   { transition: width .45s cubic-bezier(.16,.8,.3,1), background-color .45s ease; }

  /* Map-pin sonar ping - the exact motif from the logo / hero chips. */
  .aw-ping { height: 10px; width: 10px; border-radius: 9999px; background: #059669; }
  .aw-ping::before, .aw-ping::after { content: ''; position: absolute; inset: 0; border-radius: 9999px; border: 2px solid #059669; animation: awPing 1.8s cubic-bezier(.16,.8,.3,1) infinite; }
  .aw-ping::after { animation-delay: .9s; }
  .aw-ping-light { background: #fff; }
  .aw-ping-light::before, .aw-ping-light::after { border-color: rgba(255,255,255,.9); }
  .aw-ping-xl { height: 11rem; width: 11rem; background: transparent; }
  .aw-ping-xl::before, .aw-ping-xl::after { border-color: rgba(5,150,105,.5); animation-duration: 2.4s; }
  .aw-ping-d2::before { animation-delay: 1.2s; }
  @keyframes awPing { 0% { opacity: .55; transform: scale(.55); } 80% { opacity: 0; } 100% { opacity: 0; transform: scale(2.6); } }

  @keyframes scanline { 0%,100% { top: 8%; } 50% { top: 88%; } }

  @media (prefers-reduced-motion: reduce) {
    .aw-tr, .aw-cap, .aw-dot { transition: none !important; }
    .aw-ping::before, .aw-ping::after, [style*="scanline"] { animation: none !important; }
  }
</style>
@endpush
@endonce
