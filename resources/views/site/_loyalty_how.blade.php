{{--
  Reusable "How loyalty works" explainer.

  Included on the public For-Business page and inside the retailer-facing
  loyalty editor (business.loyalty). Uses the shared brand classes from
  resources/css/app.css (emerald / ink / muted / hair / rounded-card /
  emerald-soft), which both layouts load, so it looks at home in either place.

  Optional vars:
    $heading  - section eyebrow (default "How loyalty works")
    $title    - big headline
    $intro    - lead paragraph
    $compact  - true = tighter spacing for the in-app editor (no big section padding)
--}}
@php
    $heading = $heading ?? 'How loyalty works';
    $title   = $title   ?? 'Free for every shop. Here is how it runs.';
    $intro   = $intro   ?? 'Loyalty is built into locolie and free on every plan, including the Free plan. No stamp cards to print, no extra app for your customers, no monthly fee. You set the rules, locolie does the counting.';
    $compact = $compact ?? false;

    $steps = [
        [
            'n'    => '1',
            'title'=> 'Customer joins with a scan',
            'body' => 'A shopper scans your locolie Badge of Honour in the window or at the till. No app download, no sign-up form - they are in.',
            'icon' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3M21 21v.01M17 21h.01M21 17h.01"/>',
        ],
        [
            'n'    => '2',
            'title'=> 'Visits and spend are tracked',
            'body' => 'Every time you verify their code at the till it counts as a visit, and any spend you key in adds up too. It is all tied to their email, so it follows them on every visit.',
            'icon' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
        ],
        [
            'n'    => '3',
            'title'=> 'They hit a threshold',
            'body' => 'When a customer reaches a target you set - "5 visits" or "spend £50" - locolie spots it automatically. They watch their progress fill up in the app as they get closer.',
            'icon' => '<path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>',
        ],
        [
            'n'    => '4',
            'title'=> 'A reward is earned',
            'body' => 'The reward you chose - a free coffee, 10% off, a gift - lands in their app as a one-time code, ready to use. If the rule repeats, the stamp card resets and they start collecting again.',
            'icon' => '<path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
        ],
        [
            'n'    => '5',
            'title'=> 'They redeem in store',
            'body' => 'Next visit, they show the reward code and you scan it to redeem. It drops into your "Rewards to give" list, you hand it over, and tap redeemed. Done.',
            'icon' => '<path d="M20 6 9 17l-5-5"/>',
        ],
    ];
@endphp

<div class="{{ $compact ? '' : 'mx-auto max-w-2xl text-center' }}">
    <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">{{ $heading }}</h2>
    <p class="mt-3 {{ $compact ? 'text-2xl' : 'text-3xl sm:text-4xl' }} font-extrabold tracking-tight text-balance">{{ $title }}</p>
    <p class="mt-4 text-muted {{ $compact ? '' : 'text-lg' }}">{{ $intro }}</p>

    {{-- Free, forever badge --}}
    <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-emerald/30 bg-emerald-soft px-4 py-2 text-sm font-bold text-emerald">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        Loyalty is free, forever - on every plan
    </div>
</div>

{{-- The five steps --}}
<ol class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-5 {{ $compact ? 'lg:gap-3' : '' }}">
    @foreach ($steps as $i => $step)
        <li class="relative rounded-card border border-hair bg-white p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-ink text-sm font-extrabold text-white">{{ $step['n'] }}</div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-soft text-emerald">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $step['icon'] !!}</svg>
                </div>
            </div>
            <h3 class="mt-4 font-bold leading-snug">{{ $step['title'] }}</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $step['body'] }}</p>
            {{-- connector arrow on large screens --}}
            @unless ($loop->last)
                <svg class="absolute -right-4 top-12 hidden h-5 w-5 text-hair lg:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            @endunless
        </li>
    @endforeach
</ol>
