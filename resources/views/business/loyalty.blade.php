@extends('business.layout')
@section('title', 'Loyalty')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
  <div>
    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Loyalty</h1>
    <p class="text-slate-500 mt-1">Reward repeat customers automatically - every time you verify a code at the till, it counts.</p>
  </div>
  <span class="px-3 py-1.5 rounded-full text-sm font-bold {{ $program->active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
    {{ $program->active ? 'Scheme live' : 'Scheme off' }}
  </span>
</div>

@if (session('status'))
  <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('status') }}</div>
@endif
@if ($errors->any())
  <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm font-medium">{{ $errors->first() }}</div>
@endif

{{-- Free, forever + how it works --}}
<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5" x-data="{ how: false }">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-start gap-3">
      <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
      </span>
      <div>
        <div class="font-bold text-emerald-900">Loyalty is free, forever.</div>
        <p class="text-sm text-emerald-800/80">It is included on every plan, the Free plan included. No add-on, no monthly fee - set your rules and locolie does the counting.</p>
      </div>
    </div>
    <button type="button" @click="how = !how" class="rounded-lg border border-emerald-300 bg-white text-sm font-semibold text-emerald-700 px-3.5 py-2 hover:bg-emerald-50">
      <span x-text="how ? 'Hide how it works' : 'How loyalty works'"></span>
    </button>
  </div>

  <div x-show="how" x-cloak x-transition class="mt-6 border-t border-emerald-200 pt-6">
    @include('site._loyalty_how', [
      'compact' => true,
      'title' => 'How loyalty works, step by step.',
      'intro' => 'Here is the journey your customers go on once your scheme is live.',
    ])
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-6">

    {{-- Scheme on/off + copy --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="font-bold text-lg text-slate-900 mb-4">Your scheme</h2>
      <form method="POST" action="{{ route('business.loyalty.save') }}" class="space-y-4">
        @csrf
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="active" value="1" @checked($program->active) class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
          <span class="text-sm font-semibold text-slate-800">Loyalty scheme is live (shoppers see their progress in the app)</span>
        </label>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Scheme name</label>
            <input name="headline" maxlength="60" value="{{ old('headline', $program->headline) }}" placeholder="e.g. Coffee Club" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">One-line pitch</label>
            <input name="blurb" maxlength="140" value="{{ old('blurb', $program->blurb) }}" placeholder="Collect stamps, get freebies" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none">
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Terms (optional)</label>
          <textarea name="terms" rows="2" maxlength="2000" placeholder="Any conditions shoppers should know - e.g. one stamp per visit, hot drinks only." class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('terms', $program->terms) }}</textarea>
        </div>
        <button class="rounded-xl bg-slate-900 text-white font-semibold px-6 py-2.5 hover:bg-slate-700">Save scheme</button>
      </form>
    </div>

    {{-- Rules --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="font-bold text-lg text-slate-900 mb-1">Rules</h2>
      <p class="text-sm text-slate-500 mb-4">Add as many as you like. Visits count each verified code; spend totals what you key in at the till.</p>

      @forelse ($rules as $rule)
        <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 p-3 mb-2 {{ $rule->active ? '' : 'opacity-60' }}">
          <div>
            <div class="font-semibold text-slate-900">{{ $rule->name }}</div>
            <div class="text-sm text-slate-500">
              {{ $rule->goalLabel() }}
              @if ($rule->repeat) <span class="text-slate-400">· repeats</span> @endif
              → <span class="font-medium text-emerald-700">{{ $rule->reward_label }}</span>
            </div>
          </div>
          <div class="flex items-center gap-1 shrink-0">
            <form method="POST" action="{{ route('business.loyalty.rules.toggle', $rule) }}">@csrf
              <button class="rounded-lg border border-slate-200 text-xs font-semibold px-2.5 py-1.5 bg-white hover:bg-slate-50">{{ $rule->active ? 'Pause' : 'Activate' }}</button>
            </form>
            <form method="POST" action="{{ route('business.loyalty.rules.destroy', $rule) }}" onsubmit="return confirm('Remove this rule?')">@csrf @method('DELETE')
              <button class="rounded-lg border border-rose-200 text-rose-600 text-xs font-semibold px-2.5 py-1.5 bg-white hover:bg-rose-50">Remove</button>
            </form>
          </div>
        </div>
      @empty
        <p class="text-sm text-slate-400 italic mb-4">No rules yet - add your first below.</p>
      @endforelse

      {{-- Add rule --}}
      <form method="POST" action="{{ route('business.loyalty.rules.store') }}" class="mt-4 border-t border-slate-100 pt-4 grid sm:grid-cols-2 gap-3"
            x-data="{ metric: 'visits', reward: 'free' }">
        @csrf
        <div class="sm:col-span-2">
          <label class="block text-xs font-semibold text-slate-700 mb-1">Rule name</label>
          <input name="name" required maxlength="80" placeholder="Buy 5, get 1 free" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Earn on</label>
          <select name="metric" x-model="metric" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
            <option value="visits">Visits (each verified code)</option>
            <option value="spend">Spend (£ keyed at till)</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1" x-text="metric === 'spend' ? 'Spend target (£)' : 'Visits needed'">Visits needed</label>
          <input name="threshold" type="number" min="1" step="any" required placeholder="5" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Reward type</label>
          <select name="reward_type" x-model="reward" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
            <option value="free">Free item</option>
            <option value="percent">% discount</option>
            <option value="amount">£ off</option>
            <option value="gift">Gift / other</option>
          </select>
        </div>
        <div x-show="reward === 'percent' || reward === 'amount'">
          <label class="block text-xs font-semibold text-slate-700 mb-1" x-text="reward === 'percent' ? 'Percent off' : 'Amount off (£)'">Value</label>
          <input name="reward_value" type="number" min="0" step="any" placeholder="10" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-semibold text-slate-700 mb-1">Reward shown to customer</label>
          <input name="reward_label" required maxlength="80" placeholder="Free coffee" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>
        <label class="sm:col-span-2 flex items-center gap-2 text-sm text-slate-700">
          <input type="checkbox" name="repeat" value="1" checked class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
          Repeats (a stamp card that resets after each reward). Uncheck for a one-time perk.
        </label>
        <div class="sm:col-span-2">
          <button class="rounded-xl bg-emerald-600 text-white text-sm font-semibold px-5 py-2.5 hover:bg-emerald-700">Add rule</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Side: rewards to honour + top members --}}
  <div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="font-bold text-lg text-slate-900 mb-1">Rewards to give</h2>
      <p class="text-sm text-slate-500 mb-4">Customers who've earned a reward. Tap redeemed once you've handed it over.</p>
      @forelse ($rewards as $reward)
        <div class="flex items-center justify-between gap-3 border-b border-slate-50 py-2.5 last:border-0">
          <div class="min-w-0">
            <div class="font-semibold text-sm text-slate-900 truncate">{{ $reward->label }}</div>
            <div class="text-xs text-slate-500 truncate">{{ $reward->customer_email }} · <span class="font-mono">{{ $reward->code }}</span></div>
          </div>
          <form method="POST" action="{{ route('business.loyalty.rewards.redeem', $reward) }}" class="shrink-0">@csrf
            <button class="rounded-lg bg-emerald-600 text-white text-xs font-semibold px-3 py-1.5 hover:bg-emerald-700">Redeemed</button>
          </form>
        </div>
      @empty
        <p class="text-sm text-slate-400 italic">No rewards waiting.</p>
      @endforelse
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="font-bold text-lg text-slate-900 mb-1">Top members</h2>
      <p class="text-sm text-slate-500 mb-4">Your most loyal customers by visits.</p>
      @forelse ($members as $m)
        <div class="flex items-center justify-between gap-3 border-b border-slate-50 py-2 last:border-0 text-sm">
          <span class="text-slate-700 truncate">{{ $m->customer_email }}</span>
          <span class="shrink-0 font-semibold text-slate-900">{{ $m->visits }} {{ \Illuminate\Support\Str::plural('visit', $m->visits) }}@if ($m->spend) · £{{ number_format($m->spend / 100, 0) }}@endif</span>
        </div>
      @empty
        <p class="text-sm text-slate-400 italic">No members yet.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection
