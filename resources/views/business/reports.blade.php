@extends('business.layout')
@section('title', 'Reports')

@section('content')
@php
    $k = $report['kpis'];
    $reach = $report['reach'];
    $ch = $report['channels'];
    $brand = $business->brandColor();

    $rangeLabel = ['7' => 'Last 7 days', '14' => 'Last 14 days', '30' => 'Last 30 days', '90' => 'Last 90 days'][(string) $days] ?? "Last {$days} days";
    $ranges = [7 => '7d', 14 => '14d', 30 => '30d', 90 => '90d'];

    $redemptionsTotal = collect($report['redemptions_series'])->sum('value');
    $newCustomersTotal = collect($report['customers_series'])->sum('value');

    // Busiest day insight
    $vp = $report['visit_pattern'];
    $vpMax = max(1, ...array_values($vp));
    $busiestDay = array_sum($vp) > 0 ? array_keys($vp, max($vp))[0] : null;
    $dayNames = ['Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday'];

    $fmtMoney = fn ($v) => '£' . number_format((float) $v, ((float) $v == floor((float) $v)) ? 0 : 2);
@endphp

<div class="space-y-6">

    {{-- 1. Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-12 h-12 rounded-xl border border-slate-200 flex items-center justify-center overflow-hidden shrink-0" style="background: {{ $brand }}1a">
                @if ($business->logoUrl())
                    <img src="{{ $business->logoUrl() }}" alt="{{ $business->name }}" class="w-full h-full object-contain">
                @else
                    <span class="font-extrabold text-base" style="color: {{ $brand }}">{{ $business->brandInitials() }}</span>
                @endif
            </div>
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Reports</h1>
                <p class="text-slate-500 mt-0.5 text-sm truncate">How {{ $business->name }} is performing · {{ $business->planConfig()['label'] }} plan</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Date range segmented control --}}
            <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 print:hidden">
                @foreach ($ranges as $value => $label)
                    <a href="?days={{ $value }}"
                       class="px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ (int) $days === $value ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">{{ $label }}</a>
                @endforeach
            </div>
            <a href="{{ route('business.reports.export') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 hover:border-slate-300 transition print:hidden">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                Export CSV
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 hover:border-slate-300 transition print:hidden">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                Print
            </button>
        </div>
    </div>

    {{-- 2. Hero KPI grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Customers</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($k['customers']) }}</div>
            <div class="text-xs font-semibold mt-1 {{ $k['new_customers'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                {{ $k['new_customers'] > 0 ? '+' . number_format($k['new_customers']) . ' new in period' : 'No new customers in period' }}
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Redemptions</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($k['redemptions']) }}</div>
            <div class="text-xs font-semibold text-slate-400 mt-1">{{ number_format($k['pending']) }} pending</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Revenue influenced</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $fmtMoney($k['revenue_influenced']) }}</div>
            <div class="text-xs font-semibold text-slate-400 mt-1">Estimated</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Savings delivered</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $fmtMoney($k['savings_delivered']) }}</div>
            <div class="text-xs font-semibold text-slate-400 mt-1">Estimated · to your customers</div>
        </div>
    </div>

    {{-- Insight strip --}}
    @if ($k['customers'] > 0)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 px-5 py-4 text-sm text-emerald-900">
            <span class="font-semibold">In brief:</span>
            @if ($k['repeat_customers'] > 0)
                {{ $k['repeat_rate'] }}% of your customers came back for more than one offer.
            @endif
            @if ($busiestDay)
                Your busiest day is {{ $dayNames[$busiestDay] }}.
            @endif
            {{ $reach['email_rate'] }}% of your customers opted in to marketing - that's a list you own.
        </div>
    @endif

    {{-- 3. Trends row --}}
    <div class="grid lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-bold text-lg text-slate-900">Redemptions over time</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $rangeLabel }}</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-extrabold text-emerald-600">{{ number_format($redemptionsTotal) }}</div>
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">total</div>
                </div>
            </div>
            <div class="mt-4">
                @if ($redemptionsTotal > 0)
                    @include('reports._trend', ['series' => $report['redemptions_series'], 'color' => '#059669', 'id' => 'redemptions', 'height' => 90])
                @else
                    <div class="h-[90px] flex items-center justify-center text-sm text-slate-400 rounded-xl bg-slate-50">No redemptions in this period yet.</div>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-bold text-lg text-slate-900">New customers</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $rangeLabel }}</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-extrabold" style="color:#0284c7">{{ number_format($newCustomersTotal) }}</div>
                    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">total</div>
                </div>
            </div>
            <div class="mt-4">
                @if ($newCustomersTotal > 0)
                    @include('reports._trend', ['series' => $report['customers_series'], 'color' => '#0284c7', 'id' => 'customers', 'height' => 90])
                @else
                    <div class="h-[90px] flex items-center justify-center text-sm text-slate-400 rounded-xl bg-slate-50">No new customers in this period yet.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- 4. Marketing reach --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="font-bold text-lg text-slate-900">Your marketing reach</h2>
                <p class="text-sm text-slate-500 mt-1 max-w-xl">This is the first-party customer list you own - the same advantage the big chains have always had. Reach them directly, any time.</p>
            </div>
            <a href="{{ route('business.messaging') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 text-white text-sm font-bold px-5 py-2.5 hover:bg-emerald-700 transition shrink-0 print:hidden">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Message these customers
            </a>
        </div>

        <div class="grid sm:grid-cols-3 gap-4 mt-5">
            <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Total customers</div>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($reach['total_customers']) }}</div>
                <div class="text-xs text-slate-400 mt-0.5">captured from redemptions</div>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Email opted-in</span>
                    <span class="text-xs font-bold" style="color:#0284c7">{{ $reach['email_rate'] }}%</span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($reach['email_optin']) }}</div>
                <div class="mt-2 h-1.5 w-full rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full rounded-full" style="width: {{ min(100, $reach['email_rate']) }}%; background:#0284c7"></div>
                </div>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                <div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">SMS reachable</div>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($reach['sms']) }}</div>
                <div class="text-xs text-slate-400 mt-0.5">opted-in mobile numbers</div>
            </div>
        </div>
        <p class="text-[11px] text-slate-400 mt-3">Push notifications reach app users across the whole locolie platform, not only your own list.</p>
    </div>

    {{-- 5. Channel performance --}}
    <div>
        <div class="flex items-end justify-between gap-3 mb-3">
            <h2 class="font-bold text-lg text-slate-900">Channel performance</h2>
        </div>
        <div class="grid sm:grid-cols-3 gap-4">
            @foreach (['email' => 'opens', 'sms' => 'opens', 'push' => 'opens'] as $key => $_)
                @php $c = $ch[$key]; @endphp
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $c['color'] }}"></span>
                        <span class="font-bold text-slate-900">{{ $c['label'] }}</span>
                        <span class="ml-auto text-xs text-slate-400">{{ number_format($c['campaigns']) }} {{ \Illuminate\Support\Str::plural('campaign', $c['campaigns']) }}</span>
                    </div>
                    @if ($c['sent'] > 0)
                        <div class="mt-4">
                            <div class="text-2xl font-extrabold text-slate-900">{{ number_format($c['sent']) }}</div>
                            <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">messages sent</div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                            <div>
                                <div class="font-bold text-slate-800">{{ number_format($c['est_opens']) }}</div>
                                <div class="text-xs text-slate-400">est. opens · {{ $c['open_rate'] }}%</div>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800">{{ number_format($c['est_clicks']) }}</div>
                                <div class="text-xs text-slate-400">est. clicks · {{ $c['click_rate'] }}%</div>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 py-4 text-sm text-slate-400">No {{ strtolower($c['label']) }} campaigns sent yet.</div>
                    @endif
                </div>
            @endforeach
        </div>
        <p class="text-[11px] text-slate-400 mt-3">Open and click figures are indicative benchmarks until live tracking is connected - they are not measured yet.</p>
    </div>

    {{-- 6. Top offers --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="font-bold text-lg text-slate-900 mb-4">Top offers</h2>
        @if (count($report['top_offers']) && collect($report['top_offers'])->sum('issued') > 0)
            <div class="overflow-x-auto -mx-2">
                <table class="w-full text-sm min-w-[520px]">
                    <thead class="text-left text-slate-400 text-xs uppercase tracking-wider">
                        <tr class="border-b border-slate-100">
                            <th class="px-2 py-2 font-medium">Offer</th>
                            <th class="px-2 py-2 font-medium text-right">Redemptions</th>
                            <th class="px-2 py-2 font-medium">Redemption rate</th>
                            <th class="px-2 py-2 font-medium text-right">Est. value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['top_offers'] as $o)
                            <tr class="border-b border-slate-50 last:border-0">
                                <td class="px-2 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-slate-800">{{ $o['title'] }}</span>
                                        @if ($o['badge'])
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 shrink-0">{{ $o['badge'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 py-3 text-right font-semibold text-slate-800">{{ number_format($o['redemptions']) }}</td>
                                <td class="px-2 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-20 rounded-full bg-slate-200 overflow-hidden shrink-0">
                                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, $o['rate']) }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500 w-9">{{ $o['rate'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-2 py-3 text-right font-semibold text-slate-800">{{ $fmtMoney($o['value']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-slate-400 text-sm">No offers have been issued or redeemed yet. Once shoppers start claiming your offers, your best performers will appear here.</div>
        @endif
    </div>

    {{-- 7. Busiest days --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-end justify-between gap-2 mb-4">
            <h2 class="font-bold text-lg text-slate-900">Busiest days</h2>
            @if ($busiestDay)
                <p class="text-sm text-slate-500">Your busiest day is <span class="font-semibold text-slate-700">{{ $dayNames[$busiestDay] }}</span>.</p>
            @endif
        </div>
        @if (array_sum($vp) > 0)
            @include('reports._bars', ['bars' => $vp, 'color' => '#7c3aed', 'height' => 110])
        @else
            <div class="h-[110px] flex items-center justify-center text-sm text-slate-400 rounded-xl bg-slate-50">No redemptions yet to show a weekly pattern.</div>
        @endif
    </div>

    {{-- 8. Recent customers --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-lg text-slate-900">Recent customers</h2>
            <a href="{{ route('business.dashboard') }}" class="text-sm font-semibold text-emerald-700 hover:underline print:hidden">Full customer list</a>
        </div>
        @if (count($report['recent_customers']))
            <div class="overflow-x-auto -mx-2">
                <table class="w-full text-sm min-w-[480px]">
                    <thead class="text-left text-slate-400 text-xs uppercase tracking-wider">
                        <tr class="border-b border-slate-100">
                            <th class="px-2 py-2 font-medium">Customer</th>
                            <th class="px-2 py-2 font-medium">Email</th>
                            <th class="px-2 py-2 font-medium text-right">Visits</th>
                            <th class="px-2 py-2 font-medium">Marketing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['recent_customers'] as $c)
                            <tr class="border-b border-slate-50 last:border-0">
                                <td class="px-2 py-2.5 font-medium text-slate-800">{{ $c['name'] }}</td>
                                <td class="px-2 py-2.5 text-slate-500">{{ $c['email'] }}</td>
                                <td class="px-2 py-2.5 text-right text-slate-500">{{ number_format($c['visits']) }}</td>
                                <td class="px-2 py-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $c['opt_in'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $c['opt_in'] ? 'Opted in' : 'Not opted in' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-slate-400 text-sm">No customers captured yet. As shoppers redeem your offers, they'll appear here - ready to market to.</div>
        @endif
    </div>

    {{-- Footer --}}
    <p class="text-xs text-slate-400 text-center pt-2">Generated {{ $report['generated_at']->format('j M Y, H:i') }}. Money figures are estimates based on offer value.</p>
</div>
@endsection
