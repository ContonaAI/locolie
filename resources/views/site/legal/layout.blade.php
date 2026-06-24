@extends('site.layout')

@push('head')
<style>
    .legal-prose { color: #262626; font-size: 15.5px; line-height: 1.75; }
    .legal-prose h2 { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em; color: #0a0a0a; margin: 2.4rem 0 0.75rem; scroll-margin-top: 6rem; }
    .legal-prose h3 { font-size: 1.05rem; font-weight: 700; color: #0a0a0a; margin: 1.6rem 0 0.5rem; }
    .legal-prose p { margin: 0.75rem 0; }
    .legal-prose ul { margin: 0.75rem 0; padding-left: 1.25rem; list-style: disc; }
    .legal-prose ol { margin: 0.75rem 0; padding-left: 1.25rem; list-style: decimal; }
    .legal-prose li { margin: 0.4rem 0; }
    .legal-prose a { color: #059669; font-weight: 600; text-decoration: underline; text-underline-offset: 2px; }
    .legal-prose strong { color: #0a0a0a; font-weight: 700; }
    .legal-prose table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 14px; }
    .legal-prose th, .legal-prose td { border: 1px solid #e5e5e5; padding: 0.6rem 0.75rem; text-align: left; vertical-align: top; }
    .legal-prose th { background: #f5f5f5; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="bg-white">
    <div class="mx-auto max-w-3xl px-5 pb-24 pt-28 sm:px-6 sm:pt-32">
        {{-- Breadcrumb / cross-links --}}
        <nav class="mb-8 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
            @php
                $legalNav = [
                    ['legal.terms', 'Terms & Conditions'],
                    ['legal.privacy', 'Privacy Policy'],
                    ['legal.cookies', 'Cookie Policy'],
                ];
            @endphp
            @foreach ($legalNav as $n)
                <a href="{{ route($n[0]) }}"
                   class="font-semibold transition {{ request()->routeIs($n[0]) ? 'text-emerald' : 'text-muted hover:text-ink' }}">{{ $n[1] }}</a>
            @endforeach
        </nav>

        <header class="border-b border-hair pb-6">
            <h1 class="text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">@yield('legal_title')</h1>
            <p class="mt-3 text-sm text-muted">Last updated: <strong class="text-ink">@yield('legal_updated', config('legal.effective_date'))</strong></p>
        </header>

        <div class="legal-prose mt-8">
            @yield('legal')
        </div>

        <div class="mt-14 rounded-2xl border border-hair bg-[#f9f9f9] p-6 text-sm text-muted">
            <p>Questions about this document? Email
                <a href="mailto:{{ config('legal.contact_email') }}" class="font-semibold text-emerald">{{ config('legal.contact_email') }}</a>.
                You can <a href="{{ route('subscriptions.preferences') }}" class="font-semibold text-emerald">manage your email & SMS preferences</a> at any time.</p>
        </div>
    </div>
</div>
@endsection
