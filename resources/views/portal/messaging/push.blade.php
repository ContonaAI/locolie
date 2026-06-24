@extends('portal.layout')
@section('title', 'Push - Messaging Studio')

@section('content')
@include('portal.messaging._nav', ['tab' => 'push'])

@php
    // Brand map drives the live preview: selecting a brand swaps colour, initials,
    // logo and (default) name in all three mockups without a round-trip.
    $brandMap = $businesses->mapWithKeys(fn ($b) => [$b->id => [
        'name' => $b->name,
        'color' => $b->brandColor(),
        'initials' => $b->brandInitials(),
        'logo' => $b->logoUrl(),
    ]])->all();
    $defaults = config('messaging.defaults', []);
    $platform = [
        'name' => $defaults['from_name'] ?? 'locolie',
        'color' => $defaults['brand_color'] ?? '#059669',
        'initials' => 'GL',
        'logo' => null,
    ];
@endphp

<div x-data="pushStudio({
        brands: {{ Js::from($brandMap) }},
        platform: {{ Js::from($platform) }},
        appName: {{ Js::from($platform['name']) }},
    })"
     class="grid gap-6 lg:grid-cols-2">

    {{-- ── LEFT: compose + providers ───────────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Audience breakdown --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-3 text-sm font-bold text-slate-900">Audience</h2>
            <div class="grid grid-cols-3 gap-3">
                @foreach ([
                    ['Web', $audience['web'], 'browsers'],
                    ['iOS', $audience['ios'], 'devices'],
                    ['Android', $audience['android'], 'devices'],
                ] as [$label, $n, $sub])
                    <div class="rounded-xl bg-amber-50 px-3 py-3 text-center ring-1 ring-amber-100">
                        <div class="text-2xl font-extrabold text-amber-700">{{ number_format($n) }}</div>
                        <div class="text-xs font-semibold text-slate-700">{{ $label }}</div>
                        <div class="text-[11px] text-slate-400">{{ $sub }}</div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-slate-400">{{ number_format($audience['total']) }} reachable in total. Web push works today; iOS + Android counts grow as the native apps roll out.</p>
        </div>

        {{-- Compose form --}}
        <form method="POST" action="{{ route('messaging.push.send') }}" class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
            @csrf
            <input type="hidden" name="business_id" x-model="brandId">

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Send as brand</label>
                <select x-model="brandId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">locolie (platform)</option>
                    @foreach ($businesses as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-[11px] text-slate-400">Drives the icon + colour shown in every notification.</p>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Title</label>
                <input type="text" name="title" x-model="title" maxlength="120" required
                       placeholder="New deal near you"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Body</label>
                <textarea name="body" x-model="body" maxlength="500" rows="3" required
                          placeholder="2-for-1 at The Anchor, 200m away. Tap to claim."
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">CTA label <span class="font-normal text-slate-400">(optional)</span></label>
                    <input type="text" name="cta_label" x-model="cta" maxlength="40"
                           placeholder="Claim offer"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">Deep-link URL <span class="font-normal text-slate-400">(optional)</span></label>
                    <input type="text" name="cta_url" x-model="ctaUrl"
                           placeholder="locolie://offer/123"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-1">
                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                    Send broadcast
                </button>
                <button type="submit" formaction="{{ route('messaging.push.test') }}"
                        class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800">
                    Send test
                </button>
            </div>
            <p class="text-[11px] text-slate-400">"Send test" and "Send broadcast" both reach current subscribers (push has no single address). In demo mode each send is logged + counted.</p>
        </form>

        {{-- Providers --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="mb-1 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Push providers</h2>
                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $connected ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $connected ? 'Connected'.($activeProvider ? ' · '.$activeProvider : '') : 'Demo mode' }}
                </span>
            </div>
            <p class="mb-4 text-xs text-slate-500">Web push works today via self-generated VAPID keys. FCM (Android) and APNs (iOS) activate the moment the native apps ship and their credentials are added.</p>

            <div class="space-y-2.5">
                @foreach ($providers as $slug => $prov)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 px-3 py-2.5">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-sm font-semibold text-slate-800">{{ $prov['label'] }}</span>
                                @if (! empty($prov['recommended']))
                                    <span class="text-xs font-semibold text-emerald-600">· recommended</span>
                                @endif
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-1">
                                @foreach ($prov['platforms'] ?? [] as $plat)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $plat }}</span>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-slate-400">{{ $prov['blurb'] ?? '' }}</p>
                        </div>
                        <div class="shrink-0">
                            @if ($activeProvider === $slug)
                                <form method="POST" action="{{ route('messaging.disconnect') }}">@csrf
                                    <input type="hidden" name="channel" value="push">
                                    <input type="hidden" name="provider" value="{{ $slug }}">
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">Disconnect</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('messaging.connect') }}">@csrf
                                    <input type="hidden" name="channel" value="push">
                                    <input type="hidden" name="provider" value="{{ $slug }}">
                                    <input type="hidden" name="label" value="{{ $prov['label'] }} (demo)">
                                    <button class="text-xs font-semibold text-emerald-600 hover:underline">Connect</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── RIGHT: live preview ─────────────────────────────────────────── --}}
    <div class="lg:sticky lg:top-24 lg:self-start">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex items-center gap-2">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
                <h2 class="text-sm font-bold text-slate-900">Live preview</h2>
                <span class="ml-auto text-xs text-slate-400">web · iOS · Android</span>
            </div>

            {{-- The reusable mockup partial, with live-bound text + icons. --}}
            <div x-ref="preview">
                @include('messaging.previews.push', ['preview' => $defaultPreview])
            </div>
        </div>
        <p class="mt-3 px-1 text-xs text-slate-400">This is exactly how the notification lands across the web app and the future iOS + Android apps.</p>
    </div>
