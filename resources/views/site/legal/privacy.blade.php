@extends('site.legal.layout')

@section('title', 'Privacy Policy')
@section('legal_title', 'Privacy Policy')
@section('legal_updated', config('legal.privacy_version'))

@section('legal')
@php
    $L = config('legal');
@endphp

<p>This Privacy Policy explains how {{ $L['trading_name'] }}, operated by {{ $L['company'] }} ("we", "us", "our"), collects and uses your personal data, and your rights under the UK General Data Protection Regulation (UK GDPR) and the Data Protection Act 2018. We are the "data controller" for the personal data described here.</p>

<h2 id="contact">1. Who we are &amp; how to contact us</h2>
<p><strong>Controller:</strong> {{ $L['company'] }}<br>
@if($L['registered_address'])<strong>Registered office:</strong> {{ $L['registered_address'] }}<br>@endif
@if($L['ico_registration'])<strong>ICO registration:</strong> {{ $L['ico_registration'] }}<br>@endif
<strong>Privacy contact:</strong> <a href="mailto:{{ $L['privacy_email'] }}">{{ $L['privacy_email'] }}</a></p>

<h2 id="data-we-collect">2. The data we collect</h2>
<table>
    <thead><tr><th>Category</th><th>Examples</th></tr></thead>
    <tbody>
        <tr><td>Identity &amp; contact</td><td>Name, email address, phone number, postcode</td></tr>
        <tr><td>Account</td><td>Login email, hashed password, role, business you own</td></tr>
        <tr><td>Business listing</td><td>Business name, address, description, photos, offers, Google Place details</td></tr>
        <tr><td>Usage &amp; redemptions</td><td>Offers you view and redeem, redemption codes, favourites</td></tr>
        <tr><td>Consent &amp; preferences</td><td>Marketing opt-ins, subscription topics, consent history, IP at consent</td></tr>
        <tr><td>Technical</td><td>IP address, browser/device type, cookie identifiers, approximate location (if you allow it)</td></tr>
    </tbody>
</table>

<h2 id="how-we-use">3. How &amp; why we use your data (lawful bases)</h2>
<table>
    <thead><tr><th>Purpose</th><th>Lawful basis</th></tr></thead>
    <tbody>
        <tr><td>Provide the Platform, your account and offer redemptions</td><td>Performance of a contract</td></tr>
        <tr><td>Show offers and businesses near you</td><td>Legitimate interests (running the service)</td></tr>
        <tr><td>Send marketing emails/SMS about offers and updates</td><td>Consent (and the "soft opt-in" for existing contacts, where it applies)</td></tr>
        <tr><td>Keep the Platform secure and prevent fraud/abuse</td><td>Legitimate interests</td></tr>
        <tr><td>Take payment for paid plans</td><td>Performance of a contract</td></tr>
        <tr><td>Meet legal, tax and accounting obligations</td><td>Legal obligation</td></tr>
    </tbody>
</table>
<p>Where we rely on consent, you can withdraw it at any time without affecting processing already carried out. Where we rely on legitimate interests, you can object - see your rights below.</p>

<h2 id="marketing">4. Marketing &amp; your choices</h2>
<p>We only send marketing where the law allows it and you can opt out at any time. You control exactly what you receive - offers near you, product updates, SMS alerts, and (for businesses) owner emails - through our <a href="{{ route('subscriptions.preferences') }}">preference centre</a>, or by using the unsubscribe link in any email. Unsubscribing is honoured promptly and free of charge.</p>

<h2 id="sharing">5. Who we share data with</h2>
<ul>
    <li><strong>Businesses you interact with:</strong> if you redeem an offer or opt in to hear from a business, that business receives the details you provided and becomes a controller of them.</li>
    <li><strong>Service providers (processors):</strong> hosting, email/SMS delivery, payment processing, analytics and mapping providers, who act on our instructions under contract.</li>
    <li><strong>Authorities:</strong> where we are required by law, or to protect our rights or safety.</li>
    <li><strong>Business transfers:</strong> if we reorganise, merge or are acquired, data may transfer to the new entity.</li>
</ul>
<p>We do not sell your personal data.</p>

<h2 id="transfers">6. International transfers</h2>
<p>Some providers may process data outside the UK/EEA. Where they do, we rely on appropriate safeguards such as UK adequacy regulations or the International Data Transfer Agreement / Standard Contractual Clauses.</p>

<h2 id="retention">7. How long we keep data</h2>
<p>We keep personal data only as long as needed for the purposes above: account data while your account is active and for a reasonable period afterwards; redemption records for our and the business's legitimate records; consent and unsubscribe records for as long as needed to prove compliance; and financial records for the periods required by tax law (normally 6 years).</p>

<h2 id="your-rights">8. Your rights</h2>
<p>Under UK GDPR you have the right to: access your data; have it corrected; have it erased; restrict or object to processing; data portability; and to withdraw consent. You also have the right not to be subject to solely automated decisions with legal effects. To exercise any right, email <a href="mailto:{{ $L['privacy_email'] }}">{{ $L['privacy_email'] }}</a>. We will respond within one month.</p>

<h2 id="complaints">9. Complaints</h2>
<p>If you have a concern we'd like the chance to resolve it. You also have the right to complain to the UK's data protection regulator, the Information Commissioner's Office (ICO), at <a href="https://ico.org.uk" rel="noopener" target="_blank">ico.org.uk</a> or 0303 123 1113.</p>

<h2 id="cookies">10. Cookies</h2>
<p>We use cookies and similar technologies as described in our <a href="{{ route('legal.cookies') }}">Cookie Policy</a>.</p>

<h2 id="children">11. Children</h2>
<p>The Platform is not intended for children under 16 and we do not knowingly collect their data.</p>

<h2 id="changes">12. Changes to this policy</h2>
<p>We may update this policy from time to time. We will post the new version here and update the "last updated" date; material changes will be brought to your attention where appropriate.</p>

<p class="mt-8 text-sm text-muted"><em>This document is not legal advice. Please have it reviewed by a qualified data protection specialist before relying on it.</em></p>
@endsection
