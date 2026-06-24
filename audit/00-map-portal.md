# Audit: Internal Team Portal (#6) + Messaging Studio (#7)

Scope: every `portal/*` and `portal/messaging/*` Blade view, `PortalController`,
`MessagingController`, the three `Messaging/*StudioController`s, the messaging
service layer, `emails/branded` + previews. Routes in `routes/web.php` lines
76-130, all behind `Route::middleware('portal')`.

House style note: no em/en-dashes; hyphens only.

---

## Gate & shell

- Auth is a single shared-password gate (`PortalController::login`,
  `config('portal.password')`, session flag `portal_authed`). Routes:
  `portal.login` (`/login`), `portal.login.submit`, `portal.logout`.
- BUT the `settings.blade.php` page explicitly warns the gate is currently
  OFF: "Heads up: this portal is currently public ... The shared-password gate
  is disabled, so anyone can reach these admin pages." So the `portal`
  middleware is effectively a pass-through right now. This is the single
  biggest risk in this surface: live admin CRM, prospecting, and message-send
  endpoints are reachable unauthenticated. `robots.txt` (web.php:27) does
  `Disallow: /portal /admin /business` but that is not access control.
- Layout: `resources/views/portal/layout.blade.php`. Uses CDN Tailwind
  (`cdn.tailwindcss.com`) + CDN Alpine + Google Fonts, NOT the Vite build that
  the rest of the app uses. So the portal is styled independently of the
  shipped `app.css`/Tailwind v4 pipeline. Nav is hardcoded in the layout
  (`$primary`, `$designChildren`, `$trailing`).

---

## Portal pages (#6)

### `/portal` -> `portal.home` (PortalController@home) - view `portal/home.blade.php`
- Purpose: NOT a dashboard. It is the full interactive consumer+business APP
  PROTOTYPE embedded in the portal (119KB Blade, ~50 `x-show` screens,
  `x-data="goLocalApp()"`). Same view is served at `/app`
  (PortalController@mobile, `solo` mode) for mobile/iOS testing.
- State: live-ish. Its JS calls the real JSON API: `fetch('/api'+path, ...)`
  (home.blade.php:928) for browse/redeem, and references device-token
  registration to `/api/devices/register` (line ~1014, Capacitor hook).
- Connections: shares the exact Capacitor web build surface. Controller passes
  `mapsKey`, `mapsId`, `vapidKey`, `mockupCount`.
- Flag: this is the heaviest, most "real" page in the portal yet it is labelled
  just "Home" in nav, which undersells it and overlaps conceptually with `/app`.
  The only difference is `solo`/`soloRole` flags. Mild duplication of intent.

### `/business-plan` -> `portal.plan` - view `portal/business-plan.blade.php`
- Renders `resources/content/business-plan.md` as HTML (Str::markdown). Static
  doc viewer. Falls back to "Business plan not found." if the md is missing.

### `/brand` -> `portal.brand` - view `portal/brand.blade.php` (25KB)
- Logo concepts, name exploration, style directions (the `#styles` anchor in
  the design subnav points here). Static design exploration page.

### `/design` -> `portal.design` - view `portal/design.blade.php`
- Thin wrapper: an `<iframe>` pointing at `portal.design.raw`
  (`/design/raw`), which serves `resources/content/app-design.html` raw
  (PortalController@designRaw). Static design HTML in an iframe.

### `/mockups` -> `portal.mockups` - view `portal/mockups.blade.php`
- Upload + gallery of mockup images stored on the `public` disk under
  `mockups/`. POST `portal.mockups.upload`. Functional.

### `/ideas` -> `portal.ideas` - view `portal/ideas.blade.php`
- CRUD ideas board backed by the `Idea` model (store + delete routes).
  Functional, simple.

### `/admin` -> `portal.admin` (PortalController@admin) - view `portal/admin.blade.php`
- Purpose: the real CRM. Alpine tabs: Businesses, Prospecting, Campaigns,
  Redemptions. KPIs from live counts (businesses, onboarded, leads, paid,
  offers, redemptions, push_subs, plan counts).
- Wired actions:
  - `admin.onboard` toggles `onboarded`/`claimed_at`.
  - `admin.plan` sets paid tier (priority+featured follow `Business::PLANS`).
  - `admin.prospect.search` -> `PlacesService::search` (live Google Maps),
    rendered via the `prospector()` Alpine fetch (needs the csrf meta tag in
    `@push('head')`).
  - `admin.prospect.add` -> `PlacesService::details`, creates a Business as a
    lead (`onboarded=false`).
