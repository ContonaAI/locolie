@extends('portal.layout')
@section('title', 'SMS - Messaging Studio')

@section('content')
@include('portal.messaging._nav', ['tab' => 'sms'])

@php
    // Brand map for live Alpine preview: id => {name, sender, color}.
    $brandMap = $businesses->mapWithKeys(fn ($b) => [$b->id => [
        'name' => $b->name,
        'sender' => $b->smsSenderId() ?: 'locolie',
        'color' => $b->brandColor() ?: '#7c3aed',
    ]])->all();
    $firstBrand = $businesses->first();
@endphp

<div
    x-data="{
        body: '',
        url: '',
        brandId: '{{ $firstBrand->id ?? '' }}',
        brands: @js($brandMap),
        now: (() => { const d = new Date(); let h = d.getHours() % 12; if (h === 0) h = 12; return h + ':' + String(d.getMinutes()).padStart(2,'0'); })(),
        get brand() { return this.brands[this.brandId] || { name: 'locolie', sender: 'locolie', color: '#7c3aed' }; },
        get composed() {
            const b = (this.body || '').trim();
            const u = (this.url || '').trim();
            if (u === '') return b;
            return b === '' ? u : b + '\n' + u;
        },
        get charCount() { return this.composed.length; },
        get segments() { return Math.max(1, Math.ceil(this.charCount / 160)); },
        get avatar() {
            const s = (this.brand.sender || 'L').replace(/[^A-Za-z0-9]/g, '') || 'L';
            return s.substring(0, 2).toUpperCase();
        },
    }"
    class="grid lg:grid-cols-2 gap-6 lg:gap-8 items-start"
