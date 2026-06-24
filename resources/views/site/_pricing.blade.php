{{-- Reusable 3-card pricing grid. Reads from Business::PLANS.
     Props: $big (bool) - larger spacing for the dedicated For-Business page. --}}
@php
    $big = $big ?? false;
    $plans = \App\Models\Business::PLANS;
    $blurbs = [
        'free' => 'Everything you need to get found and start posting offers.',
        'featured' => 'Stand out in the feed and search, plus a monthly email feature.',
        'premium' => 'Maximum reach: top placement, push notifications and analytics.',
    ];
@endphp
<div class="mx-auto mt-12 grid max-w-5xl gap-6 lg:grid-cols-3 {{ $big ? 'lg:gap-7' : '' }}">
    @foreach ($plans as $key => $plan)
        @php($highlight = $key === 'featured')
        <div class="relative flex flex-col rounded-card border bg-white {{ $big ? 'p-8' : 'p-7' }} {{ $highlight ? 'border-emerald shadow-xl shadow-emerald/10 lg:-translate-y-2' : 'border-hair' }}">
            @if ($highlight)
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-emerald px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white">Most popular</span>
            @endif
            <h3 class="text-base font-bold">{{ $plan['label'] }}</h3>
            <p class="mt-1 text-sm text-muted">{{ $blurbs[$key] }}</p>
            <div class="mt-5 flex items-baseline gap-1">
                <span class="text-4xl font-extrabold tracking-tight">£{{ $plan['price'] }}</span>
                <span class="text-sm font-medium text-muted">/ month</span>
            </div>
            <ul class="mt-6 flex-1 space-y-3">
                @foreach ($plan['perks'] as $perk)
                    <li class="flex items-start gap-2.5 text-sm">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span class="text-ink/80">{!! $perk !!}</span>
                    </li>
                @endforeach
            </ul>
            <a href="/business/login"
               class="mt-7 inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-semibold transition
                      {{ $highlight ? 'bg-emerald text-white hover:bg-ink' : 'border border-hair text-ink hover:border-ink' }}">
                {{ $key === 'free' ? 'List my shop free' : 'Choose '.$plan['label'] }}
            </a>
        </div>
    @endforeach
</div>
