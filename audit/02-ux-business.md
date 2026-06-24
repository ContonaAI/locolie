# UX audit: business-facing surfaces (CRM + team portal + Messaging Studio)

Scope: surface #3 (business self-serve CRM), #6 (team portal), #7 (Messaging
Studio). Grounded in the real Blade views, `BusinessPortalController`, routes
(`routes/web.php` lines 47-130) and the maps/benchmarks in this folder
(`00-map-business.md`, `00-map-portal.md`, `01-bench-services.md`). House style:
hyphens only, no em/en-dashes.

Benchmarks referenced: Thumbtack (guided onboarding, profile-completion,
predictable outcomes per tier), Checkatrade (plain-spoken honesty about what is
and is not verified/measured), Yelp (status/freshness cues), TaskRabbit (earned
status framing).

---

## Owner journey walkthrough (login -> dashboard -> messaging -> reports -> upgrade)

1. Land on `/business/login` (`business.login.blade.php`). Clean. But "New
   here? List your business" links to the marketing page, not a signup. A real
   owner who clicks expecting to register hits a dead end (account creation is
   admin-only via `admin.prospect.add` / manual). No forgot-password either.
2. The demo button prints live creds in plain HTML: `demo@locolie.test ·
   golocal` (login.blade lines 36-42). Fine in a prototype, a trust/leak risk at
   launch.
3. Dashboard (`dashboard.blade.php`): four KPI tiles, a listing form, a
   read-only offers list, the customers table, and a plan switcher. The offers
   panel and "Manage in app" punt every offer action to `/app` - you cannot
   create, edit, pause or delete an offer in the web CRM at all. That is the
   single biggest "where do I actually run my shop" gap.
4. "Quick email" (dashboard) and the full Messaging Studio are two separate
   email entry points. The quick-email posts to `business.customers.email`
   (`emailCustomers`) which only writes a `Campaign` row and flashes "Email
   queued to N opted-in customers" - it dispatches nothing (no `Mail::`, no
   queue; controller lines 117-135). It lies about sending.
5. Messaging Studio (`messaging.blade.php`) is polished, with a live preview and
   a Connected/Demo badge - good. Reports (`reports.blade.php`) is the most
   finished surface and is honest about estimates. Upgrade switches plan
   instantly with no payment (free-at-launch) and the Stripe return path is
   unverified.

## Internal-team journey (portal home -> admin -> messaging studio -> reports -> settings)

The team nav (`portal/layout.blade.php` lines 35-51) lists Home, Business Plan,
Design, Admin, Messaging, Reports, Settings, Mockups, Ideas. "Home" is actually
the full app prototype, which is confusing labelling. Admin is the real CRM;
its Campaigns tab is a legacy duplicate of the Messaging Studio. Settings openly
states the auth gate is OFF.

---

## P0 - must fix before any real launch / charging

### P0-1. Billing can be bypassed for free (integrity hole)
`upgradeSuccess()` (controller lines 345-352) is a plain GET that trusts
`?plan=` and calls `applyPlan` with no Stripe session/webhook verification. A
logged-in owner can hit `/business/upgrade/success?plan=premium` directly and
self-upgrade even with Stripe configured. Separately, when Stripe is not
configured `upgrade()` falls straight through to `applyPlan` so anyone clicks to
Premium for free. There is no webhook handler anywhere.
Fix: verify the Stripe Checkout session server-side in `upgradeSuccess` (confirm
`payment_status`/session id belongs to this business) and add a
`checkout.session.completed` webhook as the source of truth. Until billing is
live, hide paid CTAs behind a "coming soon" state rather than silently granting
the tier.

### P0-2. Team portal admin + send endpoints are unauthenticated
`settings.blade.php` lines 75-78 admit it: "this portal is currently public ...
anyone can reach these admin pages." The `portal` middleware is a pass-through.
That exposes `admin.onboard`, `admin.plan`, `admin.prospect.add`,
`admin.campaign` and every `messaging.*` send/connect route to the public web.
`robots.txt` Disallow is not access control.
Fix: re-enable the shared-password gate (or proper auth) before this is
reachable in production. This is the highest-severity item across both surfaces.

