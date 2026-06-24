# Audit 04 - Copy & content audit (all surfaces)

Scope: every `resources/views/**/*.blade.php` read in full or grepped for copy. Grounded against
`01-bench-services.md` (Thumbtack/TaskRabbit/Checkatrade/Yelp lessons: earned-not-bought trust,
plain-spoken vetting, concrete-units pricing) and `03-current-design.md` (two parallel design
systems; marketing site is the strong, finished voice, the logged-in surfaces are thinner).

Headline finding: the **marketing site voice is genuinely good** - warm, confident, plain-spoken
British ("Back the indies. Bag the deals.", "Is this your shop?", "Bin the marketing stack."). The
weak copy lives in the **logged-in surfaces** (business dashboard, portal, messaging studios),
where it slips into generic SaaS ("Demo mode", "Send campaign", bare empty states) and where two
real **brand-name bugs** survive: the printable window sticker and the portal login still say
**"GoLocal"** in the old orange palette, not "locolie" in green.

House rule reminder: NO em-dashes (`—`) or en-dashes (`–`). Use hyphens. All rewrites below obey
this; a dedicated section flags the existing dashes to remove.

---

## Brand voice guide (use for every rewrite)

1. **Warm and plain-spoken, British.** Talk like a neighbour, not a brand deck. "Pop in", "have a
   proper go", "bag the deals". Contractions are fine. No corporate filler ("leverage",
   "solutions", "seamless").
2. **Confident, not hypey.** State the real benefit and stop. "Customers you own." beats
   "Revolutionary loyalty platform." Back claims with the concrete unit (a number, a £, a count),
   never vague superlatives.
3. **Two clear audiences, never blurred.** Shoppers get "find / reveal / redeem / save". Retailers
   get "list / footfall / own your customers / message them". A button or heading should make it
   obvious which side it speaks to.
4. **Honest about stage and limits.** We are live in Newcastle NE1, rolling out next; offers are
   estimates; demo data is demo data. The benchmark trust lesson (Checkatrade) is that honesty about
   what we do and do not do is the moat. Never trust-wash.
5. **Verbs in CTAs, outcomes in labels.** "Find local deals", "List my shop free", "Show my
   locolie" - not "Submit", "Enter", "Send campaign". The button should say what you get.

---

## A. Brand-name / palette bugs (fix first - these are wrong, not just weak)

| File | Current copy | Issue | Rewrite / fix |
|---|---|---|---|
| `resources/views/demo/sticker.blade.php` (L7, L37) | `Window sticker - {{ $business->name }}` title; `<div class="brand">Go<span class="pin"></span>Local</div>` | The printable window sticker still renders **"GoLocal"**, in the old orange palette (`--accent:#D9603B; --cta:#FFA41C`, L11). This is the offline trust mark that goes in real shop windows. Off-brand. | Wordmark to `lo<pin>colie` (or `Locolie`), recolour `--accent`/`--ink` to brand green `#059669` / ink `#0a0a0a` to match `<x-seal>`. Title: `locolie window sticker - {{ $business->name }}`. |
| `resources/views/demo/sticker.blade.php` (L20) | eyebrow `Local offers` | Generic, and the marketing "Badge of Honour" section calls this a "Scan for our offers" mark. | `Scan for our offers` (match `for-business.blade.php` L287). |
| `resources/views/portal/login.blade.php` (L8) | `Go<span class="text-emerald-600">Local</span> Portal` | Portal login still branded "GoLocal". | `lo<span class="text-emerald-600">colie</span> Portal` (or `locolie team`). |
| `resources/views/portal/login.blade.php` (L9) | `Private working space - enter the password to continue.` | Fine; keep. |

---

## B. Marketing site - home (`resources/views/site/home.blade.php`)

Mostly excellent. Tightening only.

