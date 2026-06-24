# Business self-serve CRM (surface #3) - map & state

Auth guard `business` (config/auth.php line 47, provider `businesses`). Login is
email + password against `Business.owner_email` / hashed `password`
(`password` cast `hashed`, model line). Controller:
`app/Http/Controllers/BusinessPortalController.php`. Layout:
`resources/views/business/layout.blade.php`. All authed routes under
`auth:business` middleware in `routes/web.php` lines 48-65.

## Owner journey, feature by feature

### 1. Login - DONE
- `showLogin()` redirects to dashboard if already authed. Surfaces a demo
  account: `Business::where('owner_email','demo@locolie.test')` and renders a
  one-click "Log in as" button with hardcoded creds in the page
  (`demo@locolie.test` / `golocal`, login.blade lines 36-42). Fine for a demo,
  but those creds are printed in plain HTML - remove before any real launch.
- `login()` uses `Auth::guard('business')->attempt([...])`, regenerates session,
  `redirect()->intended(route('business.dashboard'))`. Error copy: "Those
  details don't match a business account." Note: that string contains a curly
  apostrophe (`don't`) - fine, but the same file's button uses curly quotes
  around the demo name too.
- No registration/forgot-password. "New here?" links to
  `route('site.for-business')` (the marketing page), not a signup flow. So
  account creation is entirely out-of-band (admin-provisioned). Dead end if a
  real owner clicks expecting to self-register.

### 2. Dashboard - DONE (mostly)
`dashboard()` loads `category`,`offers`, builds KPI stats and
`customersFor()`. View `dashboard.blade.php`.
- KPIs: Active offers, Redeemed, Pending, Rating (lines 19-26). Real counts off
  `Redemption` via `redemptionsFor()` (whereHas offer.business_id).
- "View in app" -> `/app?b={slug}` new tab. Header plan badge correct.
- Listing edit form (lines 33-51): description, phone, website. Posts to
  `business.listing` (`updateListing`). GAP: `updateListing()` ALSO validates
  and saves `category_id` (controller line 315), but the dashboard form has NO
  category field (confirmed: no `category_id` in dashboard.blade). So category
  is editable by the controller but unreachable from the UI - dead validation
  rule / missing field.
- Offers panel (lines 54-72): read-only list of active offers; "Manage in app"
  -> `/app?as=business`. You CANNOT create/edit/delete an offer in this web CRM
  at all - it punts entirely to the Capacitor/app surface. Empty state: "No
  active offers yet - add one in the app." This is a real boundary, not a bug,
  but worth flagging: the web CRM is read-only for offers.

### 3. Customers panel - DONE
- `customersFor()` groups redemptions by `customer_email`, dedupes, computes
  visits/opt_in/last. Table shows top 25 (`->take(25)`).
- Export CSV -> `business.customers.export` (`exportCustomers`), streams CSV.
  Works. Filename `golocal-customers.csv` (brand drift: rest of app says
  locolie; reports CSV is `locolie-report.csv`).
- "Quick email" (Alpine `compose` toggle) posts to `business.customers.email`
  (`emailCustomers`). ROUGH/MISLEADING: it does NOT send anything. It only
  creates a `Campaign` row with `sent_count` = opted-in count and flashes
  "Email queued to N opted-in customers." No mail dispatch, no queue (confirmed:
  no Mail::/dispatch in this method). The real send path is the Messaging Studio
  (`messagingSend` -> `MessagingService::dispatch`). So there are two parallel
  email entry points and the dashboard quick-email is a stub that fibs "queued."
- Empty-state opt-in cell renders `' - '` literally (dashboard line 112).

### 4. Upgrade plan / Stripe - DONE (scaffolded, graceful)
- Plan cards from `Business::PLANS` (free/featured/premium, £0/£19/£49).
  `upgrade()` calls `BillingService::checkoutUrl()`.
- `BillingService` (app/Services/BillingService.php): `configured()` true only
  when `services.stripe.secret` set AND `\Stripe\StripeClient` exists. Builds a
  subscription Checkout session (uses `services.stripe.prices.{plan}` if set,
  else inline `price_data` from PLANS price * 100, GBP). Returns session URL.
