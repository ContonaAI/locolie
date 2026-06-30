@extends('portal.layout')
@section('title', 'Handles & accounts')

@php use App\Models\SocialAccount; @endphp

@section('content')
@include('portal.social._nav', ['tab' => 'accounts'])

<p class="text-sm text-slate-500 max-w-2xl mb-6">
    Store the {{ $llCity }} handle for each platform now so the calendar reads cleanly. Connecting an account for direct publishing needs the platform's approved developer app - the connect button starts the OAuth flow once that app's client id/secret are in <span class="font-mono text-xs">.env</span>.
</p>

<div class="grid md:grid-cols-2 gap-4">
    @foreach ($platforms as $p)
        @php $acct = $accounts->get($p); $live = $acct?->isConnected(); @endphp
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold" style="background: {{ SocialAccount::color($p) }}">
                        {{ strtoupper(substr(SocialAccount::label($p), 0, 1)) }}
                    </span>
                    <span class="font-bold text-slate-900">{{ SocialAccount::label($p) }}</span>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $live ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $live ? 'Connected' : 'Not connected' }}
                </span>
            </div>

            {{-- Handle + display name --}}
            <form method="POST" action="{{ route('social.accounts.save', $p) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Handle</label>
                    <input type="text" name="handle" value="{{ $acct?->handle }}" placeholder="@locolie{{ strtolower($llCity) }}"
                           class="w-full rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Display name</label>
                    <input type="text" name="display_name" value="{{ $acct?->display_name }}" placeholder="locolie {{ $llCity }}"
                           class="w-full rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                </div>
                <button class="text-sm font-semibold text-emerald-700 hover:underline">Save details</button>
            </form>

            <div class="border-t border-slate-100 my-4"></div>

            {{-- Connect / disconnect (OAuth) --}}
            <div class="flex items-center justify-between gap-3">
                @if ($live)
                    <span class="text-xs text-slate-500">
                        Token {{ $acct->isExpired() ? 'expired' : 'active' }}@if($acct->token_expires_at) · expires {{ $acct->token_expires_at->format('j M Y') }}@endif
                    </span>
                    <form method="POST" action="{{ route('social.disconnect', $p) }}">
                        @csrf
                        <button class="text-xs font-semibold text-rose-600 hover:underline">Disconnect</button>
                    </form>
                @else
                    <span class="text-xs text-slate-400">Publishing disabled until connected</span>
                    <a href="{{ route('social.connect', $p) }}" class="rounded-lg bg-slate-900 text-white text-xs font-semibold px-3.5 py-2 hover:bg-slate-800">Connect {{ SocialAccount::label($p) }}</a>
                @endif
            </div>
        </div>
    @endforeach
</div>

{{-- ── Developer app setup notes ───────────────────────────────────────── --}}
<div class="mt-10 rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="text-lg font-bold text-slate-900 mb-2">Going live with direct publishing</h2>
    <p class="text-sm text-slate-500 mb-4">The publishing plumbing is built. Each platform needs an approved developer app before connect/publish work. Add the credentials to <span class="font-mono text-xs">.env</span> (see <span class="font-mono text-xs">config/services.php</span> &rarr; <span class="font-mono text-xs">services.social</span>), then use Connect above.</p>
    <ul class="space-y-2 text-sm text-slate-600">
        <li><span class="font-semibold text-slate-800">Facebook / Instagram:</span> Meta app with <span class="font-mono text-xs">pages_manage_posts</span> + <span class="font-mono text-xs">instagram_content_publish</span> (App Review). Set <span class="font-mono text-xs">FB_APP_ID</span> / <span class="font-mono text-xs">FB_APP_SECRET</span>.</li>
        <li><span class="font-semibold text-slate-800">TikTok:</span> TikTok for Developers app with the Content Posting API + <span class="font-mono text-xs">video.publish</span> scope (audit required). Set <span class="font-mono text-xs">TIKTOK_CLIENT_KEY</span> / <span class="font-mono text-xs">TIKTOK_CLIENT_SECRET</span>.</li>
        <li><span class="font-semibold text-slate-800">LinkedIn:</span> LinkedIn app with the Community Management / Share API + <span class="font-mono text-xs">w_member_social</span> scope (access request). Set <span class="font-mono text-xs">LINKEDIN_CLIENT_ID</span> / <span class="font-mono text-xs">LINKEDIN_CLIENT_SECRET</span>.</li>
    </ul>
</div>
@endsection
