@extends('portal.layout')
@section('title', 'Reports')

@section('content')
@php
    $k = $report['kpis'];
    $busiestTotal = array_sum(array_column($report['redemptions_series'], 'value'));
@endphp

<div class="flex flex-wrap items-end justify-between gap-3 mb-7">
    <div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Platform reports</h1>
        <p class="text-slate-500 mt-2 max-w-2xl">Marketplace performance across every onboarded business - redemptions, savings delivered to shoppers, and messaging reach.</p>
    </div>
    <div class="flex items-center gap-1 rounded-xl bg-white border border-slate-200 p-1 text-sm">
        @foreach ([7 => '7d', 14 => '14d', 30 => '30d', 90 => '90d'] as $d => $lbl)
            <a href="{{ route('portal.reports', ['days' => $d]) }}"
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $days === $d ? 'bg-emerald-600 text-white' : 'text-slate-500 hover:bg-slate-100' }}">{{ $lbl }}</a>
        @endforeach
    </div>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-8">
    @foreach ([
        ['Onboarded businesses', number_format($k['businesses']), $k['paid'].' on paid plans', 'text-slate-900'],
        ['Redemptions', number_format($k['redemptions']), 'all time', 'text-emerald-600'],
        ['Shoppers reached', number_format($k['customers']), 'distinct customers', 'text-sky-600'],
        ['Savings delivered', '£'.number_format($k['savings_delivered']), 'to shoppers (estimated)', 'text-violet-600'],
        ['Messages sent', number_format($k['messages_sent']), 'email + SMS + push', 'text-amber-600'],
        ['Avg per business', $k['businesses'] ? number_format($k['redemptions'] / max(1, $k['businesses']), 1) : '0', 'redemptions / business', 'text-slate-900'],
    ] as [$label, $value, $sub, $accent])
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="text-2xl sm:text-3xl font-extrabold {{ $accent }}">{{ $value }}</div>
            <div class="text-sm font-semibold text-slate-700 mt-1">{{ $label }}</div>
            <div class="text-xs text-slate-400">{{ $sub }}</div>
        </div>
    @endforeach
</div>

{{-- Redemptions trend --}}
<div class="rounded-2xl border border-slate-200 bg-white p-6 mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-lg text-slate-900">Redemptions over the last {{ $days }} days</h2>
        <span class="text-sm font-bold text-emerald-600">{{ number_format($busiestTotal) }} in period</span>
    </div>
    @if ($busiestTotal)
        @include('reports._trend', ['series' => $report['redemptions_series'], 'color' => '#059669', 'id' => 'platform-redemptions', 'height' => 110])
        <div class="flex justify-between text-xs text-slate-400 mt-2">
            <span>{{ $report['redemptions_series'][0]['label'] ?? '' }}</span>
            <span>{{ end($report['redemptions_series'])['label'] ?? '' }}</span>
        </div>
    @else
        <p class="text-center text-slate-400 text-sm py-10">No redemptions in this period yet.</p>
    @endif
</div>

{{-- Channel performance --}}
<h2 class="font-bold text-lg text-slate-900 mb-3">Messaging reach</h2>
<div class="grid sm:grid-cols-3 gap-4 mb-4">
    @foreach ($report['channels'] as $key => $ch)
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $ch['color'] }}"></span>
                <span class="font-bold text-slate-900">{{ $ch['label'] }}</span>
                <span class="ml-auto px-2 py-0.5 rounded-full text-[10px] font-bold {{ ($ch['measured'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ ($ch['measured'] ?? false) ? 'Measured' : 'Estimated' }}
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-900">{{ number_format($ch['sent']) }}</div>
            <div class="text-xs text-slate-400 mb-3">messages sent across {{ $ch['campaigns'] }} campaign{{ $ch['campaigns'] === 1 ? '' : 's' }}</div>
            <dl class="text-sm space-y-1.5">
                <div class="flex justify-between"><dt class="text-slate-500">Est. opens</dt><dd class="font-semibold text-slate-800">{{ number_format($ch['est_opens']) }} <span class="text-slate-400 font-normal">({{ $ch['open_rate'] }}%)</span></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Est. clicks</dt><dd class="font-semibold text-slate-800">{{ number_format($ch['est_clicks']) }} <span class="text-slate-400 font-normal">({{ $ch['click_rate'] }}%)</span></dd></div>
            </dl>
        </div>
    @endforeach
</div>
@php $anyMeasured = collect($report['channels'])->contains(fn ($c) => $c['measured'] ?? false); @endphp
<p class="text-xs text-slate-400 mb-8">
    @if ($anyMeasured)Channels marked "Measured" use real open/click tracking; "Estimated" ones use industry benchmarks until they have tracked sends.@else Engagement figures are indicative industry benchmarks until live open/click tracking is connected.@endif
    Money figures are estimates based on offer value.
</p>

{{-- Shopper-facing report preview --}}
<div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 mb-8 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h3 class="font-bold text-slate-900">Shopper report</h3>
        <p class="text-sm text-slate-600 mt-0.5">Preview the "Your locolie" savings summary customers see - the kind of report we link from their emails.</p>
    </div>
    <a href="{{ route('customer.report.entry') }}" target="_blank" class="rounded-lg bg-emerald-600 text-white text-sm font-semibold px-4 py-2.5 hover:bg-emerald-700">Preview shopper report</a>
</div>

<p class="text-xs text-slate-400">Generated {{ $report['generated_at']->format('j M Y, H:i') }}.</p>
@endsection