| Line | Current copy | Rewrite |
|---|---|---|
| L334 | `it works a treat on any device.` (live-demo subhead) | Keep - on-voice. (No change; cited as the bar the rest should hit.) |
| L344-346 | live-feature list uses HTML entities `Live map &amp; feed`, `Fast &amp; lightweight` | Fine as rendered; no copy change. |
| L364 | `Tip: open it, then resize your browser - it's one responsive app...` | Keep, but this is a developer-ish aside on a consumer page. Consider dropping the "resize your browser" tip for shoppers: `One app for your phone, tablet and laptop.` |
| L453 | `Illustrative results from early locolie pilots with indies in Newcastle NE1.` | Good honest disclaimer (voice principle 4). Keep. |
| L579 | `Coming soon to the App Store and Google Play. Have a proper go in your browser right now.` | Keep - on-voice. |
| L410, L88 (and `for-business` L88) | mock button `✉ Email these customers` | Fine in a mock; matches the real dashboard button. Keep for consistency. |

Verdict: home needs no real rewrites, only the optional L364 trim. It is the voice benchmark.

---

## C. Marketing site - for-business (`resources/views/site/for-business.blade.php`)

Strong. Two small things.

| Line | Current copy | Rewrite |
|---|---|---|
| L109 | `Ping nearby shoppers and your followers the moment you post a fresh offer (Premium).` | Good and concrete. Keep. |
| L365 (FAQ) | `Featured (£19/mo) gets you featured-rail placement, a "Sponsored" badge, priority in search...` | Per benchmark (Thumbtack/Yelp: trust should not look pay-to-win), the word **"Sponsored"** as a perk reads as buying credibility. Reframe the badge as status, not an ad label: `...a "Featured local" badge, priority in search and on the map, and a monthly email feature.` (Pair with the `_pricing` change below and the listing-page change in F.) |

---

## D. Pricing partial (`resources/views/site/_pricing.blade.php`) - weakest marketing copy

The benchmark flags these blurbs explicitly as abstract; rewrite to concrete units (Thumbtack
"predictability" lesson).

| Line | Current copy | Rewrite |
|---|---|---|
| L7 | `free` blurb: `Everything you need to get found and start posting offers.` | `On the map, in search, posting offers and redeeming at the till. Free forever.` |
| L8 | `featured` blurb: `Stand out in the feed and search, plus a monthly email feature.` | `Top of your category, a "Featured local" badge and one email blast to your list each month.` |
| L9 | `premium` blurb: `Maximum reach: top placement, push notifications and analytics.` | `Everything in Featured, plus push to nearby shoppers, unlimited email and your numbers dashboard.` |
| L17 | badge `Most popular` | Generic SaaS (benchmark direction 4). Make it local-status: `Most chosen by indies` or keep `Most popular` if A/B-untested. Low priority. |
| L36 | CTA `Choose '.$plan['label']` -> "Choose Featured" / "Choose Premium" | Verbs-and-outcomes: `Go Featured` / `Go Premium` (free stays `List my shop free`). |

---

## E. Category page (`resources/views/site/category.blade.php`)

| Line | Current copy | Rewrite |
|---|---|---|
| L56 | empty state: `No independent {cat} on locolie just yet. We are adding new local spots every week, so pop back soon.` | Good voice. Keep. |
| L57 | `Run an independent {cat} business? Be the first to list it, free →` | Good. Keep. |
| L43, L44 | card badge `Sponsored` (shown when `plan !== 'free'`) | Same pay-to-win concern as C/F. When the "Featured local" badge ships, replace `Sponsored` here with the earned-looking badge so promotion and trust read differently (benchmark direction 1). |

---

## F. Business public listing (`resources/views/site/business.blade.php`)

| Line | Current copy | Rewrite |
|---|---|---|
| L32 | `Sponsored` pill (amber, by the category chip) | Per Yelp/Thumbtack benchmark, this is the spot users look for a *trust* mark; an ad label here erodes it. Replace with `Featured` and add a separate green "Independent verified" mark by the name (`admin.onboard` already gates verification). |
| L61 | offer fallback terms: `Open it in the locolie app and show your code at the till` | Good and clear. Keep. |
| L71 | `Reviews <span>· via Google</span>` | Honest sourcing, keep. Benchmark suggests collecting own verified-redemption reviews later, labelled `Verified - left after redeeming`. |
| L92-94 | `Is this your shop?` / `Claim your free listing, post offers... message your regulars by email, SMS and push.` | On-voice. Keep. |
| L42 | CTA `Open in the app` | Fine. Benchmark (Yelp) suggests adding a second `Message this shop` action later; copy-wise the current single CTA is clear. |

