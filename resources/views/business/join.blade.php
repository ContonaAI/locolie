@extends('business.layout')
@section('title', 'For retailers - list your shop free')

@php
    $loginErrors    = $errors->getBag('login');
    $registerErrors = $errors->getBag('register');
    // Open the sign-in tab only when a sign-in attempt just failed; otherwise lead with sign-up.
    $startTab = $loginErrors->isNotEmpty() ? 'signin' : 'register';

    $pinPath = 'M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z';
@endphp

@section('content')
<div x-data="{ tab: '{{ $startTab }}' }">

  {{-- ============================================================ HERO + AUTH (top) --}}
  <section class="grid items-start gap-10 lg:grid-cols-[1.1fr_minmax(0,420px)] lg:gap-14">
    {{-- pitch --}}
    <div class="pt-2 lg:pt-6">
      <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1.5 text-xs font-semibold text-emerald-700">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="{{ $pinPath }}"/></svg>
        For independent UK businesses
      </span>
      <h1 class="mt-5 text-4xl font-extrabold leading-[1.05] tracking-tight text-slate-900 sm:text-5xl">
        Get found by locals who want to <span class="text-emerald-600">back the indies</span>.
      </h1>
      <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-600">
        List your shop free, post offers that bring real footfall through the door, and turn every redemption into a customer you actually own. Email, SMS and push - all built in.
      </p>

      <ul class="mt-7 grid gap-3 sm:grid-cols-2">
        @php
          $quick = [
            'Free listing, live in minutes',
            'Own your customer list',
            'Offers redeemed by QR at the till',
            'Email, SMS &amp; push in one place',
          ];
        @endphp
        @foreach ($quick as $q)
          <li class="flex items-center gap-2.5 text-sm font-medium text-slate-700">
            <svg class="h-5 w-5 flex-shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            {!! $q !!}
          </li>
        @endforeach
      </ul>
    </div>

    {{-- auth card --}}
    <div id="register" class="scroll-mt-24">
      <div id="signin" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-lg sm:p-7">
        {{-- tabs --}}
        <div class="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1 text-sm font-semibold">
          <button type="button" @click="tab = 'register'"
            :class="tab === 'register' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'"
            class="rounded-lg py-2 transition">Create account</button>
          <button type="button" @click="tab = 'signin'"
            :class="tab === 'signin' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'"
            class="rounded-lg py-2 transition">Sign in</button>
        </div>

        {{-- REGISTER --}}
        <div x-show="tab === 'register'" x-cloak class="mt-6">
          <h2 class="text-lg font-bold text-slate-900">List your shop, free</h2>
          <p class="mt-1 text-sm text-slate-500">No card needed. You'll be in your dashboard in under a minute.</p>

          @if ($registerErrors->isNotEmpty())
            <div class="mt-4 rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $registerErrors->first() }}</div>
          @endif

          <form method="POST" action="{{ route('business.register.submit') }}" class="mt-5 space-y-3.5">
            @csrf
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Shop name</label>
              <input name="name" type="text" value="{{ old('name') }}" required autocomplete="organization"
                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Town</label>
              <input name="city" type="text" value="{{ old('city') }}" required placeholder="e.g. Newcastle"
                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
              <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
            </div>
            <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                <input name="password" type="password" required autocomplete="new-password" minlength="8"
                  class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Confirm</label>
                <input name="password_confirmation" type="password" required autocomplete="new-password" minlength="8"
                  class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
              </div>
            </div>
            <label class="flex items-start gap-2.5 pt-1 text-sm text-slate-600">
              <input name="terms" type="checkbox" value="1" required class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
              <span>I agree to the <a href="/terms" class="font-semibold text-emerald-700 hover:underline">Terms</a> and <a href="/privacy" class="font-semibold text-emerald-700 hover:underline">Privacy Policy</a>.</span>
            </label>
            <button class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 transition">Create my free listing</button>
          </form>
          <p class="mt-4 text-center text-sm text-slate-500">
            Already listed? <button type="button" @click="tab = 'signin'" class="font-semibold text-emerald-700 hover:underline">Sign in</button>
          </p>
        </div>

        {{-- SIGN IN --}}
        <div x-show="tab === 'signin'" x-cloak class="mt-6">
          <h2 class="text-lg font-bold text-slate-900">Welcome back</h2>
          <p class="mt-1 text-sm text-slate-500">Manage your listing, offers and plan.</p>

          @if ($loginErrors->isNotEmpty())
            <div class="mt-4 rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $loginErrors->first() }}</div>
          @endif

          <form method="POST" action="{{ route('business.login.submit') }}" class="mt-5 space-y-3.5">
            @csrf
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
              <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
              <input name="password" type="password" required autocomplete="current-password"
                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
            </div>
            <button class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 transition">Log in</button>
          </form>
          @if (!empty($demo))
            <p class="mt-4 text-center text-xs text-slate-400">Just looking? Demo login: <span class="font-semibold text-slate-500">demo@locolie.test</span></p>
          @endif
          <p class="mt-3 text-center text-sm text-slate-500">
            New here? <button type="button" @click="tab = 'register'" class="font-semibold text-emerald-700 hover:underline">List your shop free</button>
          </p>
        </div>
      </div>
      <p class="mt-4 text-center text-xs text-slate-400">Backing independents, starting in Newcastle NE1.</p>
    </div>
  </section>

  {{-- ============================================================ WHY (value props) --}}
  <section id="why" class="scroll-mt-24 mt-20 sm:mt-28">
    <div class="mx-auto max-w-2xl text-center">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Why locolie</h2>
      <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Everything a high-street shop needs, in one place.</p>
    </div>
    <div class="mt-12 grid gap-6 md:grid-cols-3">
      @php
        $values = [
          ['Get discovered by locals', 'Show up on the map and in the feed when nearby shoppers are browsing for independents and deciding where to go.',
           '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/>'],
          ['Drive real footfall', 'Post offers shoppers redeem in store with a quick QR scan at the till, so you see exactly how much footfall each one brings in.',
           '<path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82Z"/><line x1="7" y1="7" x2="7.01" y2="7"/>'],
          ['Free to start, pay to grow', 'Your listing is free forever. Upgrade for featured placement, push notifications and email campaigns whenever you\'re ready.',
           '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>'],
        ];
      @endphp
      @foreach ($values as $v)
        <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $v[2] !!}</svg>
          </div>
          <h3 class="mt-5 text-lg font-bold text-slate-900">{{ $v[0] }}</h3>
          <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $v[1] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- ============================================================ OWN YOUR CUSTOMERS --}}
  <section class="mt-16 overflow-hidden rounded-3xl bg-slate-900 p-8 text-white sm:p-12">
    <div class="grid items-center gap-10 lg:grid-cols-2">
      <div>
        <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-300">The unfair advantage</h2>
        <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Finally, own your customers.</p>
        <p class="mt-5 text-lg leading-relaxed text-white/70">
          Every offer you redeem adds a real customer - their name, email and how often they pop in - straight into <span class="font-semibold text-white">your own list</span>. The kind of loyalty data the big chains built empires on, and independents never had.
        </p>
        <p class="mt-4 text-lg leading-relaxed text-white/70">
          Stop renting reach from Facebook and Deliveroo. Bring regulars back with your own email &amp; push offers, and keep that relationship for good.
        </p>
        <a href="#register" @click="tab = 'register'" class="mt-7 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-emerald-500 hover:text-white">
          Start building your list, free
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>
      <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-6">
        @php $rows = [['Sarah J.', '4 visits'], ['Mark T.', '2 visits'], ['Priya K.', '6 visits'], ['Dan W.', '1 visit']]; @endphp
        <div class="flex items-center justify-between"><div class="font-bold">Your customers</div><span class="rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold text-emerald-300">128 captured</span></div>
        <div class="mt-4 divide-y divide-white/10">
          @foreach ($rows as $r)
            <div class="flex items-center justify-between py-2.5">
              <div class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-xs font-bold">{{ substr($r[0],0,1) }}</span><span class="text-sm font-medium">{{ $r[0] }}</span></div>
              <span class="text-xs text-white/50">{{ $r[1] }}</span>
            </div>
          @endforeach
        </div>
        <button type="button" class="mt-4 w-full rounded-xl bg-emerald-500 py-2.5 text-sm font-bold text-white">✉ Email these customers</button>
      </div>
    </div>
  </section>

  {{-- ============================================================ FEATURES (toolkit) --}}
  <section id="features" class="scroll-mt-24 mt-20 sm:mt-28">
    <div class="mx-auto max-w-2xl text-center">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Your marketing toolkit</h2>
      <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Reach your customers, your way.</p>
      <p class="mt-4 text-slate-500">Redeem an offer and that shopper joins your list. Bring them back with built-in email, SMS and push. No separate Mailchimp, no agency, no ad spend.</p>
    </div>
    <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      @php
        $tools = [
          ['Email campaigns', 'Send branded offers and newsletters to your opted-in customers in a couple of taps.', '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/>'],
          ['SMS text blasts', 'Quiet Tuesday or a table just freed up? Text your regulars and fill it. Texts get read in minutes.', '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
          ['Push notifications', 'Ping nearby shoppers and your followers the moment you post a fresh offer (Premium).', '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>'],
          ['Win-back automations', 'Automatic "we miss you" and birthday offers that win lapsed customers back on autopilot.', '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>'],
        ];
      @endphp
      @foreach ($tools as $t)
        <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $t[2] !!}</svg>
          </div>
          <h3 class="mt-5 text-lg font-bold text-slate-900">{{ $t[0] }}</h3>
          <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $t[1] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- ============================================================ DASHBOARD WALKTHROUGH --}}
  <section class="mt-20 sm:mt-28">
    <div class="mx-auto max-w-2xl text-center">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-700">A look inside</h2>
      <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Your whole shop, one dashboard.</p>
      <p class="mt-4 text-slate-500">Here's the retailer dashboard in action - watch it walk through your offers, the customers you capture, messaging and reports. Or click a page to jump in.</p>
    </div>
    <div class="mt-12 mx-auto max-w-4xl">
      @include('business._adminwalk')
    </div>
  </section>

  {{-- ============================================================ BADGE / SEAL --}}
  <section class="mt-16 grid items-center gap-10 rounded-3xl border border-slate-200 bg-slate-50 p-8 sm:p-12 lg:grid-cols-[auto_1fr]">
    <div class="flex justify-center">
      <x-seal class="h-40 w-40 drop-shadow-lg" />
    </div>
    <div>
      <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Your Badge of Honour</h2>
      <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">A trust-mark for the window and the till.</p>
      <p class="mt-4 max-w-xl text-slate-600">Every verified locolie shop gets the seal for the door and the counter. Shoppers scan it to grab your offers, follow you and join your list - one quick scan, no app faff.</p>
      <a href="#register" @click="tab = 'register'" class="mt-6 inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600">
        Claim your badge, free
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>
  </section>

  {{-- ============================================================ PRICING --}}
  <section id="pricing" class="scroll-mt-24 mt-20 sm:mt-28">
    <div class="mx-auto max-w-2xl text-center">
      <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Pricing</h2>
      <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Simple plans. Cancel anytime.</p>
      <p class="mt-4 text-slate-500">Start free and upgrade whenever you want more reach. No contracts, no setup fees, no catch.</p>
    </div>

    {{-- Free = loyalty only / paid = full marketing, made explicit --}}
    <div class="mx-auto mt-8 max-w-3xl rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-center text-sm text-slate-600">
      <span class="font-semibold text-slate-900">Free</span> covers your listing, offers and loyalty scheme.
      <span class="font-semibold text-slate-900">Paid plans</span> add full marketing - email, SMS &amp; push campaigns to your own customers, within a monthly send allowance.
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      @foreach ($plans as $key => $plan)
        @php $featured = $key === 'featured'; @endphp
        <div class="relative flex flex-col rounded-2xl border p-6 shadow-sm {{ $featured ? 'border-emerald-500 ring-1 ring-emerald-200 bg-white' : 'border-slate-200 bg-white' }}">
          @if ($featured)
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white">Most popular</span>
          @endif
          <div class="text-sm font-bold uppercase tracking-wider text-slate-500">{{ $plan['label'] }}</div>
          <div class="mt-3 flex items-baseline gap-1">
            @if (is_null($plan['price']))
              <span class="text-3xl font-extrabold text-slate-900">Custom</span>
            @else
              <span class="text-4xl font-extrabold text-slate-900">£{{ $plan['price'] }}</span>
              <span class="text-sm font-medium text-slate-400">/mo</span>
            @endif
          </div>

          {{-- Monthly send allowance --}}
          <div class="mt-4 rounded-xl bg-slate-50 px-3.5 py-2.5 text-xs">
            <div class="font-semibold uppercase tracking-wider text-slate-400">Monthly sends</div>
            @if (! ($plan['marketing'] ?? false))
              <div class="mt-1 font-medium text-slate-700">Loyalty scheme only - no marketing sends</div>
            @elseif (($plan['sends']['email'] ?? 0) >= PHP_INT_MAX)
              <div class="mt-1 font-medium text-emerald-700">Unlimited email, SMS &amp; push</div>
            @else
              <div class="mt-1 font-medium text-slate-700">
                {{ number_format($plan['sends']['email']) }} email · {{ number_format($plan['sends']['sms']) }} SMS @if (! empty($plan['sends']['push'])) · push @endif
              </div>
            @endif
          </div>

          <ul class="mt-5 flex-1 space-y-2.5">
            @foreach ($plan['perks'] as $perk)
              <li class="flex items-start gap-2.5 text-sm text-slate-600">
                <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                {!! $perk !!}
              </li>
            @endforeach
          </ul>
          @if ($key === 'enterprise')
            <a href="{{ route('site.contact') }}?topic=enterprise" class="mt-7 block rounded-xl py-3 text-center text-sm font-bold transition border border-slate-300 text-slate-900 hover:border-slate-900">
              Contact sales
            </a>
          @else
            <a href="#register" @click="tab = 'register'" class="mt-7 block rounded-xl py-3 text-center text-sm font-bold transition {{ $featured ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'border border-slate-300 text-slate-900 hover:border-slate-900' }}">
              {{ ($plan['price'] === 0) ? 'Start free' : 'Choose '.$plan['label'] }}
            </a>
          @endif
        </div>
      @endforeach
    </div>
  </section>

  {{-- ============================================================ AUTH (bottom) --}}
  <section class="mt-20 overflow-hidden rounded-3xl bg-emerald-600 p-8 text-center text-white sm:mt-28 sm:p-14">
    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Put your shop on the map.</h2>
    <p class="mx-auto mt-4 max-w-xl text-emerald-50">Join the independents already backing local on locolie. Free to start, live in minutes.</p>
    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
      <a href="#register" @click="tab = 'register'"
         class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-bold text-emerald-700 transition hover:bg-slate-900 hover:text-white">
        List my shop free
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
      <a href="#signin" @click="tab = 'signin'"
         class="inline-flex items-center justify-center rounded-full border border-white/40 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">
        I already have an account
      </a>
    </div>
  </section>

</div>
@endsection