>
    {{-- ── LEFT: compose + providers ─────────────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Channel status --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-violet-600 text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </span>
                        <h2 class="text-lg font-bold text-slate-900">Compose SMS</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">Audience: <span class="font-semibold text-violet-600">{{ number_format($audienceCount) }}</span> opted-in phones.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $channel->connected() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $channel->connected() ? 'Live · '.$activeProvider : 'Demo mode' }}
                </span>
            </div>
        </div>

        {{-- Compose form (drives test + send) --}}
        <form method="POST" action="{{ route('messaging.sms.send') }}" class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
            @csrf

            {{-- Brand selector --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Send as brand</label>
                <select name="business_id" x-model="brandId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none">
                    <option value="">locolie (platform)</option>
                    @foreach ($businesses as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-400">Sender ID: <span class="mono font-semibold text-slate-600" x-text="brand.sender"></span></p>
            </div>

            {{-- Message body --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-semibold text-slate-500">Message</label>
                    <span class="text-xs text-slate-400">
                        <span x-text="charCount"></span> chars ·
                        <span x-text="segments"></span> <span x-text="segments == 1 ? 'segment' : 'segments'"></span>
                    </span>
                </div>
                <textarea name="body" x-model="body" rows="4" maxlength="2000"
                    placeholder="2 for 1 cocktails tonight at The Anchor. Show this text at the bar."
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none resize-y"></textarea>
                <p class="mt-1 text-xs text-slate-400">160 chars per segment. Keep it short - every segment is billed.</p>
            </div>

            {{-- Optional link --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Link (optional)</label>
                <input type="text" name="url" x-model="url" placeholder="https://locolie.com/o/anchor-cocktails"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none">
            </div>

            <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-violet-600 text-white text-sm font-bold px-5 py-3 hover:bg-violet-700 transition">
                Send campaign to {{ number_format($audienceCount) }} phones
            </button>
            <p class="text-center text-xs text-slate-400">
                {{ $channel->connected() ? 'Delivered live via '.$activeProvider.'.' : 'Logged + counted in demo mode until a provider with real keys is connected.' }}
            </p>
        </form>

        {{-- Send test --}}
        <form method="POST" action="{{ route('messaging.sms.test') }}" class="rounded-2xl border border-slate-200 bg-white p-5">
            @csrf
            <input type="hidden" name="body" :value="body">
            <input type="hidden" name="url" :value="url">
            <input type="hidden" name="business_id" :value="brandId">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Send a test to</label>
            <div class="flex gap-2">
                <input type="text" name="phone" placeholder="+44 7700 900123" required
                    class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none">
                <button type="submit" class="rounded-lg bg-slate-900 text-white text-sm font-semibold px-4 py-2 hover:bg-slate-800 whitespace-nowrap">Send test</button>
            </div>
        </form>

        {{-- Provider panel --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-bold text-slate-900 mb-1">SMS providers</h2>
            <p class="text-sm text-slate-500 mb-4">Connect a provider to go live. "Ready" means env keys are present - delivery is real. Demo-connect (no keys) flips the UI live while still logging + counting.</p>

            <div class="space-y-1.5">
                @foreach ($providers as $slug => $p)
                    @php $ready = $readiness[$slug] ?? false; @endphp
                    <div class="flex items-start justify-between gap-3 py-2 border-b border-slate-100 last:border-0">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-slate-800 text-sm">{{ $p['label'] }}</span>
                                @if (!empty($p['recommended']))
                                    <span class="text-violet-600 text-xs font-semibold">recommended</span>
                                @endif
                                @if ($ready)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">keys ready</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold">demo</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $p['blurb'] }}</p>
                        </div>
                        <div class="shrink-0">
                            @if (($connection?->provider) === $slug)
                                <form method="POST" action="{{ route('messaging.disconnect') }}">@csrf
                                    <input type="hidden" name="channel" value="sms">
                                    <input type="hidden" name="provider" value="{{ $slug }}">
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">Disconnect</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('messaging.connect') }}">@csrf
                                    <input type="hidden" name="channel" value="sms">
                                    <input type="hidden" name="provider" value="{{ $slug }}">
                                    <input type="hidden" name="label" value="{{ $p['label'] }}{{ $ready ? '' : ' (demo)' }}">
                                    <button class="text-xs font-semibold text-violet-600 hover:underline">Connect</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── RIGHT: live phone-mockup preview ──────────────────────────────── --}}
    <div class="lg:sticky lg:top-24">
        <div class="rounded-2xl border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400">Live preview</h2>
                <span class="text-xs text-slate-400">as the customer sees it</span>
            </div>

            {{-- Alpine-driven phone mockup (mirrors messaging/previews/sms.blade.php) --}}
            <div class="mx-auto w-full" style="max-width: 22rem;">
                <div class="relative mx-auto rounded-[2.75rem] bg-slate-900 p-2.5 shadow-2xl ring-1 ring-black/10" style="width: 20rem;">
                    <div class="relative overflow-hidden rounded-[2.25rem] bg-gradient-to-b from-slate-50 to-slate-100">
                        <div class="pointer-events-none absolute left-1/2 top-2 z-20 h-6 w-24 -translate-x-1/2 rounded-full bg-slate-900"></div>

                        {{-- Status bar --}}
                        <div class="relative z-10 flex items-center justify-between px-6 pt-3 pb-1 text-[11px] font-semibold text-slate-900">
                            <span x-text="now"></span>
                            <span class="flex items-center gap-1.5">
                                <svg class="h-3 w-3.5" viewBox="0 0 24 24" fill="currentColor"><rect x="1" y="14" width="3" height="6" rx="1"/><rect x="7" y="10" width="3" height="10" rx="1"/><rect x="13" y="6" width="3" height="14" rx="1"/><rect x="19" y="2" width="3" height="18" rx="1"/></svg>
                                <svg class="h-3 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 18.5a1.6 1.6 0 100 3.2 1.6 1.6 0 000-3.2zM5.6 12.3a9 9 0 0112.8 0l-1.7 1.7a6.6 6.6 0 00-9.4 0zM2 8.7a14 14 0 0120 0l-1.7 1.7a11.6 11.6 0 00-16.6 0z"/></svg>
                                <span class="inline-flex items-center">
                                    <span class="relative inline-block h-3 w-6 rounded-[3px] border border-slate-900/70">
                                        <span class="absolute inset-y-0.5 left-0.5 w-4 rounded-[1px] bg-slate-900"></span>
                                    </span>
                                    <span class="ml-0.5 inline-block h-1.5 w-0.5 rounded-r bg-slate-900/70"></span>
                                </span>
                            </span>
                        </div>

                        {{-- Header --}}
                        <div class="relative z-10 flex flex-col items-center border-b border-slate-200/80 bg-white/70 px-4 pb-3 pt-2 backdrop-blur">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full text-sm font-extrabold text-white shadow-sm"
                                 :style="`background: ${brand.color};`" x-text="avatar"></div>
                            <div class="mt-1 flex items-center gap-1 text-[13px] font-semibold text-slate-900">
                                <span x-text="brand.sender"></span>
                                <svg class="h-3 w-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                            </div>
                            <div class="text-[10px] text-slate-400">SMS / Text Message</div>
                        </div>

                        {{-- Conversation --}}
                        <div class="relative z-10 min-h-[15rem] space-y-2 px-4 py-4">
                            <div class="text-center text-[10px] font-medium text-slate-400">Text Message - Today <span x-text="now"></span></div>
                            <div class="flex justify-start">
                                <div class="max-w-[80%]">
                                    <div class="rounded-[1.25rem] rounded-bl-md bg-slate-200 px-3.5 py-2.5 text-[13px] leading-snug text-slate-900 shadow-sm">
                                        <span x-show="body.trim() !== ''" class="whitespace-pre-line break-words" x-text="body"></span>
                                        <span x-show="body.trim() === ''" class="italic text-slate-400">Your message preview appears here as the customer sees it.</span>
                                        <a x-show="url.trim() !== ''" href="#" class="mt-1 block break-all font-medium underline" :style="`color: ${brand.color};`" x-text="url"></a>
                                        <div class="mt-1.5 text-[10px] text-slate-500">Reply STOP to opt out</div>
                                    </div>
                                    <div class="mt-1 pl-1 text-[10px] text-slate-400"><span x-text="brand.name"></span> - delivered</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center pb-2 pt-1">
                            <span class="h-1 w-28 rounded-full bg-slate-300"></span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-center gap-3 text-[11px] text-slate-400">
                    <span><span class="font-semibold text-slate-600" x-text="charCount"></span> chars</span>
                    <span class="text-slate-300">|</span>
                    <span><span class="font-semibold text-slate-600" x-text="segments"></span> <span x-text="segments == 1 ? 'segment' : 'segments'"></span></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
