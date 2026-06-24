@extends('site.layout')
@section('title', 'For Business - locolie')
@section('meta_description', 'List your indie business on locolie for free. Get found by local shoppers in Newcastle NE1, drive footfall with offers, and message customers by email, SMS and push. Upgrade from £19/mo.')

@section('content')

{{-- ============================================================ HERO --}}
<section class="relative overflow-hidden hero-grid">
    <div class="pointer-events-none absolute -top-24 right-0 h-[380px] w-[600px] rounded-full bg-emerald-soft/60 blur-3xl"></div>
    <div class="relative mx-auto max-w-4xl px-5 pb-14 pt-32 text-center sm:px-6 lg:pt-40">
        <span class="inline-flex items-center gap-2 rounded-full border border-hair bg-white px-3.5 py-1.5 text-xs font-semibold text-emerald shadow-sm">For independent businesses</span>
        <h1 class="mt-6 text-4xl font-extrabold leading-[1.05] tracking-tight text-balance sm:text-5xl lg:text-6xl">
            Get found by locals who want to <span class="text-emerald">back the indies</span>.
        </h1>
        <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-muted">
            locolie puts your shop in front of nearby shoppers hunting for independents, not chains. List free, post offers that bring real footfall through the door, and stay in touch by email, SMS and push. Only pay when you want more reach.
        </p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="/business/login" class="inline-flex items-center justify-center gap-2 rounded-full bg-ink px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-ink/10 transition hover:bg-emerald">
                List my shop free
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <a href="/app?as=business" class="inline-flex items-center justify-center rounded-full border border-hair bg-white px-6 py-3.5 text-sm font-semibold text-ink transition hover:border-ink">
                See the business view
            </a>
        </div>
    </div>
</section>

{{-- ============================================================ VALUE PROPS --}}
<section class="py-20 sm:py-24">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="grid gap-6 md:grid-cols-3">
            @php
                $values = [
                    ['title' => 'Get discovered by locals', 'body' => 'Show up on the map and in the feed when nearby shoppers are browsing for independents and deciding where to go.',
                     'icon' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/>'],
                    ['title' => 'Drive real footfall', 'body' => 'Post offers shoppers redeem in store with a quick QR scan at the till, so you can see exactly how much footfall each one brings in.',
                     'icon' => '<path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82Z"/><line x1="7" y1="7" x2="7.01" y2="7"/>'],
                    ['title' => 'Free to start, pay to grow', 'body' => 'Your listing is free forever. Upgrade for featured placement, push notifications and email campaigns whenever you\'re ready for more.',
                     'icon' => '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>'],
                ];
            @endphp
            @foreach ($values as $v)
                <div class="rounded-card border border-hair bg-white p-7">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-soft text-emerald">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $v['icon'] !!}</svg>
                    </div>
                    <h3 class="mt-5 text-lg font-bold">{{ $v['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $v['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ OWN YOUR CUSTOMERS --}}
<section class="border-y border-hair bg-ink py-20 text-white sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-soft">The unfair advantage</h2>
                <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Finally, own your customers.</p>
                <p class="mt-5 text-lg leading-relaxed text-white/70">
                    Every offer you redeem adds a real customer - their name, email and how often they pop in - straight into <span class="font-semibold text-white">your own list</span>. It's the kind of loyalty data the big chains built whole empires on, and that independents have never had.
                </p>
                <p class="mt-4 text-lg leading-relaxed text-white/70">
                    Stop renting reach from Facebook and Deliveroo. Bring your regulars back with your own email &amp; push offers, and keep that relationship for good.
                </p>
                <a href="/business/login" class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold text-ink transition hover:bg-emerald hover:text-white">
                    Start building your list, free
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            <div class="rounded-card border border-white/10 bg-white/[0.04] p-6">
                @php
                    $rows = [['Sarah J.', '4 visits'], ['Mark T.', '2 visits'], ['Priya K.', '6 visits'], ['Dan W.', '1 visit']];
                @endphp
                <div class="flex items-center justify-between"><div class="font-bold">Your customers</div><span class="rounded-full bg-emerald/20 px-3 py-1 text-xs font-bold text-emerald-soft">128 captured</span></div>
                <div class="mt-4 divide-y divide-white/10">
                    @foreach ($rows as $r)
                        <div class="flex items-center justify-between py-2.5">
                            <div class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-xs font-bold">{{ substr($r[0],0,1) }}</span><span class="text-sm font-medium">{{ $r[0] }}</span></div>
                            <span class="text-xs text-white/50">{{ $r[1] }}</span>
                        </div>
                    @endforeach
                </div>
                <button class="mt-4 w-full rounded-xl bg-emerald py-2.5 text-sm font-bold text-white">✉ Email these customers</button>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ MARKETING TOOLKIT --}}
