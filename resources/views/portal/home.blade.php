@extends('portal.layout')
@section('title', 'Home')

@push('head')
{{-- Map: Google Maps JavaScript API (AdvancedMarkerElement + marker clustering). --}}
@include('partials.google-maps', ['key' => $mapsKey ?? null])
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
<style>
  .gl-app{ --radius:18px; --radius-sm:12px; }
  .mono{ font-family:'JetBrains Mono',monospace; }
  .gl-app svg.ic{ width:16px;height:16px;stroke:currentColor;stroke-width:1.75;fill:none;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0; }
  .gl-app .ic-sm{ width:12px;height:12px; } .gl-app .ic-lg{ width:20px;height:20px; } .gl-app .ic-filled{ fill:currentColor;stroke:none; }
  .pin-dot{ display:inline-block; width:.5em;height:.5em; background:var(--accent); border-radius:50% 50% 50% 0; transform:rotate(-45deg); margin:0 .02em 0 .06em; }
  .pin-letter{ height:.96em; width:auto; display:inline-block; vertical-align:-0.1em; margin:0 -.006em; fill:var(--accent); filter:drop-shadow(0 2px 6px color-mix(in srgb, var(--accent) 42%, transparent)); }
  .wordmark{ font-weight:800; letter-spacing:-0.035em; }

  /* studio */
  .studio{ position:relative; border-radius:34px; overflow:hidden; padding:36px 20px 48px;
    background:
      radial-gradient(40rem 30rem at 15% 0%, color-mix(in srgb, var(--accent) 22%, transparent), transparent 60%),
      radial-gradient(40rem 30rem at 100% 100%, color-mix(in srgb, var(--ink) 18%, transparent), transparent 55%),
      linear-gradient(160deg, #fbfdff, #eef2f7); }
  .studio::after{ content:''; position:absolute; inset:0; background-image:radial-gradient(circle at 1px 1px, rgba(15,45,56,.06) 1px, transparent 0); background-size:26px 26px; pointer-events:none; }

  /* control strip */
  .ctrls{ position:relative; z-index:2; display:flex; flex-wrap:wrap; gap:18px 28px; align-items:center; justify-content:center; margin-bottom:30px; }
  .ctrl-group{ display:flex; align-items:center; gap:8px; }
  .ctrl-label{ font-family:'JetBrains Mono',monospace; font-size:10px; text-transform:uppercase; letter-spacing:.14em; color:#64748b; }
  .npill{ padding:6px 12px; border-radius:999px; font-size:13px; font-weight:600; cursor:pointer; background:#fff; color:#475569; border:1px solid #e2e8f0; transition:.12s; }
  .npill.on{ background:var(--ink); color:#fff; border-color:transparent; }
  .sw{ width:26px;height:26px;border-radius:8px;cursor:pointer;box-shadow:inset 0 0 0 1px rgba(0,0,0,.08); transition:transform .12s; }
  .sw:hover{ transform:scale(1.1); } .sw.on{ box-shadow:0 0 0 2px #fff,0 0 0 4px var(--accent); }
  .loginseg{ display:inline-flex; align-items:center; gap:4px; background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:5px; box-shadow:0 6px 18px rgba(15,23,42,.06); }
  .loginseg button{ display:inline-flex; align-items:center; gap:6px; border:0; background:transparent; color:#475569; font-weight:700; font-size:13px; padding:8px 16px; border-radius:10px; cursor:pointer; transition:.12s; }
  .loginseg button.on{ background:#0a0a0a; color:#fff; }

  /* phones */
  .stage{ position:relative; z-index:2; display:grid; gap:30px 48px; justify-items:center; }
  @media (min-width:980px){ .stage{ grid-template-columns:1fr 1fr; } }
  .phone-col{ display:flex; flex-direction:column; align-items:center; }
  .phone-cap{ margin-bottom:16px; text-align:center; }
  .phone-cap .t{ font-weight:800; font-size:18px; letter-spacing:-.02em; color:#0f2d38; }
  .phone-cap .s{ font-size:13px; color:#64748b; margin-top:2px; }

  .iphone{ width:312px; max-width:86vw; border-radius:54px; padding:13px; position:relative;
    background:linear-gradient(150deg,#43464d,#17181b 55%,#3a3d44); box-shadow:0 50px 90px -34px rgba(15,23,42,.55), inset 0 0 0 2px rgba(255,255,255,.07); }
  .iphone::before{ content:''; position:absolute; right:-2px; top:150px; width:3px; height:64px; border-radius:2px; background:#2a2c30; }
  .iphone::after{ content:''; position:absolute; left:-2px; top:120px; width:3px; height:34px; border-radius:2px; background:#2a2c30; box-shadow:0 52px 0 #2a2c30; }
  .iphone-screen{ position:relative; height:660px; border-radius:43px; overflow:hidden; background:var(--bg); display:flex; flex-direction:column; }
  .island{ position:absolute; top:10px; left:50%; transform:translateX(-50%); width:86px; height:25px; background:#000; border-radius:14px; z-index:40; }
  .sb{ height:44px; display:flex; align-items:flex-end; justify-content:space-between; padding:0 24px 5px; font-size:12px; font-weight:600; color:#fff; background:var(--ink); flex-shrink:0; }

  .scr{ flex:1; overflow-y:auto; background:var(--bg); min-height:0; -webkit-overflow-scrolling:touch; overscroll-behavior:contain; cursor:grab; }
  .scr:active{ cursor:grabbing; }
  .scr::-webkit-scrollbar{ width:5px; } .scr::-webkit-scrollbar-thumb{ background:rgba(0,0,0,.18); border-radius:3px; }
  /* Every screen fills the phone's content area with a definite size, so .scr is always scrollable. */
  .screens{ position:relative; flex:1; min-height:0; }
  .screens > [x-show]{ position:absolute; inset:0; }

  .app-header{ background:color-mix(in srgb, var(--ink) 80%, transparent); -webkit-backdrop-filter:blur(22px) saturate(180%); backdrop-filter:blur(22px) saturate(180%); padding:12px 15px 14px; flex-shrink:0; }
  .app-header-row{ display:grid; grid-template-columns:1fr auto 1fr; align-items:center; gap:10px; min-height:40px; }
  .app-header-brand{ color:#fff; font-size:28px; font-weight:800; justify-self:center; letter-spacing:-.04em; display:inline-flex; align-items:center; line-height:1; text-shadow:0 1px 14px rgba(0,0,0,.22); }
  .app-header-brand .pin-letter{ height:.9em; vertical-align:-0.05em; margin:0 -.012em; }
  .app-header-loc{ display:flex; align-items:center; gap:4px; color:rgba(255,255,255,.75); font-size:12px; cursor:pointer; justify-self:start; min-width:0; }
  .app-header-loc strong{ white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .bell{ justify-self:end; }
  .app-header-loc strong{ color:#fff; } .app-header-loc svg{ color:var(--accent); }
  .bell{ position:relative; color:#fff; cursor:pointer; }
  .bell-dot{ position:absolute; top:-3px; right:-3px; min-width:15px; height:15px; padding:0 3px; border-radius:8px; background:var(--accent); color:#fff; font-size:9px; font-weight:800; display:grid; place-items:center; }
  .searchbar{ margin-top:13px; display:flex; gap:8px; }
  .search-input{ flex:1; display:flex; align-items:center; gap:8px; background:rgba(255,255,255,.12); color:rgba(255,255,255,.7); border-radius:11px; padding:10px 13px; font-size:13px; cursor:pointer; }
  .search-btn{ width:40px; display:grid; place-items:center; background:var(--cta); color:var(--cta-text); border-radius:11px; }
  .titlebar{ background:var(--ink); color:#fff; padding:13px 15px; font-weight:800; font-size:16px; letter-spacing:-.02em; flex-shrink:0; display:flex; align-items:center; justify-content:space-between; }

  .feed{ padding:15px; }
  .pill-row{ display:flex; gap:7px; overflow-x:auto; padding-bottom:4px; margin-bottom:13px; scrollbar-width:none; }
  .pill-row::-webkit-scrollbar{ display:none; }
  .pill{ flex-shrink:0; padding:6px 13px; border-radius:999px; font-size:12px; font-weight:600; cursor:pointer; background:var(--surface-2); color:var(--text-2); white-space:nowrap; }
  .pill.on{ background:var(--ink); color:#fff; }
  .filter-row{ display:flex; gap:7px; margin-bottom:13px; }
  .fchip{ display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:600; padding:6px 11px; border-radius:999px; border:1px solid var(--line); color:var(--text-2); cursor:pointer; background:var(--bg); }
  .fchip.on{ background:var(--accent-soft); color:var(--accent); border-color:transparent; }
  .sec-head{ display:flex; align-items:baseline; justify-content:space-between; margin-bottom:9px; }
  .sec-title{ font-weight:800; font-size:15px; color:var(--text); letter-spacing:-.02em; }
  .sec-link{ font-size:12px; color:var(--accent); font-weight:600; }

  .feat{ border-radius:18px; overflow:hidden; background:var(--bg); border:1px solid color-mix(in srgb, var(--text) 7%, transparent); box-shadow:0 1px 2px rgba(15,26,34,.04), 0 10px 28px rgba(15,26,34,.07); cursor:pointer; transition:transform .15s, box-shadow .15s; }
  .feat:active{ transform:scale(.99); }
  .feat-img{ height:110px; position:relative; background:linear-gradient(135deg,var(--accent),var(--ink)); }
  .feat-tag{ position:absolute; top:11px; left:11px; background:var(--cta); color:var(--cta-text); font-weight:800; font-size:11px; padding:4px 9px; border-radius:8px; }
  .feat-info{ padding:12px 14px; }
  .feat-name{ font-weight:800; font-size:15px; color:var(--text); letter-spacing:-.02em; }
  .feat-meta{ display:flex; align-items:center; gap:5px; font-size:11px; color:var(--muted); margin-top:3px; flex-wrap:wrap; }
  .feat-offer{ margin-top:8px; display:inline-block; font-size:11px; font-weight:700; letter-spacing:.04em; color:var(--accent); background:var(--accent-soft); padding:4px 8px; border-radius:7px; }
  .star{ color:var(--star); display:inline-flex; align-items:center; gap:2px; font-weight:700; }

  .row{ display:flex; align-items:center; gap:11px; padding:10px 0; cursor:pointer; border-bottom:1px solid var(--line); }
  .row:last-child{ border-bottom:0; }
  .row-img{ width:50px;height:50px;border-radius:12px;flex-shrink:0; background:linear-gradient(135deg,var(--sage),var(--ink)); }
  .row-info{ flex:1; min-width:0; }
  .row-name{ font-weight:700; font-size:14px; color:var(--text); }
  .row-meta{ font-size:11px; color:var(--muted); display:flex; align-items:center; gap:5px; margin-top:2px; flex-wrap:wrap; }
  .offer-pill{ flex-shrink:0; font-size:11px; font-weight:800; color:var(--cta-text); background:var(--cta); padding:4px 8px; border-radius:8px; }

  .pf-hero{ height:140px; position:relative; background:linear-gradient(135deg,var(--accent),var(--ink)); }
  .pf-top{ position:absolute; top:13px; left:13px; right:13px; display:flex; justify-content:space-between; }
  .pf-bar{ flex-shrink:0; z-index:20; display:flex; align-items:center; gap:10px; background:var(--ink); color:#fff; padding:10px 12px; }
  .pf-bar .bk, .pf-bar .hb{ width:32px; height:32px; border-radius:50%; background:rgba(255,255,255,.14); display:grid; place-items:center; cursor:pointer; flex-shrink:0; }
  .pf-bar .ttl{ flex:1; font-weight:700; font-size:15px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .pf-bar .hb.on{ color:var(--accent); }
  .solo .pf-bar{ padding-top:calc(10px + env(safe-area-inset-top)); }
  .pf-icon{ width:32px;height:32px;border-radius:50%; background:rgba(0,0,0,.35); color:#fff; display:grid; place-items:center; cursor:pointer; }
  .pf-body{ padding:16px; }
  .pf-name{ font-family:var(--display),serif; font-weight:var(--display-weight); font-size:22px; letter-spacing:-.02em; color:var(--text); }
  .pf-meta{ font-size:12px; color:var(--muted); display:flex; gap:5px; align-items:center; flex-wrap:wrap; margin-top:4px; }
  .open{ color:#15803d; font-weight:700; } .closed{ color:#dc2626; font-weight:700; }
  .qa{ display:flex; gap:7px; margin:15px 0; }
  .qa div{ flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; background:var(--surface); border-radius:12px; padding:10px 5px; font-size:11px; font-weight:600; color:var(--text-2); cursor:pointer; }
  .qa svg{ color:var(--accent); }
  .pf-offer{ border:1.5px solid var(--accent); border-radius:var(--radius-sm); padding:15px; background:var(--accent-soft); margin-bottom:12px; }
  .pf-offer:last-child{ margin-bottom:0; }
  .cta-btn:disabled{ background:var(--surface-2); color:var(--muted); box-shadow:none; cursor:default; }
  .otag{ display:inline-flex; align-items:center; font-size:10.5px; font-weight:800; letter-spacing:.03em; text-transform:uppercase; padding:3px 8px; border-radius:7px; background:color-mix(in srgb, var(--accent) 16%, transparent); color:var(--accent); }
  .otag.hot{ background:#fee2e2; color:#dc2626; }
  .otag.seasonal{ background:#fef3c7; color:#b45309; }
  .pf-hours div{ display:flex; justify-content:space-between; font-size:12.5px; padding:4px 0; color:var(--text-2); border-bottom:1px solid var(--line); }
  .pf-hours div:last-child{ border-bottom:0; }
  .review{ padding:11px 0; border-bottom:1px solid var(--line); } .review:last-child{ border-bottom:0; }
  .review-top{ display:flex; justify-content:space-between; font-size:13px; }
  .review-author{ font-weight:700; color:var(--text); }
  .review-text{ font-size:12.5px; color:var(--text-2); margin-top:3px; line-height:1.5; }
  .heart.on{ color:var(--accent); }

  .cta-btn{ display:flex; align-items:center; justify-content:center; gap:8px; width:100%; background:var(--cta); color:var(--cta-text); font-weight:700; letter-spacing:-.01em; padding:15px; border-radius:14px; border:0; cursor:pointer; margin-top:14px; font-size:15px; box-shadow:0 6px 16px color-mix(in srgb, var(--cta) 34%, transparent); transition:transform .12s, filter .12s; }
  .cta-btn:active{ transform:scale(.985); filter:brightness(.97); }
  .ghost-btn{ width:100%; background:transparent; color:var(--muted); border:0; padding:10px; font-size:13px; cursor:pointer; margin-top:4px; }

  .code-wrap{ flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:22px; text-align:center; background:var(--bg); }
  .code-tick{ width:46px;height:46px;border-radius:50%; background:var(--sage); color:#fff; display:grid; place-items:center; margin-bottom:13px; }
  .code-h{ font-family:var(--display),serif; font-weight:var(--display-weight); font-size:24px; color:var(--text); }
  .code-sub{ font-size:12.5px; color:var(--muted); max-width:230px; margin:6px 0 18px; }
  .ticket{ position:relative; width:100%; max-width:260px; background:var(--surface); border:1.5px solid var(--line); border-radius:17px; padding:20px; }
  .ticket::before,.ticket::after{ content:''; position:absolute; top:50%; transform:translateY(-50%); width:16px;height:16px;border-radius:50%; background:var(--bg); border:1.5px solid var(--line); }
  .ticket::before{ left:-9px; } .ticket::after{ right:-9px; }
  .ticket-eb{ font-family:'JetBrains Mono',monospace; font-size:10px; text-transform:uppercase; letter-spacing:.16em; color:var(--muted); }
  .ticket-code{ font-family:'JetBrains Mono',monospace; font-weight:600; font-size:31px; letter-spacing:.08em; color:var(--accent); margin:8px 0 13px; }
  .ticket-div{ border-top:1.5px dashed var(--line); margin:0 -20px 13px; }
  .ticket-biz{ font-weight:700; color:var(--text); }
  .ticket-offer{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:.08em; color:var(--muted); text-transform:uppercase; margin-top:2px; }
  .timer{ display:inline-flex; align-items:center; gap:7px; margin-top:16px; padding:7px 13px; border-radius:999px; font-size:13px; font-weight:600; }
  .timer.live{ background:var(--accent-soft); color:var(--accent); } .timer.done{ background:#dcfce7; color:#15803d; } .timer.expired{ background:#fee2e2; color:#dc2626; }

  .mapel{ flex:1; min-height:260px; }
  #cmap, #bizmap{ background:var(--surface-2); }
  /* User's current-location dot. */
  .user-dot{ width:16px; height:16px; border-radius:50%; background:#2563eb; border:3px solid #fff; box-shadow:0 0 0 3px rgba(37,99,235,.28); }
  /* Shop-name caption under a pin (Google marker label, shown only when zoomed in). */
  /* ===== Styled map pins (dot + name/category card). Card shows when zoomed in (.labels-on). ===== */
  .pin-wrap{ background:none; border:0; }
  .pin{ position:relative; width:26px; height:34px; }
  /* Branded map pin: accent teardrop + white ring + inner dot, with a drop-in. */
  .pin-dot{ position:absolute; left:3px; top:1px; width:20px; height:20px; background:var(--accent); border:2.5px solid #fff; border-radius:50% 50% 50% 0; transform:rotate(-45deg); box-shadow:0 5px 9px rgba(0,0,0,.32); transform-origin:50% 50%; animation:pinDrop .42s cubic-bezier(.2,.8,.3,1.3) both; }
  .pin-dot::after{ content:''; position:absolute; left:50%; top:50%; width:6.5px; height:6.5px; border-radius:50%; background:#fff; transform:translate(-50%,-50%) rotate(45deg); }
  @keyframes pinDrop{ 0%{ opacity:0; transform:rotate(-45deg) translate(6px,-12px) scale(.5); } 100%{ opacity:1; transform:rotate(-45deg) translate(0,0) scale(1); } }
  .pin-card{ display:none; position:absolute; left:30px; top:11px; transform:translateY(-50%); flex-direction:column; gap:1px; background:rgba(255,255,255,.92); -webkit-backdrop-filter:blur(8px) saturate(160%); backdrop-filter:blur(8px) saturate(160%); padding:5px 10px; border-radius:11px; box-shadow:0 4px 14px rgba(0,0,0,.16); white-space:nowrap; }
  .pin-top{ display:flex; align-items:center; gap:6px; }
  .pin-name{ font-weight:800; font-size:11.5px; color:#0f172a; line-height:1.1; }
  .pin-cat{ font-size:9.5px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--accent); }
  .pin-badge{ background:var(--accent); color:#fff; font-size:8.5px; font-weight:800; padding:2px 5px; border-radius:5px; }
  .labels-on .pin-card{ display:flex; }
  /* Google Maps AdvancedMarkerElement content sits in a wrapper; strip default gmp styling. */
  gmp-advanced-marker .pin{ cursor:pointer; }
  .gm-style-iw{ border-radius:13px; } .gm-style-iw-d{ overflow:hidden !important; }
  .map-pop{ text-align:left; min-width:170px; }
  .map-pop .po-name{ font-weight:800; font-size:14px; color:#111; }
  .map-pop .po-sec{ font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; margin-top:1px; }
  .map-pop .po-off{ font-size:12px; font-weight:700; color:var(--accent); background:var(--accent-soft); padding:5px 8px; border-radius:7px; margin:8px 0; }
  .map-pop .pv{ background:var(--accent); color:#fff; border:0; border-radius:8px; padding:8px 12px; font-weight:700; font-size:12px; cursor:pointer; width:100%; }
  .bizmap{ height:150px; border-radius:14px; overflow:hidden; border:1px solid var(--line); }
  .codeqr{ display:flex; justify-content:center; margin-bottom:12px; }
  .codeqr img, .codeqr canvas{ border-radius:8px; }
  .scan-frame{ position:absolute; width:62%; aspect-ratio:1; border:3px solid rgba(255,255,255,.9); border-radius:22px; box-shadow:0 0 0 9999px rgba(0,0,0,.32); pointer-events:none; }
  #qr-reader video, #biz-qr-reader video{ width:100% !important; border-radius:12px; }

  .tabbar{ display:flex; gap:2px; flex-shrink:0; background:color-mix(in srgb, var(--bg) 68%, transparent); -webkit-backdrop-filter:saturate(180%) blur(26px); backdrop-filter:saturate(180%) blur(26px); border:1px solid color-mix(in srgb, var(--text) 7%, transparent); border-radius:26px; margin:8px 12px calc(10px + env(safe-area-inset-bottom)); padding:7px 7px; box-shadow:0 2px 6px rgba(15,23,42,.05), 0 16px 40px rgba(15,23,42,.16), inset 0 1px 0 rgba(255,255,255,.5); }
  .tab{ flex:1; display:flex; flex-direction:column; align-items:center; gap:3px; padding:7px 4px; border-radius:16px; color:var(--muted); font-size:10px; font-weight:600; letter-spacing:-.01em; cursor:pointer; transition:color .2s ease, background .25s ease, transform .12s ease; }
  .tab svg{ width:22px; height:22px; transition:transform .3s cubic-bezier(.2,.8,.3,1.5); }
  .tab:active{ transform:scale(.9); }
  .tab.on{ color:var(--accent); background:color-mix(in srgb, var(--accent) 13%, transparent); }
  .tab.on svg{ transform:translateY(-1px) scale(1.14); }

  /* ===== Slick interactions (Apple-ish press + lift) ===== */
  .hcard, .cat-tile, .row, .cta-btn, .pri-btn, .offer-pill, .qa > div { transition:transform .14s ease, box-shadow .22s ease, filter .14s ease; }
  .hcard:active, .cat-tile:active, .row:active, .qa > div:active { transform:scale(.97); }
  .cta-btn:active, .pri-btn:active { transform:scale(.975); filter:brightness(.98); }
  @media (hover:hover){ .hcard:hover{ transform:translateY(-3px); box-shadow:0 8px 18px rgba(15,26,34,.10), 0 20px 44px rgba(15,26,34,.12); } }
  @media (prefers-reduced-motion:reduce){ .pin-dot{ animation:none !important; } .tab svg, .hcard, .cta-btn{ transition:none; } }

  .notif-panel{ position:absolute; top:calc(100% - 2px); right:11px; left:11px; z-index:50; background:var(--bg); border-radius:15px; box-shadow:0 20px 50px rgba(0,0,0,.3); border:1px solid var(--line); overflow:hidden; }
  .notif-panel .h{ padding:11px 14px; font-weight:800; font-size:14px; color:var(--text); border-bottom:1px solid var(--line); }
  .notif-item{ padding:10px 14px; border-bottom:1px solid var(--line); font-size:12.5px; color:var(--text-2); display:flex; gap:8px; align-items:flex-start; }
  .notif-item:last-child{ border-bottom:0; } .notif-item b{ color:var(--text); }

  .chip{ padding:8px 13px; border-radius:999px; font-size:13px; font-weight:600; cursor:pointer; background:var(--surface-2); color:var(--text-2); border:2px solid transparent; }
  .chip.on{ background:var(--accent-soft); color:var(--accent); border-color:var(--accent); }
  .dots{ display:flex; gap:6px; justify-content:center; margin-top:16px; }
  .dots i{ width:7px;height:7px;border-radius:50%; background:rgba(255,255,255,.3); } .dots i.on{ background:#fff; width:18px; border-radius:4px; }

  .bcard{ background:var(--surface); border-radius:var(--radius-sm); padding:15px; }
  .field{ width:100%; border:1px solid var(--line); background:var(--bg); color:var(--text); border-radius:11px; padding:10px 12px; font-size:13px; outline:none; }
  .field:focus{ border-color:var(--accent); }
  .label{ font-family:'JetBrains Mono',monospace; font-size:10px; text-transform:uppercase; letter-spacing:.12em; color:var(--muted); }
  .pri-btn{ width:100%; background:var(--cta); color:var(--cta-text); font-weight:800; border:0; border-radius:11px; padding:12px; cursor:pointer; font-size:13px; }
  .gmock{ position:absolute; z-index:20; left:0; right:0; background:#fff; border:1px solid var(--line); border-radius:11px; box-shadow:0 12px 30px rgba(0,0,0,.14); margin-top:4px; overflow:hidden; }
  .gmock div{ padding:9px 12px; font-size:12.5px; cursor:pointer; color:var(--text); display:flex; gap:8px; align-items:center; }
  .gmock div:hover{ background:var(--surface); }

  .theme-glass .feat,.theme-glass .bcard{ background:rgba(255,255,255,.6); backdrop-filter:blur(12px) saturate(160%); }
  .theme-glass .scr,.theme-glass .code-wrap{ background:linear-gradient(160deg,#eef9ff,#e7fff4); }

  /* ===== Home: category tiles + explore-by-category rows ===== */
  .cat-tiles{ display:flex; gap:12px; overflow-x:auto; padding:2px 0 16px; scrollbar-width:none; }
  .cat-tiles::-webkit-scrollbar{ display:none; }
  .cat-tile{ flex-shrink:0; display:flex; flex-direction:column; align-items:center; gap:7px; width:74px; border:0; background:transparent; cursor:pointer; font-size:11px; font-weight:600; color:var(--text-2); }
  .cat-tile .cat-ic{ width:58px; height:58px; border-radius:18px; display:grid; place-items:center; background:var(--surface-2); color:var(--text); transition:.15s; }
  .cat-tile .cat-ic svg{ width:26px; height:26px; stroke:currentColor; stroke-width:1.7; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .cat-tile.on .cat-ic{ background:var(--ink); color:#fff; }
  .cat-tile > span:last-child{ line-height:1.2; text-align:center; }
  .hscroll{ display:flex; gap:14px; overflow-x:auto; padding:2px 2px 20px; margin:0 -2px; scrollbar-width:none; }
  .hscroll::-webkit-scrollbar{ display:none; }
  .hcard{ flex-shrink:0; width:228px; border-radius:16px; overflow:hidden; background:var(--bg); border:1px solid color-mix(in srgb, var(--text) 8%, transparent); box-shadow:0 1px 2px rgba(15,26,34,.04), 0 8px 22px rgba(15,26,34,.06); cursor:pointer; transition:transform .15s; }
  .hcard:active{ transform:scale(.985); }
  .hcard-img{ height:128px; position:relative; background:linear-gradient(135deg,var(--accent),var(--ink)); background-size:cover; background-position:center; }
  .hcard-tag{ position:absolute; top:10px; left:10px; background:var(--cta); color:var(--cta-text); font-weight:800; font-size:11px; padding:4px 9px; border-radius:8px; }
  .spon-tag{ position:absolute; top:10px; right:10px; background:rgba(17,24,39,.72); color:#fff; font-weight:700; font-size:9.5px; letter-spacing:.04em; text-transform:uppercase; padding:3px 7px; border-radius:7px; backdrop-filter:blur(2px); }
  .hcard-body{ padding:11px 12px 13px; }
  .hcard-name{ font-weight:700; font-size:14px; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .hcard-meta{ font-size:11px; color:var(--muted); margin-top:3px; display:flex; gap:5px; align-items:center; }
  .hcard-meta .star{ color:var(--star); font-weight:700; }
  .hcard-offer{ margin-top:9px; font-size:11px; font-weight:700; color:var(--accent); background:var(--accent-soft); padding:4px 8px; border-radius:7px; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  /* Lazy: offscreen category rows skip rendering + their images don't load until scrolled near. */
  .explore{ margin-top:4px; content-visibility:auto; contain-intrinsic-size:0 300px; }
  .row{ content-visibility:auto; contain-intrinsic-size:0 72px; }
  /* Google Display Network ad placement (mock) */
  .ad-banner{ position:relative; display:flex; gap:13px; align-items:center; border-radius:16px; overflow:hidden; margin:4px 0 22px; background:linear-gradient(135deg,#4338ca,#0ea5e9); color:#fff; padding:15px 15px; box-shadow:0 8px 22px rgba(15,26,34,.14); }
  .ad-banner .ad-thumb{ width:60px; height:60px; border-radius:12px; flex-shrink:0; background:rgba(255,255,255,.18); display:grid; place-items:center; }
  .ad-banner .ad-tag{ position:absolute; top:9px; right:9px; background:rgba(255,255,255,.9); color:#334155; font-size:8.5px; font-weight:800; letter-spacing:.05em; padding:3px 7px; border-radius:6px; }
  .ad-banner .ad-eyebrow{ font-size:9.5px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; opacity:.85; }
  .ad-banner .ad-title{ font-weight:800; font-size:15.5px; margin:2px 0; line-height:1.2; }
  .ad-banner .ad-sub{ font-size:12px; opacity:.92; }
  .ad-banner .ad-cta{ display:inline-block; margin-top:9px; background:#fff; color:#0f172a; font-weight:700; font-size:12px; padding:7px 13px; border-radius:9px; }

  /* ===== Filter sheet ===== */
  .sheet-overlay{ position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:90; display:flex; align-items:flex-end; justify-content:center; }
  .sheet{ background:var(--bg); width:100%; max-width:560px; max-height:90vh; border-radius:22px 22px 0 0; display:flex; flex-direction:column; }
  .sheet-hd{ display:flex; align-items:center; gap:12px; padding:14px 16px; border-bottom:1px solid var(--line); }
  .sheet-hd .t{ flex:1; text-align:center; font-weight:800; font-size:16px; color:var(--text); }
  .sheet-body{ overflow-y:auto; padding:16px 18px; }
  .fgroup{ margin-bottom:20px; }
  .fgroup-h{ font-weight:800; font-size:14px; color:var(--text); margin-bottom:11px; display:flex; align-items:center; gap:8px; }
  .fopts{ display:flex; flex-wrap:wrap; gap:9px; }
  .fopt{ padding:10px 15px; border-radius:12px; border:1.5px solid var(--line); background:var(--bg); color:var(--text-2); font-size:13px; font-weight:600; cursor:pointer; transition:.12s; }
  .fopt.on{ background:var(--accent-soft); color:var(--accent); border-color:var(--accent); }
  .sheet-ft{ display:flex; gap:12px; padding:14px 18px calc(16px + env(safe-area-inset-bottom)); border-top:1px solid var(--line); }
  .sheet-ft button{ flex:1; padding:14px; border-radius:13px; font-weight:700; font-size:14px; border:0; cursor:pointer; }
  .btn-clear{ background:var(--surface-2); color:var(--text-2); }
  .btn-apply{ background:var(--cta); color:var(--cta-text); }

  /* ===== /m (solo) = a real responsive web app =====
     The PAGE scrolls natively; header is sticky, tab bar is fixed & always visible.
     One centred column: full-width on mobile, tidy app column on desktop. */
  .solo .gl-chrome, .solo .ctrls, .solo .phone-cap, .solo .island, .solo .sb{ display:none !important; }
  /* solo shows ONE phone: shopper by default, business when ?as=business */
  .solo:not(.solo-biz) .gl-bizphone{ display:none !important; }
  .solo.solo-biz .gl-shopperphone{ display:none !important; }
  /* device-preview showcase (dev site) */
  .dev-previews{ display:flex; flex-direction:column; gap:38px; align-items:center; overflow-x:auto; padding-bottom:8px; }
  .dev-col{ display:flex; flex-direction:column; align-items:center; }
  .dev-cap{ text-align:center; margin-bottom:14px; }
  .dev-cap .t{ display:block; font-weight:800; font-size:18px; color:#0f2d38; letter-spacing:-.02em; }
  .dev-cap .s{ display:block; font-size:13px; color:#64748b; margin-top:2px; }
  .dev-frame{ background:#0c0c0e; padding:10px; border-radius:30px; box-shadow:0 40px 80px -34px rgba(15,23,42,.45), inset 0 0 0 2px rgba(255,255,255,.06); }
  .dev-frame iframe{ display:block; border:0; border-radius:20px; background:#fff; }
  .gl-solo footer, .gl-solo header{ display:none !important; }
  .gl-solo main{ padding:0 !important; }
  body.gl-solo{ height:auto; min-height:100%; background:#edf0f4; overflow-x:hidden; }
  /* unwrap the phone frame into a centred, responsive column */
  .solo .studio{ padding:0 !important; margin:0 !important; background:var(--bg) !important; border-radius:0 !important; overflow:visible !important; }
  .solo .studio::after{ display:none !important; }
  .solo .stage{ display:block !important; }
  .solo .phone-col, .solo .iphone{ display:block !important; width:auto !important; max-width:none !important; height:auto !important; min-height:0 !important; margin:0 !important; padding:0 !important; background:none !important; box-shadow:none !important; border-radius:0 !important; }
  .solo .iphone::before, .solo .iphone::after{ display:none !important; }
  .solo .iphone-screen{ display:block !important; width:100% !important; max-width:560px !important; min-height:100svh; height:auto !important; margin:0 auto !important; border-radius:0 !important; overflow:visible !important; background:var(--bg); box-shadow:0 0 0 1px rgba(15,23,42,.05); }
  .solo .appwrap{ display:block !important; overflow:visible !important; height:auto !important; min-height:0 !important; position:static !important; }
  /* screens + scroll areas flow naturally into the page */
  .solo .screens{ position:static !important; display:block !important; flex:none !important; min-height:0 !important; }
  /* NB: never force `display` here - Alpine x-show toggles display to show ONE screen at a time. */
  .solo .screens > [x-show]{ position:static !important; height:auto !important; min-height:0 !important; }
  .solo .scr{ position:static !important; overflow:visible !important; height:auto !important; min-height:0 !important; flex:none !important; cursor:auto; }
  /* sticky headers (main tabs + store), fixed floating tab bar (every page) */
  .solo .app-header{ position:sticky; top:0; z-index:40; padding-top:calc(6px + env(safe-area-inset-top)); }
  .solo .pf-bar{ position:sticky; top:0; z-index:40; padding-top:calc(10px + env(safe-area-inset-top)); }
  .solo .tabbar{ position:fixed; left:50%; transform:translateX(-50%); bottom:calc(10px + env(safe-area-inset-bottom)); width:min(516px, calc(100vw - 24px)); margin:0; z-index:60; }
  /* keep content clear of the fixed tab bar */
  .solo .feed, .solo .pf-body{ padding-bottom:104px !important; }
  /* map + scanner need an explicit height in natural flow */
  .solo .mapel{ height:60vh !important; min-height:300px; }
  .solo #qr-reader{ min-height:62vh; }
  .solo .code-wrap{ min-height:calc(100dvh - 120px); justify-content:center; padding-bottom:110px; }
  .solo .scan-frame{ width:240px; height:240px; }
</style>
@endpush

@php $solo = $solo ?? false; $soloRole = $soloRole ?? 'shopper'; @endphp
@section('mainClass', $solo ? 'w-full' : 'mx-auto w-full max-w-[1400px] px-4 sm:px-6 py-6 sm:py-10')
@section('bodyClass', $solo ? 'gl-solo' : '')

@section('content')
<div class="gl-app {{ $solo ? 'solo' : '' }} {{ $solo && $soloRole==='business' ? 'solo-biz' : '' }}" x-data="goLocalApp()" x-init="init()" :style="themeVars" :class="theme.cls">

  {{-- HERO --}}
  <div class="gl-chrome text-center max-w-2xl mx-auto mb-7">
    <div class="mono text-[11px] uppercase tracking-[0.18em] text-emerald-600 mb-3">locolie · Live prototype</div>
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">One app, <span class="text-emerald-600">two logins</span>.</h1>
    <p class="mt-3 text-slate-500 text-lg">Log in as a shopper to discover and redeem local offers, or as a business to publish and verify them - same app, two front doors.</p>
  </div>

  <section class="studio">
    @if($solo)
    <div class="stage">
      {{-- ============ SHOPPER LOGIN ============ --}}
      <div class="gl-shopperphone phone-col" :style="focus==='business' ? 'opacity:.38;transition:.2s' : 'opacity:1;transition:.2s'">
        <div class="phone-cap"><div class="t">Shopper login</div><div class="s">Discover &amp; redeem local offers</div></div>
        <div class="iphone">
          <div class="iphone-screen" x-on:wheel="scrollPhone($event)">
            <div class="island"></div>

            {{-- onboarding --}}
            <template x-if="!customer">
              <div class="scr" style="background:var(--ink);">
                <div style="padding:64px 24px 24px; color:#fff; min-height:100%; display:flex; flex-direction:column;">
                  <div x-show="onb===1">
                    <div class="wordmark" style="font-size:34px;color:#fff;" x-html="wordmark()"></div>
                    <p style="color:rgba(255,255,255,.7); margin-top:8px; font-size:14px;">Discover the best offers from independent shops near you. Free, forever.</p>
                    <div style="margin-top:24px; display:flex; flex-direction:column; gap:10px;">
                      <input class="field" style="background:rgba(255,255,255,.95);border:0;" x-model="form.cName" placeholder="Your name">
                      <input class="field" type="email" style="background:rgba(255,255,255,.95);border:0;" x-model="form.cEmail" placeholder="Your email (for offers near you)">
                      <select class="field" style="background:rgba(255,255,255,.95);border:0;color:var(--text-2);" x-model="form.cLoc">
                        <option value="NE">Newcastle (NE)</option><option value="all">Everywhere</option>
                      </select>
                    </div>
                    <div style="margin-top:16px; display:flex; flex-direction:column; gap:10px; font-size:12.5px; color:rgba(255,255,255,.8); line-height:1.5;">
                      <label style="display:flex; gap:9px; align-items:flex-start; cursor:pointer;">
                        <input type="checkbox" x-model="form.agreeTerms" style="margin-top:2px; width:16px; height:16px; accent-color:#059669; flex:0 0 auto;">
                        <span>I agree to the <a href="/terms" target="_blank" style="color:#6ee7b7; font-weight:600; text-decoration:underline;">Terms</a> and <a href="/privacy" target="_blank" style="color:#6ee7b7; font-weight:600; text-decoration:underline;">Privacy Policy</a>.</span>
                      </label>
                      <label style="display:flex; gap:9px; align-items:flex-start; cursor:pointer;">
                        <input type="checkbox" x-model="form.mktOptIn" style="margin-top:2px; width:16px; height:16px; accent-color:#059669; flex:0 0 auto;">
                        <span>Email me offers &amp; discounts from indies near me. Unsubscribe anytime.</span>
                      </label>
                    </div>
                  </div>
                  <div x-show="onb===2">
                    <div style="font-family:var(--display),serif;font-weight:var(--display-weight);font-size:24px;">What are you into?</div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:18px;">
                      <template x-for="c in parents" :key="c.slug"><div class="chip" :class="form.prefs.includes(c.slug) && 'on'" @click="togglePref(c.slug)" x-text="c.name"></div></template>
                    </div>
                  </div>
                  <div x-show="onb===3" style="text-align:center;">
                    <div style="font-family:var(--display),serif;font-weight:var(--display-weight);font-size:22px;margin-top:30px;">Never miss a deal</div>
                    <p style="color:rgba(255,255,255,.7); margin-top:8px; font-size:13px;">Get alerts when new offers drop nearby.</p>
                  </div>
                  <div class="dots"><i :class="onb===1&&'on'"></i><i :class="onb===2&&'on'"></i><i :class="onb===3&&'on'"></i></div>
                  <div style="margin-top:auto; padding-top:20px; display:flex; flex-direction:column; gap:8px;">
                    <button class="pri-btn" @click="onbNext()" x-text="onb<3 ? 'Continue' : 'Enable alerts & finish'"></button>
                    <button x-show="onb===3" class="ghost-btn" style="color:rgba(255,255,255,.6);" @click="finishOnboarding(false)">Maybe later</button>
                  </div>
                </div>
              </div>
            </template>

            {{-- app --}}
            <template x-if="customer">
              <div class="appwrap" style="flex:1; display:flex; flex-direction:column; overflow:hidden; position:relative; min-height:0;">
                <div class="sb"><span>9:41</span><span class="mono" style="font-size:11px;">5G</span></div>

                {{-- GLOBAL HEADER - shown on every main tab --}}
                <div class="app-header" x-show="view==='none'" style="position:relative;z-index:30;">
                  <div class="app-header-row">
                    <span class="app-header-loc" @click="cycleLocation()"><svg class="ic ic-sm" viewBox="0 0 24 24"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg><strong x-text="locationLabel"></strong><svg class="ic ic-sm" viewBox="0 0 24 24" style="color:rgba(255,255,255,.5)"><polyline points="6 9 12 15 18 9"/></svg></span>
                    <span class="app-header-brand wordmark" x-html="wordmark()"></span>
                    <span class="bell" @click="openNotifs()"><svg class="ic" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="bell-dot" x-show="unread>0" x-text="unread"></span></span>
                  </div>
                  <div class="searchbar"><div class="search-input" @click="view='search'"><svg class="ic ic-sm" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Search shops &amp; offers</div><div class="search-btn" @click="filterOpen=true" style="position:relative;"><svg class="ic ic-sm" viewBox="0 0 24 24"><line x1="4" y1="6" x2="20" y2="6"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="10" y1="18" x2="14" y2="18"/></svg><span x-show="filterCount>0" style="position:absolute;top:-4px;right:-4px;background:var(--accent);color:#fff;font-size:9px;font-weight:800;min-width:15px;height:15px;border-radius:8px;display:grid;place-items:center;" x-text="filterCount"></span></div></div>
                  <div class="notif-panel" x-show="notifOpen" x-transition @click.outside="notifOpen=false" style="display:none">
                    <div class="h">Notifications</div>
                    <div style="max-height:300px;overflow-y:auto;">
                      <template x-for="n in notifications" :key="n.id">
                        <div class="notif-item"><svg class="ic ic-sm" viewBox="0 0 24 24" style="color:var(--accent);margin-top:2px;"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg><div><b x-text="n.biz"></b> - <span x-text="n.text"></span></div></div>
                      </template>
                      <p x-show="!notifications.length" style="padding:22px;text-align:center;color:var(--muted);font-size:13px;">No alerts yet. Publish an offer in the business app →</p>
                    </div>
                  </div>
                </div>

                {{-- FILTER SHEET (Distance first) --}}
                <div class="sheet-overlay" x-show="filterOpen" x-transition @click.self="filterOpen=false" style="display:none">
                  <div class="sheet">
                    <div class="sheet-hd">
                      <button class="pf-icon" style="background:var(--surface-2);color:var(--text);" @click="filterOpen=false"><svg class="ic ic-sm" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></button>
                      <div class="t">Filter</div><div style="width:32px;"></div>
                    </div>
                    <div class="sheet-body">
                      <div class="fgroup">
                        <div class="fgroup-h"><svg class="ic ic-sm" viewBox="0 0 24 24" style="color:var(--accent)"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg> Distance</div>
                        <div class="fopts">
                          <div class="fopt" :class="fDist===null && 'on'" @click="fDist=null">Any</div>
                          <div class="fopt" :class="fDist===1 && 'on'" @click="fDist=1">Within 1 mi</div>
                          <div class="fopt" :class="fDist===3 && 'on'" @click="fDist=3">Within 3 mi</div>
                          <div class="fopt" :class="fDist===5 && 'on'" @click="fDist=5">Within 5 mi</div>
                        </div>
                      </div>
                      <div class="fgroup">
                        <div class="fgroup-h">Availability</div>
                        <div class="fopts"><div class="fopt" :class="openNow && 'on'" @click="openNow=!openNow">Open now</div></div>
                      </div>
                      <div class="fgroup">
                        <div class="fgroup-h">Rating</div>
                        <div class="fopts">
                          <div class="fopt" :class="fRating===null && 'on'" @click="fRating=null">Any</div>
                          <div class="fopt" :class="fRating===3 && 'on'" @click="fRating=3">3+ rating</div>
                          <div class="fopt" :class="fRating===4 && 'on'" @click="fRating=4">4+ rating</div>
                          <div class="fopt" :class="fRating===4.5 && 'on'" @click="fRating=4.5">4.5+ rating</div>
                        </div>
                      </div>
                      <div class="fgroup">
                        <div class="fgroup-h">Offer</div>
                        <div class="fopts">
                          <div class="fopt" :class="fOffer===null && 'on'" @click="fOffer=null">Any</div>
                          <div class="fopt" :class="fOffer===10 && 'on'" @click="fOffer=10">10%+ off</div>
                          <div class="fopt" :class="fOffer===25 && 'on'" @click="fOffer=25">25%+ off</div>
                          <div class="fopt" :class="fOffer===50 && 'on'" @click="fOffer=50">50%+ off</div>
                        </div>
                      </div>
                      <div class="fgroup">
                        <div class="fgroup-h">Sale type</div>
                        <div class="fopts">
                          <div class="fopt" :class="fSale===null && 'on'" @click="fSale=null">Any</div>
                          <div class="fopt" :class="fSale==='ongoing' && 'on'" @click="fSale='ongoing'">Ongoing</div>
                          <div class="fopt" :class="fSale==='limited' && 'on'" @click="fSale='limited'">Limited stock</div>
                          <div class="fopt" :class="fSale==='seasonal' && 'on'" @click="fSale='seasonal'">Seasonal</div>
                        </div>
                      </div>
                    </div>
                    <div class="sheet-ft">
                      <button class="btn-clear" @click="fDist=null; fRating=null; fOffer=null; fSale=null; openNow=false">Clear</button>
                      <button class="btn-apply" @click="filterOpen=false">Apply</button>
                    </div>
                  </div>
                </div>

                <div class="screens">
                {{-- HOME --}}
                <div x-show="tab==='home' && view==='none'" style="flex:1;display:flex;flex-direction:column;min-height:0;">
                  <div class="scr"><div class="feed">
                    {{-- parent category tiles (icons) --}}
                    <div class="cat-tiles">
                      <button class="cat-tile" :class="cCat===null && 'on'" @click="cCat=null; cSub=null; cSub2=null"><span class="cat-ic" x-html="catIcon('all')"></span><span>All</span></button>
                      <template x-for="c in parents" :key="c.slug"><button class="cat-tile" :class="cCat===c.slug && 'on'" @click="selectParent(c.slug)"><span class="cat-ic" x-html="catIcon(c.slug)"></span><span x-text="c.name"></span></button></template>
                    </div>
                    {{-- sub-category chips for the selected parent --}}
                    <div class="pill-row" x-show="cCat && cCat!=='foryou' && subCats.length" x-cloak style="margin-top:-4px;">
                      <div class="pill" :class="!cSub && 'on'" @click="selectSub(null)">All <span x-text="cCatName"></span></div>
                      <template x-for="s in subCats" :key="s.slug"><div class="pill" :class="cSub===s.slug && 'on'" @click="selectSub(s.slug)" x-text="s.name"></div></template>
                    </div>
                    {{-- third-level chips (e.g. builder trades) for the selected sub-category --}}
                    <div class="pill-row" x-show="cSub && subCats2.length" x-cloak style="margin-top:-6px;">
                      <div class="pill" :class="!cSub2 && 'on'" @click="cSub2=null">All <span x-text="cSubName"></span></div>
                      <template x-for="s in subCats2" :key="s.slug"><div class="pill" :class="cSub2===s.slug && 'on'" @click="cSub2 = cSub2===s.slug ? null : s.slug" x-text="s.name"></div></template>
                    </div>
                    <p x-show="loading" style="text-align:center;color:var(--muted);font-size:13px;padding:28px 0;">Loading…</p>

                    {{-- ALL view: featured + explore-by-category rows --}}
                    <template x-if="!loading && !cCat">
                      <div>
                        <div class="sec-head"><div class="sec-title">Featured today</div></div>
                        <div class="hscroll">
                          <template x-for="b in featured" :key="'f'+b.id">
                            <div class="hcard" @click="openBusiness(b)">
                              <div class="hcard-img" :style="b.image ? ('background-image:url('+b.image+')') : ''"><span class="hcard-tag" x-text="b.offers[0].badge"></span><span class="spon-tag" x-show="b.featured">Sponsored</span></div>
                              <div class="hcard-body"><div class="hcard-name" x-text="b.name"></div><div class="hcard-meta"><span class="star">★&nbsp;<span x-text="b.rating"></span></span><span x-text="'· '+b.category"></span></div><div class="hcard-offer" x-text="b.offers[0].title"></div></div>
                            </div>
                          </template>
                        </div>
                        {{-- Google Display Network ad placement (mock) --}}
                        <div class="ad-banner">
                          <span class="ad-tag">Ad · Google</span>
                          <div class="ad-thumb"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-6 9 6v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 21V12h6v9"/></svg></div>
                          <div style="flex:1;min-width:0;">
                            <div class="ad-eyebrow">Sponsored</div>
                            <div class="ad-title">Grow your local business</div>
                            <div class="ad-sub">Reach thousands of shoppers nearby with locolie.</div>
                            <span class="ad-cta">Learn more</span>
                          </div>
                        </div>
                        <template x-for="c in categoriesWithBiz" :key="c.slug">
                          <div class="explore">
                            <div class="sec-head"><div class="sec-title">Explore <span x-text="c.name"></span></div><div class="sec-link" @click="cCat=c.slug; cSub=null; cSub2=null">See all</div></div>
                            <div class="hscroll">
                              <template x-for="b in byCat(c.slug)" :key="c.slug+b.id">
                                <div class="hcard" @click="openBusiness(b)">
                                  <div class="hcard-img" :style="b.image ? ('background-image:url('+b.image+')') : ''"><span class="hcard-tag" x-text="b.offers[0].badge"></span></div>
                                  <div class="hcard-body"><div class="hcard-name" x-text="b.name"></div><div class="hcard-meta"><span class="star">★&nbsp;<span x-text="b.rating"></span></span><span x-text="'· '+dist(b)+' mi'"></span><span x-show="isOpen(b.hours)" class="open">· Open</span></div><div class="hcard-offer" x-text="b.offers[0].title"></div></div>
                                </div>
                              </template>
                            </div>
                          </div>
                        </template>
                      </div>
                    </template>

                    {{-- category selected: vertical list --}}
                    <template x-if="!loading && cCat">
                      <div>
                        <div class="sec-head"><div class="sec-title" x-text="cCat==='foryou' ? 'For you' : (cSub2 ? cSub2Name : (cSub ? cSubName : cCatName))"></div><div class="sec-link" @click="cCat=null; cSub=null; cSub2=null">Clear</div></div>
                        <template x-for="b in visible" :key="b.id">
                          <div class="row" @click="openBusiness(b)">
                            <div class="row-img" :style="b.image ? ('background-image:url('+b.image+');background-size:cover;background-position:center') : ''"></div>
                            <div class="row-info"><div class="row-name" x-text="b.name"></div><div class="row-meta"><span class="star"><svg class="ic ic-sm ic-filled" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> <span x-text="b.rating"></span></span><span>·</span><span x-text="b.category+' · '+dist(b)+' mi'"></span><span x-show="isOpen(b.hours)" class="open">· Open</span></div></div>
                            <div class="offer-pill" x-text="b.offers[0].badge"></div>
                          </div>
                        </template>
                        <p x-show="!visible.length" style="text-align:center;color:var(--muted);font-size:13px;padding:28px 0;">No offers match here.</p>
                      </div>
                    </template>
                  </div></div>
                </div>

                {{-- SEARCH --}}
                <div x-show="view==='search'" style="flex:1;display:flex;flex-direction:column;min-height:0;">
                  <div style="background:var(--ink);padding:11px 15px;display:flex;gap:10px;align-items:center;">
                    <div style="flex:1;display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);border-radius:10px;padding:9px 12px;"><svg class="ic ic-sm" viewBox="0 0 24 24" style="color:rgba(255,255,255,.6)"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg><input x-model="searchQ" placeholder="Search" style="flex:1;background:transparent;border:0;color:#fff;outline:none;font-size:14px;"></div>
                    <span style="color:#fff;font-size:13px;cursor:pointer;" @click="view='none'; searchQ=''">Cancel</span>
                  </div>
                  <div class="scr"><div class="feed">
                    <template x-if="!searchQ"><div><div class="label" style="margin-bottom:10px;">Browse categories</div><div style="display:flex;flex-wrap:wrap;gap:7px;"><template x-for="c in parents" :key="c.slug"><div class="pill" @click="cCat=c.slug; cSub=null; cSub2=null; view='none'; tab='home'" x-text="c.name"></div></template></div></div></template>
                    <template x-for="b in searchResults" :key="b.id"><div class="row" @click="openBusiness(b)"><div class="row-img" :style="b.image ? ('background-image:url('+b.image+');background-size:cover;background-position:center') : ''"></div><div class="row-info"><div class="row-name" x-text="b.name"></div><div class="row-meta"><span x-text="b.category"></span></div></div><div class="offer-pill" x-show="b.offers[0]" x-text="b.offers[0]?.badge"></div></div></template>
                    <p x-show="searchQ && !searchResults.length" style="text-align:center;color:var(--muted);font-size:13px;padding:28px 0;">No results.</p>
                  </div></div>
                </div>

                {{-- MAP --}}
                <div x-show="tab==='map' && view==='none'" style="flex:1;display:flex;flex-direction:column;min-height:0;">
                  <div class="pill-row" style="padding:9px 12px 7px;flex-shrink:0;background:var(--bg);border-bottom:1px solid var(--line);">
                    <div class="pill" :class="cCat===null && 'on'" @click="cCat=null; cSub=null; cSub2=null; mapRefresh()">All</div>
                    <template x-for="c in parents" :key="c.slug"><div class="pill" :class="cCat===c.slug && 'on'" @click="cCat=c.slug; cSub=null; cSub2=null; mapRefresh()" x-text="c.name"></div></template>
                  </div>
                  <div class="mapel" id="cmap"></div>
                </div>

                {{-- SAVED --}}
                <div x-show="tab==='saved' && view==='none'" style="flex:1;display:flex;flex-direction:column;min-height:0;">
                  <div class="scr"><div class="feed">
                    <div class="sec-title" style="margin-bottom:12px;">Saved</div>
                    <template x-for="f in favs" :key="f.id"><div class="row" @click="openSaved(f)"><div class="row-img" :style="f.image ? ('background-image:url('+f.image+');background-size:cover;background-position:center') : ''"></div><div class="row-info"><div class="row-name" x-text="f.name"></div><div class="row-meta"><span x-text="f.category"></span></div></div><div class="offer-pill" x-show="f.badge" x-text="f.badge"></div></div></template>
                    <p x-show="!favs.length" style="text-align:center;color:var(--muted);font-size:13px;padding:36px 0;">Tap the heart on any shop to save it.</p>
                  </div></div>
                </div>

                {{-- PROFILE --}}
                <div x-show="view==='profile'" style="display:flex;flex-direction:column;">
                    <div class="pf-bar" x-show="active">
                      <div class="bk" @click="view='none'"><svg class="ic ic-sm" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></div>
                      <div class="ttl" x-text="active?.name"></div>
                      <div class="hb" :class="isFav(active?.id) && 'on'" @click="toggleFav(active)"><svg class="ic ic-sm" :class="isFav(active?.id) && 'ic-filled'" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
                    </div>
                  <div class="scr">
                  <div x-show="active">
                    <div class="pf-hero" :style="active?.image ? ('background-image:url('+active.image+');background-size:cover;background-position:center') : ''"></div>
                    <div class="pf-body">
                      <div class="pf-name" x-text="active?.name"></div>
                      <div class="pf-meta"><span class="star"><svg class="ic ic-sm ic-filled" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> <span x-text="active?.rating"></span></span><span x-text="'('+active?.reviews_count+')'"></span><span>·</span><span x-text="active?.category"></span><span>·</span><span :class="isOpen(active?.hours)?'open':'closed'" x-text="isOpen(active?.hours)?'Open':'Closed'"></span></div>
                      <div class="qa"><div @click="callBiz()"><svg class="ic" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>Call</div><div @click="directions()"><svg class="ic" viewBox="0 0 24 24"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>Directions</div><div @click="share()"><svg class="ic" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>Share</div></div>
                      <div x-show="active?.offers?.length" style="margin-top:6px;">
                        <div class="label" style="color:var(--accent);margin-bottom:8px;"><span x-text="active?.offers?.length>1 ? (active.offers.length+' live offers') : 'Live offer'"></span></div>
                        <template x-for="o in (active?.offers||[])" :key="o.id">
                          <div class="pf-offer">
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:2px;">
                              <span class="otag" x-show="o.sale_type==='limited'" :class="o.remaining<=5 && 'hot'" x-text="o.remaining>0 ? (o.remaining+' left') : 'Sold out'"></span>
                              <span class="otag seasonal" x-show="o.sale_type==='seasonal'">Ends soon</span>
                            </div>
                            <div style="font-weight:800;font-size:16px;color:var(--text);margin:2px 0;"><span x-text="o.badge"></span> - <span x-text="o.title"></span></div>
                            <div style="font-size:12px;color:var(--text-2);" x-text="o.terms || 'See in store'"></div>
                            <button class="cta-btn" :disabled="o.sale_type==='limited' && o.remaining<=0" @click="redeem(o)"><svg class="ic ic-sm" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg> <span x-text="(o.sale_type==='limited' && o.remaining<=0) ? 'Sold out' : 'Reveal code'"></span></button>
                          </div>
                        </template>
                      </div>
                      <div x-show="active?.description" style="font-size:13px;color:var(--text-2);line-height:1.6;margin-top:16px;" x-text="active?.description"></div>
                      <div style="margin-top:18px;"><div class="sec-title" style="margin-bottom:6px;">Opening hours</div><div class="pf-hours"><template x-for="(v,d) in (active?.hours||{})" :key="d"><div><span style="text-transform:capitalize;" x-text="d"></span><span x-text="v"></span></div></template></div></div>
                      <div style="margin-top:18px;" x-show="active?.reviews?.length"><div class="sec-title" style="margin-bottom:4px;">Reviews <span style="font-weight:500;font-size:12px;color:var(--muted);">· via Google</span></div><template x-for="rv in (active?.reviews||[])" :key="rv.author"><div class="review"><div class="review-top"><span class="review-author" x-text="rv.author"></span><span class="star">★ <span x-text="rv.rating"></span></span></div><div class="review-text" x-text="rv.text"></div></div></template></div>
                      <div style="margin-top:18px;">
                        <div class="sec-title" style="margin-bottom:8px;">Location</div>
                        <div style="font-size:12.5px;color:var(--muted);margin-bottom:8px;" x-text="active?.address || active?.postcode"></div>
                        <div id="bizmap" class="bizmap"></div>
                        <button class="cta-btn" style="background:var(--surface);color:var(--text);margin-top:10px;" @click="directions()"><svg class="ic ic-sm" viewBox="0 0 24 24"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg> Get directions</button>
                      </div>
                    </div>
                  </div>
                  </div>
                </div>

                {{-- CODE --}}
                <div x-show="view==='code'" class="code-wrap">
                  <div class="code-tick"><svg class="ic ic-lg" viewBox="0 0 24 24" style="stroke-width:2.5"><polyline points="20 6 9 17 4 12"/></svg></div>
                  <div class="code-h">You're in.</div>
                  <div class="code-sub">Show this QR at the till for staff to scan - or read out the code.</div>
                  <div class="ticket">
                    <div class="ticket-eb">Show to redeem</div>
                    <div id="codeqr" class="codeqr"></div>
                    <div class="ticket-code" x-text="grouped(lastCode?.code)"></div>
                    <div class="ticket-div"></div><div class="ticket-biz" x-text="lastCode?.biz"></div><div class="ticket-offer" x-text="lastCode?.badge"></div>
                  </div>
                  <div class="timer" :class="lastCode?.status==='redeemed' ? 'done' : (codeExpired ? 'expired' : 'live')"><span x-show="lastCode?.status==='redeemed'">✓ Redeemed - enjoy!</span><span x-show="lastCode?.status!=='redeemed' && !codeExpired">Valid for <span class="mono" x-text="countdown"></span></span><span x-show="lastCode?.status!=='redeemed' && codeExpired">Code expired</span></div>
                  <button class="ghost-btn" style="max-width:260px;" @click="view='none'; tab='home'">Done</button>
                </div>

                {{-- SCAN --}}
                <div x-show="tab==='scan' && view==='none'" style="flex:1;display:flex;flex-direction:column;min-height:0;background:#0b0b0c;">
                  <div style="flex:1;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                    <div id="qr-reader" style="width:100%;"></div>
                    <div class="scan-frame"></div>
                  </div>
                  <div style="padding:16px;text-align:center;color:rgba(255,255,255,.85);font-size:13px;">
                    <p x-text="scanMsg || 'Scan a shop\'s window sticker to open its offers.'"></p>
                    <button class="ghost-btn" style="color:rgba(255,255,255,.7);" @click="goTab('home')">Browse instead</button>
                  </div>
                </div>
                </div>{{-- /.screens --}}

                {{-- TABBAR --}}
                <div class="tabbar">
                  <div class="tab" :class="tab==='home' && view==='none' && 'on'" @click="goTab('home')"><svg class="ic" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>Home</div>
                  <div class="tab" :class="tab==='map' && view==='none' && 'on'" @click="goTab('map')"><svg class="ic" viewBox="0 0 24 24"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>Map</div>
                  <div class="tab" :class="tab==='scan' && 'on'" @click="goTab('scan')"><svg class="ic" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><line x1="3" y1="12" x2="21" y2="12"/></svg>Scan</div>
                  <div class="tab" :class="tab==='saved' && view==='none' && 'on'" @click="goTab('saved')"><svg class="ic" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>Saved</div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

      {{-- ============ BUSINESS LOGIN ============ --}}
      <div class="gl-bizphone phone-col" :style="focus==='shopper' ? 'opacity:.38;transition:.2s' : 'opacity:1;transition:.2s'">
        <div class="phone-cap"><div class="t">Business login</div><div class="s">Publish offers &amp; verify codes</div></div>
        <div class="iphone">
          <div class="iphone-screen" x-on:wheel="scrollPhone($event)">
            <div class="island"></div>

            {{-- signup --}}
            <template x-if="!business">
              <div class="scr" style="background:var(--ink);">
                <div style="padding:64px 24px 24px; color:#fff; min-height:100%; display:flex; flex-direction:column;">
                  <div class="wordmark" style="font-size:30px;color:#fff;" x-html="wordmark()"></div>
                  <div style="font-family:var(--display),serif;font-weight:var(--display-weight);font-size:23px;margin-top:6px;">List your business</div>
                  <p style="color:rgba(255,255,255,.7); margin-top:4px; font-size:13px;">Free to list. Reach local customers in 5 minutes.</p>
                  <div style="margin-top:20px; display:flex; flex-direction:column; gap:10px;">
                    <div style="position:relative;">
                      <input class="field" style="background:rgba(255,255,255,.95);border:0;" x-model="form.gSearch" @input="googleMock()" @focus="googleMock()" placeholder="Find your business on Google…">
                      <div class="gmock" x-show="gResults.length" @click.outside="gResults=[]" style="display:none"><template x-for="g in gResults" :key="g.name"><div @click="pickGoogle(g)"><svg class="ic ic-sm" viewBox="0 0 24 24" style="color:var(--accent)"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg><span><b x-text="g.name"></b> · <span style="color:var(--muted)" x-text="g.postcode"></span></span></div></template></div>
                    </div>
                    <input class="field" style="background:rgba(255,255,255,.95);border:0;" x-model="form.bName" placeholder="Business name">
                    <select class="field" style="background:rgba(255,255,255,.95);border:0;color:var(--text-2);" x-model="form.bCat"><option value="">Choose a category…</option><template x-for="o in catOptions" :key="o.key"><option :value="o.id || ''" :disabled="o.header" x-text="o.label"></option></template></select>
                    <input class="field" style="background:rgba(255,255,255,.95);border:0;" x-model="form.bPostcode" placeholder="Postcode (e.g. NE1 6QF)">
                    <input class="field" type="email" style="background:rgba(255,255,255,.95);border:0;" x-model="form.bEmail" placeholder="Your email">
                    <div style="display:flex; flex-direction:column; gap:9px; font-size:12px; color:rgba(255,255,255,.8); line-height:1.5; margin-top:2px;">
                      <label style="display:flex; gap:8px; align-items:flex-start; cursor:pointer;">
                        <input type="checkbox" x-model="form.bAgreeTerms" style="margin-top:2px; width:15px; height:15px; accent-color:#059669; flex:0 0 auto;">
                        <span>I agree to the <a href="/terms" target="_blank" style="color:#6ee7b7; font-weight:600; text-decoration:underline;">Terms</a> &amp; <a href="/privacy" target="_blank" style="color:#6ee7b7; font-weight:600; text-decoration:underline;">Privacy Policy</a>, and I'm authorised to list this business.</span>
                      </label>
                      <label style="display:flex; gap:8px; align-items:flex-start; cursor:pointer;">
                        <input type="checkbox" x-model="form.bMktOptIn" style="margin-top:2px; width:15px; height:15px; accent-color:#059669; flex:0 0 auto;">
                        <span>Send me tips &amp; product updates from locolie.</span>
                      </label>
                    </div>
                    <button class="pri-btn" @click="signupBusiness()">Create business account</button>
                    <p x-show="bizError" style="color:#fca5a5;font-size:12px;text-align:center;" x-text="bizError"></p>
                  </div>
                </div>
              </div>
            </template>

            {{-- dashboard --}}
            <template x-if="business">
              <div style="flex:1; display:flex; flex-direction:column; overflow:hidden; min-height:0;">
                <div class="sb"><span>9:41</span><span class="mono" style="font-size:11px;">5G</span></div>

                <div class="screens">
                {{-- biz: home --}}
                <div x-show="btab==='home'" style="flex:1;display:flex;flex-direction:column;min-height:0;">
                  <div class="app-header"><div class="app-header-row"><span class="app-header-brand wordmark" x-html="wordmark()"></span><span style="color:rgba(255,255,255,.7);font-size:12px;">Business</span></div></div>
                  <div class="scr"><div class="feed">
                    <div class="bcard" style="display:flex;align-items:center;gap:12px;">
                      <div style="height:44px;width:44px;border-radius:11px;background:var(--accent);color:#fff;display:grid;place-items:center;font-weight:800;" x-text="initials(business.name)"></div>
                      <div><div style="font-weight:800;font-size:15px;color:var(--text);" x-text="business.name"></div><div style="color:var(--muted);font-size:12px;" x-text="(business.category||'')+(business.postcode?' · '+business.postcode:'')"></div></div>
                    </div>
                    <div class="bcard" style="margin-top:12px;display:grid;grid-template-columns:repeat(3,1fr);text-align:center;gap:8px;">
                      <div><div style="font-size:22px;font-weight:800;color:var(--text);" x-text="bizOffers.length"></div><div class="label">Offers</div></div>
                      <div><div style="font-size:22px;font-weight:800;color:var(--text);" x-text="stats.redeemed"></div><div class="label">Redeemed</div></div>
                      <div><div style="font-size:22px;font-weight:800;color:var(--text);" x-text="stats.pending"></div><div class="label">Pending</div></div>
                    </div>
                    <a :href="'/s/'+secret" target="_blank" style="display:flex;align-items:center;justify-content:center;gap:7px;margin-top:12px;background:var(--surface);border-radius:12px;padding:13px;font-size:13px;font-weight:700;color:var(--accent);text-decoration:none;"><svg class="ic ic-sm" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg> Print QR window sticker</a>
                    <div class="sec-title" style="margin:18px 0 8px;">Recent redemptions</div>
                    <template x-for="r in stats.recent" :key="r.code"><div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;padding:7px 0;border-bottom:1px solid var(--line);"><span class="mono" style="color:var(--text);" x-text="grouped(r.code)"></span><span style="color:var(--muted);font-size:11px;" x-text="r.offer"></span><span style="color:var(--sage);">✓</span></div></template>
                    <p x-show="!stats.recent || !stats.recent.length" style="color:var(--muted);font-size:13px;text-align:center;padding:14px 0;">None yet - verify a code in the Verify tab.</p>
                  </div></div>
                </div>

                {{-- biz: offers --}}
                <div x-show="btab==='offers'" style="flex:1;display:flex;flex-direction:column;min-height:0;">
                  <div class="titlebar">Offers</div>
                  <div class="scr"><div class="feed">
                    <div class="bcard"><div style="font-weight:800;color:var(--text);margin-bottom:11px;">Publish an offer</div><div style="display:flex;flex-direction:column;gap:9px;"><input class="field" x-model="form.oTitle" placeholder="Title (e.g. 20% off everything)"><input class="field" x-model="form.oBadge" placeholder="Badge (e.g. 20% OFF)"><input class="field" x-model="form.oTerms" placeholder="Terms (e.g. Mon-Fri)">
                      <select class="field" x-model="form.oSale"><option value="ongoing">Ongoing - always available</option><option value="limited">Limited stock - caps redemptions</option><option value="seasonal">Seasonal - short-run promo</option></select>
                      <input class="field" x-show="form.oSale==='limited'" x-model.number="form.oQty" type="number" min="1" placeholder="How many available? (e.g. 20)">
                      <button class="pri-btn" @click="createOffer()">Publish - goes live instantly</button></div></div>
                    <div class="sec-title" style="margin:16px 0 8px;">Your live offers</div>
                    <template x-for="o in bizOffers" :key="o.id"><div style="display:flex;justify-content:space-between;align-items:center;border:1px solid var(--line);border-radius:11px;padding:10px;margin-bottom:8px;"><div style="min-width:0;"><div style="font-weight:600;font-size:13px;color:var(--text);" x-text="o.title"></div><div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin-top:2px;"><span class="mono" style="font-size:11px;color:var(--accent);" x-text="o.badge"></span><span class="otag" x-show="o.sale_type==='limited'" :class="o.remaining<=5 && 'hot'" x-text="o.sold_out ? 'Sold out' : (o.remaining+' left')"></span><span class="otag seasonal" x-show="o.sale_type==='seasonal'">Seasonal</span></div><div style="font-size:10.5px;color:var(--muted);margin-top:3px;" x-text="o.redeemed_count+' redeemed'+(o.quantity? (' / '+o.quantity):'')"></div></div><button @click="deleteOffer(o.id)" style="border:0;background:transparent;color:#dc2626;font-size:12px;cursor:pointer;flex:0 0 auto;">Remove</button></div></template>
                    <p x-show="!bizOffers.length" style="color:var(--muted);font-size:13px;text-align:center;padding:12px 0;">No offers yet.</p>
                  </div></div>
                </div>

                {{-- biz: verify --}}
                <div x-show="btab==='verify'" style="flex:1;display:flex;flex-direction:column;min-height:0;">
                  <div class="titlebar">Verify</div>
                  <div class="scr"><div class="feed">
                    <div class="bcard" style="text-align:center;">
                      <div style="font-weight:800;color:var(--text);">Verify a redemption</div>
                      <p style="color:var(--muted);font-size:12px;margin:4px 0 12px;">Scan the customer's QR, or type their 6-digit code.</p>
                      <button class="pri-btn" style="background:var(--ink);color:#fff;margin-bottom:12px;display:flex;align-items:center;justify-content:center;gap:7px;" @click="scanMode==='business' ? stopScan() : startScan('biz-qr-reader','business')">
                        <svg class="ic ic-sm" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><line x1="3" y1="12" x2="21" y2="12"/></svg>
                        <span x-text="scanMode==='business' ? 'Stop scanning' : 'Scan customer QR'"></span>
                      </button>
                      <div id="biz-qr-reader" x-show="scanMode==='business'" style="width:100%;border-radius:12px;overflow:hidden;margin-bottom:12px;"></div>
                      <input class="mono field" x-model="form.verify" maxlength="6" inputmode="numeric" placeholder="000000" style="text-align:center;font-size:24px;letter-spacing:.3em;">
                      <button class="pri-btn" style="margin-top:11px;" @click="verifyCode()">Verify &amp; redeem</button>
                      <div x-show="verifyMsg" x-transition style="margin-top:11px;font-size:13px;border-radius:10px;padding:9px 12px;" :style="verifyOk ? 'background:#dcfce7;color:#15803d' : 'background:#fee2e2;color:#dc2626'" x-text="verifyMsg"></div>
                    </div>
                  </div></div>
                </div>
                </div>{{-- /.screens --}}

                {{-- biz tabbar --}}
                <div class="tabbar">
                  <div class="tab" :class="btab==='home' && 'on'" @click="btab='home'; loadBiz()"><svg class="ic" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>Home</div>
                  <div class="tab" :class="btab==='offers' && 'on'" @click="btab='offers'"><svg class="ic" viewBox="0 0 24 24"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>Offers</div>
                  <div class="tab" :class="btab==='verify' && 'on'" @click="btab='verify'"><svg class="ic" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Verify</div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
    @else
    {{-- Responsive device preview - the SAME app at mobile / tablet / desktop, business below --}}
    <div class="ctrls">
      <span class="ctrl-label">Preview at</span>
      <button class="npill" :class="device==='mobile' && 'on'" @click="device='mobile'">Mobile</button>
      <button class="npill" :class="device==='tablet' && 'on'" @click="device='tablet'">Tablet</button>
      <button class="npill" :class="device==='desktop' && 'on'" @click="device='desktop'">Desktop</button>
    </div>
    <div class="dev-previews">
      <div class="dev-col">
        <div class="dev-cap"><span class="t">Shopper app</span><span class="s">Discover &amp; redeem local offers</span></div>
        <div class="dev-frame"><iframe src="/app" :style="devInner" title="Shopper app"></iframe></div>
      </div>
      <div class="dev-col">
        <div class="dev-cap"><span class="t">Business login</span><span class="s">Publish offers &amp; verify codes</span></div>
        <div class="dev-frame"><iframe src="/app?as=business" :style="devInner" title="Business app"></iframe></div>
      </div>
    </div>
    @endif
  </section>

  {{-- QUICK LINKS (internal team navigation only; never rendered in the consumer/business solo app) --}}
  @unless($solo)
  <div class="gl-chrome mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @php
        $svgAttr = 'width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
        $cards = [
            ['route' => 'portal.plan',    'svg' => '<svg '.$svgAttr.'><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/></svg>', 'title' => 'Business Plan',  'desc' => 'Market, product, GTM, financials - 3 co-founders, equal equity.'],
            ['route' => 'portal.brand',   'svg' => '<svg '.$svgAttr.'><path d="M12 3l1.9 4.6 4.9.4-3.7 3.2 1.1 4.8L12 13.9 7.8 16l1.1-4.8L5.2 8l4.9-.4z"/></svg>', 'title' => 'Brand & Logos',  'desc' => '10 logo & colour concepts, 5 style directions, the name.'],
            ['route' => 'portal.design',  'svg' => '<svg '.$svgAttr.'><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>', 'title' => 'App Screens',    'desc' => 'Full brand system and 10 polished app screens.'],
            ['route' => 'portal.admin',   'svg' => '<svg '.$svgAttr.'><path d="M3 3v18h18"/><rect x="7" y="9" width="3" height="9"/><rect x="14" y="5" width="3" height="13"/></svg>', 'title' => 'Data & Admin',   'desc' => 'Every business, offer and redemption in the live database.'],
            ['route' => 'portal.mockups', 'svg' => '<svg '.$svgAttr.'><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>', 'title' => 'Mockups',        'desc' => $mockupCount > 0 ? $mockupCount.' image'.($mockupCount === 1 ? '' : 's').' uploaded' : 'Upload your designs.'],
            ['route' => 'portal.ideas',   'svg' => '<svg '.$svgAttr.'><path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.3 1 2.3h6c0-1 .4-1.8 1-2.3A7 7 0 0 0 12 2z"/></svg>', 'title' => 'Ideas',          'desc' => 'Shared scratchpad for product, brand and GTM ideas.'],
        ];
    @endphp
    @foreach ($cards as $c)
        <a href="{{ route($c['route']) }}"
           class="group rounded-2xl border border-slate-200/80 bg-white/80 backdrop-blur p-6 shadow-sm hover:shadow-md hover:border-emerald-300 hover:-translate-y-0.5 transition">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">{!! $c['svg'] !!}</div>
            <h2 class="mt-3 font-semibold text-slate-900 group-hover:text-emerald-700">{{ $c['title'] }}</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $c['desc'] }}</p>
        </a>
    @endforeach
  </div>
  @endunless
</div>

<script>
function goLocalApp() {
  return {
    tab:'home', view:'none', btab:'home', cCat:null, cSub:null, cSub2:null, active:null, activeOffer:null, focus:null,
    lastCode:null, countdown:'', codeExpired:false, loading:false,
    verifyMsg:'', verifyOk:false, bizError:'',
    mapsKey: @json($mapsKey ?? ''), mapsId: @json($mapsId ?? 'DEMO_MAP_ID'), mapsReady:false,
    vapidKey: @json($vapidKey ?? ''),
    brand:'locolie', themeKey:'mono', location:'NE',
    brandNames:['locolie','Vicinity','Patch','TownLoop','Mooch','Highstreet'],
    onb:1, sort:'distance', openNow:false, searchQ:'', device:'mobile',
    filterOpen:false, fDist:null, fRating:null, fOffer:null, fSale:null,
    parents:[], categories:[], businesses:[], customer:null, business:null, secret:null,
    bizOffers:[], stats:{redeemed:0,pending:0,recent:[]}, favs:[], prefs:[],
    notifications:[], unread:0, notifOpen:false, seen:[], notifyOn:false,
    gResults:[], tickN:0, map:null, markers:[], bizMap:null,
    userLat:null, userLng:null, scanMsg:'', scanMode:null,
    form:{ cName:'', cEmail:'', cLoc:'NE', prefs:[], agreeTerms:false, mktOptIn:true, bName:'', bCat:'', bPostcode:'', bEmail:'', bAgreeTerms:false, bMktOptIn:true, oTitle:'', oBadge:'', oTerms:'', oSale:'ongoing', oQty:20, verify:'', gSearch:'', placeId:'' },

    googlePlaces:[
      {name:'The Quayside Tap', postcode:'NE1 3RW', cat:'pubs-bars'},
      {name:'Bigg Market Barbers', postcode:'NE1 1EW', cat:'hairdressers'},
      {name:'Grainger Street Garage', postcode:'NE1 5JG', cat:'mechanics'},
      {name:'Pilgrim Street Kitchen', postcode:'NE1 6QF', cat:'food-drink'},
      {name:'Jesmond Dene Florist', postcode:'NE2 2EY', cat:'retail'},
      {name:'Tyneside Joinery', postcode:'NE1 2EX', cat:'builders'},
    ],

    themes:{
      mono:    { label:'Black & Green', cls:'theme-mono', vars:{ '--ink':'#0a0a0a','--accent':'#059669','--accent-soft':'#d1fae5','--cta':'#0a0a0a','--cta-text':'#ffffff','--star':'#f59e0b','--sage':'#059669','--bg':'#ffffff','--surface':'#f5f5f5','--surface-2':'#ececec','--text':'#0a0a0a','--text-2':'#404040','--muted':'#737373','--line':'#e5e5e5','--display':"'Inter'",'--display-weight':'800','--font':"'Inter'" } },
      warm:    { label:'Warm Commercial', cls:'theme-warm',  vars:{ '--ink':'#0F2D38','--accent':'#D9603B','--accent-soft':'#FDEBE0','--cta':'#FFA41C','--cta-text':'#0F2D38','--star':'#FFA41C','--sage':'#6FA286','--bg':'#FFFFFF','--surface':'#F7F8FA','--surface-2':'#EEF0F4','--text':'#131A22','--text-2':'#4A5560','--muted':'#6B7480','--line':'#E4E6EB','--display':"'Inter'",'--display-weight':'800','--font':"'Inter'" } },
      mint:    { label:'Fresh Mint', cls:'theme-mint', vars:{ '--ink':'#064e3b','--accent':'#059669','--accent-soft':'#d1fae5','--cta':'#059669','--cta-text':'#ffffff','--star':'#f59e0b','--sage':'#34d399','--bg':'#FFFFFF','--surface':'#f0fdf4','--surface-2':'#dcfce7','--text':'#0f172a','--text-2':'#475569','--muted':'#64748b','--line':'#e2e8f0','--display':"'Inter'",'--display-weight':'800','--font':"'Inter'" } },
      glass:   { label:'Liquid Glass', cls:'theme-glass', vars:{ '--ink':'#0c4a6e','--accent':'#0ea5e9','--accent-soft':'#e0f2fe','--cta':'#0ea5e9','--cta-text':'#ffffff','--star':'#f59e0b','--sage':'#22d3ee','--bg':'#f6fdff','--surface':'rgba(255,255,255,.6)','--surface-2':'#e0f2fe','--text':'#0f172a','--text-2':'#475569','--muted':'#64748b','--line':'#cfeefe','--display':"'Inter'",'--display-weight':'800','--font':"'Inter'" } },
      dark:    { label:'Premium Dark', cls:'theme-dark', vars:{ '--ink':'#000000','--accent':'#fbbf24','--accent-soft':'#3a2f10','--cta':'#fbbf24','--cta-text':'#111111','--star':'#fbbf24','--sage':'#34d399','--bg':'#0f172a','--surface':'#1e293b','--surface-2':'#334155','--text':'#f1f5f9','--text-2':'#cbd5e1','--muted':'#94a3b8','--line':'#334155','--display':"'Inter'",'--display-weight':'800','--font':"'Inter'" } },
      editorial:{ label:'Editorial', cls:'theme-editorial', vars:{ '--ink':'#3b2417','--accent':'#9a3412','--accent-soft':'#fef3e2','--cta':'#7c2d12','--cta-text':'#fff7ed','--star':'#b45309','--sage':'#7d8b6a','--bg':'#fdfaf3','--surface':'#f7f0e3','--surface-2':'#efe6d4','--text':'#292017','--text-2':'#5c4f3f','--muted':'#8a7a64','--line':'#e6dbc7','--display':"'Instrument Serif'",'--display-weight':'400','--font':"'Inter'" } },
      pop:     { label:'Bold Pop', cls:'theme-pop', vars:{ '--ink':'#111111','--accent':'#ea580c','--accent-soft':'#ffedd5','--cta':'#111111','--cta-text':'#fde047','--star':'#f59e0b','--sage':'#16a34a','--bg':'#fffdf5','--surface':'#fef9c3','--surface-2':'#fef08a','--text':'#111111','--text-2':'#3f3f46','--muted':'#71717a','--line':'#fde68a','--display':"'Fredoka'",'--display-weight':'700','--font':"'Fredoka'" } },
    },

    get theme(){ return this.themes[this.themeKey]; },
    get themeVars(){ return Object.entries(this.theme.vars).map(([k,v])=>`${k}:${v}`).join(';'); },
    get locationLabel(){ return this.location==='all'?'Everywhere':'Newcastle'; },
    get devInner(){ const d={ mobile:[390,760], tablet:[768,720], desktop:[1120,680] }[this.device] || [390,760]; return `width:${d[0]}px;height:${d[1]}px;max-width:calc(100vw - 80px);`; },
    get withOffers(){ return this.businesses.filter(b=>b.offers.length); },
    applyFilters(list){
      if(this.openNow) list = list.filter(b=>this.isOpen(b.hours));
      if(this.fRating) list = list.filter(b=>(+b.rating||0) >= this.fRating);
      if(this.fOffer) list = list.filter(b=>this.offerPct(b) >= this.fOffer);
      if(this.fDist) list = list.filter(b=>parseFloat(this.dist(b)) <= this.fDist);
      if(this.fSale) list = list.filter(b=>(b.offers||[]).some(o=>o.sale_type===this.fSale));
      return list;
    },
    // Does a business fall under the active category selection (parent slug, or a drilled-in sub)?
    // Match a business against the deepest active selection, using its ancestor path.
    inCat(b){
      if(this.cCat==='foryou') return this.matchesPrefs(b);
      const sel = this.cSub2 || this.cSub || this.cCat;
      if(!sel) return true;
      return (b.cat_slugs||[]).includes(sel);
    },
    matchesPrefs(b){ const s=b.cat_slugs||[]; return !this.prefs.length || this.prefs.some(p=>s.includes(p)); },
    selectParent(slug){ this.cCat = this.cCat===slug ? null : slug; this.cSub = null; this.cSub2 = null; },
    selectSub(slug){ this.cSub = this.cSub===slug ? null : slug; this.cSub2 = null; },
    get visible(){
      let list = this.applyFilters(this.withOffers.filter(b=>this.inCat(b)));
      return [...list].sort((a,b)=> this.sort==='rating' ? (b.rating-a.rating) : (parseFloat(this.dist(a))-parseFloat(this.dist(b))));
    },
    get filterCount(){ return [this.fDist, this.fRating, this.fOffer, this.fSale].filter(v=>v!==null).length + (this.openNow?1:0); },
    get featured(){ const near = this.userLat ? (b=>parseFloat(this.dist(b))||999) : (()=>0); return [...this.applyFilters(this.withOffers)].sort((a,b)=>((b.featured?1:0)-(a.featured?1:0))||(near(a)-near(b))||(b.rating-a.rating)||(b.reviews_count-a.reviews_count)).slice(0,8); },
    // Explore-by-category rows are grouped by PARENT (only parents that have live offers).
    get categoriesWithBiz(){ const list=this.applyFilters(this.withOffers); return this.parents.filter(p=>list.some(b=>(b.cat_slugs||[]).includes(p.slug))); },
    get currentParent(){ return this.parents.find(p=>p.slug===this.cCat); },
    get subCats(){ return this.currentParent ? (this.currentParent.children||[]) : []; },
    get currentSub(){ return this.subCats.find(c=>c.slug===this.cSub); },
    get subCats2(){ return this.currentSub ? (this.currentSub.children||[]) : []; },
    get cCatName(){ if(this.cCat==='foryou') return 'For you'; return this.currentParent ? this.currentParent.name : ''; },
    get cSubName(){ return this.currentSub ? this.currentSub.name : ''; },
    get cSub2Name(){ const s=this.subCats2.find(c=>c.slug===this.cSub2); return s?s.name:''; },
    // Grouped <select> options for business signup: top parents as headers, every
    // descendant selectable and indented by depth (so builder trades are pickable).
    get catOptions(){ const out=[]; const walk=(nodes,depth)=>{ nodes.forEach(n=>{ if(depth===0){ out.push({key:'h-'+n.slug, header:true, label:n.name}); } else { out.push({key:String(n.id), id:n.id, label:' '.repeat(depth*2)+n.name}); } if(n.children&&n.children.length) walk(n.children, depth+1); }); }; walk(this.parents,0); return out; },
    byCat(slug){ return this.applyFilters(this.withOffers.filter(b=>(b.cat_slugs||[]).includes(slug))).sort((a,b)=>parseFloat(this.dist(a))-parseFloat(this.dist(b))); },
    offerPct(b){ const o=b.offers&&b.offers[0]; if(!o) return 0; const m=String(o.badge||'').match(/(\d+)\s*%/); if(m) return +m[1]; return /free|bogo|2[- ]?for|no fee/i.test((o.badge||'')+' '+(o.title||'')) ? 100 : 0; },
    catIcon(slug){
      const I={
        all:'<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>',
        // Parent (top-level) groups - shown as the home category tiles.
        'eat-drink':'<path d="M18 8h1a3 3 0 0 1 0 6h-1"/><path d="M4 8h14v6a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5z"/><line x1="6" y1="2" x2="6" y2="4.5"/><line x1="10" y1="2" x2="10" y2="4.5"/><line x1="14" y1="2" x2="14" y2="4.5"/>',
        'health-beauty':'<path d="M12 2.5l1.7 4.9 4.9 1.6-4.9 1.6L12 15.5l-1.7-4.9L5.4 9l4.9-1.6z"/><path d="M18 14l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7z"/>',
        'fitness-leisure':'<path d="M6.5 6.5l11 11"/><path d="M4 7 7 4l2.5 2.5L7 9z"/><path d="M20 17l-3 3-2.5-2.5L17 15z"/><path d="M4.5 11 2 13l2 2"/><path d="M19.5 13 22 11l-2-2"/>',
        'home-maintenance':'<path d="M3 11 12 4l9 7"/><path d="M5 10v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9"/><path d="M10 20v-5h4v5"/>',
        'motoring':'<circle cx="7" cy="17" r="1.8"/><circle cx="17" cy="17" r="1.8"/><path d="M5.2 17H3v-4.5l1.8-4h8.5l3.5 4H21V17h-2.2"/><line x1="8.8" y1="17" x2="15.2" y2="17"/>',
        'shopping':'<path d="M6 2 3 6v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'pets':'<circle cx="6.5" cy="9.5" r="1.8"/><circle cx="11" cy="6" r="1.8"/><circle cx="16.5" cy="7.5" r="1.8"/><circle cx="19" cy="13" r="1.6"/><path d="M11.5 12c-2.2 0-4.5 1.7-4.5 4 0 1.9 1.6 3 3.3 3 1.3 0 1.7-.6 3.2-.6s1.9.6 3.2.6c1.7 0 3.3-1.1 3.3-3 0-2.3-2.3-4-4.5-4z"/>',
        'professional':'<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
        'food-drink':'<path d="M18 8h1a3 3 0 0 1 0 6h-1"/><path d="M4 8h14v6a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5z"/><line x1="6" y1="2" x2="6" y2="4.5"/><line x1="10" y1="2" x2="10" y2="4.5"/><line x1="14" y1="2" x2="14" y2="4.5"/>',
        'pubs-bars':'<path d="M6 3h12l-1.4 9.2A4 4 0 0 1 12.65 16h-1.3a4 4 0 0 1-3.95-3.8z"/><line x1="12" y1="16" x2="12" y2="21"/><line x1="8" y1="21" x2="16" y2="21"/>',
        retail:'<path d="M6 2 3 6v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        hairdressers:'<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.1" y2="15.9"/><line x1="14.5" y1="14.5" x2="20" y2="20"/><line x1="8.1" y1="8.1" x2="12" y2="12"/>',
        beauty:'<path d="M12 2.5l1.7 4.9 4.9 1.6-4.9 1.6L12 15.5l-1.7-4.9L5.4 9l4.9-1.6z"/><path d="M18 14l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7z"/>',
        fitness:'<path d="M6.5 6.5l11 11"/><path d="M4 7 7 4l2.5 2.5L7 9z"/><path d="M20 17l-3 3-2.5-2.5L17 15z"/><path d="M4.5 11 2 13l2 2"/><path d="M19.5 13 22 11l-2-2"/>',
        builders:'<path d="M2 19h20v2H2z"/><path d="M4 19v-3a8 8 0 0 1 16 0v3"/><line x1="12" y1="4" x2="12" y2="8"/><path d="M9 8h6"/>',
        mechanics:'<circle cx="7" cy="17" r="1.8"/><circle cx="17" cy="17" r="1.8"/><path d="M5.2 17H3v-4.5l1.8-4h8.5l3.5 4H21V17h-2.2"/><line x1="8.8" y1="17" x2="15.2" y2="17"/>',
        trades:'<polygon points="13 2 4 14 11 14 11 22 20 10 13 10 13 2"/>',
        'pet-care':'<circle cx="6.5" cy="9.5" r="1.8"/><circle cx="11" cy="6" r="1.8"/><circle cx="16.5" cy="7.5" r="1.8"/><circle cx="19" cy="13" r="1.6"/><path d="M11.5 12c-2.2 0-4.5 1.7-4.5 4 0 1.9 1.6 3 3.3 3 1.3 0 1.7-.6 3.2-.6s1.9.6 3.2.6c1.7 0 3.3-1.1 3.3-3 0-2.3-2.3-4-4.5-4z"/>',
        health:'<path d="M20.8 5.6a5.5 5.5 0 0 0-7.8 0L12 6.7l-1-1.1a5.5 5.5 0 1 0-7.8 7.8L12 22l8.8-8.6a5.5 5.5 0 0 0 0-7.8z"/>',
        services:'<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
      };
      return '<svg viewBox="0 0 24 24" aria-hidden="true">'+(I[slug]||I.services)+'</svg>';
    },
    get searchResults(){ const q=(this.searchQ||'').toLowerCase().trim(); if(!q) return []; return this.withOffers.filter(b=> b.name.toLowerCase().includes(q) || (b.category||'').toLowerCase().includes(q) || b.offers.some(o=>(o.title||'').toLowerCase().includes(q)||(o.badge||'').toLowerCase().includes(q))); },

    wordmark(){ return this.markFor(this.brand); },
    pinGlyph(){ return '<svg class="pin-letter" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>'; },
    markFor(n){ const s=String(n); const cap=s.charAt(0).toUpperCase()+s.slice(1); return cap.replace(/o/i, this.pinGlyph()); },
    grouped(c){ return c ? String(c).replace(/(\d{2})(\d{2})(\d{2})/,'$1 · $2 · $3') : ''; },
    initials(n){ return (n||'?').split(' ').map(x=>x[0]).slice(0,2).join('').toUpperCase(); },

    async api(path, opts){
      const res = await fetch('/api'+path, Object.assign({ headers:{ 'Accept':'application/json', 'Content-Type':'application/json', 'ngrok-skip-browser-warning':'true' } }, opts));
      if(!res.ok){ const e = await res.json().catch(()=>({message:'Error'})); throw new Error(e.message || ('HTTP '+res.status)); }
      return res.json();
    },

    async init(){
      this.brand = this.load('gl_brand','locolie');
      this.themeKey = this.load('gl_theme','mono');
      // One-time switch to the new black & green default, even if an older style was saved.
      if(!this.load('gl_default_mono', false)){ this.themeKey='mono'; this.save('gl_theme','mono'); this.save('gl_default_mono', true); }
      this.customer = this.load('gl_customer', null);
      this.business = this.load('gl_biz', null);
      this.secret = this.load('gl_secret', null);
      this.favs = this.load('gl_favs', []);
      this.notifications = this.load('gl_notifs', []);
      this.seen = this.load('gl_seen', []);
      // Showcase: start the shopper phone already signed in (guest) so the app, map & tabs show.
      if(!this.customer){ this.customer = { name:'Guest', location:'NE', prefs:[] }; this.save('gl_customer', this.customer); }
      this.location = this.customer.location||'NE1'; this.prefs = this.customer.prefs||[]; this.notifyOn = !!this.customer.notify;
      this.$watch('brand', v=>this.save('gl_brand',v));
      this.$watch('themeKey', v=>this.save('gl_theme',v));
      // Always start a new screen at the top (fixes "retailer loads below").
      this.$watch('view', ()=>this.resetScroll());
      this.$watch('tab', ()=>this.resetScroll());

      this.parents = await this.api('/categories').catch(()=>[]);
      // Flatten the whole tree (parents → subs → sub-trades) for search/lookup.
      const flat=[]; const walk=n=>{ flat.push(n); (n.children||[]).forEach(walk); };
      this.parents.forEach(walk);
      this.categories = flat;
      await this.fetchBusinesses(true);

      const slug = new URLSearchParams(location.search).get('b');
      if(slug){ const b=this.businesses.find(x=>x.slug===slug); if(b) this.openBusiness(b); }

      if(this.business && this.secret){ await this.loadBiz(); }
      setInterval(()=>this.tick(), 1000);
      setInterval(()=>this.pollOffers(), 15000);

      // Real distance from the user's current location (falls back to seed distance if denied).
      if(navigator.geolocation){ navigator.geolocation.getCurrentPosition(p=>{ this.userLat=p.coords.latitude; this.userLng=p.coords.longitude; if(this.tab==='map'&&this.gm().map){ this.renderMarkers(); this.recenter(); } }, ()=>{}, {timeout:8000, maximumAge:300000}); }
    },
    loadScript(src){ return new Promise((res,rej)=>{ if(document.querySelector('script[data-src="'+src+'"]')){ res(); return; } const s=document.createElement('script'); s.src=src; s.dataset.src=src; s.onload=res; s.onerror=rej; document.head.appendChild(s); }); },
    load(k,d){ try{ const v=localStorage.getItem(k); return v?JSON.parse(v):d; }catch(e){ return d; } },
    save(k,v){ localStorage.setItem(k, JSON.stringify(v)); },

    async fetchBusinesses(initial){
      this.loading = true;
      const qs = this.location==='all' ? '' : ('?postcode='+encodeURIComponent(this.location));
      this.businesses = await this.api('/businesses'+qs).catch(()=>[]);
      this.loading = false;
      if(initial){ this.seen = this.allOfferIds(); this.save('gl_seen', this.seen); }
      if(this.tab==='map' && this.gm().map) this.renderMarkers();
    },
    allOfferIds(){ const ids=[]; this.businesses.forEach(b=>b.offers.forEach(o=>ids.push(o.id))); return ids; },
    cycleLocation(){ const order=['NE','all']; this.location = order[(order.indexOf(this.location)+1)%order.length]; this.customer.location=this.location; this.save('gl_customer',this.customer); this.cCat=null; this.fetchBusinesses(true).then(()=>{ if(this.tab==='map') this.recenter(); }); },

    togglePref(slug){ const i=this.form.prefs.indexOf(slug); if(i<0) this.form.prefs.push(slug); else this.form.prefs.splice(i,1); },
    onbNext(){ if(this.onb===1){ if(!this.form.cName){ alert('Enter your name'); return; } if(!this.form.agreeTerms){ alert('Please agree to the Terms and Privacy Policy to continue.'); return; } this.onb=2; } else if(this.onb===2){ this.onb=3; } else { this.finishOnboarding(true); } },
    async finishOnboarding(notify){
      if(notify){ await this.subscribePush(); }
      this.location=this.form.cLoc; this.prefs=[...this.form.prefs]; this.notifyOn=!!notify;
      this.customer={ name:this.form.cName, email:this.form.cEmail, location:this.location, prefs:this.prefs, notify:this.notifyOn, mkt:!!this.form.mktOptIn, termsAt:new Date().toISOString() };
      this.save('gl_customer',this.customer); this.tab='home'; this.view='none';
      await this.fetchBusinesses(true); if(this.prefs.length) this.cCat='foryou';
    },
    // Register the service worker + subscribe this browser to web push (VAPID).
    async subscribePush(){
      // Native app (Capacitor shell): use the OS push token (APNs/FCM), not web VAPID.
      if(window.Capacitor?.isNativePlatform?.()){ return await this.registerNativePush(); }
      try{
        if(!('serviceWorker' in navigator) || !('PushManager' in window) || !this.vapidKey) return false;
        const perm = await Notification.requestPermission();
        if(perm !== 'granted') return false;
        const reg = await navigator.serviceWorker.register('/sw.js');
        await navigator.serviceWorker.ready;
        let sub = await reg.pushManager.getSubscription();
        if(!sub){
          sub = await reg.pushManager.subscribe({ userVisibleOnly:true, applicationServerKey:this.urlB64ToUint8(this.vapidKey) });
        }
        const j = sub.toJSON();
        await this.api('/push/subscribe', { method:'POST', body: JSON.stringify({ endpoint:j.endpoint, keys:j.keys, category_prefs:this.form.prefs }) });
        return true;
      }catch(e){ return false; }
    },
    // Native push: ask iOS/Android for permission, register, and POST the device
    // token to /api/devices/register (backend storage already exists). The Capacitor
    // runtime is injected by the native shell, so no JS bundle change is needed.
    async registerNativePush(){
      try{
        const Push = window.Capacitor?.Plugins?.PushNotifications;
        if(!Push) return false;
        if(!this._nativePushBound){
          this._nativePushBound = true;
          Push.addListener('registration', (t)=>{
            const platform = (window.Capacitor.getPlatform && window.Capacitor.getPlatform()) || 'ios';
            this.api('/devices/register', { method:'POST', body: JSON.stringify({ platform, token: t.value, topics: this.prefs }) }).catch(()=>{});
          });
          Push.addListener('registrationError', ()=>{});
        }
        const perm = await Push.requestPermissions();
        if(perm.receive !== 'granted') return false;
        await Push.register();
        return true;
      }catch(e){ return false; }
    },
    urlB64ToUint8(s){ const pad='='.repeat((4-s.length%4)%4); const b=(s+pad).replace(/-/g,'+').replace(/_/g,'/'); const raw=atob(b); return Uint8Array.from([...raw].map(c=>c.charCodeAt(0))); },

    goTab(t){ if(this.tab==='scan' && t!=='scan') this.stopScan(); this.tab=t; this.view='none'; this.notifOpen=false; if(t==='map') this.$nextTick(()=>this.initMap()); if(t==='scan') this.$nextTick(()=>this.startScan('qr-reader','shopper')); },
    resetScroll(){ const go=()=>{ try{ window.scrollTo(0,0); }catch(e){} if(document.scrollingElement) document.scrollingElement.scrollTop=0; document.querySelectorAll('.scr').forEach(s=>{ s.scrollTop=0; }); }; this.$nextTick(()=>{ go(); requestAnimationFrame(go); setTimeout(go,70); }); },
    // Robust desktop wheel scrolling inside the phone - forwards wheel delta to the active scroll area.
    scrollPhone(e){
      let el = e.target.closest ? e.target.closest('.scr') : null;
      if(!el){ const screen=e.currentTarget; el=[...screen.querySelectorAll('.scr')].find(s=>s.offsetParent!==null && s.scrollHeight>s.clientHeight+2); }
      if(el && el.scrollHeight>el.clientHeight+2){ el.scrollTop += e.deltaY; e.preventDefault(); }
    },

    openBusiness(b){ this.stopScan(); this.active=b; this.activeOffer=b.offers?.[0]||null; this.view='profile'; this.tab='home'; this.initBizMap(b);
      this.api('/businesses/'+b.slug).then(full=>{ this.active = Object.assign({}, b, full); this.activeOffer = this.active.offers?.[0]||this.activeOffer; }).catch(()=>{}); },
    openSaved(f){ const b=this.businesses.find(x=>x.id===f.id); if(b){ this.openBusiness(b); } else { this.api('/businesses/'+f.slug).then(full=>{ this.active=full; this.activeOffer=full.offers?.[0]||null; this.view='profile'; this.initBizMap(full); }).catch(()=>{}); } },

    isFav(id){ return this.favs.some(f=>f.id===id); },
    toggleFav(b){ if(!b) return; const i=this.favs.findIndex(f=>f.id===b.id); if(i<0){ this.favs.unshift({id:b.id,name:b.name,slug:b.slug,category:b.category,badge:b.offers?.[0]?.badge,image:b.image}); } else { this.favs.splice(i,1); } this.save('gl_favs',this.favs); },

    // QR + camera scanning (lazy-loaded libs).
    QR_LIB:'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js',
    SCAN_LIB:'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js',
    renderCodeQr(){ this.loadScript(this.QR_LIB).then(()=>{ this.$nextTick(()=>{ const el=document.getElementById('codeqr'); if(!el||!this.lastCode||!window.QRCode) return; el.innerHTML=''; new QRCode(el,{text:this.lastCode.code,width:150,height:150,colorDark:'#0a0a0a',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.M}); }); }).catch(()=>{}); },
    async startScan(elId, mode){
      this.scanMode=mode; this.scanMsg='Starting camera…';
      try{
        await this.loadScript(this.SCAN_LIB);
        await new Promise(r=>setTimeout(r,150));
        if(this._qr && this._qrEl!==elId){ try{ await this._qr.stop(); }catch(e){} this._qr=null; }
        if(!this._qr){ this._qr=new Html5Qrcode(elId); this._qrEl=elId; }
        await this._qr.start({facingMode:'environment'},{fps:10,qrbox:200},(t)=>this.onScanText(t),()=>{});
        this.scanMsg='';
      }catch(e){ this.scanMsg = mode==='business' ? 'Camera unavailable - type the code instead.' : 'Camera unavailable - browse instead.'; }
    },
    async stopScan(){ this.scanMode=null; try{ if(this._qr && this._qr.isScanning) await this._qr.stop(); }catch(e){} },
    onScanText(text){
      if(this.scanMode==='business'){ const m=String(text).match(/\d{6}/); if(m){ this.form.verify=m[0]; this.stopScan(); this.verifyCode(); } return; }
      try{ const u=new URL(text, location.origin); const slug=u.searchParams.get('b'); const tk=u.pathname.match(/\/c\/([^/?#]+)/);
        if(slug){ const b=this.businesses.find(x=>x.slug===slug); if(b){ this.stopScan(); this.openBusiness(b); return; } }
        if(tk){ this.stopScan(); this.api('/businesses/by-token/'+tk[1]).then(full=>{ this.tab='home'; this.active=full; this.activeOffer=full.offers?.[0]||null; this.view='profile'; this.initBizMap(full); }).catch(()=>{ this.scanMsg='Sticker not recognised.'; }); return; }
      }catch(e){}
      this.scanMsg='Scanned: '+text;
    },

    callBiz(){ if(this.active?.phone) location.href='tel:'+this.active.phone; else alert('Phone: 0191 000 0000 (demo)'); },
    directions(){ if(this.active?.lat) window.open('https://www.google.com/maps?q='+this.active.lat+','+this.active.lng,'_blank'); else alert('Directions (demo)'); },
    share(){ if(navigator.share){ navigator.share({title:this.active?.name,url:location.origin+'/?b='+this.active?.slug}).catch(()=>{}); } else { alert('Share (demo)'); } },

    isOpen(hours){ if(!hours) return false; const day=['sun','mon','tue','wed','thu','fri','sat'][new Date().getDay()]; const v=hours[day]; if(!v||/closed/i.test(v)) return false; const m=v.split(/[--]/); if(m.length<2) return true; const toMin=s=>{ const p=s.trim().split(':'); return (+p[0])*60+(+(p[1]||0)); }; const now=new Date().getHours()*60+new Date().getMinutes(); return now>=toMin(m[0]) && now<=toMin(m[1]); },

    async redeem(offer){
      const o = offer || this.activeOffer; if(!o) return;
      this.activeOffer = o;
      try{
        const r = await this.api('/offers/'+o.id+'/redeem', { method:'POST', body: JSON.stringify({ customer_name: this.customer?.name, customer_email: this.customer?.email, marketing_opt_in: this.customer?.mkt !== false }) });
        this.lastCode = { code:r.code, biz:r.business, badge:r.badge, offer:r.offer, status:'pending', expiresAt: Date.now()+(r.ttl_seconds*1000) };
        this.codeExpired=false; this.view='code'; this.renderCodeQr();
        if(this.secret) this.loadBiz();
      }catch(e){ alert(e.message); }
    },
    async tick(){
      this.tickN++;
      if(this.lastCode){
        const ms = this.lastCode.expiresAt - Date.now();
        if(this.lastCode.status!=='redeemed'){ if(ms<=0){ this.codeExpired=true; this.countdown='0:00'; } else { this.codeExpired=false; const s=Math.floor(ms/1000); this.countdown=Math.floor(s/60)+':'+String(s%60).padStart(2,'0'); } }
        if(this.view==='code' && this.lastCode.status==='pending' && this.tickN%3===0){ const st = await this.api('/redemptions/'+this.lastCode.code).catch(()=>null); if(st && st.status!=='pending'){ this.lastCode.status=st.status; } }
      }
    },

    async pollOffers(){
      const qs = this.location==='all' ? '' : ('?postcode='+encodeURIComponent(this.location));
      const data = await this.api('/businesses'+qs).catch(()=>null); if(!data) return;
      const fresh=[];
      data.forEach(b=>b.offers.forEach(o=>{ if(!this.seen.includes(o.id)){ if(this.matchesPrefs(b)){ fresh.push({biz:b.name, text:o.badge+' - '+o.title}); } this.seen.push(o.id); } }));
      this.businesses = data; if(this.tab==='map' && this.gm().map) this.renderMarkers();
      if(fresh.length){ this.save('gl_seen', this.seen); fresh.forEach(f=>{ this.notifications.unshift({id:Date.now()+Math.random(), biz:f.biz, text:f.text}); this.unread++; if(this.notifyOn && 'Notification' in window && Notification.permission==='granted'){ try{ new Notification('New offer at '+f.biz, {body:f.text, icon:'/icon.svg'}); }catch(e){} } }); this.notifications = this.notifications.slice(0,20); this.save('gl_notifs', this.notifications); }
      if(this.secret) this.loadBiz();
    },
    openNotifs(){ this.notifOpen=!this.notifOpen; this.unread=0; },

    // Map - Google Maps JavaScript API (AdvancedMarkerElement + clustering).
    // Build the styled pin element (coloured dot + name/category card) for a marker.
    pinEl(b){
      const esc=s=>String(s||'').replace(/</g,'&lt;');
      const off=b.offers&&b.offers[0];
      const badge = off ? '<span class="pin-badge">'+esc(off.badge)+'</span>' : '';
      const el=document.createElement('div');
      el.className='pin';
      el.innerHTML='<span class="pin-dot"></span>'
        +'<span class="pin-card">'
          +'<span class="pin-top"><span class="pin-name">'+esc(b.name)+'</span>'+badge+'</span>'
          +'<span class="pin-cat">'+esc(b.category||'')+'</span>'
        +'</span>';
      return el;
    },
    // Non-reactive holder for Google Maps objects. CRITICAL: map / markers /
    // clusterer must NOT live on `this` - Alpine wraps component state in a
    // reactive Proxy, and a proxied AdvancedMarkerElement never reaches the
    // native renderer (setting .map silently no-ops). Keep them off the proxy.
    gm(){ return window.__glmap || (window.__glmap = { markers: [] }); },
    // Load the Maps + Marker libraries once (async).
    async ensureMaps(){
      const g = this.gm();
      if(this.mapsReady && g.lib) return true;
      if(!this.mapsKey || !(window.google && google.maps && google.maps.importLibrary)) return false;
      try{
        const [maps, marker] = await Promise.all([
          google.maps.importLibrary('maps'),
          google.maps.importLibrary('marker'),
        ]);
        g.lib = { Map: maps.Map, InfoWindow: maps.InfoWindow, AdvancedMarkerElement: marker.AdvancedMarkerElement };
        this.mapsReady = true;
        return true;
      }catch(e){ console.error('Google Maps failed to load', e); return false; }
    },
    async initMap(n){
      n = n || 0;
      const el = document.getElementById('cmap');
      // Wait until the container is visible (has height) - the usual "blank map" cause.
      if(!el || el.clientHeight < 40){ if(n<80){ setTimeout(()=>this.initMap(n+1), 120); } return; }
      if(!await this.ensureMaps()) return;
      const g = this.gm();
      if(!g.map){
        g.map = new g.lib.Map(el, {
          center:{lat:54.9733, lng:-1.6122}, zoom:13, mapId:this.mapsId,
          disableDefaultUI:true, zoomControl:true, clickableIcons:false, gestureHandling:'greedy',
        });
        g.infoWin = new g.lib.InfoWindow();
        g.map.addListener('zoom_changed', ()=>this.updateMapLabels());
      }
      this.renderMarkers(); this.recenter();
    },
    recenter(){
      const g = this.gm();
      if(!g.map) return;
      const bounds = new google.maps.LatLngBounds();
      let any=false;
      this.visible.filter(b=>b.lat).forEach(b=>{ bounds.extend({lat:+b.lat, lng:+b.lng}); any=true; });
      if(this.userLat){ bounds.extend({lat:this.userLat, lng:this.userLng}); any=true; }
      if(any){ g.map.fitBounds(bounds, 50); google.maps.event.addListenerOnce(g.map,'idle',()=>{ if(g.map.getZoom()>15) g.map.setZoom(15); }); }
    },
    mapRefresh(){ if(this.gm().map){ this.renderMarkers(); this.recenter(); } },
    renderMarkers(){
      const g = this.gm();
      if(!g.map || !this.mapsReady) return;
      const esc=s=>String(s||'').replace(/</g,'&lt;');
      const AME = g.lib.AdvancedMarkerElement;
      // Clear previous markers.
      (g.markers||[]).forEach(m=>m.map=null);
      g.markers=[];
      if(g.userMarker){ g.userMarker.map=null; g.userMarker=null; }
      if(this.userLat){
        const u=document.createElement('div'); u.className='user-dot'; u.title='You';
        g.userMarker = new AME({ map:g.map, position:{lat:this.userLat, lng:this.userLng}, content:u });
      }
      this.visible.filter(b=>b.lat).forEach(b=>{
        const off=b.offers&&b.offers[0];
        const m = new AME({ position:{lat:+b.lat, lng:+b.lng}, content:this.pinEl(b) });
        m.content.addEventListener('click', ()=>{
          const pop=document.createElement('div'); pop.className='map-pop';
          pop.innerHTML='<div class="po-name">'+esc(b.name)+'</div><div class="po-sec">'+esc(b.category)+'</div>'+(off?'<div class="po-off">'+esc(off.badge)+' - '+esc(off.title)+'</div>':'')+'<button class="pv" type="button">View offers</button>';
          pop.querySelector('.pv').onclick=()=>{ g.infoWin.close(); this.openBusiness(b); };
          g.infoWin.setContent(pop); g.infoWin.open({ map:g.map, anchor:m });
        });
        g.markers.push(m);
      });
      // Clustered group: dense pins collapse into a count bubble, split as you zoom in.
      if(g.cluster){ g.cluster.clearMarkers(); g.cluster.addMarkers(g.markers); }
      else { g.cluster = new markerClusterer.MarkerClusterer({ map:g.map, markers:g.markers }); }
      this.updateMapLabels();
    },
    // Show the name/category cards only when zoomed in (≥14); zoomed out stays as clean dots + clusters.
    updateMapLabels(){ const g=this.gm(); if(!g.map) return; const el=document.getElementById('cmap'); if(el) el.classList.toggle('labels-on', (g.map.getZoom()||0)>=14); },
    // Small map inside a business profile.
    async initBizMap(b){
      if(!b || !b.lat) return;
      if(!await this.ensureMaps()) return;
      const g = this.gm();
      const AME = g.lib.AdvancedMarkerElement;
      this.$nextTick(()=>{ setTimeout(()=>{
        const el=document.getElementById('bizmap'); if(!el) return;
        if(!g.bizMap){ g.bizMap = new g.lib.Map(el, { center:{lat:+b.lat, lng:+b.lng}, zoom:15, mapId:this.mapsId, disableDefaultUI:true, gestureHandling:'none', clickableIcons:false }); }
        else { g.bizMap.setCenter({lat:+b.lat, lng:+b.lng}); }
        if(g.bizMarker) g.bizMarker.map=null;
        g.bizMarker = new AME({ map:g.bizMap, position:{lat:+b.lat, lng:+b.lng}, content:this.pinEl(b) });
      }, 90); });
    },
    // Haversine distance (miles) from the user's location; falls back to seed distance.
    dist(b){ if(this.userLat && b && b.lat){ const R=3958.8, rad=Math.PI/180; const dLat=(b.lat-this.userLat)*rad, dLng=(b.lng-this.userLng)*rad; const a=Math.sin(dLat/2)**2+Math.cos(this.userLat*rad)*Math.cos(b.lat*rad)*Math.sin(dLng/2)**2; return (R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a))).toFixed(1); } return b?.distance ?? '-'; },

    // Live Google Places search (debounced) - pulls the same data customers see.
    googleMock(){
      const q=(this.form.gSearch||'').trim();
      clearTimeout(this._gTimer);
      if(q.length<3){ this.gResults=[]; return; }
      this._gTimer=setTimeout(async()=>{ this.gResults = await this.api('/places/search?q='+encodeURIComponent(q)).catch(()=>[]); }, 350);
    },
    pickGoogle(g){ this.form.bName=g.name; this.form.bPostcode=g.postcode||''; this.form.placeId=g.place_id; this.form.gSearch=g.name; this.gResults=[]; },

    async signupBusiness(){
      this.bizError=''; if(!this.form.bName || !this.form.bCat){ this.bizError='Enter a name and category.'; return; }
      if(!this.form.bAgreeTerms){ this.bizError='Please agree to the Terms and Privacy Policy.'; return; }
      try{ const r = await this.api('/businesses', { method:'POST', body: JSON.stringify({ name:this.form.bName, category_id:Number(this.form.bCat), postcode:this.form.bPostcode, email:this.form.bEmail, place_id:this.form.placeId, terms_accepted:this.form.bAgreeTerms, marketing_opt_in:!!this.form.bMktOptIn }) });
        this.business = r.business; this.secret = r.owner_secret; this.save('gl_biz', this.business); this.save('gl_secret', this.secret);
        this.btab='offers'; await this.loadBiz(); await this.fetchBusinesses();
      }catch(e){ this.bizError=e.message; }
    },
    async loadBiz(){ this.bizOffers = await this.api('/businesses/secret/'+this.secret+'/offers').catch(()=>{ this.clearBiz(); return []; }); this.stats = await this.api('/businesses/secret/'+this.secret+'/redemptions').catch(()=>this.stats); },
    clearBiz(){ this.business=null; this.secret=null; localStorage.removeItem('gl_biz'); localStorage.removeItem('gl_secret'); },
    async createOffer(){ if(!this.form.oTitle){ alert('Enter an offer title'); return; } try{ await this.api('/businesses/secret/'+this.secret+'/offers', { method:'POST', body: JSON.stringify({ title:this.form.oTitle, badge:this.form.oBadge, terms:this.form.oTerms, sale_type:this.form.oSale, quantity:this.form.oSale==='limited'?this.form.oQty:null }) }); this.form.oTitle=''; this.form.oBadge=''; this.form.oTerms=''; this.form.oSale='ongoing'; this.form.oQty=20; await this.loadBiz(); await this.fetchBusinesses(); }catch(e){ alert(e.message); } },
    async deleteOffer(id){ await this.api('/businesses/secret/'+this.secret+'/offers/'+id, { method:'DELETE' }).catch(()=>{}); await this.loadBiz(); await this.fetchBusinesses(); },
    async verifyCode(){ const code=(this.form.verify||'').trim(); try{ const r = await this.api('/redemptions/verify', { method:'POST', body: JSON.stringify({ secret:this.secret, code }) }); this.verifyOk=r.ok; this.verifyMsg=r.message + (r.offer?(' ('+r.offer+')'):''); if(r.ok) this.form.verify=''; await this.loadBiz(); }catch(e){ this.verifyOk=false; this.verifyMsg=e.message; } },

    resetAll(){ if(confirm('Reset the demo identity? (Businesses & offers stay in the database.)')){ ['gl_customer','gl_biz','gl_secret','gl_wallet','gl_favs','gl_notifs','gl_seen'].forEach(k=>localStorage.removeItem(k)); location.href='/'; } },
  }
}

// Mouse drag-to-scroll inside the phone screens (grab & drag like a touchscreen).
(function(){
  let sc=null, startY=0, startTop=0, moved=false;
  document.addEventListener('pointerdown', e=>{
    if(e.pointerType==='touch') return; // native touch already scrolls
    const el = e.target.closest && e.target.closest('.scr');
    if(!el || el.scrollHeight<=el.clientHeight) return;
    sc=el; startY=e.clientY; startTop=el.scrollTop; moved=false;
  });
  document.addEventListener('pointermove', e=>{
    if(!sc) return;
    const dy=e.clientY-startY; if(Math.abs(dy)>4) moved=true;
    sc.scrollTop=startTop-dy;
  });
  document.addEventListener('pointerup', ()=>{
    if(sc && moved){ const blk=ev=>{ ev.stopPropagation(); ev.preventDefault(); document.removeEventListener('click', blk, true); }; document.addEventListener('click', blk, true); }
    sc=null;
  });
})();
</script>
@endsection
