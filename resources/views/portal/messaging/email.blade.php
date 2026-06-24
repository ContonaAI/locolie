@extends('portal.layout')
@section('title', 'Email - Messaging Studio')

@section('content')
@include('portal.messaging._nav', ['tab' => 'email'])

@php
    $templatesJs = $templates->map(fn ($t) => [
        'id' => $t->id,
        'name' => $t->name,
        'subject' => $t->subject,
        'body' => $t->body,
    ])->values();
@endphp

<div
    x-data="emailStudio({
        brands: {{ \Illuminate\Support\Js::from($brandMap) }},
        templates: {{ \Illuminate\Support\Js::from($templatesJs) }},
        defaults: {
            color: '{{ config('messaging.defaults.brand_color', '#059669') }}',
            fromName: '{{ config('messaging.defaults.from_name', 'locolie') }}',
            name: 'locolie',
            initials: 'GL',
        },
    })"
    class="grid lg:grid-cols-2 gap-6 items-start"
>
    {{-- ════════════════ LEFT: compose + connection ════════════════ --}}
    <div class="space-y-6">

        {{-- Compose card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-1">Compose email</h2>
            <p class="text-sm text-slate-500 mb-5">Branded, responsive and bespoke to the brand you pick. The preview updates as you type.</p>

            <form method="POST" action="{{ route('messaging.email.send') }}" class="space-y-4">
                @csrf

                {{-- Brand selector --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Send as brand</label>
                    <select name="business_id" x-model="form.business_id" @change="applyBrand()"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">locolie (platform - all onboarded businesses)</option>
                        @foreach ($businesses as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Template picker --}}
                @if ($templates->isNotEmpty())
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Start from a template</label>
                        <select x-model="templateId" @change="applyTemplate()"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">Blank</option>
                            @foreach ($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Subject</label>
                    <input type="text" name="subject" x-model="form.subject" maxlength="160" required
                           placeholder="A fresh local offer just for you"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Preheader <span class="text-slate-400 font-normal">(inbox preview line)</span></label>
                    <input type="text" name="preheader" x-model="form.preheader" maxlength="160"
                           placeholder="Pop in this week to claim it"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Body</label>
                    <textarea name="body" x-model="form.body" rows="7" maxlength="8000" required
                              placeholder="Write your message. Blank lines become paragraphs."
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm leading-relaxed focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Button label</label>
                        <input type="text" name="cta_label" x-model="form.cta_label" maxlength="60"
                               placeholder="See the offer"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Button link</label>
                        <input type="url" name="cta_url" x-model="form.cta_url"
                               placeholder="https://locolie.com"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 pt-2">
                    <p class="text-xs text-slate-400">
                        @if ($connected)
                            Live - sending via <span class="font-semibold text-emerald-600">{{ $activeProvider ?? 'configured provider' }}</span>.
                        @else
                            Demo mode - sends are logged + counted until a provider is connected.
                        @endif
                    </p>
                    <button class="rounded-lg bg-emerald-600 text-white text-sm font-bold px-5 py-2.5 hover:bg-emerald-700 shrink-0">
                        Send campaign
                    </button>
                </div>
            </form>
        </div>

        {{-- Send test card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h3 class="text-sm font-bold text-slate-900 mb-1">Send a test</h3>
            <p class="text-xs text-slate-500 mb-4">Fires one copy of the current draft to an address you choose.</p>
            <form method="POST" action="{{ route('messaging.email.test') }}" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <input type="hidden" name="business_id" :value="form.business_id">
                <input type="hidden" name="subject" :value="form.subject">
                <input type="hidden" name="body" :value="form.body">
                <input type="hidden" name="preheader" :value="form.preheader">
                <input type="hidden" name="cta_label" :value="form.cta_label">
                <input type="hidden" name="cta_url" :value="form.cta_url">
                <input type="email" name="test_email" required placeholder="you@example.com"
                       class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                <button class="rounded-lg bg-slate-900 text-white text-sm font-semibold px-4 py-2.5 hover:bg-slate-800 shrink-0">
                    Send test
                </button>
            </form>
        </div>

        {{-- Connection / providers card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-slate-900">Email provider</h3>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $connected ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $connected ? 'Connected' . ($activeProvider ? ' · ' . $activeProvider : '') : 'Demo mode' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mb-4">Connect a provider to send for real. Until then everything is logged and counted.</p>

            <div class="space-y-2.5">
                @foreach ($providers as $slug => $p)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3.5 py-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-800">
                                {{ $p['label'] }}
                                @if (!empty($p['recommended']))<span class="text-emerald-600 text-xs font-semibold">· recommended</span>@endif
                            </div>
                            <div class="text-xs text-slate-400 truncate">{{ $p['blurb'] ?? '' }}</div>
                        </div>

                        @if ($activeProvider === $slug)
                            <form method="POST" action="{{ route('messaging.disconnect') }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="channel" value="email">
                                <input type="hidden" name="provider" value="{{ $slug }}">
                                <button class="text-xs font-semibold text-rose-600 hover:underline">Disconnect</button>
                            </form>
                        @elseif ($slug === 'google')
                            <a href="{{ route('messaging.email.google') }}"
                               class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.26 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.3 9.14 5.38 12 5.38z"/></svg>
                                Connect to Google
                            </a>
                        @else
                            <form method="POST" action="{{ route('messaging.connect') }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="channel" value="email">
                                <input type="hidden" name="provider" value="{{ $slug }}">
                                <input type="hidden" name="label" value="{{ $p['label'] }} (demo)">
                                <button class="text-xs font-semibold text-emerald-600 hover:underline">Connect</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ════════════════ RIGHT: live preview ════════════════ --}}
    <div class="lg:sticky lg:top-24">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-bold text-slate-900">Live preview</h2>
            <span class="text-xs text-slate-400">Updates as you type</span>
        </div>

        {{-- Alpine-driven inbox mockup (mirrors messaging.previews.email) --}}
        <div class="mx-auto w-full max-w-md">
            <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
                <div class="flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 border-b border-slate-200">
                    <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                    <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                    <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                    <span class="ml-3 text-xs font-medium text-slate-400">Inbox</span>
                </div>

                {{-- Inbox row --}}
                <div class="flex items-start gap-3 px-4 py-3.5 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-full shrink-0 flex items-center justify-center overflow-hidden text-white font-extrabold text-sm"
                         :style="`background-color: ${preview.color}`">
                        <template x-if="preview.logoUrl"><img :src="preview.logoUrl" class="w-full h-full object-cover"></template>
                        <template x-if="!preview.logoUrl"><span x-text="preview.initials"></span></template>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-slate-900 text-sm truncate" x-text="preview.fromName"></span>
                            <span class="text-xs text-slate-400 shrink-0">now</span>
                        </div>
                        <div class="text-sm font-medium text-slate-800 truncate" x-text="preview.subject || '(no subject)'"></div>
                        <div class="text-xs text-slate-400 truncate" x-text="preview.snippet || 'No preview text yet.'"></div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="bg-slate-50 p-4 max-h-[520px] overflow-y-auto">
                    <div class="rounded-xl overflow-hidden bg-white border border-slate-200">
                        <div class="flex items-center gap-3 px-5 py-4" :style="`background-color: ${preview.color}`">
                            <div class="w-9 h-9 rounded-lg shrink-0 flex items-center justify-center overflow-hidden text-white font-extrabold text-xs"
                                 style="background-color: rgba(255,255,255,0.18);">
                                <template x-if="preview.logoUrl"><img :src="preview.logoUrl" class="w-full h-full object-contain bg-white"></template>
                                <template x-if="!preview.logoUrl"><span x-text="preview.initials"></span></template>
                            </div>
                            <span class="text-white font-bold text-sm" x-text="preview.brandName"></span>
                        </div>

                        <div class="px-5 pt-5 pb-1">
                            <h1 class="text-lg font-extrabold tracking-tight text-slate-900 leading-snug mb-3"
                                x-text="preview.subject || 'Your subject line'"></h1>
                            <div class="text-sm leading-relaxed text-slate-600 whitespace-pre-line"
                                 x-text="preview.body || 'Your message body will appear here as you type.'"
                                 :class="preview.body ? '' : 'text-slate-300 italic'"></div>
                        </div>

                        <div class="px-5 py-4" x-show="preview.ctaLabel" x-cloak>
                            <span class="inline-block rounded-lg px-5 py-2.5 text-sm font-bold text-white"
                                  :style="`background-color: ${preview.color}`" x-text="preview.ctaLabel"></span>
                        </div>

                        <div class="px-5 py-4 border-t border-slate-100">
                            <p class="text-xs text-slate-400 leading-relaxed" x-text="preview.footer"></p>
                            <p class="text-[11px] text-slate-300 mt-1.5">
                                Sent by <span x-text="preview.brandName"></span> on
                                <span class="text-emerald-600 font-semibold">locolie</span>.
                                <span class="underline">Unsubscribe</span>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-center text-xs text-slate-400 mt-3">
                Audience: <span class="font-semibold text-slate-500" x-text="audienceLabel"></span>
            </p>
        </div>
    </div>
</div>

<script>
    function emailStudio(config) {
        return {
            brands: config.brands || {},
            templates: config.templates || [],
            defaults: config.defaults,
            templateId: '',
            platformAudience: {{ (int) $platformAudience }},
            form: {
                business_id: '',
                subject: '',
                preheader: '',
                body: '',
                cta_label: '',
                cta_url: '',
            },
            get brand() {
                return this.form.business_id && this.brands[this.form.business_id]
                    ? this.brands[this.form.business_id]
                    : null;
            },
            get audienceLabel() {
                return this.brand
                    ? `${this.brand.name} opted-in customers`
                    : `${this.platformAudience} onboarded businesses`;
            },
            get preview() {
                const b = this.brand;
                const color = b ? b.color : this.defaults.color;
                const brandName = b ? b.name : this.defaults.name;
                const snippet = (this.form.preheader || this.form.body || '').slice(0, 80);
                return {
                    color: color,
                    brandName: brandName,
                    fromName: b ? b.fromName : this.defaults.fromName,
                    initials: b ? b.initials : this.defaults.initials,
                    logoUrl: b ? b.logoUrl : null,
                    subject: this.form.subject,
                    body: this.form.body,
                    snippet: snippet,
                    ctaLabel: this.form.cta_label,
                    footer: `You are receiving this because you shop local with ${brandName}.`,
                };
            },
            applyBrand() {
                // getters recompute automatically; nothing imperative needed.
            },
            applyTemplate() {
                if (!this.templateId) return;
                const t = this.templates.find(x => String(x.id) === String(this.templateId));
                if (!t) return;
                if (t.subject) this.form.subject = t.subject;
                if (t.body) this.form.body = t.body;
            },
        };
    }
</script>
@endsection