- Campaigns tab: `admin.campaign` (PortalController@adminSendCampaign). This is
  an OLDER, separate send path from the Messaging Studio. It only supports
  channel `email|push`; email "send" is literally just a recipient COUNT
  (`Business::where('onboarded',true)->whereNotNull('owner_email')->count()`,
  controller line 206) - it does not send anything. Push goes through
  `PushService::broadcast`. The view copy admits it: "Delivery wiring is
  scaffolded - see notes." DUPLICATE/legacy vs the Messaging Studio email/push
  studios, which are the real send paths.

### `/admin/settings` -> `portal.settings` (PortalController@settings) - view `portal/settings.blade.php`
- Data-sync config page: shows whether `config('sync.token')` is set (masked),
  the `/api/sync` endpoint, `Cache::get('sync.last_at')`, and counts
  (businesses/offers/categories/images on `public` disk). Documents
  `php artisan sync:push` (and `--skip-images`). Contains the public-gate
  warning banner noted above. Read-only config display, no form to set the
  token (token lives in env).

### `/reports` -> `portal.reports` (ReportsController@platform) - view `portal/reports.blade.php`
- NOTE: `portal/reports.blade.php` is currently UNTRACKED in git (shows in
  `git status` as `??`), i.e. newly added and not yet committed.
- Purpose: platform-wide marketplace reporting. `ReportingService::platform($days)`
  with day toggle (7/14/30/90). KPIs: onboarded businesses, redemptions,
  shoppers reached, savings delivered (est.), messages sent, avg/business.
- Redemptions trend via `@include('reports._trend', ...)`. Messaging "reach"
  cards per channel with EST opens/clicks - the view itself states: "Engagement
  figures are indicative industry benchmarks until live open/click tracking is
  connected." So engagement is mocked, send counts are real
  (Campaign.sent_count).
- Links out to the shopper-facing `customer.report.entry` ("Your locolie").

---

## Messaging Studio (#7)

Sub-nav partial `portal/messaging/_nav.blade.php` (tabs: Overview/Email/SMS/Push).
Service layer: `App\Services\Messaging\{MessagingService, BaseChannel,
EmailChannel, SmsChannel, PushChannel, SendResult}`, catalogue in
`config/messaging.php`. Models: `Campaign` (send log), `MessagingChannel`
(connection rows), `MessageTemplate`, `DeviceToken`, `PushSubscription`.

Core design pattern (consistent everywhere): "demo-able now, live when keys
added". `MessagingService::dispatch()` always logs a `Campaign` row regardless
of demo/live. `connect()` writes a `MessagingChannel` row with status
`connected` even for a "demo connect" with no real keys - the UI flips to a
connected look, but each Channel's `connected()` re-checks for REAL keys before
delivering. Good separation: UI-connected != deliverable.

### `/messaging` -> `messaging.studio` (MessagingController@studio) - `studio.blade.php`
- Overview: 4 headline stats (email/sms/push audience, all-time sent), per-channel
  connection cards with inline Connect/Disconnect forms (`messaging.connect` /
  `messaging.disconnect`), a per-brand identity editor (`messaging.brand`),
  and a recent-messages feed (last 12 Campaigns).
- Brand identity form saves `brand_color, email_from_name, reply_to_email,
  sms_sender_id, logo` onto the `Business` row (MessagingController@saveBrand).
  These feed every channel's `previewData()` via Business helpers
  (`brandColor()`, `logoUrl()`, `brandInitials()`, `emailFromName()`,
  `smsSenderId()`).

### `/messaging/email` -> `messaging.email` (EmailStudioController) - `email.blade.php`
- WIRED for real. Left: compose form (brand selector, template picker, subject,
  preheader, body, CTA), a separate test-send card, and a provider panel.
  Right: live Alpine inbox mockup that mirrors `messaging/previews/email`.
- Routes: index, preview (server render of `messaging.previews.email`),
  test, send, connect/google, google/callback.
- Delivery: `EmailChannel::deliverable()` is true if Gmail refresh token OR a
  Resend key OR a non-log/array default mailer exists; otherwise demo (logs
  "[email] would send" + returns `SendResult::demo`). Live path sends the
  `BrandedCampaign` mailable (renders `emails/branded.blade.php`) - real and
  complete.
- Google OAuth: `connectGoogle()` redirects to the real Google consent screen
  for `gmail.send` IF `services.google.gmail_client_id` is set; otherwise it
  demo-connects. `googleCallback()` does NOT actually exchange the code for a
  token - it just records a (stubbed) connection storing a truncated code.
  STUBBED: there is no token exchange, so "Connected to Google" via the live
  branch is cosmetic until a real exchange + refresh-token storage is added.
  EmailChannel only treats Gmail as deliverable when
  `services.google.gmail_refresh_token` is present, which this flow never sets.
- Audience: brand-scoped = opted-in (`marketing_opt_in`) redemption customer
  emails; platform = all onboarded business owner emails.
