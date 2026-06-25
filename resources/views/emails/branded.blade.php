@php
    // Normalised email data - shared by the real email and the on-screen mockup.
    $brandColor = $preview['brand_color'] ?? '#059669';
    $brandName = $preview['brand_name'] ?? 'locolie';
    $subject = $preview['subject'] ?? '';
    $preheader = $preview['preheader'] ?? '';
    $bodyHtml = $preview['body_html'] ?? '';
    $ctaLabel = $preview['cta_label'] ?? '';
    $ctaUrl = $preview['cta_url'] ?? '';
    $logoUrl = $preview['logo_url'] ?? null;
    $initials = $preview['brand_initials'] ?? 'LO';
    $footer = $preview['footer'] ?? '';
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>{{ $subject }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">
    {{-- Preheader: shown in the inbox preview line, hidden in the body --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#f1f5f9; opacity:0;">
        {{ $preheader ?: \Illuminate\Support\Str::limit(strip_tags($bodyHtml), 110) }}
        &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;">
        <tr>
            <td align="center" style="padding:24px 12px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px; background-color:#ffffff; border-radius:16px; overflow:hidden; font-family:'Inter','Helvetica Neue',Helvetica,Arial,sans-serif; box-shadow:0 1px 3px rgba(15,23,42,0.08);">

                    {{-- Brand header band --}}
                    <tr>
                        <td style="background-color:{{ $brandColor }}; padding:28px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td valign="middle" style="width:48px;">
                                        @if ($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $brandName }}" width="48" height="48" style="display:block; width:48px; height:48px; border-radius:12px; object-fit:contain; background-color:#ffffff;" />
                                        @else
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td align="center" valign="middle" style="width:48px; height:48px; background-color:rgba(255,255,255,0.18); border-radius:12px; color:#ffffff; font-size:18px; font-weight:800; font-family:'Inter',Arial,sans-serif;">
                                                        {{ $initials }}
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                    <td valign="middle" style="padding-left:14px; color:#ffffff; font-size:18px; font-weight:700; letter-spacing:-0.01em;">
                                        {{ $brandName }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:36px 32px 8px;">
                            <h1 style="margin:0 0 18px; font-size:26px; line-height:1.25; font-weight:800; color:#0f172a; letter-spacing:-0.02em;">
                                {{ $subject }}
                            </h1>
                            <div style="font-size:16px; line-height:1.6; color:#334155;">
                                {!! $bodyHtml !!}
                            </div>
                        </td>
                    </tr>

                    {{-- CTA button --}}
                    @if ($ctaLabel && $ctaUrl)
                        <tr>
                            <td style="padding:12px 32px 36px;">
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td align="center" style="border-radius:10px; background-color:{{ $brandColor }};">
                                            <a href="{{ $ctaUrl }}" target="_blank" style="display:inline-block; padding:14px 30px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:10px;">
                                                {{ $ctaLabel }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @else
                        <tr><td style="padding:0 32px 28px;"></td></tr>
                    @endif

                    {{-- Divider --}}
                    <tr>
                        <td style="padding:0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr><td style="border-top:1px solid #e2e8f0; font-size:0; line-height:0;">&nbsp;</td></tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:22px 32px 30px; font-family:'Inter',Arial,sans-serif;">
                            @if ($footer)
                                <p style="margin:0 0 10px; font-size:13px; line-height:1.5; color:#64748b;">
                                    {{ $footer }}
                                </p>
                            @endif
                            <p style="margin:0; font-size:12px; line-height:1.5; color:#94a3b8;">
                                Sent with care by {{ $brandName }} on
                                <span style="color:#059669; font-weight:600;">locolie</span> -
                                your local-deals marketplace.
                            </p>

                            {{-- Compliant GDPR/PECR unsubscribe + preferences footer.
                                 $recipient is ['email' => ..., 'name' => ...] from BrandedCampaign;
                                 the partial expects $recipientEmail (string) and optional $topic. --}}
                            @include('emails.partials.footer', [
                                'recipientEmail' => $recipient['email'] ?? null,
                                'topic' => $preview['topic'] ?? null,
                            ])
                        </td>
                    </tr>

                </table>

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px;">
                    <tr>
                        <td align="center" style="padding:18px 12px; font-family:'Inter',Arial,sans-serif; font-size:11px; color:#94a3b8;">
                            locolie - discover and support local businesses.
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
@if (! empty($preview['open_pixel_url']))
    <img src="{{ $preview['open_pixel_url'] }}" width="1" height="1" alt="" style="display:block; width:1px; height:1px; border:0;" />
@endif
</body>
</html>
