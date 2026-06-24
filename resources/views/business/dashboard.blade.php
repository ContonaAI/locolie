@extends('business.layout')
@section('title', 'Dashboard')

@section('content')
@php $cfg = $plans[$business->plan] ?? $plans['free']; @endphp

<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
  <div>
    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $business->name }}</h1>
    <p class="text-slate-500 mt-1">{{ $business->category?->name }} · {{ $business->postcode }}</p>
  </div>
  <div class="flex items-center gap-2">
    <span class="px-3 py-1.5 rounded-full text-sm font-bold {{ $business->plan==='premium' ? 'bg-amber-100 text-amber-800' : ($business->plan==='featured' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600') }}">{{ $cfg['label'] }} plan</span>
    <a href="/app?b={{ $business->slug }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-700">View in app ↗</a>
  </div>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
  @foreach ([['Active offers',$stats['offers']],['Redeemed',$stats['redeemed']],['Pending',$stats['pending']],['Rating',number_format((float)$business->rating,1)]] as $kpi)
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
      <div class="text-3xl font-extrabold text-slate-900">{{ $kpi[1] }}</div>
      <div class="text-[11px] uppercase tracking-widest text-slate-400 mt-1 font-semibold">{{ $kpi[0] }}</div>
    </div>
  @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6">
  {{-- Listing edit --}}
  <div class="lg:col-span-2 space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="font-bold text-lg text-slate-900 mb-4">Your listing</h2>
      <form method="POST" action="{{ route('business.listing') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
          <textarea name="description" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('description',$business->description) }}</textarea>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Phone</label>
            <input name="phone" value="{{ old('phone',$business->phone) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Website</label>
            <input name="website" value="{{ old('website',$business->website) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none">
          </div>
        </div>
        <button class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3 transition">Save changes</button>
      </form>
    </div>

    {{-- Offers --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-lg text-slate-900">Offers</h2>
        <a href="/app?as=business" target="_blank" class="text-sm font-semibold text-emerald-700 hover:underline">Manage in app ↗</a>
      </div>
      @forelse ($business->offers->where('status','active') as $o)
        <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
          <div>
            <div class="font-semibold text-slate-800">{{ $o->title }}</div>
            <div class="text-xs text-slate-400 mt-0.5">
              <span class="font-bold text-emerald-700">{{ $o->badge }}</span>
              · {{ ucfirst($o->sale_type) }}@if($o->quantity) · {{ $o->remaining() }} left @endif · {{ $o->redeemed_count }} redeemed
            </div>
          </div>
        </div>
      @empty
        <p class="text-slate-400 text-sm py-4 text-center">No active offers yet - add one in the app.</p>
      @endforelse
    </div>
  </div>

    {{-- Customers - the first-party data the chains have and independents never did --}}
    <div class="lg:col-span-3 order-last bg-white rounded-2xl border border-slate-200 p-6" x-data="{ compose:false }">
      <div class="flex items-center justify-between mb-1">
        <h2 class="font-bold text-lg text-slate-900">Your customers</h2>
        <div class="flex items-center gap-2">
          <a href="{{ route('business.customers.export') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Export CSV</a>
          <button @click="compose=!compose" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Draft email</button>
          <a href="{{ route('business.messaging') }}" class="rounded-lg bg-emerald-600 text-white text-sm font-semibold px-4 py-2 hover:bg-emerald-700 inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            Messaging Studio
          </a>
        </div>
      </div>
      <p class="text-sm text-slate-500 mb-4">Every shopper who redeems an offer is captured here - <span class="font-semibold text-slate-700">your own customer list to market to</span>, something the big chains have always had and independents never did. {{ $customers->count() }} captured · {{ $customers->where('opt_in',true)->count() }} opted in to marketing.</p>

      <div x-show="compose" x-cloak class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50/40 p-4">
        <p class="text-xs text-slate-500 mb-3">Jot down a campaign now and save it as a draft. When you are ready to send it as branded email to your {{ $customers->where('opt_in',true)->count() }} opted-in customers, open the <a href="{{ route('business.messaging') }}" class="font-semibold text-emerald-700 hover:underline">Messaging Studio</a>.</p>
        <form method="POST" action="{{ route('business.customers.email') }}" class="space-y-3">
          @csrf
          <input name="subject" required placeholder="Subject (e.g. A treat for our regulars 🎉)" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
          <textarea name="body" rows="3" required placeholder="Your message to opted-in customers…" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
          <button class="rounded-lg bg-emerald-600 text-white text-sm font-bold px-5 py-2.5 hover:bg-emerald-700">Save draft</button>
        </form>
      </div>

      @if ($customers->count())
        <div class="overflow-x-auto -mx-2">
          <table class="w-full text-sm">
            <thead class="text-left text-slate-400 text-xs uppercase tracking-wider">
              <tr class="border-b border-slate-100"><th class="px-2 py-2 font-medium">Customer</th><th class="px-2 py-2 font-medium">Email</th><th class="px-2 py-2 font-medium">Visits</th><th class="px-2 py-2 font-medium">Marketing</th></tr>
            </thead>
            <tbody>
              @foreach ($customers->take(25) as $c)
                <tr class="border-b border-slate-50">
                  <td class="px-2 py-2.5 font-medium text-slate-800">{{ $c->name }}</td>
                  <td class="px-2 py-2.5 text-slate-500">{{ $c->email }}</td>
                  <td class="px-2 py-2.5 text-slate-500">{{ $c->visits }}</td>
                  <td class="px-2 py-2.5">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $c->opt_in ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $c->opt_in ? 'Opted in' : ' - ' }}</span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-center py-8 text-slate-400 text-sm">No customers captured yet. As shoppers redeem your offers, they’ll appear here - ready to market to.</div>
      @endif
    </div>

  {{-- Plan / upgrade --}}
  <div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="font-bold text-lg text-slate-900 mb-1">Your plan</h2>
      <p class="text-sm text-slate-500 mb-4">Change anytime. Free at launch - paid tiers preview the upgrade path.</p>
      <div class="space-y-3">
        @foreach ($plans as $key => $p)
          <div class="rounded-xl border p-4 {{ $business->plan===$key ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-200' }}">
            <div class="flex items-center justify-between">
              <div class="font-bold text-slate-900">{{ $p['label'] }}</div>
              <div class="text-sm font-semibold text-slate-500">{{ $p['price'] ? '£'.$p['price'].'/mo' : 'Free' }}</div>
            </div>
            <ul class="mt-2 space-y-1">
              @foreach ($p['perks'] as $perk)
                <li class="text-xs text-slate-500 flex gap-1.5"><span class="text-emerald-500">✓</span>{{ $perk }}</li>
              @endforeach
            </ul>
            @if ($business->plan===$key)
              <div class="mt-3 text-center text-xs font-bold text-emerald-700 uppercase tracking-wide">Current plan</div>
            @else
              <form method="POST" action="{{ route('business.upgrade') }}" class="mt-3">
                @csrf <input type="hidden" name="plan" value="{{ $key }}">
                <button class="w-full rounded-lg {{ $key==='free' ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-slate-900 text-white hover:bg-slate-700' }} font-semibold py-2 text-sm transition">
                  {{ $key==='free' ? 'Downgrade to Free' : 'Switch to '.$p['label'] }}
                </button>
              </form>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
