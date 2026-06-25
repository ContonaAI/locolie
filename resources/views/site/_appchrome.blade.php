{{-- Shared "real app" chrome for the marketing demos (_appwalk, _phone) so they
     mirror the actual app exactly: the same map-pin wordmark, the same dark
     header layout, and the same rounded bottom tab bar. Include once per page;
     styles are pushed @once. The pin glyph + tab icons are copied verbatim from
     the live app (portal/home.blade.php). --}}
@once
@push('head')
<style>
  /* Wordmark: "L" + map-pin (first o) + "colie", exactly like the app's markFor(). */
  .dm-wm { font-weight: 800; letter-spacing: -.04em; display: inline-flex; align-items: center; line-height: 1; color: #fff; }
  .dm-wm .dm-pin { height: .64em; width: auto; display: inline-block; vertical-align: -.02em; margin: 0 -.012em; fill: #10b981; filter: drop-shadow(0 2px 6px rgba(5,150,105,.5)); }

  /* Header - mirrors .app-header / .app-header-row. */
  .dm-head { background: #0a0a0a; padding: 11px 14px 13px; }
  .dm-head-row { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 10px; min-height: 34px; }
  .dm-loc { display: inline-flex; align-items: center; gap: 3px; color: rgba(255,255,255,.72); font-size: 11px; font-weight: 600; justify-self: start; min-width: 0; }
  .dm-loc strong { color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .dm-loc svg { height: 13px; width: 13px; }
  .dm-loc .dm-loc-pin { color: #10b981; }
  .dm-head-bell { justify-self: end; color: rgba(255,255,255,.7); }
  .dm-search { margin-top: 11px; display: flex; gap: 8px; }
  .dm-search-input { flex: 1; display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,.12); color: rgba(255,255,255,.6); border-radius: 11px; padding: 9px 12px; font-size: 11px; }
  .dm-search-btn { width: 36px; display: grid; place-items: center; background: #059669; color: #fff; border-radius: 11px; }

  /* Bottom tab bar - mirrors .tabbar / .tab. */
  .dm-tabbar { display: flex; gap: 2px; margin: 8px 12px; padding: 6px 6px; border-radius: 24px; background: rgba(255,255,255,.85); -webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px); border: 1px solid rgba(15,23,42,.07); box-shadow: 0 2px 6px rgba(15,23,42,.05), 0 14px 34px rgba(15,23,42,.16), inset 0 1px 0 rgba(255,255,255,.6); }
  .dm-tab { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 2px; border-radius: 15px; color: rgba(10,10,10,.4); font-size: 8.5px; font-weight: 600; letter-spacing: -.01em; transition: color .3s ease, background-color .3s ease; }
  .dm-tab svg { height: 19px; width: 19px; transition: transform .3s cubic-bezier(.2,.8,.3,1.4); }
  .dm-tab.on { color: #059669; background: rgba(5,150,105,.12); }
  .dm-tab.on svg { transform: translateY(-1px) scale(1.12); }
</style>
@endpush
@endonce
