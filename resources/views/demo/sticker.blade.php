<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Window sticker - {{ $business->name }}</title>
<script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs@master/qrcode.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root{ --ink:#0a0a0a; --accent:#059669; --cta:#059669; }
  *{ box-sizing:border-box; }
  body{ font-family:'Inter',sans-serif; margin:0; background:#eef0f4; color:var(--ink); display:flex; flex-direction:column; align-items:center; padding:32px 16px; }
  .toolbar{ width:360px; max-width:92vw; display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
  .toolbar a{ color:#64748b; text-decoration:none; font-size:14px; }
  .btn{ background:var(--ink); color:#fff; border:0; border-radius:10px; padding:9px 16px; font-weight:600; font-size:14px; cursor:pointer; }
  .sticker{ width:360px; max-width:92vw; background:#fff; border-radius:24px; box-shadow:0 20px 50px rgba(15,45,56,.18); padding:34px 30px; text-align:center; border:2px solid var(--ink); }
  .brand{ font-weight:800; font-size:28px; letter-spacing:-.04em; color:var(--ink); }
  .brand .o{ color:var(--accent); }
  .eyebrow{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:.16em; text-transform:uppercase; color:var(--accent); margin-top:6px; }
  .qr{ display:inline-block; padding:14px; background:#fff; border-radius:16px; margin:22px 0 8px; }
  .qr img, .qr canvas{ display:block; margin:0 auto; }
  .scan{ font-size:22px; font-weight:800; letter-spacing:-.02em; }
  .biz{ font-size:15px; color:#4a5560; margin-top:4px; }
  .foot{ margin-top:18px; padding-top:16px; border-top:1px dashed #d7dce2; font-size:12px; color:#8a93a0; }
  .url{ font-family:'JetBrains Mono',monospace; font-size:11px; color:#6b7480; word-break:break-all; margin-top:6px; }
  @@media print { body{ background:#fff; padding:0; } .toolbar{ display:none; } .sticker{ box-shadow:none; margin:24px auto; } }
</style>
</head>
<body>
  <div class="toolbar">
    <a href="javascript:history.back()">&larr; Back</a>
    <button class="btn" onclick="window.print()">Print sticker</button>
  </div>

  <div class="sticker">
    <div class="brand">l<span class="o">o</span>colie</div>
    <div class="eyebrow">Local offers</div>
    <div class="qr" id="qr"></div>
    <div class="scan">Scan for today's offer</div>
    <div class="biz">{{ $business->name }}</div>
    <div class="foot">
      Point your phone camera here to open <b>locolie</b> and reveal this shop's live discount.
      <div class="url">{{ $url }}</div>
    </div>
  </div>

<script>
  new QRCode(document.getElementById('qr'), {
    text: @json($url),
    width: 200, height: 200,
    colorDark: '#0a0a0a', colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
  });
</script>
</body>
</html>