### P0-3. Dashboard "Quick email" claims to send but does not
`emailCustomers` only inserts a `Campaign` row and flashes "Email queued to N
opted-in customers" (controller line 134). No delivery. An owner believes they
emailed their customers; nothing left the building. This is worse than a missing
feature because it gives false confidence and inflates the "messages sent" stat
the Studio and reports read back.
Fix: either route this form through `MessagingService::dispatch` (real send /
honest demo-mode label) or remove it and point the "Quick email" button at
`business.messaging`. Do not ship copy that says "queued/sent" when nothing is.

## P1 - high: friction, dead ends, trust

### P1-1. No offer management in the web CRM
The dashboard offers panel is read-only; create/edit/pause/delete all live in
`/app?as=business` (dashboard lines 54-72). Offers are the core unit of the
product, yet a desktop owner cannot touch them. Benchmark: Thumbtack/Checkatrade
let pros manage their core listing/services from the dashboard. At minimum add
create + pause/end-offer here.

### P1-2. Category is validated but has no field (dead form path)
`updateListing` validates and saves `category_id` (controller line 315) but the
dashboard listing form (lines 33-50) has only description/phone/website - no
category picker. So the rule can never fire and an owner cannot fix a wrong
category. Either add the picker or drop the dead rule. Ties to bench #15 (full
listing ranks/converts better).

### P1-3. No profile-completion guidance, no onboarding nudge
The dashboard drops a bare description/phone/website form with no sense of "what
makes a good listing" - no photo, hours, or completion meter. Benchmark:
Thumbtack's guided onboarding + completeness, Yelp's "complete your profile to
get more messages." Add a completion checklist card (photo, description, phone,
website, first offer) with a % bar; gate the "View in app" share prominence on
completion to drive quality listings.

### P1-4. Plan perks are abstract; no payment reality
Plan cards show `$p['perks']` blurbs but every switch is instant and free, and
copy says "Free at launch - paid tiers preview the upgrade path." A free
"Downgrade to Free" / "Switch to Premium" button that does nothing financial
trains owners that tiers are meaningless. Benchmark (Thumbtack predictability):
state each tier's outcome in concrete units ("Featured = top of your category
grid + 1 monthly email blast") and clearly mark paid tiers as preview/coming
soon until billing is wired (see P0-1).

### P1-5. "Your own customer list" pitch undercut by platform-wide push
Dashboard and reports repeatedly sell "your own list the big chains have" yet
the push audience count is `PushSubscription::count() + DeviceToken::count()`
(controller line 165) - the whole platform, not this brand. The reports footnote
admits it ("across the whole locolie platform, not only your own list",
reports.blade line 181) but the messaging tab still shows that inflated number
next to "Push" with no caveat. A brand could broadcast a push to the entire
platform thinking it is their list. Either scope push to the brand's own
device tokens or label the Push tab clearly as a platform broadcast (and gate
who may use it).

### P1-6. Non-Twilio SMS + native push report success while sending nothing
Per `00-map-portal.md`: five of six SMS providers and both iOS/Android push are
stubbed - they log and optimistically report `sent`. In the retailer Studio the
Connected/Demo badge is driven by `overview[channel]['connected']`, so a brand
on, say, Vonage with real keys would see "Connected" and "SMS sent to N
recipients" with zero delivery. This is the same false-confidence class as
P0-3, scoped to channels. Fix: a connected-but-not-deliverable provider must
surface "logged only, delivery not wired" rather than "sent".

### P1-7. Branded emails ship a dead unsubscribe link (compliance)
`emails/branded.blade.php` hardcodes `<a href="#">Unsubscribe</a>` while the
correct UK GDPR/PECR-compliant `emails/partials/footer.blade.php` (real signed
`Subscription::unsubscribeUrl/preferencesUrl` + List-Unsubscribe headers) sits
unused. Any real email send is non-compliant. Fix: include the footer partial in
`branded.blade.php` and delete the hardcoded footer. The on-screen preview
(email.blade line 250) also shows a fake `<span class="underline">Unsubscribe
</span>` - wire it to the real link so preview == delivery.

