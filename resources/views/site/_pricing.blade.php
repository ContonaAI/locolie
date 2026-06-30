{{-- Reusable pricing grid. Reads Business::plans() so prices stay config-driven
     (config/locolie.php → locolie.pricing). Shows the free = loyalty only /
     paid = full marketing split, plus each tier's monthly send thresholds.
     Props: $big (bool) - larger spacing for the dedicated For-Business page. --}}
@php
    $big = $big ?? false;
    $plans = \App\Models\Business::plans();
    $blurbs = [
        'free' => 'Get found and run your loyalty scheme. Listing free, forever.',
        'featured' => 'Stand out in search, plus email &amp; SMS to your own customers.',
        'premium' => 'Maximum reach: top placement, push, analytics and bigger send limits.',
        'enterprise' => 'For chains and multi-site indies: unlimited sends and a success manager.',
    ];
@endphp

{{-- Free = loyalty only / paid = marketing - made explicit up top --}}
<div class="mx-auto mt-8 max-w-5xl rounded-2xl border border-hair bg-[#fafafa] px-5 py-4 text-center text-sm text-muted">
    <span class="font-semibold text-ink">Free</span> covers your listing, offers and loyalty scheme.
    <span class="font-semibold text-ink">Paid plans</span> add full marketing - email, SMS &amp; push campaigns to your own customers, within a monthly send allowance.
</div>

<div class="mx-auto mt-8 grid max-w-6xl gap-6 sm:grid-cols-2 lg:grid-cols-4 {{ $big ? 'lg:gap-7' : '' }}">
    @foreach ($plans as $key => $plan)
        @php($highlight = $key === 'featured')
        <div class="relative flex flex-col rounded-card border bg-white {{ $big ? 'p-7' : 'p-6' }} {{ $highlight ? 'border-emerald shadow-xl shadow-emerald/10 lg:-translate-y-2' : 'border-hair' }}">
            @if ($highlight)
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-emerald px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white">Most popular</span>
            @endif
            <h3 class="text-base font-bold">{{ $plan['label'] }}</h3>
            <p class="mt-1 text-sm text-muted">{!! $blurbs[$key] !!}</p>
            <div class="mt-5 flex items-baseline gap-1">
                @if (is_null($plan['price']))
                    <span class="text-3xl font-extrabold tracking-tight">Custom</span>
                @else
                    <span class="text-4xl font-extrabold tracking-tight">£{{ $plan['price'] }}</span>
                    <span class="text-sm font-medium text-muted">/ month</span>
                @endif
            </div>

            {{-- Monthly send allowance --}}
            <div class="mt-4 rounded-xl bg-[#fafafa] px-3.5 py-2.5 text-xs">
                <div class="font-semibold uppercase tracking-wider text-muted">Monthly sends</div>
                @if (! ($plan['marketing'] ?? false))
                    <div class="mt-1 font-medium text-ink">Loyalty scheme only - no marketing sends</div>
                @elseif (($plan['sends']['email'] ?? 0) >= PHP_INT_MAX)
                    <div class="mt-1 font-medium text-emerald">Unlimited email, SMS &amp; push</div>
                @else
                    <div class="mt-1 font-medium text-ink">
                        {{ number_format($plan['sends']['email']) }} email · {{ number_format($plan['sends']['sms']) }} SMS @if (! empty($plan['sends']['push'])) · push @endif
                    </div>
                @endif
            </div>

            <ul class="mt-5 flex-1 space-y-3">
                @foreach ($plan['perks'] as $perk)
                    <li class="flex items-start gap-2.5 text-sm">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span class="text-ink/80">{!! $perk !!}</span>
                    </li>
                @endforeach
            </ul>
            <a href="{{ $key === 'enterprise' ? route('site.contact').'?topic=enterprise' : '/business/join' }}"
               class="mt-7 inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-semibold transition
                      {{ $highlight ? 'bg-emerald text-white hover:bg-ink' : 'border border-hair text-ink hover:border-ink' }}">
                @if ($key === 'free')
                    List my shop free
                @elseif ($key === 'enterprise')
                    Contact sales
                @else
                    Choose {{ $plan['label'] }}
                @endif
            </a>
        </div>
    @endforeach
</div>