---

## G. Business dashboard (`resources/views/business/dashboard.blade.php`) - has en-dash-style copy + weak empties

| Line | Current copy | Rewrite |
|---|---|---|
| L70 | empty offers: `No active offers yet - add one in the app.` | Hyphen is fine; tighten and add a verb: `No live offers yet. Add your first one in the app to start pulling footfall.` |
| L112 | non-opted-in cell shows `' - '` (a bare hyphen-dash for "no") | Replace the dash with a clear word: `Not opted in` (or a muted `No`). A lone " - " reads as a glitch. |
| L120 | empty customers: `No customers captured yet. As shoppers redeem your offers, they'll appear here - ready to market to.` | Good voice; keep (hyphen ok). |
| L128 | `Change anytime. Free at launch - paid tiers preview the upgrade path.` | "preview the upgrade path" is jargon. `Change anytime. Free at launch; paid plans are a preview of what is coming.` |
| L93 | placeholder `Subject (e.g. A treat for our regulars 🎉)` | Great. Keep. |
| L14 | `View in app ↗` | Fine. |

---

## H. Business messaging (`resources/views/business/messaging.blade.php`)

| Line | Current copy | Rewrite |
|---|---|---|
| L11 | `Reach your own customers with branded email, SMS and push - the same list the big chains have always had, now yours.` | Strong; keep (hyphen ok). |
| L79 | channel status badge `Demo mode` (when not connected) | "Demo mode" is dev-speak shown to retailers. Soften: `Preview only` or `Not connected yet`. |
| L114 | `Customers who opted in at redemption only. Marketing rules (an unsubscribe link, STOP keyword) are added automatically.` | Clear and honest. Keep. |
| L97 | SMS placeholder `Your text message (160 chars per segment)` | Fine. |

---

## I. Customer report (`resources/views/customer/entry.blade.php`, `report.blade.php`) - good, minor

| File:Line | Current copy | Rewrite |
|---|---|---|
| entry L11 | `See how much you've saved shopping local with locolie.` | Keep. |
| entry L39 | helper `Use the same email you gave when redeeming an offer.` | Keep - clear. |
| entry L42 | CTA `Show my locolie` | On-brand and distinctive. Keep. |
| report L24 | empty: `We couldn't find any redeemed offers for this email. Grab a deal at an independent shop near you and your savings will show up here.` | Keep. |
| report L57 | favourite-category fallback `{{ $fav ?? '-' }}` | A bare `-` in a stat tile reads as broken. Use a word: `Still exploring` (or `-` only if a glyph dash like `–`; keep it a plain hyphen, not en-dash). |
| report L156 | `Savings shown are estimates based on the offers you redeemed.` | Honest, keep (principle 4). |

---

## J. Messaging previews (`resources/views/messaging/previews/{email,sms,push}.blade.php`)

These are realistic device mockups; copy is mostly placeholder/sample and fine. Flags:

| File:Line | Current copy | Rewrite |
|---|---|---|
| email L51 | snippet fallback `No preview text yet.` | Keep. |
| email L78 | body placeholder `Your message body will appear here as you type.` | Keep. |
| sms L74 | `Your message preview appears here as the customer sees it.` | Keep. |
| push L17 | default title `A deal near you` | Good sample. Keep. |
| sms L65, L81 | `Text Message - Today {time}`, `{brand} - delivered` | Plain hyphens, fine. (These mimic iOS chrome - leave verbatim.) |
| email L11, push L19 | brand initials default `'GL'` (GoLocal) | Leftover GoLocal initials. Change default to `'LO'` (locolie) so demo previews are not branded GL. (Also `branded.blade.php` L11.) |

---

## K. Branded email template (`resources/views/emails/branded.blade.php`)

