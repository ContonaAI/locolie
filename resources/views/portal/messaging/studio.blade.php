@extends('portal.layout')
@section('title', 'Messaging Studio')

@section('content')
@include('portal.messaging._nav', ['tab' => 'overview'])

{{-- ── Headline stats ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
    @foreach ([
        ['Email audience', $stats['email_audience'], 'businesses reachable', 'text-sky-600'],
        ['SMS audience', $stats['sms_audience'], 'opted-in phones', 'text-violet-600'],
        ['Push audience', $stats['push_audience'], 'devices + browsers', 'text-amber-600'],
        ['Messages sent', $stats['sent'], 'all time', 'text-emerald-600'],
    ] as [$label, $value, $sub, $accent])
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="text-3xl font-extrabold {{ $accent }}">{{ number_format($value) }}</div>
            <div class="text-sm font-semibold text-slate-700 mt-1">{{ $label }}</div>
            <div class="text-xs text-slate-400">{{ $sub }}</div>
        </div>
    @endforeach
</div>

{{-- ── Channel connection cards ────────────────────────────────────────── --}}
<h2 class="text-lg font-bold text-slate-900 mb-3">Channels</h2>
<div class="grid md:grid-cols-3 gap-4 mb-10">
    @foreach ($overview as $key => $ch)
        @php $live = $ch['connected']; @endphp
        <div class="rounded-2xl border border-slate-200 bg-white p-5 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <span class="font-bold text-slate-900">{{ $ch['label'] }}</span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $live ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $live ? 'Connected · '.$ch['provider'] : 'Demo mode' }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mb-4">{{ $channels[$key]['blurb'] }}</p>

            <div class="space-y-1.5 mb-4">
                @foreach ($ch['providers'] as $slug => $p)
                    <div class="flex items-center justify-between gap-2 text-sm">
                        <span class="text-slate-600">{{ $p['label'] }} @if(!empty($p['recommended']))<span class="text-emerald-600 text-xs font-semibold">· recommended</span>@endif</span>
                        @if ($ch['provider'] === $slug)
                            <form method="POST" action="{{ route('messaging.disconnect') }}">@csrf
                                <input type="hidden" name="channel" value="{{ $key }}"><input type="hidden" name="provider" value="{{ $slug }}">
                                <button class="text-xs font-semibold text-rose-600 hover:underline">Disconnect</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('messaging.connect') }}">@csrf
                                <input type="hidden" name="channel" value="{{ $key }}"><input type="hidden" name="provider" value="{{ $slug }}">
                                <input type="hidden" name="label" value="{{ $p['label'] }} (demo)">
                                <button class="text-xs font-semibold text-emerald-600 hover:underline">Connect</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            <a href="{{ route('messaging.'.$key) }}" class="mt-auto inline-flex items-center justify-center rounded-lg bg-slate-900 text-white text-sm font-semibold px-4 py-2.5 hover:bg-slate-800">
                Open {{ $ch['label'] }} studio
            </a>
        </div>
    @endforeach
</div>

{{-- ── Per-brand identity (logos & colours) ────────────────────────────── --}}
<div x-data="{ sel: {{ $businesses->first()->id ?? 'null' }} }" class="rounded-2xl border border-slate-200 bg-white p-6 mb-10">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-1">
        <h2 class="text-lg font-bold text-slate-900">Brand identity</h2>
        <select x-model="sel" class="rounded-lg border border-slate-300 text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 outline-none">
            @foreach ($businesses as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>
    </div>
    <p class="text-sm text-slate-500 mb-5">Upload a logo and set the accent colour + sender names. Every email, SMS and push for this brand is rendered with these.</p>

    @forelse ($businesses as $b)
        <form x-show="sel == {{ $b->id }}" method="POST" action="{{ route('messaging.brand', $b) }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-5" x-cloak>
            @csrf
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl border border-slate-200 flex items-center justify-center overflow-hidden shrink-0" style="background: {{ $b->brandColor() }}1a">
                    @if ($b->logoUrl())
                        <img src="{{ $b->logoUrl() }}" alt="{{ $b->name }}" class="w-full h-full object-contain">
                    @else
                        <span class="font-extrabold text-lg" style="color: {{ $b->brandColor() }}">{{ $b->brandInitials() }}</span>
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
                    <input type="color" name="brand_color" value="{{ $b->brandColor() }}" class="h-10 w-14 rounded-lg border border-slate-300 bg-white p-1">
                    <span class="mono text-sm text-slate-500">{{ $b->brandColor() }}</span>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Email sender name</label>
                <input type="text" name="email_from_name" value="{{ $b->email_from_name }}" placeholder="{{ $b->name }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Reply-to email</label>
                <input type="email" name="reply_to_email" value="{{ $b->reply_to_email }}" placeholder="hello@{{ $b->slug }}.co.uk" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">SMS sender ID (max 11 chars)</label>
                <input type="text" name="sms_sender_id" maxlength="11" value="{{ $b->sms_sender_id }}" placeholder="{{ $b->smsSenderId() }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div class="sm:col-span-2">
                <button class="rounded-lg bg-emerald-600 text-white text-sm font-bold px-5 py-2.5 hover:bg-emerald-700">Save brand identity</button>
            </div>
        </form>
    @empty
        <p class="text-sm text-slate-400">No businesses yet - onboard one in Admin first.</p>
    @endforelse
</div>

{{-- ── Recent activity ─────────────────────────────────────────────────── --}}
<div class="rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="text-lg font-bold text-slate-900 mb-4">Recent messages</h2>
    @forelse ($campaigns as $c)
        <div class="flex items-center justify-between gap-3 py-2.5 border-b border-slate-100 last:border-0">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold
                        {{ ['email' => 'bg-sky-100 text-sky-700', 'sms' => 'bg-violet-100 text-violet-700', 'push' => 'bg-amber-100 text-amber-700'][$c->channel] ?? 'bg-slate-100 text-slate-600' }}">
                        {{ ucfirst($c->channel) }}
                    </span>
                    <span class="font-semibold text-slate-800 truncate">{{ $c->subject ?: \Illuminate\Support\Str::limit($c->body, 50) }}</span>
                </div>
                <div class="text-xs text-slate-400 mt-0.5">{{ $c->business->name ?? 'Platform' }} · {{ $c->created_at?->diffForHumans() }} · {{ ucfirst($c->status ?? 'sent') }}</div>
            </div>
            <div class="text-sm font-bold text-slate-700 shrink-0">{{ number_format($c->sent_count) }} sent</div>
        </div>
    @empty
        <p class="text-center text-slate-400 text-sm py-6">No messages sent yet. Open a channel studio to compose one.</p>
    @endforelse
</div>
@endsection
