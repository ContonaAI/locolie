@extends('business.layout')
@section('title', 'Messaging')

@section('content')
<div x-data="retailerMessaging()" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Messaging</h1>
            <p class="text-slate-500 mt-1 max-w-2xl text-sm">Reach your own customers with branded email, SMS and push - the same list the big chains have always had, now yours. Compose on the left, see exactly what the customer gets on the right.</p>
        </div>
        <a href="{{ route('business.dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Back to dashboard</a>
    </div>

    {{-- Brand identity --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="font-bold text-lg text-slate-900 mb-1">Your brand</h2>
        <p class="text-sm text-slate-500 mb-5">Every message is rendered with your logo, colour and sender name.</p>
        <form method="POST" action="{{ route('business.brand') }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-5">
            @csrf
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl border border-slate-200 flex items-center justify-center overflow-hidden shrink-0" style="background: {{ $business->brandColor() }}1a">
                    @if ($business->logoUrl())
                        <img src="{{ $business->logoUrl() }}" alt="{{ $business->name }}" class="w-full h-full object-contain">
                    @else
                        <span class="font-extrabold text-lg" style="color: {{ $business->brandColor() }}">{{ $business->brandInitials() }}</span>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Logo (PNG/SVG, max 2MB)</label>
                    <input type="file" name="logo" accept="image/*" class="text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-emerald-700 file:font-semibold">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Accent colour</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="brand_color" value="{{ $business->brandColor() }}" class="h-10 w-14 rounded-lg border border-slate-300 bg-white p-1">
                    <span class="text-sm text-slate-500">{{ $business->brandColor() }}</span>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Email sender name</label>
                <input type="text" name="email_from_name" value="{{ $business->email_from_name }}" placeholder="{{ $business->name }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">SMS sender ID (max 11 chars)</label>
                <input type="text" name="sms_sender_id" maxlength="11" value="{{ $business->sms_sender_id }}" placeholder="{{ $business->smsSenderId() }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div class="sm:col-span-2">
                <button class="rounded-lg bg-emerald-600 text-white text-sm font-bold px-5 py-2.5 hover:bg-emerald-700">Save brand</button>
            </div>
        </form>
    </section>

    {{-- Channel tabs --}}
    <div class="flex items-center gap-1 border-b border-slate-200">
        @foreach (['email' => 'Email', 'sms' => 'SMS', 'push' => 'Push'] as $key => $label)
            <button @click="switchChannel('{{ $key }}')"
                    :class="channel === '{{ $key }}' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition">
                {{ $label }}
                <span class="ml-1 text-xs text-slate-400">{{ number_format($audience[$key]) }}</span>
            </button>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6 items-start">
        {{-- Compose --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <form method="POST" action="{{ route('business.messaging.send') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="channel" x-model="channel">

                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-lg text-slate-900" x-text="channelLabel()"></h2>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                          :class="connected ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                          x-text="connected ? 'Connected' : 'Demo mode'"></span>
                </div>

                {{-- Email subject + preheader --}}
                <template x-if="channel === 'email'">
                    <div class="space-y-3">
                        <input type="text" name="subject" x-model="form.subject" @input="schedulePreview" placeholder="Subject line" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        <input type="text" name="preheader" x-model="form.preheader" @input="schedulePreview" placeholder="Preheader (preview snippet)" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </template>

                {{-- Push title --}}
                <template x-if="channel === 'push'">
                    <input type="text" name="title" x-model="form.title" @input="schedulePreview" placeholder="Notification title" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </template>

                <div>
                    <textarea name="body" x-model="form.body" @input="schedulePreview" rows="5" required
                              :placeholder="channel === 'sms' ? 'Your text message (160 chars per segment)' : 'Your message to customers'"
                              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                    <p x-show="channel === 'sms'" class="text-xs text-slate-400 mt-1" x-text="smsMeta()"></p>
                </div>

                {{-- CTA for email + push --}}
                <template x-if="channel !== 'sms'">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="cta_label" x-model="form.cta_label" @input="schedulePreview" placeholder="Button label" class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        <input type="text" name="cta_url" x-model="form.cta_url" @input="schedulePreview" placeholder="Link URL" class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </template>

                {{-- Optional schedule --}}
                <div x-data="{ later: false }" class="rounded-lg border border-slate-200 bg-slate-50/60 px-3 py-2.5">
                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                        <input type="checkbox" x-model="later" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        Schedule for later
                    </label>
                    <input x-show="later" x-cloak type="datetime-local" name="scheduled_at"
                           class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <p class="text-xs text-slate-500">Sends to <span class="font-bold text-slate-700" x-text="audienceCount()"></span> <span x-text="audienceLabel()"></span>.</p>
                    <button class="rounded-xl bg-emerald-600 text-white font-bold px-5 py-2.5 hover:bg-emerald-700 text-sm">Send <span x-text="channelLabel().toLowerCase()"></span></button>
                </div>
                <p class="text-[11px] text-slate-400 leading-relaxed">Customers who opted in at redemption only. Marketing rules (an unsubscribe link, STOP keyword) are added automatically.</p>
            </form>
        </div>

        {{-- Live preview --}}
        <div class="lg:sticky lg:top-6">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2 text-center">What your customer receives</div>
            <div id="retailer-preview" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-6 overflow-hidden">
                @include('messaging.previews.email', ['preview' => $previews['email']])
            </div>
        </div>
    </div>

    {{-- Recent --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="font-bold text-lg text-slate-900 mb-4">Recent messages</h2>
        @forelse ($campaigns as $c)
            <div class="flex items-center justify-between gap-3 py-2.5 border-b border-slate-100 last:border-0">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ ['email' => 'bg-sky-100 text-sky-700', 'sms' => 'bg-violet-100 text-violet-700', 'push' => 'bg-amber-100 text-amber-700'][$c->channel] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($c->channel) }}</span>
                        <span class="font-semibold text-slate-800 truncate">{{ $c->subject ?: \Illuminate\Support\Str::limit($c->body, 50) }}</span>
                    </div>
                    <div class="text-xs text-slate-400 mt-0.5">{{ $c->created_at?->diffForHumans() }} · {{ ucfirst($c->status ?? 'sent') }}</div>
                </div>
                <div class="text-sm font-bold text-slate-700 shrink-0">{{ number_format($c->sent_count) }} sent</div>
            </div>
        @empty
            <p class="text-center text-slate-400 text-sm py-6">No messages sent yet.</p>
        @endforelse
    </section>
</div>

<script>
function retailerMessaging() {
    return {
        channel: 'email',
        connected: @js(collect($overview)->mapWithKeys(fn($o,$k) => [$k => $o['connected']])),
        audienceMap: @js($audience),
        _timer: null,
        samples: @js($samples),
        form: {
            subject: @js($samples['email']['subject'] ?? ''),
            preheader: @js($samples['email']['preheader'] ?? ''),
            title: @js($samples['push']['title'] ?? ''),
            body: @js($samples['email']['body'] ?? ''),
            cta_label: @js($samples['email']['cta_label'] ?? ''),
            cta_url: @js($samples['email']['cta_url'] ?? ''),
        },
        get connectedNow() { return !!this.connected[this.channel]; },
        channelLabel() { return {email:'Email', sms:'SMS', push:'Push'}[this.channel]; },
        audienceCount() { return (this.audienceMap[this.channel] || 0).toLocaleString(); },
        audienceLabel() { return {email:'opted-in inboxes', sms:'opted-in phones', push:'devices'}[this.channel]; },
        smsMeta() {
            const len = (this.form.body || '').length;
            const seg = Math.max(1, Math.ceil(len / 160));
            return `${len} characters · ${seg} segment${seg > 1 ? 's' : ''}`;
        },
        init() {
            this.$watch('channel', () => { this.connected = this.connectedNow; });
            this.connected = this.connectedNow;
            this.refreshPreview();
        },
        switchChannel(c) {
            this.channel = c;
            this.connected = !!this.connectedNow;
            const s = this.samples[c] || {};
            this.form.subject = s.subject || '';
            this.form.preheader = s.preheader || '';
            this.form.title = s.title || '';
            this.form.body = s.body || '';
            this.form.cta_label = s.cta_label || '';
            this.form.cta_url = s.cta_url || '';
            this.refreshPreview();
        },
        schedulePreview() {
            clearTimeout(this._timer);
            this._timer = setTimeout(() => this.refreshPreview(), 250);
        },
        refreshPreview() {
            const token = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value;
            fetch(@js(route('business.messaging.preview')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ channel: this.channel, ...this.form }),
            })
            .then(r => r.json())
            .then(d => { if (d.html) document.getElementById('retailer-preview').innerHTML = d.html; })
            .catch(() => {});
        },
    };
}
</script>
@endsection