| Line | Current copy | Rewrite |
|---|---|---|
| L109-111 | `Sent with care by {brand} on locolie — your local-deals marketplace.` | **Contains an em-dash** (see dashes section). Rewrite: `Sent with care by {brand} on locolie, your local-deals marketplace.` |
| L123 | footer `locolie - discover and support local businesses.` | Hyphen ok; keep. Optionally warmer: `locolie - back the indies.` to echo the master tagline. |
| L11 | initials default `'GL'` | Change to `'LO'` (see J). |

---

## L. Portal Messaging Studio (`portal/messaging/{studio,email,sms}.blade.php`)

Internal tool; lighter touch, but "Demo mode" leaks everywhere.

| File:Line | Current copy | Rewrite |
|---|---|---|
| studio L33 | status `Demo mode` | `Not connected` (clearer than dev-speak; "Demo mode" is fine internally but inconsistent with the "Connected" antonym). |
| studio L116 | empty `No businesses yet - onboard one in Admin first.` | Keep (hyphen ok). |
| email L107 | `Demo mode - sends are logged + counted until a provider is connected.` | Keep meaning; tidy: `Not connected yet. Sends are logged and counted until you connect a provider.` |
| email L45 | option `locolie (platform - all onboarded businesses)` | Keep. |
| sms L89 | `160 chars per segment. Keep it short - every segment is billed.` | Keep. |
| email L111 | CTA `Send campaign` | Add the audience like SMS does (L101 `Send campaign to {n} phones`): `Send to {n} inboxes`. Verbs+outcomes principle. |

---

## M. Subscriptions / preferences (`site/subscriptions/*`)

Both pages are well-written and on-voice. No rewrites.

| File:Line | Current copy | Note |
|---|---|---|
| preferences L10 | `Choose what you hear from locolie about. You're in control - change this any time, and we'll always honour it.` | Keep. |
| unsubscribed L12-20 | `You're unsubscribed` / `Changed your mind...?` | Keep. |

---

## N. Site layout chrome (`resources/views/site/layout.blade.php`)

| Line | Current copy | Note |
|---|---|---|
| L415 | `© {{ config('legal.company') }} 2026. All rights reserved.` | Fine. |
| L436-441 | cookie bar `We use cookies 🍪` / `Accept all` / `Reject optional` | On-voice, clear consent copy. Keep. |
| L274 | language switcher `Soon` pill | Fine. |

---

## Existing em/en-dashes to remove (house-style violations)

Grep results for `—` (U+2014) and `–` (U+2013) across `resources/views/`:

| File:Line | Context | Fix |
|---|---|---|
| `emails/branded.blade.php` L110 | `on <span...>locolie</span> —` (rendered footer text) | **Live copy** - replace em-dash with `,` or `.` (see section K). |
| `emails/partials/footer.blade.php` L4 | comment `$recipientEmail  — the contact's email` | Comment only; replace `—` with `-`. |
| `emails/partials/footer.blade.php` L5 | comment `$topic           — the subscription topic...` | Comment only; replace `—` with `-`. |
| `portal/home.blade.php` L895 | comment `Parent (top-level) groups — shown as...` | Comment only; replace `—` with `-`. |

No en-dashes (`–`) found anywhere. Note: most user-facing copy already uses plain hyphens
correctly (e.g. home L27 "redeem it at the till -"), so only the four above need touching, one of
which (`branded.blade.php`) is live email copy.

---

## Summary of proposed changes

- **2 brand-name bugs** (sticker + portal login still say "GoLocal" / orange) - highest priority.
- **3 default-initials** `'GL'` -> `'LO'` (branded email + 2 previews).
- **1 live em-dash** to remove (`branded.blade.php` footer) + **3 in code comments**.
- **~26 copy rewrites/tightenings** across pricing blurbs (weakest), dashboard empties and bare
  `-`/` - ` placeholders, "Demo mode" softening, "Sponsored"->"Featured/verified" reframes, and
  CTA verb-and-outcome tweaks.

**Total: ~32 copy/content fixes proposed.** The marketing site (home, for-business) is already on
voice and needs almost nothing; the value is in the logged-in surfaces and the two off-brand
"GoLocal" leaks.