- Templates: `MessageTemplate` is READ-ONLY here. No UI or seeder anywhere
  creates a MessageTemplate (grep: zero `MessageTemplate::create`). So the
  "Start from a template" picker is dead in practice (always empty). Half-built.

### `/messaging/sms` -> `messaging.sms` (SmsStudioController) - `sms.blade.php`
- PARTIALLY wired. Compose form + live Alpine phone mockup (mirrors
  `messaging/previews/sms`), provider panel with per-provider "keys ready" vs
  "demo" badges from `SmsChannel::readiness()`.
- Routes: index, preview, test, send.
- Six providers in config: twilio, vonage, messagebird, plivo, aws_sns,
  clicksend. ONLY TWILIO has real HTTP delivery (`sendViaTwilio` hits the
  Messages REST API). The other five call `stubProvider()`, which logs and
  optimistically reports as `sent` with the note "<label> live-send not yet
  wired." So a connected non-Twilio provider with real keys reports success but
  sends nothing. STUBBED x5.
- `connected()`/`activeProvider()` only return live when env creds for a
  provider are actually present (`providerReady`), so demo is the default.

### `/messaging/push` -> `messaging.push` (PushStudioController) - `push.blade.php`
- PARTIALLY wired. Compose form (title/body/CTA/deep-link), audience breakdown
  (web/iOS/Android from `PushChannel::audienceBreakdown()`), provider panel,
  and a live preview using `messaging/previews/push` (three mockups: iOS lock
  screen, Android heads-up, web toast) driven by the inline `pushStudio()`
  Alpine that swaps text/colour/logo via `data-push="*"` hooks.
- Routes: index, preview, test, send. "Send test" and "Send broadcast" both
  hit current subscribers (push has no single address); test reuses the send
  path.
- Web push is REAL: `PushChannel` delegates to `PushService::broadcast`, which
  is live when `services.vapid.public` is set AND `Minishlink\WebPush\WebPush`
  exists. iOS (APNs) and Android (FCM) are STUBBED: `sendApns`/`sendFcm` only
  log + count even when creds are present ("Stubbed for now; structured for
  drop-in"). So push is genuinely deliverable on web only today.

### Shared previews (`resources/views/messaging/previews/*`)
- `email.blade.php`, `sms.blade.php`, `push.blade.php` are standalone (no
  layout) partials, explicitly designed to be `@include`d from BOTH the portal
  studio AND the retailer business dashboard (#3, BusinessPortalController
  routes `/business/messaging` etc.). Confirmed reuse, not duplication. The
  studio email/sms pages also re-implement the same mockup inline in Alpine for
  the live-as-you-type preview, mirroring these partials.

### Email templates: `emails/branded.blade.php` + `emails/partials/footer.blade.php`
- `branded.blade.php`: solid table-based responsive HTML email, brand band,
  body, optional CTA, footer. Driven by the same `$preview` array as the
  on-screen mockup (so preview == delivery).
- ORPHAN: `emails/partials/footer.blade.php` is a proper UK GDPR/PECR/CAN-SPAM
  compliant footer with REAL signed Manage-preferences / Unsubscribe links
  (`Subscription::unsubscribeUrl/preferencesUrl`) and documents the
  List-Unsubscribe headers. But `branded.blade.php` does NOT include it - it
  hardcodes its own footer with a dead `<a href="#">Unsubscribe</a>`. So the
  real emails currently ship a non-functional unsubscribe link while a correct,
  compliant partial sits unused. Compliance gap + orphaned file.

---

## Wired vs stubbed summary (channels)

- Email: REAL send (BrandedCampaign mailable) when a mailer/Resend key exists.
  Google OAuth callback STUBBED (no token exchange). Dead unsubscribe link.
- SMS: REAL only via Twilio. Vonage/MessageBird/Plivo/AWS SNS/ClickSend STUBBED
  (log + falsely report sent).
- Push: REAL on web (VAPID + Minishlink/WebPush). iOS/APNs + Android/FCM STUBBED
  (log + count only).

## Orphaned / duplicated / half-built

1. Public portal gate (settings warns it is disabled) - admin/send endpoints
   are effectively open.
2. `portal/reports.blade.php` is untracked in git (uncommitted).
3. Admin "Campaigns" tab (`admin.campaign`) is a legacy send path that
   duplicates the Messaging Studio; its email send only counts recipients and
   sends nothing.
4. `MessageTemplate` picker (email studio) has no create path anywhere - always
   empty.
5. `emails/partials/footer.blade.php` (compliant unsubscribe) is unused;
   `branded.blade.php` ships a dead `#` unsubscribe link instead.
6. Five of six SMS providers + both native push surfaces are stubbed (report
   success without delivering).
7. Portal uses CDN Tailwind/Alpine, diverging from the app's Vite build.
8. `portal.home` doubles as the full app prototype, overlapping `/app`.
