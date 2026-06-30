{{--
    Printable in-store QR poster. Self-contained: the QR is an inline SVG (no
    external request), so it prints and renders offline. Stand it on the counter
    or stick it in the window; shoppers scan it to join the list.
--}}
@php $accent = $business->brandColor(); @endphp
<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>QR poster - {{ $business->name }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
    :root{ --accent: {{ $accent }}; }
    *{ box-sizing:border-box; }
    body{ font-family:'Inter',system-ui,sans-serif; margin:0; background:#eef0f4; color:#0a0a0a;
          display:flex; flex-direction:column; align-items:center; padding:28px 16px; }
    .toolbar{ width:420px; max-width:94vw; display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
    .toolbar a{ color:#64748b; text-decoration:none; font-size:14px; font-weight:600; }
    .btn{ background:#0a0a0a; color:#fff; border:0; border-radius:10px; padding:10px 18px; font-weight:700; font-size:14px; cursor:pointer; }
    .poster{ width:420px; max-width:94vw; background:#fff; border-radius:28px; box-shadow:0 24px 60px rgba(15,45,56,.18);
             padding:40px 34px; text-align:center; border:3px solid var(--accent); }
    .powered{ font-size:12px; font-weight:700; letter-spacing:.02em; color:#94a3b8; }
    .powered .dot{ display:inline-block; width:9px; height:9px; border-radius:50%; background:var(--accent); vertical-align:middle; margin-right:5px; }
    .logo{ width:64px; height:64px; border-radius:18px; margin:18px auto 10px; display:flex; align-items:center; justify-content:center;
           background:color-mix(in srgb, var(--accent) 12%, #fff); overflow:hidden; }
    .logo img{ width:100%; height:100%; object-fit:contain; }
    .logo span{ font-weight:900; font-size:24px; color:var(--accent); }
    .name{ font-size:26px; font-weight:900; letter-spacing:-.03em; }
    .headline{ font-size:18px; font-weight:800; margin-top:14px; }
    .sub{ font-size:14px; color:#64748b; margin-top:6px; line-height:1.5; }
    .qr{ display:inline-block; padding:16px; background:#fff; border:2px solid #e2e8f0; border-radius:20px; margin:22px 0 12px; }
    .qr svg{ display:block; }
    .scan{ font-size:18px; font-weight:800; }
    .perks{ display:flex; justify-content:center; gap:14px; margin-top:16px; flex-wrap:wrap; }
    .perk{ font-size:12px; font-weight:700; color:#475569; background:#f1f5f9; border-radius:999px; padding:6px 12px; }
    .foot{ margin-top:20px; padding-top:16px; border-top:1px dashed #d7dce2; font-size:12px; color:#8a93a0; }
    .url{ font-size:11px; color:#6b7480; word-break:break-all; margin-top:6px; }
    @@media print{ body{ background:#fff; padding:0; } .toolbar{ display:none; } .poster{ box-shadow:none; margin:24px auto; } }
</style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('business.marketing') }}">&larr; Back to marketing</a>
        <button class="btn" onclick="window.print()">Print poster</button>
    </div>

    <div class="poster">
        <div class="powered"><span class="dot"></span>powered by locolie</div>

        <div class="logo">
            @if ($business->logoUrl())
                <img src="{{ $business->logoUrl() }}" alt="{{ $business->name }}">
            @else
                <span>{{ $business->brandInitials() }}</span>
            @endif
        </div>
        <div class="name">{{ $business->name }}</div>

        <div class="headline">Scan to join our list</div>
        <div class="sub">Be first to get our offers and news. No app needed - just point your phone camera at the code.</div>

        <div class="qr">{!! $qrSvg !!}</div>
        <div class="scan">Point your camera here</div>

        <div class="perks">
            <span class="perk">Exclusive offers</span>
            <span class="perk">Email + text alerts</span>
            <span class="perk">Unsubscribe anytime</span>
        </div>

        <div class="foot">
            Your customers' details stay protected. GDPR consent is captured automatically.
            <div class="url">{{ $captureUrl }}</div>
        </div>
    </div>
</body>
</html>