### P1-8. Google "Connect" is cosmetic (callback never exchanges the token)
`googleCallback` records a stubbed connection storing a truncated code and never
exchanges it for a refresh token, while `EmailChannel` only treats Gmail as
deliverable when `gmail_refresh_token` exists (which this flow never sets). So
"Connected to Google" on the email studio is a lie - it will silently fall back
to demo on send. Either finish the OAuth exchange or relabel the button
"Connect (coming soon)".

## P2 - polish, consistency, smaller trust items

### P2-1. CRM and portal use CDN Tailwind/Alpine, not the Vite build
`business/layout.blade.php` lines 7-9 and `portal/layout.blade.php` lines 12-13
load `cdn.tailwindcss.com` (the "not for production" play CDN) plus CDN Alpine
and Google Fonts, diverging from the project's compiled `app.css`/`app.js`
Tailwind v4 pipeline used on surfaces 1/2. Breaks offline, slower, inconsistent.
Move both to the shared Vite build.

### P2-2. Brand/filename drift (locolie vs golocal)
Customer export filename is `golocal-customers.csv` (controller line 112) while
the report export is `locolie-report.csv` (line 264). The demo login uses
`golocal` as the password and `demo@locolie.test` as the email. Pick one brand
string everywhere; "golocal" is internal-only and leaks the old name to owners
who open the CSV.

### P2-3. Literal placeholder glyphs in tables
Dashboard renders a bare `' - '` in the marketing cell for not-opted-in
customers (line 112) and the customer name defaults to a literal `'—'`
em-dash-style glyph in the controller (`customersFor`, line 92 - violates house
style). Admin tables also print `' - '` for empty category/offer/business cells
(admin.blade lines 71, 165, 188-189). Use a muted "Not opted in" / "Unknown"
label instead of a dash, and replace the `'—'` with a hyphen or a real empty
state.

### P2-4. Reports CSV ignores the selected range
`reportsExport` hardcodes a 90-day window (`forBusiness($business, 90)`,
controller line 255) regardless of the 7/14/30/90 control the owner just used on
screen. Export should honour the visible range, or the button should say "Export
(90 days)".

### P2-5. Admin Campaigns tab is a legacy duplicate that mostly does not send
`admin.campaign` (`adminSendCampaign`) is a second, older send path beside the
Messaging Studio. Its email "send" is literally a recipient count (no delivery),
push goes via `PushService::broadcast`. The view copy concedes "Delivery wiring
is scaffolded - see notes." Two send UIs for the team is confusing and the email
one fibs. Fold this into the Messaging Studio or clearly mark it deprecated.

### P2-6. Empty template picker
The email studio "Start from a template" select is guarded by
`$templates->isNotEmpty()`, but nothing anywhere creates a `MessageTemplate`
(no `MessageTemplate::create`, no seeder). So the picker never renders - dead
feature. Either seed a few starter templates or remove the picker until there is
a create path.

### P2-7. Messaging preview always first-paints email
`messaging.blade.php` line 122 hardcodes the email preview; switching to SMS/
push re-fetches via AJAX, but the first paint is always the email mockup even if
a user expects parity. Minor, but render the matching channel on load.

### P2-8. "Home" label undersells/duplicates the app
Team nav labels the heaviest page (the full 119KB app prototype) just "Home",
overlapping conceptually with `/app`. Rename to "App preview" or similar so the
team knows what it is.

### P2-9. Untracked report view
`portal/reports.blade.php` is uncommitted (`??` in git status). Commit it so the
team Reports page is not lost.

---

## Quick wins (cheap, high trust payoff)
- Remove or fix the lying "Quick email" (P0-3) - one route swap.
- Include the compliant unsubscribe footer in `branded.blade.php` (P1-7).
- Add a profile-completion card to the dashboard (P1-3) - pure Blade, reuses
  existing fields, lands the Thumbtack/Yelp completeness benchmark.
- Relabel stubbed channels/Google "demo / not wired" instead of "Connected/sent"
  (P1-6, P1-8) - honesty over false confidence, the Checkatrade lesson.
- Fix filename/glyph drift (P2-2, P2-3).
