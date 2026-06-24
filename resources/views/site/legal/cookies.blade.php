@extends('site.legal.layout')

@section('title', 'Cookie Policy')
@section('legal_title', 'Cookie Policy')
@section('legal_updated', config('legal.cookies_version'))

@section('legal')
@php
    $L = config('legal');
@endphp

<p>This Cookie Policy explains how {{ $L['trading_name'] }} uses cookies and similar technologies on our website. It should be read alongside our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>.</p>

<h2 id="what">1. What are cookies?</h2>
<p>Cookies are small text files stored on your device when you visit a website. Similar technologies include local storage, pixels and SDKs. They help a site work, remember your choices, and understand how it is used.</p>

<h2 id="types">2. The cookies we use</h2>
<table>
    <thead><tr><th>Type</th><th>Purpose</th><th>Consent needed?</th></tr></thead>
    <tbody>
        <tr><td><strong>Strictly necessary</strong></td><td>Security, session, load balancing, remembering your cookie choices and language. The site can't work properly without these.</td><td>No (exempt)</td></tr>
        <tr><td><strong>Functional</strong></td><td>Remembering your chosen area and preferences for a better experience.</td><td>Yes</td></tr>
        <tr><td><strong>Analytics</strong></td><td>Understanding how visitors use the site so we can improve it (aggregated and, where possible, anonymised).</td><td>Yes</td></tr>
        <tr><td><strong>Marketing</strong></td><td>Measuring campaigns and showing relevant content. We only set these with your consent.</td><td>Yes</td></tr>
    </tbody>
</table>

<h2 id="third-party">3. Third-party tools</h2>
<p>Some features rely on third parties that may set their own cookies, including Google Maps (to show the map), Google Translate (the language switcher), and OpenStreetMap/Nominatim (approximate location). These providers have their own privacy and cookie notices.</p>

<h2 id="managing">4. Managing your choices</h2>
<p>When you first visit, we ask for your consent to non-essential cookies through our cookie banner. You can change your choice at any time using the <button type="button" onclick="try{localStorage.removeItem('ll_cookie_consent');location.reload();}catch(e){}" class="legal-cookie-reset">cookie settings</button> link below, or by clearing cookies in your browser. Most browsers also let you block or delete cookies in their settings - though some parts of the site may not work as well if you do.</p>

<h2 id="changes">5. Changes</h2>
<p>We may update this policy as our use of cookies changes. Please check back for the latest version.</p>

<h2 id="contact">6. Contact</h2>
<p>Questions about cookies? Email <a href="mailto:{{ $L['privacy_email'] }}">{{ $L['privacy_email'] }}</a>.</p>

<style>
    .legal-cookie-reset { color: #059669; font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }
</style>
@endsection
