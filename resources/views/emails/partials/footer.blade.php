{{--
    Compliant marketing-email footer (UK GDPR / PECR / CAN-SPAM).
    Required for any marketing message. Pass:
      $recipientEmail  - the contact's email (for the signed links)
      $topic           - the subscription topic this email was sent under (optional)

    Also set these mail headers when you send, so Gmail/Apple show a native
    one-click unsubscribe:
      List-Unsubscribe: <{{ isset($recipientEmail) ? \App\Models\Subscription::unsubscribeUrl($recipientEmail, $topic ?? null) : '' }}>
      List-Unsubscribe-Post: List-Unsubscribe=One-Click
--}}
@php
    $L = config('legal');
    $manageUrl = isset($recipientEmail) ? \App\Models\Subscription::preferencesUrl($recipientEmail) : route('subscriptions.preferences');
    $unsubUrl = isset($recipientEmail) ? \App\Models\Subscription::unsubscribeUrl($recipientEmail, $topic ?? null) : route('subscriptions.preferences');
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:32px;border-top:1px solid #e5e5e5;">
    <tr>
        <td style="padding:20px 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#737373;">
            <p style="margin:0 0 6px;">
                You're receiving this because you subscribed to {{ $L['trading_name'] }}.
            </p>
            <p style="margin:0 0 6px;">
                <a href="{{ $manageUrl }}" style="color:#059669;font-weight:bold;">Manage preferences</a>
                &nbsp;·&nbsp;
                <a href="{{ $unsubUrl }}" style="color:#059669;font-weight:bold;">Unsubscribe</a>
            </p>
            <p style="margin:0;color:#a3a3a3;">
                {{ $L['company'] }}, {{ $L['registered_address'] }}
            </p>
        </td>
    </tr>
</table>