<section class="py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">Your marketing toolkit</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Reach your customers, your way.</p>
            <p class="mt-4 text-muted">Redeem an offer and that shopper joins your list. Bring them back with built-in email, SMS and push. No separate Mailchimp, no agency, no ad spend.</p>
        </div>
        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $tools = [
                    ['Email campaigns', 'Send branded offers and newsletters to your opted-in customers in a couple of taps.',
                     '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/>'],
                    ['SMS text blasts', 'Quiet Tuesday or a table just freed up? Text your regulars and fill it. Texts get read in minutes.',
                     '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
                    ['Push notifications', 'Ping nearby shoppers and your followers the moment you post a fresh offer (Premium).',
                     '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>'],
                    ['Win-back automations', 'Automatic "we miss you" and birthday offers that win lapsed customers back on autopilot.',
                     '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>'],
                ];
            @endphp
            @foreach ($tools as $t)
                <div class="card-hover rounded-card border border-hair bg-white p-7">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-soft text-emerald">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $t[2] !!}</svg>
                    </div>
                    <h3 class="mt-5 text-lg font-bold">{{ $t[0] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $t[1] }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-8 text-center text-sm text-muted">All from one dashboard, built on a customer list you actually own.</p>
    </div>
</section>

{{-- ============================================================ PRICING --}}
<section id="pricing" class="border-y border-hair bg-[#f5f5f5] py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">Pricing</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Simple plans. Cancel anytime.</p>
            <p class="mt-4 text-muted">Start free and upgrade whenever you want more reach. No contracts, no setup fees, no catch.</p>
        </div>
        @include('site._pricing', ['big' => true])
    </div>
</section>

{{-- ============================================================ ONBOARDING --}}
<section class="py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">Getting started</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Live in under 10 minutes.</p>
        </div>
        <div class="mx-auto mt-14 grid max-w-4xl gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $onboard = [
                    ['n' => '1', 'title' => 'Claim your listing', 'body' => 'Find your shop (or add it) and verify you\'re the owner.'],
                    ['n' => '2', 'title' => 'Add your details', 'body' => 'Pop in photos, opening hours and a description that shows you off.'],
                    ['n' => '3', 'title' => 'Post an offer', 'body' => 'Create your first deal and we\'ll generate the redemption QR for you.'],
                    ['n' => '4', 'title' => 'Go live', 'body' => 'You\'re on the map. Print the window sticker and welcome the locals in.'],
                ];
            @endphp
            @foreach ($onboard as $o)
                <div class="relative">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-ink text-base font-extrabold text-white">{{ $o['n'] }}</div>
                    <h3 class="mt-4 font-bold">{{ $o['title'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $o['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ FAQ --}}
<section class="border-t border-hair py-20 sm:py-28">
    <div class="mx-auto max-w-3xl px-5 sm:px-6">
        <div class="text-center">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">FAQ</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Questions, answered.</p>
        </div>
        <div class="mt-12 space-y-3" x-data="{ open: 0 }">
            @php
                $faqs = [
                    ['q' => 'Is it really free to list?', 'a' => 'Yes, and free forever. On the Free plan you show up in search and on the map, post offers, and redeem them with a QR scan at the till. You only pay if you choose a paid plan for extra reach.'],
                    ['q' => 'Who can join locolie?', 'a' => 'locolie is just for independent UK businesses. We don\'t list the national chains, and that\'s the whole point. We\'re backing local, starting in Newcastle NE1.'],
                    ['q' => 'How do offer redemptions work?', 'a' => 'You post an offer, a shopper reveals the code in the app, and at the till you scan their QR (or they scan your window sticker). Every redemption is logged, so you can see exactly what footfall each offer drove.'],
                    ['q' => 'What\'s the difference between Featured and Premium?', 'a' => 'Featured (£19/mo) gets you featured-rail placement, a "Sponsored" badge, priority in search and on the map, and a monthly email feature. Premium (£49/mo) adds top placement, push notifications to nearby shoppers, unlimited email campaigns and an analytics dashboard.'],
                    ['q' => 'Can I cancel or change plan anytime?', 'a' => 'Of course. There are no contracts. Upgrade, downgrade or cancel whenever you like, and changes take effect from your next billing cycle.'],
                    ['q' => 'Do I really get to keep my customer data?', 'a' => 'Yes, and that\'s the whole point. Every shopper who redeems an offer joins your own customer list (name, email, visit count), which you can export anytime and market to by email and push. Customers opt in to marketing and it\'s all GDPR-compliant, but the relationship is yours, not ours and not a chain\'s.'],
                ];
            @endphp
            @push('head')
            <script type="application/ld+json">
            {"@@context":"https://schema.org","@@type":"FAQPage","mainEntity":[@foreach($faqs as $i => $faq){"@@type":"Question","name":{!! json_encode($faq['q']) !!},"acceptedAnswer":{"@@type":"Answer","text":{!! json_encode($faq['a']) !!}}}@if(!$loop->last),@endif @endforeach]}
            </script>
            @endpush
            @foreach ($faqs as $i => $faq)
                <div class="overflow-hidden rounded-card border border-hair bg-white">
                    <button @click="open === {{ $i }} ? open = null : open = {{ $i }}" class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left">
                        <span class="font-semibold">{{ $faq['q'] }}</span>
                        <svg :class="open === {{ $i }} ? 'rotate-180' : ''" class="h-5 w-5 flex-shrink-0 text-muted transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak x-transition>
                        <p class="px-6 pb-5 text-sm leading-relaxed text-muted">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ CTA --}}
<section class="border-t border-hair bg-ink py-20 text-center text-white sm:py-24">
    <div class="mx-auto max-w-2xl px-5 sm:px-6">
        <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Put your shop on the map.</h2>
        <p class="mt-4 text-white/70">Join the independents already backing local on locolie in Newcastle NE1. Free to start.</p>
        <a href="/business/login" class="mt-8 inline-flex items-center justify-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-semibold text-ink transition hover:bg-emerald hover:text-white">
            List my shop free
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</section>

@endsection
