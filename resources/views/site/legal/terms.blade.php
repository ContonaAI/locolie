@extends('site.legal.layout')

@section('title', 'Terms & Conditions')
@section('legal_title', 'Terms & Conditions')
@section('legal_updated', config('legal.terms_version'))

@section('legal')
@php
    $L = config('legal');
    $controller = $L['company']
        .($L['company_number'] ? ' (company no. '.$L['company_number'].')' : '')
        .($L['registered_address'] ? ', whose registered office is at '.$L['registered_address'] : '');
@endphp

<p>These Terms &amp; Conditions ("Terms") govern your use of {{ $L['trading_name'] }} (the "Platform", "we", "us", "our"), a service operated by {{ $controller }}. By creating an account, listing a business, or otherwise using the Platform, you agree to these Terms. If you do not agree, please do not use the Platform.</p>

<h2 id="who-we-are">1. Who we are &amp; what locolie does</h2>
<p>locolie helps people discover real discounts and offers from independent local businesses, and helps those independents reach nearby customers. We provide a marketplace and listing service. We are <strong>not</strong> a party to any transaction between a shopper and a business: any purchase, offer, discount or service is a contract solely between you and the relevant business.</p>

<h2 id="eligibility">2. Eligibility &amp; accounts</h2>
<ul>
    <li>You must be at least 16 years old to use the Platform.</li>
    <li>If you register on behalf of a business, you confirm you are authorised to bind that business to these Terms.</li>
    <li>You are responsible for keeping your login details secure and for all activity under your account.</li>
    <li>You must give accurate information and keep it up to date.</li>
</ul>

<h2 id="shoppers">3. Using locolie as a shopper</h2>
<p>Offers are provided by independent businesses, not by locolie. We do not guarantee that any offer will be available, accurate, or honoured. Offers may be limited in quantity or time, may carry their own conditions set by the business, and may be withdrawn at any time. When you redeem an offer you may be asked to show a code in store. Any dispute about an offer is between you and the business, though we will help where we reasonably can.</p>

<h2 id="businesses">4. Listing a business</h2>
<p>If you list a business on locolie:</p>
<ul>
    <li>You confirm the business is a genuine, independently owned business and that all listing details are accurate and lawful.</li>
    <li>You are responsible for the offers you publish, for honouring them, and for complying with all laws that apply to them (including consumer protection, advertising and pricing rules).</li>
    <li>You grant us a non-exclusive, royalty-free licence to display your business name, logo, photos, descriptions and offers on the Platform and in our marketing of the Platform.</li>
    <li>You must not list content that is false, misleading, offensive, infringing, or that you do not have the right to use.</li>
    <li>Where you collect customer details through the Platform (for example shoppers who opt in to hear from you), you become a data controller of those details and must handle them lawfully - see our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>.</li>
</ul>

<h2 id="plans">5. Plans, fees &amp; payment</h2>
<p>Basic listings are free. Paid plans (such as priority placement) are charged at the prices shown on our <a href="/for-business">For Business</a> page at the time you subscribe. Unless stated otherwise:</p>
<ul>
    <li>Paid plans are billed in advance on a recurring basis until cancelled.</li>
    <li>You can cancel a paid plan at any time; cancellation takes effect at the end of the current billing period and we do not provide pro-rata refunds for part-periods unless required by law.</li>
    <li>We may change plan pricing on reasonable notice; changes do not affect the period you have already paid for.</li>
    <li>Prices are stated inclusive or exclusive of VAT as indicated at checkout.</li>
</ul>

<h2 id="acceptable-use">6. Acceptable use</h2>
<p>You must not misuse the Platform. In particular you must not: break any law; infringe anyone's rights; upload malware; scrape or harvest data without our permission; attempt to gain unauthorised access; impersonate others; or use the Platform to send spam or unlawful marketing. We may suspend or remove any account or content that breaches these Terms.</p>

<h2 id="ip">7. Intellectual property</h2>
<p>The Platform, including its design, software, and the locolie name and branding, belongs to {{ $L['company'] }} or its licensors and is protected by law. We grant you a limited, non-transferable licence to use the Platform for its intended purpose. You keep ownership of content you submit, but grant us the licence described in section 4.</p>

<h2 id="availability">8. Availability &amp; changes</h2>
<p>We work hard to keep the Platform available but provide it "as is" and "as available". We may change, suspend or withdraw features, and we do not guarantee the Platform will be uninterrupted or error-free.</p>

<h2 id="liability">9. Our liability</h2>
<p>Nothing in these Terms limits liability that cannot be limited by law (such as for death or personal injury caused by our negligence, or for fraud). Subject to that:</p>
<ul>
    <li>We are not liable for the acts, omissions, offers, goods or services of any business listed on the Platform.</li>
    <li>We are not liable for indirect or consequential loss, loss of profit, or loss of goodwill.</li>
    <li>Where we are liable to a business, our total liability in any 12-month period is limited to the fees you paid us in that period (or £100 if you paid nothing).</li>
    <li>If you are a consumer, you have legal rights that these Terms do not affect.</li>
</ul>

<h2 id="indemnity">10. Indemnity (business users)</h2>
<p>If you use the Platform as a business, you agree to indemnify us against claims, losses and costs arising from your listings, offers, content, or breach of these Terms.</p>

<h2 id="termination">11. Suspension &amp; termination</h2>
<p>You may stop using the Platform and close your account at any time. We may suspend or end your access if you breach these Terms or if we reasonably need to. On termination, the licences you granted us for content already displayed may continue to the extent needed for our records and legal obligations.</p>

<h2 id="privacy">12. Privacy &amp; marketing</h2>
<p>We process personal data in line with our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>. You can manage or withdraw marketing consent at any time through our <a href="{{ route('subscriptions.preferences') }}">preference centre</a> or the unsubscribe link in any message.</p>

<h2 id="changes">13. Changes to these Terms</h2>
<p>We may update these Terms from time to time. If we make material changes we will take reasonable steps to let you know. Continuing to use the Platform after changes take effect means you accept the updated Terms.</p>

<h2 id="law">14. Governing law</h2>
<p>These Terms are governed by the laws of {{ $L['jurisdiction'] }}, and the courts of {{ $L['jurisdiction'] }} have non-exclusive jurisdiction over any dispute.</p>

<h2 id="contact">15. Contact us</h2>
<p>{{ $L['company'] }}<br>
@if($L['registered_address']){{ $L['registered_address'] }}<br>@endif
Email: <a href="mailto:{{ $L['contact_email'] }}">{{ $L['contact_email'] }}</a></p>

<p class="mt-8 text-sm text-muted"><em>This document is not legal advice. Please have it reviewed by a qualified solicitor before relying on it.</em></p>
@endsection
