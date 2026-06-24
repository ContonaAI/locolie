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

{{-- ============================================================ REPLACE YOUR STACK --}}
<section class="border-t border-hair py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">One login, not five</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Bin the marketing stack.</p>
            <p class="mt-4 text-muted">Email usually means Mailchimp or Klaviyo. Texts mean Twilio. Push means OneSignal. Loyalty means yet another app, each with its own login and monthly bill. locolie does the lot, built right in.</p>
        </div>

        <div class="mt-14 grid items-center gap-6 lg:grid-cols-[1fr_auto_1fr] lg:gap-4">
            {{-- the old stack --}}
            <div class="rounded-card border border-hair bg-white p-7">
                <div class="text-sm font-bold uppercase tracking-wider text-muted">Your stack today</div>
                @php
                    $stack = [
                        ['Klaviyo / Mailchimp', 'Email', '£25+/mo'],
                        ['Twilio / TextMagic', 'SMS', '£20+/mo'],
                        ['OneSignal', 'Push', '£9+/mo'],
                        ['Loyalty app / stamp cards', 'Loyalty', '£30+/mo'],
                        ['Spreadsheets', 'Customer data', 'a headache'],
                    ];
                @endphp
                <ul class="mt-5 space-y-2.5">
                    @foreach ($stack as $s)
                        <li class="flex items-center justify-between gap-3 rounded-xl bg-[#f7f7f7] px-4 py-3">
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="h-4 w-4 flex-shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                                <span class="min-w-0"><span class="font-semibold text-ink line-through decoration-red-300/70">{{ $s[0] }}</span> <span class="text-xs text-muted">{{ $s[1] }}</span></span>
                            </span>
                            <span class="flex-shrink-0 text-xs font-semibold text-muted">{{ $s[2] }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-5 flex items-center justify-between border-t border-hair pt-4">
                    <span class="text-sm font-bold text-ink">Roughly</span>
                    <span class="text-lg font-extrabold text-ink">£84+/mo <span class="text-xs font-medium text-muted">&amp; 4 logins</span></span>
                </div>
            </div>

            {{-- arrow --}}
            <div class="flex items-center justify-center py-1">
                <div class="flex h-12 w-12 rotate-90 items-center justify-center rounded-full bg-emerald text-white shadow-lg shadow-emerald/20 lg:rotate-0">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </div>
            </div>

            {{-- locolie does it all --}}
            <div class="relative overflow-hidden rounded-card border-2 border-emerald bg-ink p-7 text-white">
                <div class="pointer-events-none absolute -top-16 -right-16 h-48 w-48 rounded-full bg-emerald/25 blur-2xl"></div>
                <div class="relative">
                    <div class="text-sm font-bold uppercase tracking-wider text-emerald-soft">With locolie</div>
                    @php $does = ['Email campaigns', 'SMS text blasts', 'Push notifications', 'Loyalty &amp; QR redemptions', 'A customer list you own']; @endphp
                    <ul class="mt-5 space-y-2.5">
                        @foreach ($does as $d)
                            <li class="flex items-center gap-3 rounded-xl bg-white/[0.06] px-4 py-3">
                                <svg class="h-4 w-4 flex-shrink-0 text-emerald-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                <span class="text-sm font-semibold">{!! $d !!}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-5 flex items-center justify-between border-t border-white/10 pt-4">
                        <span class="text-sm font-bold">All in</span>
                        <span class="text-lg font-extrabold">From £0 <span class="text-xs font-medium text-white/60">up to £49/mo</span></span>
                    </div>
                </div>
            </div>
        </div>
        <p class="mt-8 text-center text-sm text-muted">One dashboard, one bill, one customer list you actually own. No Zapier, no plugins, no agency.</p>
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

{{-- ============================================================ BADGE OF HONOUR --}}
@php
    $pinPath = 'M12 2C7.58 2 4 5.58 4 10c0 5.25 8 12 8 12s8-6.75 8-12c0-4.42-3.58-8-8-8Zm0 5.5A2.5 2.5 0 1 0 12 12.5 2.5 2.5 0 0 0 12 7.5Z';

    // Deterministic decorative QR (looks the part for a mockup).
    $n = 21; $c = 96 / $n; $o = 2; $mods = '';
    for ($r = 0; $r < $n; $r++) {
        for ($k = 0; $k < $n; $k++) {
            $fin = (($r < 7 && $k < 7) || ($r < 7 && $k >= $n - 7) || ($r >= $n - 7 && $k < 7));
            if ($fin) continue;
            if (((($r + 1) * ($k + 2) + $r * 3 + $k * 7) % 5) < 2) {
                $mods .= '<rect x="'.round($o + $k * $c, 2).'" y="'.round($o + $r * $c, 2).'" width="'.round($c + 0.25, 2).'" height="'.round($c + 0.25, 2).'"/>';
            }
        }
    }
    $fp = function ($gr, $gk) use ($c, $o) {
        $x = $o + $gk * $c; $y = $o + $gr * $c; $s = 7 * $c;
        return '<rect x="'.round($x, 2).'" y="'.round($y, 2).'" width="'.round($s, 2).'" height="'.round($s, 2).'" rx="2.5" fill="#0a0a0a"/>'
            .'<rect x="'.round($x + $c, 2).'" y="'.round($y + $c, 2).'" width="'.round($s - 2 * $c, 2).'" height="'.round($s - 2 * $c, 2).'" rx="1.5" fill="#fff"/>'
            .'<rect x="'.round($x + 2 * $c, 2).'" y="'.round($y + 2 * $c, 2).'" width="'.round($s - 4 * $c, 2).'" height="'.round($s - 4 * $c, 2).'" rx="1" fill="#059669"/>';
    };
    $qr = '<svg viewBox="0 0 100 100" class="h-full w-full"><rect width="100" height="100" rx="8" fill="#fff"/><g fill="#0a0a0a">'.$mods.'</g>'.$fp(0, 0).$fp(0, $n - 7).$fp($n - 7, 0).'</svg>';

    // Round "Badge of Honour" seal.
    $seal = '<svg viewBox="0 0 200 200" class="h-full w-full" aria-hidden="true">'
        .'<defs><path id="loco-seal-top" d="M22 100 A78 78 0 0 1 178 100"/><path id="loco-seal-bot" d="M24 100 A76 76 0 0 0 176 100"/></defs>'
        .'<circle cx="100" cy="100" r="97" fill="#0a0a0a"/>'
        .'<circle cx="100" cy="100" r="97" fill="none" stroke="#059669" stroke-width="3"/>'
        .'<circle cx="100" cy="100" r="89" fill="none" stroke="#1f3b32" stroke-width="1"/>'
        .'<circle cx="100" cy="100" r="61" fill="#fff"/>'
        .'<text font-family="Inter,sans-serif" font-size="10.5" font-weight="800" letter-spacing="1.6" fill="#d1fae5" text-anchor="middle"><textPath href="#loco-seal-top" startOffset="50%">PROUD LOCAL INDEPENDENT</textPath></text>'
        .'<text font-family="Inter,sans-serif" font-size="9.5" font-weight="700" letter-spacing="1.8" fill="#6ee7b7" text-anchor="middle"><textPath href="#loco-seal-bot" startOffset="50%">★ BACKED BY LOCOLIE ★</textPath></text>'
        .'<g transform="translate(84.5,44) scale(1.3)"><path d="'.$pinPath.'" fill="#059669"/></g>'
        .'<text x="100" y="118" text-anchor="middle" font-family="Inter,sans-serif" font-size="21" font-weight="800" fill="#0a0a0a" letter-spacing="-0.5">locolie</text>'
        .'<text x="100" y="135" text-anchor="middle" font-family="Inter,sans-serif" font-size="8" font-weight="700" letter-spacing="2" fill="#737373">EST. 2026 · NE1</text>'
        .'</svg>';
@endphp
<section class="border-t border-hair bg-[#f5f5f5] py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">Show your colours</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Your Badge of Honour.</p>
            <p class="mt-4 text-muted">Every locolie shop gets a Badge of Honour for the window and the till. Shoppers scan it to grab your offers, follow you and join your list - one quick scan, no app faff.</p>
        </div>

        <div class="mt-14 grid items-stretch gap-6 lg:grid-cols-2">
            {{-- WINDOW DECAL MOCKUP --}}
            <div class="rounded-card border border-hair bg-white p-8">
                <div class="mb-6 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-muted">
                    <svg class="h-4 w-4 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    In the window
                </div>
                <div class="relative mx-auto aspect-[4/5] w-full max-w-xs overflow-hidden rounded-[1.25rem] border-[12px] border-[#1c242e] bg-gradient-to-br from-[#d6e7ea] via-[#f2f8f8] to-[#bcd3d8] shadow-2xl">
                    {{-- glass glare --}}
                    <div class="pointer-events-none absolute inset-0 opacity-70" style="background:linear-gradient(115deg,rgba(255,255,255,.55) 0%,rgba(255,255,255,0) 35%,rgba(255,255,255,0) 60%,rgba(255,255,255,.35) 78%,rgba(255,255,255,0) 100%)"></div>
                    {{-- the decal, stuck on at a slight angle --}}
                    <div class="absolute inset-0 flex -rotate-2 flex-col items-center justify-center gap-3 p-5">
                        <div class="h-32 w-32 drop-shadow-lg">{!! $seal !!}</div>
                        <div class="w-[170px] rounded-2xl bg-ink p-4 text-center shadow-xl">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-soft">Scan for our offers</div>
                            <div class="mx-auto mt-2.5 h-24 w-24">{!! $qr !!}</div>
                            <div class="mt-2.5 text-xs font-semibold text-white">The Corner Café</div>
                            <div class="text-[10px] text-white/50">Live on locolie</div>
                        </div>
                    </div>
                </div>
                <p class="mt-6 text-center text-sm text-muted">A vinyl decal for the door or window. Passers-by scan and land straight on your offers.</p>
            </div>

            {{-- TILL CARD MOCKUP --}}
            <div class="rounded-card border border-hair bg-white p-8">
                <div class="mb-6 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-muted">
                    <svg class="h-4 w-4 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    At the till
                </div>
                <div class="relative mx-auto aspect-[4/5] w-full max-w-xs overflow-hidden rounded-[1.25rem] bg-gradient-to-b from-[#eef1f4] to-[#dfe4ea]">
                    {{-- counter surface --}}
                    <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-b from-[#c9a27a] to-[#a97e54]"></div>
                    <div class="absolute inset-x-0 bottom-1/3 h-px bg-black/10"></div>
                    {{-- acrylic stand --}}
                    <div class="absolute left-1/2 top-1/2 w-[185px] -translate-x-1/2 -translate-y-[58%]">
                        <div class="rounded-2xl border border-black/5 bg-white p-4 text-center shadow-[0_18px_30px_-12px_rgba(0,0,0,.35)]">
                            <div class="flex items-center justify-center gap-1.5 text-ink">
                                <svg class="h-4 w-auto" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="{{ $pinPath }}"/></svg>
                                <span class="text-base font-extrabold lowercase tracking-tight">locolie</span>
                            </div>
                            <div class="mt-2 text-[11px] font-bold uppercase tracking-[0.15em] text-emerald">Scan &amp; save</div>
                            <div class="mx-auto mt-2.5 h-28 w-28">{!! $qr !!}</div>
                            <div class="mt-2.5 text-xs font-semibold text-ink">Reveal today's offer</div>
                            <div class="text-[10px] text-muted">Point your camera here</div>
                        </div>
                        {{-- little acrylic foot --}}
                        <div class="mx-auto mt-1 h-2 w-20 rounded-b-lg bg-black/10"></div>
                    </div>
                </div>
                <p class="mt-6 text-center text-sm text-muted">A countertop card so regulars scan, redeem and join your list as they pay.</p>
            </div>
        </div>

        {{-- what the scan does --}}
        <div class="mx-auto mt-6 grid max-w-4xl gap-4 sm:grid-cols-3">
            @php
                $badgeFeatures = [
                    ['Proud local badge', 'A mark of honour that tells shoppers you are a genuine, locally backed independent.',
                     '<path d="M12 2 4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5z"/><path d="m9 12 2 2 4-4"/>'],
                    ['One quick scan', 'No app to download first. The camera opens your offers, and they can follow you in a tap.',
                     '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3M21 21v.01M17 21h.01M21 17h.01"/>'],
                    ['Straight onto your list', 'Every scan and redemption adds a real customer you can reach again by email, SMS and push.',
                     '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>'],
                ];
            @endphp
            @foreach ($badgeFeatures as $bf)
                <div class="rounded-card border border-hair bg-white p-6">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-soft text-emerald">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $bf[2] !!}</svg>
                    </div>
                    <h3 class="mt-4 font-bold">{{ $bf[0] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $bf[1] }}</p>
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
