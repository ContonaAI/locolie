@extends('site.layout')
@section('title', 'How loyalty works - locolie')
@section('meta_description', 'Loyalty is built into locolie and free for every shop on every plan. See how customers join with a scan, earn rewards by visits or spend, and redeem in store - no stamp cards, no extra app.')

@section('content')

{{-- ============================================================ HERO --}}
<section class="relative overflow-hidden hero-grid">
    <div class="pointer-events-none absolute -top-24 right-0 h-[380px] w-[600px] rounded-full bg-emerald-soft/60 blur-3xl"></div>
    <div class="relative mx-auto max-w-4xl px-5 pb-12 pt-32 text-center sm:px-6 lg:pt-40">
        <span class="inline-flex items-center gap-2 rounded-full border border-hair bg-white px-3.5 py-1.5 text-xs font-semibold text-emerald shadow-sm">Loyalty, built in</span>
        <h1 class="mt-6 text-4xl font-extrabold leading-[1.05] tracking-tight text-balance sm:text-5xl lg:text-6xl">
            Turn first-timers into <span class="text-emerald">regulars</span>, free.
        </h1>
        <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-muted">
            Loyalty comes free with every locolie listing, including the Free plan. Set your own rules, and locolie counts visits and spend for you. No plastic stamp cards, no separate app for your customers, no monthly fee.
        </p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="/business/join" class="inline-flex items-center justify-center gap-2 rounded-full bg-ink px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-ink/10 transition hover:bg-emerald">
                Set up loyalty free
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <a href="/for-business" class="inline-flex items-center justify-center rounded-full border border-hair bg-white px-6 py-3.5 text-sm font-semibold text-ink transition hover:border-ink">
                See everything locolie does
            </a>
        </div>
    </div>
</section>

{{-- ============================================================ HOW IT WORKS --}}
<section class="py-16 sm:py-20">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        @include('site._loyalty_how')
    </div>
</section>

{{-- ============================================================ WHAT IT LOOKS LIKE --}}
<section class="border-t border-hair bg-[#fafafa] py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">In the customer's app</h2>
                <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Progress they can actually see.</p>
                <p class="mt-4 text-lg text-muted">Shoppers watch their stamp card fill up and their spend tick towards a reward, right in the locolie app. The pull of "two more to go" is what brings them back to you instead of the chain down the road.</p>
                <ul class="mt-6 space-y-3 text-sm">
                    @foreach ([
                        'Your rules: "scan 5 times, get one free" or "spend £50, unlock 10% off"',
                        'Counts itself - a visit lands every time you verify a code at the till',
                        'Rewards arrive as a code you scan to redeem in store',
                        'Free on every plan, including Free - no add-on, no upsell',
                    ] as $point)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            <span class="text-ink/80">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="space-y-4">
                {{-- Example: stamp card --}}
                <div class="rounded-card border border-hair bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-emerald">Coffee Club</div>
                            <div class="mt-1 font-bold text-ink">Buy 5, get 1 free</div>
                        </div>
                        <span class="rounded-full bg-emerald-soft px-3 py-1 text-xs font-bold text-emerald">3 / 5</span>
                    </div>
                    <div class="mt-4 flex gap-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="flex h-9 flex-1 items-center justify-center rounded-lg {{ $i <= 3 ? 'bg-emerald text-white' : 'border border-dashed border-hair text-hair' }}">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                        @endfor
                    </div>
                    <p class="mt-3 text-xs text-muted">2 more visits for a free coffee.</p>
                </div>
                {{-- Example: spend target --}}
                <div class="rounded-card border border-hair bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="font-bold text-ink">Spend £50, unlock 10% off</div>
                        <span class="rounded-full bg-emerald-soft px-3 py-1 text-xs font-bold text-emerald">£32 / £50</span>
                    </div>
                    <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-[#eef1f4]">
                        <div class="h-full rounded-full bg-emerald" style="width:64%"></div>
                    </div>
                    <p class="mt-3 text-xs text-muted">£18 more to unlock the reward.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ CTA --}}
<section class="border-t border-hair bg-ink py-20 text-center text-white sm:py-24">
    <div class="mx-auto max-w-2xl px-5 sm:px-6">
        <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Loyalty is free. Switch it on.</h2>
        <p class="mt-4 text-white/70">Every locolie shop in {{ $llPlace }} can run loyalty at no extra cost. Set your first rule in a couple of minutes.</p>
        <a href="/business/join" class="mt-8 inline-flex items-center justify-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-semibold text-ink transition hover:bg-emerald hover:text-white">
            Set up loyalty free
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</section>

@endsection