- If not configured (MVP "free at launch"), `upgrade()` falls through to
  `applyPlan()` which sets `plan`,`priority`,`featured` directly and flashes
  "You're now on the X plan." This means without Stripe keys ANY user can set
  themselves to Premium for free by clicking the button - intended for launch
  but a real billing hole once you want to charge.
- `upgradeSuccess()` is the Stripe return; activates plan. NOTE: it trusts the
  `?plan=` query param and applies it on GET with no Stripe webhook / session
  verification - a user could hit `/business/upgrade/success?plan=premium`
  directly and self-upgrade even WITH Stripe configured. No webhook handler
  exists. This is the main billing integrity gap.
- `applyPlan` writes `featured`/`priority` - both are in `$fillable`, OK.

### 5. Reports - DONE (polished)
`reports()` -> `ReportingService::forBusiness($business,$days)`; `$days`
clamped to {7,14,30,90}. View `reports.blade.php` is the most finished surface:
hero KPIs (customers, redemptions, revenue influenced, savings delivered),
insight strip, two trend charts (`reports._trend`), marketing reach card, channel
performance (email/sms/push), top offers table, busiest-days bars
(`reports._bars`), recent customers, print button, range segmented control.
- Honest disclaimers present: "Open and click figures are indicative benchmarks
  until live tracking is connected - they are not measured yet." and money
  figures "estimates." Good.
- `reportsExport` -> `business.reports.export`, CSV of top offers (90d window
  hardcoded regardless of selected range). Filename `locolie-report.csv`.

### 6. Messaging Studio - DONE (UI) / partly demo (delivery)
`messaging()` builds overview, channels, last 10 campaigns, per-channel sample
messages (`sampleMessage` pre-fills from the brand's latest active offer),
previews, and audience counts. View `messaging.blade.php` (Alpine
`retailerMessaging()`).
- Brand form -> `business.brand` (`saveBrand`): logo upload (stored
  `brands/{id}` on `public` disk), accent colour (normalises to `#`), email
  sender name, SMS sender ID (<=11, regex). Uses model helpers `brandColor()`,
  `logoUrl()`, `brandInitials()`, `smsSenderId()`. Works.
- Compose tabs email/SMS/push with live AJAX preview -> `business.messaging.preview`
  (`messagingPreview`) returning rendered `messaging.previews.{channel}`.
- Send -> `business.messaging.send` (`messagingSend`). Recipients: email = own
  opted-in customers; sms = `smsCustomersFor` (phone + sms_opt_in); push =
  EMPTY array with comment "broadcast to subscribed shoppers + app devices".
  So push audience count shown is `PushSubscription::count() + DeviceToken::count()`
  (whole platform, not this brand) - the reports footnote admits this: "Push
  notifications reach app users across the whole locolie platform, not only your
  own list." Slightly off-message for a "your own customers" pitch.
- "Connected" vs "Demo mode" badge driven by `$overview[channel]['connected']`,
  so whether anything actually sends depends on `MessagingService` channel
  config (Twilio/mail/push keys). In demo it still flashes "X sent to N
  recipients (status)".
- Minor: initial preview markup hardcodes the email preview only (line 122);
  switching channels re-fetches via AJAX, fine, but first paint always email.

## Cross-cutting issues
- LAYOUT loads Tailwind via `cdn.tailwindcss.com` and Alpine + Google Fonts
  from CDNs (layout.blade lines 7-9). This contradicts the project's Vite/
  Tailwind v4 build pipeline used elsewhere - the business CRM is NOT using the
  compiled `app.css`/`app.js`. Will break offline / under the artifact CSP and
  is the "play CDN, not for production" build. Inconsistent with surfaces 1/2.
- Two email-send paths (dashboard quick-email stub vs Messaging Studio real
  dispatch) - confusing and the stub lies about sending.
- No offer CRUD, no listing photo/hours editing, no category picker - the web
  CRM defers a lot to `/app`. A first-time owner can edit only description/
  phone/website here.
- Billing: free self-upgrade with no Stripe; unverified `upgradeSuccess` GET
  with Stripe. No webhook.
- Demo creds printed in login HTML.
