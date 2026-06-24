# locolie - Audit implementation changelog

Running log of changes made during the audit-driven overhaul. Newest first.
House style: hyphens only, no em/en-dashes.

## Session 2026-06-24 (overnight pass)

Scope: safe Wave-0 fixes (parallel) + the keystone Vite/design-system switch (Wave 1),
with local build + Playwright verification. Deferred items that need Tom's decision
(billing P0-5, retailer auth model, self-registration P2-13, native APNs P2-10) and the
behaviour-changing core-flow work (redeem->report email gate P0-2, oversell race P0-8).

### Done and verified

**Security and integrity (Wave 0)**
- P0-1 Re-enabled the team-portal password gate. `app/Http/Middleware/PortalAuth.php` no longer auto-authorises everyone; it redirects unauthenticated visitors to the portal login. Verified: GET /portal now returns 302 (was rendering admin pages to anyone). Prod note: PORTAL_PASSWORD is set to `GoLocal` in the Deploy.dev env, so the team can still log in.
- P0-4 Internal team navigation (Business Plan / Brand / Admin / Mockups / Ideas links) is now wrapped in `@unless($solo)` in `resources/views/portal/home.blade.php`, so it is never emitted in the consumer/business `/app`. Previously it was only CSS-hidden, leaking admin URLs into the consumer HTML source.
- P0-6 "Quick email" no longer claims to send. `BusinessPortalController::emailCustomers` now persists a `draft` Campaign (sent_count 0) and the flash says "Draft saved. Head to Messaging to send...". Dashboard button relabelled "Draft email" / "Save draft" with a helper line linking to the real Messaging Studio send path. (`app/Http/Controllers/BusinessPortalController.php`, `resources/views/business/dashboard.blade.php`)
- P0-7 Rate limiting added. `routes/api.php` wrapped in `throttle:api` (60/min/IP) with `throttle:20,1` on places-search, register, redeem and verify; both login POST routes (`/business/login`, `/login`) get `throttle:5,1`. Added the missing `api` limiter to `AppServiceProvider::boot()`. Verified via `php artisan route:list`.
- P0-10 GoLocal brand leaks fixed. Printed window sticker (`demo/sticker.blade.php`) now renders the locolie wordmark in ink + brand green (was GoLocal in orange #D9603B/#FFA41C); portal login title is "locolie Portal" (was "GoLocal Portal").

**Performance and infrastructure (Wave 1 keystone)**
- P0-9 + P1-1 (foundation) Killed the runtime Tailwind CDN on the three user-facing layouts (`site/layout`, `business/layout`, `customer/layout`; legal pages inherit via `@extends('site.layout')`) and switched them to the compiled Vite build `@vite([...])`. This removes ~400KB of render-blocking CDN JS per page and the "not for production" console warning, and is required for the Capacitor app to style offline.
- Rewrote `resources/css/app.css` with the full unified design-token `@theme` (brand 50-900, warm sand neutrals, semantic colours, type/radius/shadow scales) from `audit/03-design-system.md`, PLUS back-compat aliases (`emerald`, `emerald-soft`, `hair`, `rounded-card`, Inter font) so the CDN-to-build switch is visually faithful. Added a base-layer focus ring and warm sand body background.
- Verified: `npm run build` clean (104KB CSS, manifest written); all four switched surfaces (home, for-business, business/login, my-locolie) return HTTP 200 with zero CDN-Tailwind refs and zero browser console errors; Playwright screenshots confirm full styling and no regression; custom utility classes (text-emerald, bg-emerald-soft, rounded-card, border-hair) and new tokens (bg-brand-600, bg-sand-50) all present in the compiled CSS.

**Data and compliance**
- P1-17 New MySQL-safe additive migration `database/migrations/2026_06_24_000006_add_hot_indexes.php` (Schema::hasColumn-guarded, explicitly named) indexing the hot filter/sort columns: businesses.status/onboarded/featured/priority/plan, offers.status, redemptions.customer_email/customer_phone/status. Verified: ran cleanly on local MySQL (389ms) and indexes confirmed via SHOW INDEX.
- P1-14 Branded email now includes the compliant signed unsubscribe footer (`@include('emails.partials.footer')`) instead of the dead `<a href="#">Unsubscribe</a>`. The partial falls back safely to the static preferences route when no per-recipient email is passed.

**Cleanup**
- P2-14 Removed plaintext demo credentials from `business/login.blade.php` (verified gone in screenshot).
- P2-22 Default brand monogram initials changed `GL` -> `LO` in branded email + email/push previews.
- P2-24 Em-dash house-style sweep: searched all of `app/`, `resources/`, `routes/`, `config/` for U+2014/U+2013. None found; codebase already clean.

### Deferred (need Tom's decision or attended build/verify)
- P0-2 Reconnect redeem -> "Your locolie" report (gate redemption on a real email + opt-in). Core flow, behaviour-changing - do attended.
- P0-5 Close the free billing self-upgrade hole (decide billing model first: real Stripe vs "free at launch").
- P0-8 Oversell race on limited offers (hot path, atomic update - do attended).
- P0-3 Point native app at locolie.com instead of the ngrok tunnel (needs iOS rebuild + `npx cap sync`).
- Retailer auth model (owner_secret in URLs), business self-registration (P2-13), native APNs push (P2-10), and the full visual re-skin sweep (slate->sand, one wordmark/button component) - the design re-skin was gated for review in the plan.

### IMPORTANT - to deploy these changes
The server does NOT build assets (Node too old for Vite 8), so the Vite switch only works in prod if the compiled assets are committed:
1. `npm run build` (already done locally)
2. `git add -f public/build` (it is gitignored; the new CSS hash app-CjemI9wi.css must be force-added or prod ships with no styles)
3. commit + push, then on the server run `php artisan migrate --force` (for the new index migration). The deploy hook does not auto-migrate.
</content>
</invoke>
