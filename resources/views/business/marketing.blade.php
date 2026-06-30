@extends('business.layout')
@section('title', 'Marketing')

@php $accent = $business->brandColor(); @endphp

@section('content')
<div class="space-y-10">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">How your marketing works</h1>
            <p class="text-slate-500 mt-1 max-w-2xl text-sm">
                Turn footfall into a customer list you own - then message them for free. Here's the whole loop,
                start to finish, the same one the big chains have always had.
            </p>
        </div>
        <a href="{{ route('business.dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Back to dashboard</a>
    </div>

    {{-- Live capture tiles --}}
    <div class="grid grid-cols-3 gap-3 sm:gap-4">
        @php
            $tiles = [
                ['label' => 'Email contacts', 'value' => $stats['emails'], 'sub' => 'opted in'],
                ['label' => 'SMS contacts', 'value' => $stats['sms'], 'sub' => 'opted in'],
                ['label' => 'Joined this week', 'value' => $stats['this_week'], 'sub' => 'new sign-ups'],
            ];
        @endphp
        @foreach ($tiles as $t)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($t['value']) }}</div>
                <div class="text-xs font-semibold text-slate-700 mt-1">{{ $t['label'] }}</div>
                <div class="text-[11px] text-slate-400">{{ $t['sub'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ── The 4-step flow ─────────────────────────────────────────────────── --}}
    <section>
        <div class="flex items-center gap-2 mb-5">
            <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full" style="background: {{ $accent }}1a; color: {{ $accent }};">The loop</span>
            <span class="text-sm text-slate-500">From a code on your counter to repeat custom.</span>
        </div>

        <div class="grid lg:grid-cols-2 gap-5">

            {{-- STEP 1 - capture by QR --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-extrabold text-white" style="background: {{ $accent }};">1</span>
                    <h2 class="font-bold text-lg text-slate-900">Capture customers with a scan</h2>
                </div>
                <p class="text-sm text-slate-500 mb-5">
                    Display your QR poster in store. A customer scans it, lands on your branded page, and opts in
                    with their email (and optionally their mobile). They join your list - and your loyalty scheme - in one tap.
                </p>

                <div class="grid sm:grid-cols-[auto_1fr] gap-5 items-center">
                    {{-- The live QR --}}
                    <div class="mx-auto sm:mx-0">
                        <div class="inline-block rounded-2xl border-2 border-slate-200 p-3 bg-white">
                            {!! $qrSvg !!}
                        </div>
                        <p class="text-[11px] text-center text-slate-400 mt-1.5">Your live code</p>
                    </div>

                    {{-- Mini flow: scan -> form -> joined --}}
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-2.5 text-sm text-slate-600">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">a</span>
                            Customer scans the code in store
                        </div>
                        <div class="flex items-center gap-2.5 text-sm text-slate-600">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">b</span>
                            Lands on <b>your</b> branded opt-in page
                        </div>
                        <div class="flex items-center gap-2.5 text-sm text-slate-600">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">c</span>
                            Ticks consent, taps Join
                        </div>
                        <div class="flex items-center gap-2.5 text-sm font-semibold" style="color: {{ $accent }};">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold text-white" style="background: {{ $accent }};">&check;</span>
                            On your list + earning loyalty
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <a href="{{ route('business.marketing.poster') }}" target="_blank"
                       class="rounded-lg text-white text-sm font-bold px-4 py-2.5 hover:brightness-110" style="background: {{ $accent }};">
                        Print your QR poster
                    </a>
                    <a href="{{ $captureUrl }}" target="_blank" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                        Preview the customer page &rarr;
                    </a>
                </div>
                <p class="text-[11px] text-slate-400 mt-3 break-all">Your scan link: {{ $captureUrl }}</p>
            </div>

            {{-- STEP 2 - build a campaign --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-extrabold text-white" style="background: {{ $accent }};">2</span>
                    <h2 class="font-bold text-lg text-slate-900">Build a campaign</h2>
                </div>
                <p class="text-sm text-slate-500 mb-5">
                    Open the Messaging studio and write an email, SMS or push - on your brand, with your logo and colour.
                    Live preview shows exactly what the customer gets before you send.
                </p>

                <div class="grid grid-cols-3 gap-3 mb-5">
                    @php
                        $channels = [
                            ['k' => 'Email', 'd' => 'Rich, branded'],
                            ['k' => 'SMS', 'd' => '98% read rate'],
                            ['k' => 'Push', 'd' => 'In-app alerts'],
                        ];
                    @endphp
                    @foreach ($channels as $c)
                        <div class="rounded-xl border border-slate-200 p-3 text-center">
                            <div class="text-sm font-bold text-slate-800">{{ $c['k'] }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $c['d'] }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Tiny mock email --}}
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-4 py-2.5 text-xs font-semibold text-white flex items-center gap-2" style="background: {{ $accent }};">
                        <span class="h-5 w-5 rounded bg-white/25 flex items-center justify-center text-[10px] font-extrabold">{{ $business->brandInitials() }}</span>
                        {{ $business->emailFromName() }}
                    </div>
                    <div class="p-4">
                        <div class="text-sm font-bold text-slate-900">This weekend: 2-for-1 on all mains</div>
                        <div class="text-xs text-slate-500 mt-1 leading-relaxed">Hi Alex, pop in this Saturday or Sunday and bring a friend - mains are 2-for-1 all weekend.</div>
                        <span class="inline-block mt-3 rounded-lg px-3 py-1.5 text-xs font-bold text-white" style="background: {{ $accent }};">See the menu</span>
                    </div>
                </div>

                <a href="{{ route('business.messaging') }}"
                   class="mt-5 inline-flex w-fit rounded-lg text-white text-sm font-bold px-4 py-2.5 hover:brightness-110" style="background: {{ $accent }};">
                    Open the Messaging studio
                </a>
            </div>

            {{-- STEP 3 - we send it (privacy promise) --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-extrabold text-white" style="background: {{ $accent }};">3</span>
                    <h2 class="font-bold text-lg text-slate-900">We send it for you</h2>
                </div>
                <p class="text-sm text-slate-500 mb-5">
                    Hit send and locolie delivers it on your behalf - email, SMS or push - with the unsubscribe link and
                    STOP keyword added automatically. You stay compliant without lifting a finger.
                </p>

                <div class="rounded-xl border p-4" style="border-color: {{ $accent }}33; background: {{ $accent }}0d;">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="{{ $accent }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
                        <span class="text-sm font-bold text-slate-900">The privacy promise</span>
                    </div>
                    <ul class="space-y-1.5 text-sm text-slate-600">
                        <li class="flex gap-2"><span style="color: {{ $accent }};">&check;</span> Contact details are <b>yours</b> and stay protected - we never sell them.</li>
                        <li class="flex gap-2"><span style="color: {{ $accent }};">&check;</span> Every send respects unsubscribes and consent automatically.</li>
                        <li class="flex gap-2"><span style="color: {{ $accent }};">&check;</span> A full GDPR consent log is kept for you.</li>
                        <li class="flex gap-2"><span style="color: {{ $accent }};">&check;</span> Export your whole list as CSV anytime - no lock-in.</li>
                    </ul>
                </div>
            </div>

            {{-- STEP 4 - measure --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-extrabold text-white" style="background: {{ $accent }};">4</span>
                    <h2 class="font-bold text-lg text-slate-900">See what worked</h2>
                </div>
                <p class="text-sm text-slate-500 mb-5">
                    Opens, clicks and redemptions land in your Reports. Learn what your customers respond to,
                    then do more of it.
                </p>

                <div class="grid grid-cols-3 gap-3">
                    @php
                        $metrics = [
                            ['k' => 'Opens', 'v' => '57%'],
                            ['k' => 'Clicks', 'v' => '16%'],
                            ['k' => 'Redemptions', 'v' => '31'],
                        ];
                    @endphp
                    @foreach ($metrics as $m)
                        <div class="rounded-xl border border-slate-200 p-4 text-center">
                            <div class="text-2xl font-extrabold" style="color: {{ $accent }};">{{ $m['v'] }}</div>
                            <div class="text-[11px] font-semibold text-slate-500 mt-0.5">{{ $m['k'] }}</div>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('business.reports') }}"
                   class="mt-5 inline-flex w-fit rounded-lg text-white text-sm font-bold px-4 py-2.5 hover:brightness-110" style="background: {{ $accent }};">
                    Open Reports
                </a>
            </div>
        </div>
    </section>

    {{-- ── Worked example ──────────────────────────────────────────────────── --}}
    @php $listIsReal = collect($sampleList)->every(fn ($r) => $r['real'] ?? false); @endphp
    <section>
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">Worked example</span>
            <span class="text-sm text-slate-500">What a built-up list and sent campaigns look like.</span>
        </div>

        <div class="grid lg:grid-cols-2 gap-5 items-start">

            {{-- Captured list --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900">Your customer list</h3>
                    @unless ($listIsReal)
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 rounded-full px-2.5 py-1">Sample data</span>
                    @endunless
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($sampleList as $c)
                        <div class="flex items-center gap-3 py-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold" style="background: {{ $accent }}1a; color: {{ $accent }};">
                                {{ strtoupper(substr($c['name'], 0, 1)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold text-slate-800 truncate">{{ $c['name'] }}</div>
                                <div class="text-xs text-slate-400 truncate">{{ $c['email'] }}</div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="text-[10px] font-bold rounded px-1.5 py-0.5 bg-sky-100 text-sky-700">Email</span>
                                @if ($c['sms'])
                                    <span class="text-[10px] font-bold rounded px-1.5 py-0.5 bg-violet-100 text-violet-700">SMS</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-400 shrink-0 w-20 text-right hidden sm:block">{{ $c['joined'] }}</div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('business.customers.export') }}" class="mt-4 inline-flex text-sm font-semibold" style="color: {{ $accent }};">Export list as CSV &rarr;</a>
            </div>

            {{-- Sent campaigns --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900">Campaigns you've sent</h3>
                    @php $campsReal = collect($sampleCampaigns)->every(fn ($c) => $c['real'] ?? false); @endphp
                    @unless ($campsReal)
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 rounded-full px-2.5 py-1">Sample data</span>
                    @endunless
                </div>
                <div class="space-y-3">
                    @php $chip = ['email' => 'bg-sky-100 text-sky-700', 'sms' => 'bg-violet-100 text-violet-700', 'push' => 'bg-amber-100 text-amber-700']; @endphp
                    @foreach ($sampleCampaigns as $c)
                        <div class="rounded-xl border border-slate-200 p-3.5">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="text-[10px] font-bold rounded-full px-2 py-0.5 {{ $chip[$c['channel']] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($c['channel']) }}</span>
                                <span class="text-xs text-slate-400">{{ $c['when'] }}</span>
                            </div>
                            <div class="text-sm font-semibold text-slate-800">{{ $c['subject'] }}</div>
                            <div class="mt-2 flex items-center gap-4 text-xs text-slate-500">
                                <span><b class="text-slate-800">{{ number_format($c['sent']) }}</b> sent</span>
                                @if ($c['channel'] === 'email')
                                    <span><b class="text-slate-800">{{ $c['sent'] ? round($c['opens'] / $c['sent'] * 100) : 0 }}%</b> opened</span>
                                @endif
                                <span><b class="text-slate-800">{{ $c['sent'] ? round($c['clicks'] / $c['sent'] * 100) : 0 }}%</b> clicked</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('business.reports') }}" class="mt-4 inline-flex text-sm font-semibold" style="color: {{ $accent }};">See full reports &rarr;</a>
            </div>
        </div>
    </section>

    {{-- CTA strip --}}
    <section class="rounded-2xl p-6 sm:p-8 text-center" style="background: {{ $accent }}; ">
        <h2 class="text-xl sm:text-2xl font-extrabold text-white">Ready to start capturing customers?</h2>
        <p class="text-white/80 mt-1.5 text-sm max-w-xl mx-auto">Print your QR poster, pop it on the counter, and watch your own list grow from day one.</p>
        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('business.marketing.poster') }}" target="_blank" class="rounded-lg bg-white text-sm font-bold px-5 py-2.5 text-slate-900 hover:bg-slate-100">Print your QR poster</a>
            <a href="{{ route('business.messaging') }}" class="rounded-lg bg-black/20 text-sm font-bold px-5 py-2.5 text-white hover:bg-black/30">Build a campaign</a>
        </div>
    </section>

</div>
@endsection
