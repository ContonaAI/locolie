@extends('portal.layout')
@section('title', 'Admin CRM')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div x-data="{ tab: 'businesses' }">

  <div class="mb-7">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Admin CRM</h1>
    <p class="text-slate-500 mt-2 max-w-2xl">Onboard businesses, manage paid tiers, prospect new leads from Google Maps, and run email / push campaigns - all wired to the live app &amp; website.</p>
  </div>

  @if (session('status'))
    <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('status') }}</div>
  @endif

  {{-- KPIs --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
      $kpis = [
        ['Live businesses', $stats['onboarded'], 'text-emerald-600'],
        ['Leads to onboard', $stats['leads'], 'text-amber-600'],
        ['Paying', $stats['paid'], 'text-slate-900'],
        ['Active offers', $stats['offers'], 'text-slate-900'],
        ['Redeemed', $stats['redeemed'], 'text-slate-900'],
        ['Push subscribers', $stats['push_subs'], 'text-slate-900'],
        ['Featured plan', $planCounts['featured'], 'text-emerald-600'],
        ['Premium plan', $planCounts['premium'], 'text-amber-600'],
      ];
    @endphp
    @foreach ($kpis as $k)
      <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <div class="text-3xl font-extrabold {{ $k[2] }}">{{ $k[1] }}</div>
        <div class="mono text-[11px] uppercase tracking-widest text-slate-400 mt-1">{{ $k[0] }}</div>
      </div>
    @endforeach
  </div>

  {{-- Tabs --}}
  <div class="flex gap-1 border-b border-slate-200 mb-6">
    @foreach (['businesses' => 'Businesses', 'prospect' => 'Prospecting', 'campaigns' => 'Campaigns', 'redemptions' => 'Redemptions'] as $key => $label)
      <button @click="tab='{{ $key }}'" :class="tab==='{{ $key }}' ? 'border-emerald-600 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'"
        class="px-4 py-2.5 -mb-px border-b-2 font-semibold text-sm transition">{{ $label }}</button>
    @endforeach
  </div>

  {{-- ============ BUSINESSES ============ --}}
  <div x-show="tab==='businesses'" class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-left text-slate-400 mono text-[11px] uppercase tracking-wider">
          <tr class="border-b border-slate-100">
            <th class="px-4 py-3 font-medium">Business</th>
            <th class="px-4 py-3 font-medium">Category</th>
            <th class="px-4 py-3 font-medium">Plan</th>
            <th class="px-4 py-3 font-medium">Offers</th>
            <th class="px-4 py-3 font-medium">Status</th>
            <th class="px-4 py-3 font-medium">Links</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($businesses as $b)
            <tr class="border-b border-slate-50 hover:bg-slate-50/60">
              <td class="px-4 py-3">
                <div class="font-semibold text-slate-800">{{ $b->name }}</div>
                <div class="text-xs text-slate-400">{{ $b->postcode }}</div>
              </td>
              <td class="px-4 py-3 text-slate-500">{{ $b->category?->name ?? ' - ' }}</td>
              <td class="px-4 py-3">
                <form method="POST" action="{{ route('admin.plan', $b) }}">
                  @csrf
                  <select name="plan" onchange="this.form.submit()" class="rounded-lg border border-slate-200 text-xs font-semibold px-2 py-1.5 bg-white">
                    @foreach (\App\Models\Business::PLANS as $pk => $pc)
                      <option value="{{ $pk }}" @selected($b->plan===$pk)>{{ $pc['label'] }}</option>
                    @endforeach
                  </select>
                </form>
              </td>
              <td class="px-4 py-3 text-slate-500">{{ $b->offers_count }}</td>
              <td class="px-4 py-3">
                <form method="POST" action="{{ route('admin.onboard', $b) }}">
                  @csrf
                  <button class="px-2.5 py-1 rounded-full text-xs font-bold {{ $b->onboarded ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $b->onboarded ? '● Live' : '○ Lead' }}
                  </button>
                </form>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <a href="/app?b={{ $b->slug }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-medium">App ↗</a>
                <span class="text-slate-300 mx-1">·</span>
                <a href="{{ route('qr.sticker', ['secret' => $b->owner_secret]) }}" target="_blank" class="text-slate-500 hover:text-slate-700">Sticker ↗</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- ============ PROSPECTING ============ --}}
  <div x-show="tab==='prospect'" x-data="prospector()" class="rounded-2xl border border-slate-200/80 bg-white shadow-sm p-6">
    <h2 class="font-bold text-lg text-slate-900">Prospect from Google Maps</h2>
    <p class="text-slate-500 text-sm mt-1 mb-4">Search for independents, then add them as leads. They land in the Businesses tab as “Lead” until you onboard them.</p>
    <div class="flex gap-2 mb-5">
      <input x-model="q" @keydown.enter="search()" placeholder="e.g. independent coffee shop Newcastle NE1"
        class="flex-1 rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none">
      <button @click="search()" :disabled="loading" class="rounded-xl bg-slate-900 text-white font-semibold px-6 hover:bg-slate-700 disabled:opacity-50">
        <span x-text="loading ? 'Searching…' : 'Search'"></span>
      </button>
    </div>
    <div class="space-y-2">
      <template x-for="r in results" :key="r.place_id">
        <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 p-3">
          <div class="min-w-0">
            <div class="font-semibold text-slate-800 truncate" x-text="r.name"></div>
            <div class="text-xs text-slate-400 truncate" x-text="r.address"></div>
          </div>
          <template x-if="r.already_added">
            <span class="text-xs font-semibold text-slate-400 shrink-0">Added ✓</span>
          </template>
          <template x-if="!r.already_added">
            <form method="POST" action="{{ route('admin.prospect.add') }}" class="shrink-0">
              @csrf
              <input type="hidden" name="place_id" :value="r.place_id">
              <button class="rounded-lg bg-emerald-600 text-white text-sm font-semibold px-4 py-2 hover:bg-emerald-700">Add as lead</button>
            </form>
          </template>
        </div>
      </template>
      <p x-show="!results.length && searched && !loading" class="text-center text-slate-400 text-sm py-6">No results.</p>
    </div>
  </div>

  {{-- ============ CAMPAIGNS ============ --}}
  <div x-show="tab==='campaigns'" class="grid lg:grid-cols-2 gap-6">
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm p-6">
      <h2 class="font-bold text-lg text-slate-900 mb-1">New campaign</h2>
      <p class="text-slate-500 text-sm mb-4">Email reaches onboarded businesses; push reaches subscribed shoppers. (Delivery wiring is scaffolded - see notes.)</p>
      <form method="POST" action="{{ route('admin.campaign') }}" class="space-y-4">
        @csrf
        <div class="flex gap-2">
          <label class="flex-1 cursor-pointer rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
            <input type="radio" name="channel" value="email" checked class="mr-2">Email
          </label>
          <label class="flex-1 cursor-pointer rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
            <input type="radio" name="channel" value="push" class="mr-2">Push
          </label>
        </div>
        <input name="subject" placeholder="Subject / title" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none">
        <textarea name="body" rows="4" required placeholder="Message…" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
        <button class="w-full rounded-xl bg-emerald-600 text-white font-bold py-3 hover:bg-emerald-700">Send campaign</button>
      </form>
    </div>
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm p-6">
      <h2 class="font-bold text-lg text-slate-900 mb-4">Recent campaigns</h2>
      @forelse ($campaigns as $c)
        <div class="py-3 border-b border-slate-100 last:border-0">
          <div class="flex items-center justify-between">
            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $c->channel==='push' ? 'bg-indigo-100 text-indigo-700' : 'bg-sky-100 text-sky-700' }}">{{ ucfirst($c->channel) }}</span>
            <span class="text-xs text-slate-400">{{ $c->sent_count }} sent · {{ $c->created_at?->diffForHumans() }}</span>
          </div>
          <div class="font-semibold text-slate-800 mt-1.5">{{ $c->subject ?: ' - ' }}</div>
          <div class="text-sm text-slate-500 truncate">{{ $c->body }}</div>
        </div>
      @empty
        <p class="text-center text-slate-400 text-sm py-6">No campaigns yet.</p>
      @endforelse
    </div>
  </div>

  {{-- ============ REDEMPTIONS ============ --}}
  <div x-show="tab==='redemptions'" class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-left text-slate-400 mono text-[11px] uppercase tracking-wider">
          <tr class="border-b border-slate-100">
            <th class="px-5 py-3 font-medium">Code</th><th class="px-5 py-3 font-medium">Offer</th>
            <th class="px-5 py-3 font-medium">Business</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">When</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($redemptions as $r)
            <tr class="border-b border-slate-50">
              <td class="px-5 py-3 mono text-slate-800">{{ $r->code }}</td>
              <td class="px-5 py-3 text-slate-500">{{ $r->offer?->title ?? ' - ' }}</td>
              <td class="px-5 py-3 text-slate-500">{{ $r->offer?->business?->name ?? ' - ' }}</td>
              <td class="px-5 py-3">
                @php $cc = ['redeemed' => 'bg-emerald-50 text-emerald-700', 'pending' => 'bg-amber-50 text-amber-700', 'expired' => 'bg-red-50 text-red-600'][$r->status] ?? 'bg-slate-100 text-slate-600'; @endphp
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $cc }}">{{ $r->status }}</span>
              </td>
              <td class="px-5 py-3 text-slate-400">{{ $r->created_at?->diffForHumans() }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No redemptions yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
  function prospector(){
    return {
      q:'', results:[], loading:false, searched:false,
      async search(){
        if(this.q.trim().length<2) return;
        this.loading=true; this.searched=true;
        try{
          const res = await fetch('{{ route('admin.prospect.search') }}', {
            method:'POST',
            headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' },
            body: JSON.stringify({ q:this.q })
          });
          this.results = await res.json();
        }catch(e){ this.results=[]; }
        this.loading=false;
      }
    }
  }
</script>
@endsection
