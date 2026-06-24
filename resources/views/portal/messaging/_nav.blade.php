{{-- Messaging Studio sub-navigation. $tab = overview|email|sms|push --}}
@php
    $tabs = [
        ['key' => 'overview', 'route' => 'messaging.studio', 'label' => 'Overview', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['key' => 'email', 'route' => 'messaging.email', 'label' => 'Email', 'icon' => 'M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['key' => 'sms', 'route' => 'messaging.sms', 'label' => 'SMS', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
        ['key' => 'push', 'route' => 'messaging.push', 'label' => 'Push', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
    ];
@endphp
<div class="mb-8">
    <div class="flex items-center gap-2.5 mb-1.5">
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-600 text-white shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Messaging Studio</h1>
    </div>
    <p class="text-slate-500 max-w-2xl">Branded email, SMS and push - composed once, bespoke to every brand, ready for web today and the iOS + Android apps tomorrow.</p>

    <nav class="mt-6 flex items-center gap-1 overflow-x-auto border-b border-slate-200 -mb-px">
        @foreach ($tabs as $t)
            <a href="{{ route($t['route']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold whitespace-nowrap border-b-2 transition
                      {{ ($tab ?? 'overview') === $t['key'] ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/></svg>
                {{ $t['label'] }}
            </a>
        @endforeach
    </nav>
</div>