</div>

@push('head')
<script>
    function pushStudio(cfg) {
        return {
            brands: cfg.brands || {},
            platform: cfg.platform,
            appName: cfg.appName || 'locolie',
            brandId: '',
            title: '',
            body: '',
            cta: '',
            ctaUrl: '',

            get brand() {
                return this.brandId && this.brands[this.brandId] ? this.brands[this.brandId] : this.platform;
            },

            init() {
                this.$watch('brandId', () => this.render());
                this.$watch('title', () => this.render());
                this.$watch('body', () => this.render());
                this.$watch('cta', () => this.render());
                this.render();
            },

            render() {
                const b = this.brand;
                const title = (this.title || '').trim() || 'A deal near you';
                const body = (this.body || '').trim() || 'Open ' + this.appName + ' to see what is on right now.';
                const cta = (this.cta || '').trim();
                const root = this.$refs.preview;
                if (!root) return;

                // Text fields shared across the three mockups.
                root.querySelectorAll('[data-push="title"]').forEach(el => el.textContent = title);
                root.querySelectorAll('[data-push="body"]').forEach(el => el.textContent = body);
                root.querySelectorAll('[data-push="app"]').forEach(el => el.textContent = b.name || this.appName);
                root.querySelectorAll('[data-push="cta"]').forEach(el => {
                    el.textContent = cta || el.textContent;
                    if (el.classList.contains('text-white')) {
                        el.style.background = b.color; // toast pill
                    } else {
                        el.style.color = b.color;       // Android inline action
                    }
                });

                // Brand icon swatches: recolour + swap initials / logo in each.
                root.querySelectorAll('.gl-push-swatch').forEach(swatch => {
                    swatch.style.background = b.color;
                    const initials = swatch.querySelector('[data-push="initials"]');
                    const img = swatch.querySelector('[data-push="icon"]');
                    if (b.logo) {
                        if (img) {
                            img.src = b.logo;
                        } else {
                            const node = document.createElement('img');
                            node.setAttribute('data-push', 'icon');
                            node.src = b.logo;
                            node.alt = b.name || this.appName;
                            node.className = 'h-full w-full object-cover';
                            swatch.innerHTML = '';
                            swatch.appendChild(node);
                        }
                    } else {
                        if (initials) {
                            initials.textContent = b.initials || 'GL';
                        } else {
                            const node = document.createElement('span');
                            node.setAttribute('data-push', 'initials');
                            node.textContent = b.initials || 'GL';
                            swatch.innerHTML = '';
                            swatch.appendChild(node);
                        }
                    }
                });
            },
        };
    }
</script>
@endpush
@endsection
