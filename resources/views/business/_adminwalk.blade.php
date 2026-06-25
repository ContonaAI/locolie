{{-- Animated browser mock of the retailer dashboard. Auto-cycles through the real
     admin pages (Dashboard -> Offers -> Customers -> Messaging -> Reports); the
     sidebar highlights each page as it goes, and the tabs are clickable. --}}
@php
    $pages = [
        ['Dashboard', '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>'],
        ['Offers', '<path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82Z"/><line x1="7" y1="7" x2="7.01" y2="7"/>'],
        ['Customers', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/>'],
        ['Messaging', '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/>'],
        ['Reports', '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>'],
    ];
@endphp

<div class="{{ $class ?? '' }}"
     x-data="{ i: 0, n: 5, t: null, paused: false,
               start() { this.t = setInterval(() => { if (!this.paused) this.i = (this.i + 1) % this.n; }, 3400); },
               go(k) { this.i = k; clearInterval(this.t); this.start(); } }"
     x-init="start()" @mouseenter="paused = true" @mouseleave="paused = false">

  {{-- browser chrome --}}
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
    <div class="flex items-center gap-2 border-b border-slate-200 bg-slate-100 px-4 py-2.5">
      <span class="h-3 w-3 rounded-full bg-[#ff5f57]"></span>
      <span class="h-3 w-3 rounded-full bg-[#febc2e]"></span>
      <span class="h-3 w-3 rounded-full bg-[#28c840]"></span>
      <div class="ml-3 flex flex-1 items-center gap-2 rounded-md bg-white px-3 py-1.5 text-[11px] text-slate-400 ring-1 ring-slate-200">
        <svg class="h-3 w-3 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        locolie.com/business<span x-text="i ? '/' + {{ \Illuminate\Support\Js::from(array_map(fn($p) => strtolower($p[0]), $pages)) }}[i] : ''" class="text-slate-500"></span>
      </div>
    </div>

    <div class="flex" style="min-height:430px;">
      {{-- sidebar nav (highlights the live page) --}}
      <div class="hidden w-44 shrink-0 border-r border-slate-200 bg-slate-50 p-3 sm:block">
        <div class="px-2 pb-3 text-base font-extrabold tracking-tight text-slate-900">l<span class="text-emerald-600">o</span>c<span class="text-emerald-600">o</span>lie <span class="text-[10px] font-semibold text-slate-400">Business</span></div>
        @foreach ($pages as $k => $p)
          <button type="button" @click="go({{ $k }})"
                  :class="i === {{ $k }} ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-500 hover:bg-slate-100'"
                  class="mb-1 flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $p[1] !!}</svg>
            {{ $p[0] }}
          </button>
        @endforeach
      </div>

      {{-- screen area --}}
      <div class="relative flex-1 overflow-hidden bg-white p-5">

        {{-- 0 · DASHBOARD --}}
        <div x-show="i === 0" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
          <div class="text-lg font-extrabold text-slate-900">The Corner Café</div>
          <div class="text-xs text-slate-400">Coffee · NE1 · Free plan</div>
          <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([['Active offers','3'],['Redeemed','128'],['Pending','6'],['Rating','4.9']] as $kpi)
              <div class="rounded-xl border border-slate-200 p-3">
                <div class="text-2xl font-extrabold text-slate-900">{{ $kpi[1] }}</div>
                <div class="mt-0.5 text-[9px] font-semibold uppercase tracking-widest text-slate-400">{{ $kpi[0] }}</div>
              </div>
            @endforeach
          </div>
          <div class="mt-4 rounded-xl border border-slate-200 p-4">
            <div class="mb-3 text-xs font-bold text-slate-600">Redemptions, last 8 weeks</div>
            <div class="flex h-24 items-end gap-2">
              @foreach ([35,52,40,68,60,82,74,96] as $h)
                <div class="flex-1 rounded-t bg-emerald-500/80" style="height: {{ $h }}%"></div>
              @endforeach
            </div>
          </div>
        </div>

        {{-- 1 · OFFERS --}}
        <div x-show="i === 1" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
          <div class="flex items-center justify-between">
            <div class="text-lg font-extrabold text-slate-900">Offers</div>
            <span class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white">+ New offer</span>
          </div>
          <div class="mt-4 space-y-2.5">
            @foreach ([['25% off breakfast','Live','All week','82'],['Free fringe trim','Live','New customers','37'],['2-for-1 cake','Scheduled','Weekends','0']] as $o)
              <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3.5">
                <div>
                  <div class="text-sm font-bold text-slate-800">{{ $o[0] }}</div>
                  <div class="mt-0.5 text-[11px] text-slate-400">{{ $o[2] }}</div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-[11px] text-slate-400">{{ $o[3] }} redeemed</span>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $o[1]==='Live' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $o[1] }}</span>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- 2 · CUSTOMERS --}}
        <div x-show="i === 2" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
          <div class="flex items-center justify-between">
            <div><div class="text-lg font-extrabold text-slate-900">Your customers</div><div class="text-xs text-slate-400">128 captured · yours to keep</div></div>
            <div class="flex gap-2"><span class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600">Export CSV</span><span class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white">✉ Email all</span></div>
          </div>
          <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
            <div class="grid grid-cols-[1fr_auto_auto] gap-3 border-b border-slate-200 bg-slate-50 px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400"><span>Customer</span><span>Visits</span><span>Opted in</span></div>
            @foreach ([['Sarah J.','sarah@…','6',true],['Mark T.','mark@…','2',true],['Priya K.','priya@…','9',true],['Dan W.','dan@…','1',false]] as $c)
              <div class="grid grid-cols-[1fr_auto_auto] items-center gap-3 border-b border-slate-100 px-4 py-2.5 last:border-0">
                <div class="flex items-center gap-2.5"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 text-[11px] font-bold text-emerald-700">{{ substr($c[0],0,1) }}</span><div><div class="text-[12px] font-semibold text-slate-800">{{ $c[0] }}</div><div class="text-[10px] text-slate-400">{{ $c[1] }}</div></div></div>
                <span class="text-[12px] font-bold text-slate-700">{{ $c[2] }}</span>
                <span>@if($c[3])<svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>@else<span class="text-slate-300">—</span>@endif</span>
              </div>
            @endforeach
          </div>
        </div>

        {{-- 3 · MESSAGING --}}
        <div x-show="i === 3" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
          <div class="text-lg font-extrabold text-slate-900">Messaging</div>
          <div class="mt-3 flex gap-2 text-xs font-semibold">
            <span class="rounded-full bg-emerald-600 px-3 py-1.5 text-white">Email</span>
            <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-500">SMS</span>
            <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-500">Push</span>
          </div>
          <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_auto]">
            <div class="space-y-3">
              <div><div class="text-[11px] font-semibold text-slate-500">Subject</div><div class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-[12px] text-slate-700">A little treat this week ☕</div></div>
              <div><div class="text-[11px] font-semibold text-slate-500">Message</div><div class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-[11px] leading-relaxed text-slate-500">Hi Sarah, here's 25% off any breakfast - just show this at the till. See you soon!</div></div>
              <div class="flex items-center gap-2"><span class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white">Send to 94 customers</span><span class="text-[11px] text-slate-400">Opted-in only</span></div>
            </div>
            <div class="hidden w-40 rounded-2xl border-[6px] border-slate-900 bg-white p-3 lg:block">
              <div class="text-[10px] font-bold text-slate-800">The Corner Café</div>
              <div class="mt-1 text-[9px] text-slate-400">A little treat this week ☕</div>
              <div class="mt-2 h-12 rounded bg-gradient-to-br from-[#3a2f2a] to-[#1a1410]"></div>
              <div class="mt-2 rounded bg-emerald-600 py-1 text-center text-[8px] font-bold text-white">Get 25% off</div>
            </div>
          </div>
        </div>

        {{-- 4 · REPORTS --}}
        <div x-show="i === 4" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
          <div class="text-lg font-extrabold text-slate-900">Reports</div>
          <div class="text-xs text-slate-400">Last 90 days</div>
          <div class="mt-4 grid grid-cols-3 gap-3">
            @foreach ([['Footfall driven','312'],['Revenue est.','£2.4k'],['New regulars','58']] as $kpi)
              <div class="rounded-xl border border-slate-200 p-3"><div class="text-xl font-extrabold text-slate-900">{{ $kpi[1] }}</div><div class="mt-0.5 text-[9px] font-semibold uppercase tracking-widest text-slate-400">{{ $kpi[0] }}</div></div>
            @endforeach
          </div>
          <div class="mt-4 rounded-xl border border-slate-200 p-4">
            <div class="mb-3 text-xs font-bold text-slate-600">Redemptions over time</div>
            <svg viewBox="0 0 300 90" class="h-24 w-full" preserveAspectRatio="none">
              <polyline fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="0,72 40,60 80,64 120,40 160,46 200,24 240,30 300,10"/>
              <polygon fill="#05966915" points="0,72 40,60 80,64 120,40 160,46 200,24 240,30 300,10 300,90 0,90"/>
            </svg>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- step dots (mobile, since sidebar hides) --}}
  <div class="mt-4 flex items-center justify-center gap-2 sm:hidden">
    <template x-for="k in [0,1,2,3,4]" :key="k">
      <button type="button" @click="go(k)" :aria-label="'Page ' + (k+1)" :class="i === k ? 'w-6 bg-emerald-600' : 'w-2 bg-slate-300'" class="h-2 rounded-full transition-all"></button>
    </template>
  </div>
</div>
