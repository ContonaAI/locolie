# Audit 03 - The unified GoLocal (locolie) design system

A single, implementable design system for every surface: marketing site (`site/*`),
the `/app` consumer + Capacitor build (`portal/home.blade.php`), the business CRM
(`business/*`), the team portal (`portal/*`), and the customer report (`customer/*`).
Built for Tailwind v4 `@theme` in `resources/css/app.css` and the compiled Vite build,
which today is dead on arrival (`03-current-design.md` section 1: only `--font-sans`
is declared and no rendered page uses it). House style: hyphens only, no em/en-dashes.

This spec preserves what already works and is loved - the brand green `#059669`, the
ink `#0a0a0a`, the `<x-seal>` "VERIFIED LOCAL / BACKED BY LOCOLIE" mark
(`components/seal.blade.php`), the glass `.tabbar` and `.cta-btn` in the app
(`home.blade.php` lines 134, 185) - and removes the drift: two greys (warm `#737373`
on the site vs cool `slate` in the portals, `03-current-design.md` section 3), two
emeralds (custom hex vs stock Tailwind ramp), the rogue blue `#2563eb` "Sponsored"
card in `_phone`, and 4-5 hand-coded wordmark pins.

Design intent: friendly local-high-street warmth with Stripe/Airbnb structural
discipline. Green is the brand and the action colour; a warm sand neutral (not cool
slate, not stark white) makes pages feel local and human; amber means "paid/sponsored"
and a separate green shield means "earned/verified" (the trust-vs-promotion split the
benchmarks demand, `01-bench-services.md` items 1, 12, 19).

---

## 1. Colour palette

All values are exact hex. Tokens map 1:1 to the `@theme` block in section 9.

### Brand

| Token | Hex | Role / rationale |
|---|---|---|
| `--color-brand-50` | `#ecfdf5` | Lightest green wash; tab/chip active fill, success surface |
| `--color-brand-100` | `#d1fae5` | The existing `emerald.soft`; badge fills, soft pills. Keep - already in code. |
| `--color-brand-200` | `#a7f3d0` | Mesh blob, hairline-on-green |
| `--color-brand-300` | `#6ee7b7` | Seal accent gradient stop, mesh (already used by `<x-seal>`) |
| `--color-brand-400` | `#34d399` | Hover-lighten of primary |
| `--color-brand-500` | `#10b981` | Gradient mid-stop (already in `gradient-text`) |
| `--color-brand-600` | `#059669` | **PRIMARY BRAND GREEN.** The canonical `emerald`/`--accent`. Unchanged. |
| `--color-brand-700` | `#047857` | Primary hover/active, gradient deep stop (already used) |
| `--color-brand-800` | `#065f46` | Text-on-light green, pressed states |
| `--color-brand-900` | `#064e3b` | Darkest green, rarely used |

Rationale: `#059669` is already the brand everywhere it matters (site config,
`<x-seal>`, app `--accent`, focus ring). We do not change it; we build a full,
consistent ramp around it so the portals stop reaching for Tailwind's *stock* emerald
(`#10b981`-based), which `03-current-design.md` section 3 flags as a near-but-wrong
second green.

### Ink + neutrals (the big fix: one neutral system)

Replace BOTH the warm-grey site set (`#737373`, `#e5e5e5`) and the cool `slate` portal
set with ONE warm-tinted "sand" neutral ramp. Warm (a hair of green/yellow, not blue)
keeps the local-high-street feel and stops the site-vs-portal grey mismatch.

| Token | Hex | Role |
|---|---|---|
| `--color-ink` | `#0a0a0a` | Primary text, primary buttons, app chrome bars. Unchanged - this is the loved near-black. |
| `--color-ink-soft` | `#1c1b19` | Headings on sand, dark cards |
| `--color-sand-50` | `#faf9f6` | App/page background (replaces `#f8fafc` slate bg and white-on-white) |
| `--color-sand-100` | `#f4f2ec` | Footer, subtle section fill (replaces `#f5f5f5`) |
| `--color-sand-200` | `#e9e6dd` | Card insets, "Soon" pill (replaces `#f0f0f0`) |
| `--color-sand-300` | `#dcd8cc` | Hairline borders on sand surfaces (replaces `#e5e5e5` `hair` and `slate-200`) |
| `--color-muted` | `#6b675e` | Secondary/meta text (replaces both `#737373` and `slate-500/600`) |
| `--color-muted-strong` | `#44413a` | Body prose on light (replaces legal `#262626`) |
| `--color-line` | `#e6e3da` | The single hairline token used everywhere a border is drawn |
| `--color-surface` | `#ffffff` | Cards, sheets, inputs |
| `--color-surface-2` | `#faf9f6` | Recessed surface = sand-50 |

Rationale: warm sand neutrals read as paper/craft/local, not corporate SaaS. One ramp
means the customer journey home -> app -> report stops showing "three different greys"
(`02-ux-consumer.md` P1-36). `#0a0a0a` ink is retained verbatim because it is the
anchor of every primary button and the app's title/header bars.

### Accent (secondary, sparing)

| Token | Hex | Role |
|---|---|---|
| `--color-accent-amber` | `#d97706` | **"Sponsored / paid placement" ONLY.** The honest-advertising colour. Distinct from green so paid never masquerades as earned (`01-bench-services.md` item 1). |
| `--color-accent-amber-soft` | `#fef3c7` | Sponsored pill fill (already the `.otag.seasonal` colour, `home.blade.php:125`) |

This is the ONLY non-green accent in the system. It kills the rogue blue
`#2563eb`/`#1e40af` from `_phone` (`03-current-design.md` section 3) - that "Sponsored"
promo card becomes amber.

### Semantic

| Token | Hex | Role |
|---|---|---|
| `--color-success` | `#15803d` | Confirmed/sent ("Code redeemed"). Matches `.timer.done` `#15803d` (`home.blade.php:151`). |
| `--color-success-soft` | `#dcfce7` | Success surface (already used by `.timer.done`) |
| `--color-warn` | `#b45309` | Warnings, "demo / not wired" channel labels (`02-ux-business.md` P1-6). Matches `.otag.seasonal` text. |
| `--color-warn-soft` | `#fef3c7` | Warn surface |
| `--color-error` | `#dc2626` | Errors, "Sold out", expired. Matches `.otag.hot`/`.timer.expired` `#dc2626`. |
| `--color-error-soft` | `#fee2e2` | Error surface (already used by `.otag.hot`) |
| `--color-info` | `#0369a1` | Neutral info notes only (NOT a brand colour) |
| `--color-info-soft` | `#e0f2fe` | Info surface |

Rationale: success/warn/error are pulled straight from values already living in the
app's offer-tag and timer CSS, so the app needs near-zero change. Brand green is the
*action* colour, kept distinct from `--color-success` so "this is clickable" and "this
succeeded" do not collide.

### Trust vs promotion (the benchmark mandate)

- **Verified/earned = green shield**, `--color-brand-600` on `--color-brand-50`, using
  `<x-seal>` or a compact tick-in-shield. Earned by the `onboarded` flag.
- **Sponsored/paid = amber pill**, `--color-accent-amber` on `--color-accent-amber-soft`.

Two different colour families so users tell credentials from ads at a glance
(`01-bench-services.md` items 1, 12; `02-ux-consumer.md` P2-15).

---

## 2. Typography

### Family

One family everywhere: **Inter** (it is what actually renders today on every surface -
`03-current-design.md` section 2). Stop declaring `Instrument Sans` (never loads) and
drop the portal-only `Instrument Serif`. Keep **one** mono for codes/figures.

```
--font-sans:  'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
--font-mono:  'JetBrains Mono', ui-monospace, 'SF Mono', Menlo, monospace;
```

Inter ships via `@import` from the Vite-bundled font OR self-hosted (preferred for the
Capacitor build, which must work offline - `02-ux-consumer.md` P1-30). Mono is used
ONLY for the redemption code (`.ticket-code`, `home.blade.php:146`) and CRM numeric
stats. No serif. Wordmark stays Inter `800` with `-0.03em` tracking (the existing
`.wordmark`).

### Type scale

A modular ramp (~1.2 minor third under `lg`, ~1.25 above). Sizes / line-height / weight:

| Token | px (rem) | line-height | weight | Use |
|---|---|---|---|---|
| `text-display` | 56 (3.5rem) | 1.04 | 800 | Hero H1 (site home) |
| `text-h1` | 40 (2.5rem) | 1.1 | 800 | Page H1 (category, shop, legal) |
| `text-h2` | 30 (1.875rem) | 1.15 | 700 | Section headings |
| `text-h3` | 22 (1.375rem) | 1.25 | 700 | Card titles, dashboard KPI labels |
| `text-h4` | 18 (1.125rem) | 1.3 | 600 | Sub-headings, business name on cards |
| `text-base` | 16 (1rem) | 1.55 | 400 | Body. Bump from the legal `15.5px` one-off. |
| `text-sm` | 14 (0.875rem) | 1.5 | 400/500 | Meta lines, nav, secondary |
| `text-xs` | 12 (0.75rem) | 1.45 | 600 | Badges, eyebrows, "Open now" cues |
| `text-2xs` | 10.5 (0.656rem) | 1.4 | 700/800 | Offer tags (`.otag` is 10.5px today), map pins |

Weights in use: `400` body, `500` emphasised meta, `600` nav/labels/badges,
`700` card titles/section heads, `800` H1/display/wordmark/offer-tags. Drop the random
`text-[13px]/[11px]/[9px]/[8px]/[7px]` ladder in `_phone` and the inline
`font-size:15.5px/1.35rem/1.05rem` in `legal/layout` (`03-current-design.md` section 2);
map them to the nearest scale token (mostly `text-2xs`/`text-xs`/`text-base`).

Headings: tracking `-0.02em` at h2+ and `-0.03em` at display/h1 (the wordmark already
uses `-0.03em`). Body: tracking `0`. Use `text-wrap: balance` on H1/display (the
existing `.text-balance`) and `text-wrap: pretty` on lead paragraphs.

---

## 3. Spacing scale

Use Tailwind's default 4px base; lock the *vocabulary* to these steps so surfaces stop
inventing one-offs:

`0, 1(4px), 2(8px), 3(12px), 4(16px), 5(20px), 6(24px), 8(32px), 10(40px), 12(48px),
16(64px), 20(80px), 24(96px)`.

Conventions:
- Card padding: `p-4` (compact, app rows) / `p-5` (default cards) / `p-6` (marketing/CRM cards).
- Section vertical rhythm: `py-16` mobile, `py-24` desktop.
- Inline gap in meta/trust lines: `gap-2`.
- Container max-widths (replacing the per-surface free-for-all in section 7): one token
  set - `--w-prose: 48rem` (legal), `--w-content: 72rem` (`max-w-6xl`-ish, CRM + category),
  `--w-wide: 80rem` (`max-w-7xl`, marketing). The app stays a fixed phone column.

---

## 4. Border-radius scale

Six ad-hoc radii today (`03-current-design.md` section 6) collapse to a 5-step scale:

| Token | Value | Use |
|---|---|---|
| `--radius-xs` | 8px | Badges, offer tags, chips, small inputs |
| `--radius-sm` | 12px | Inputs, list-row thumbnails (`.row-img` is 12px today), small cards |
| `--radius-md` | 16px | Cards (`.hcard` is 16px today), sheets-inner, app CTA (`.cta-btn` is 14px -> 16px) |
| `--radius-lg` | 22px | Hero/feature cards, modals, mobile menus, the app `.tabbar` (26px -> 22px) |
| `--radius-pill` | 9999px | Pill buttons + nav pills + tab highlight |

Decision on the site's pill-vs-rect button split (`03-current-design.md` section 5):
**primary actions are pills (`--radius-pill`)** across ALL surfaces (the site/customer
pattern wins - it is the more distinctive, friendlier shape and matches "Launch app",
"Accept all"). **Containers/cards/inputs use `--radius-md`/`--radius-sm`.** The portal's
`rounded-lg` buttons become pills; its cards become `--radius-md`. Keep `rounded-card`
(18px) callers by aliasing them to `--radius-md` (16px) - close enough, one fewer token.

---

## 5. Shadow / elevation scale

Five tokens replacing the bespoke per-use shadows in section 6:

| Token | Value | Use |
|---|---|---|
| `--shadow-xs` | `0 1px 2px rgba(15,26,34,.05)` | Hairline lift on inputs/rows (matches `.hcard` base) |
| `--shadow-sm` | `0 1px 2px rgba(15,26,34,.04), 0 8px 22px rgba(15,26,34,.06)` | Resting card (this is the exact `.hcard` shadow today) |
| `--shadow-md` | `0 8px 18px rgba(15,26,34,.10), 0 20px 44px rgba(15,26,34,.12)` | Hover card (exact `.hcard:hover`), dropdowns |
| `--shadow-lg` | `0 18px 50px -18px rgba(0,0,0,.22)` | Glass/feature cards, sticky headers (matches `.glass-card`) |
| `--shadow-xl` | `0 30px 70px -25px rgba(0,0,0,.45)` | Phone frame, modals over content |
| `--shadow-brand` | `0 6px 16px color-mix(in srgb, var(--color-brand-600) 34%, transparent)` | Primary-button glow (exact `.cta-btn` shadow) |

Elevation rule: surfaces use at most `xs/sm` at rest, `md` on hover, `lg`/`xl` only for
overlays. Tints in `--shadow-sm/md` use the app's existing `rgba(15,26,34,...)` slate-ink
so the app needs no change.

Glass: keep ONE glass recipe (the site's `.glass-card`), aliased as `--glass-bg:
rgba(255,255,255,.7)` + `blur(20px) saturate(160%)` + `border: 1px solid rgba(255,255,255,.7)`.
Delete the portal's separate `.gl-glass` and point it at the same tokens.

---

## 6. Component specs (with Tailwind classes)

### Buttons

One `.btn` base + variants. Pills everywhere, `font-semibold`, `transition`,
`active:scale-[.98]`, visible focus ring.

Base:
`inline-flex items-center justify-center gap-2 rounded-full font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:opacity-60 disabled:cursor-default active:scale-[.98]`

| Variant | Classes |
|---|---|
| Primary | `bg-ink text-white hover:bg-brand-700 active:bg-brand-800` (black at rest, greens on interaction - the loved site pattern) |
| Primary-brand (app CTA) | `bg-brand-600 text-white shadow-brand hover:bg-brand-700` (the `.cta-btn` look, for the reveal action) |
| Secondary | `bg-white text-ink border border-line hover:border-ink hover:bg-sand-100` |
| Ghost | `text-muted hover:text-ink hover:bg-sand-100` (nav/log-out) |
| Danger | `bg-error text-white hover:brightness-95` (destructive only) |

Sizes: `sm` `px-3.5 py-2 text-sm`; `md` (default) `px-5 py-2.5 text-sm`; `lg`
`px-6 py-3 text-base`; app full-width CTA `w-full py-4 text-base`.
States: hover/active per variant; `disabled` -> `opacity-60` + no shadow (the app's
`.cta-btn:disabled` "Sold out" state maps here); focus -> 2px `brand-600` ring,
offset 2 (this generalises the site-only focus ring to every surface,
`03-current-design.md` section 7).

### Inputs / forms

There is no shared input style today (`03-current-design.md` section 6). Define one:

Input/select/textarea:
`w-full rounded-[--radius-sm] border border-line bg-white px-4 py-2.5 text-base text-ink
placeholder:text-muted shadow-xs focus:border-brand-600 focus:outline-none
focus:ring-2 focus:ring-brand-600/30 disabled:bg-sand-100`

(This matches the app's `.field:focus{border-color:var(--accent)}` at `home.blade.php:211`.)

- Label: `text-sm font-semibold text-ink mb-1.5 block`.
- Help: `text-xs text-muted mt-1`.
- Error: input gets `border-error focus:ring-error/30`; message `text-xs text-error mt-1`.
  This replaces the `alert()` validation flagged in `02-ux-consumer.md` P1-20 with inline
  field errors.
- Checkbox/radio: `accent-brand-600` (consent opt-in, `02-ux-consumer.md` P1-27).

### Cards - listing card (the canonical one)

Standardise the three card treatments (site glass / portal gl-glass / flat slate) into
ONE listing card used by `site/category.blade.php`, `site/business.blade.php` related,
the app `.hcard`/`.row`, and CRM lists. Structure: image -> body -> ONE trust line.

```html
<article class="group rounded-[--radius-md] bg-white border border-line shadow-sm
                hover:shadow-md hover:-translate-y-0.5 transition overflow-hidden">
  <div class="relative aspect-[16/10] bg-gradient-to-br from-brand-600 to-ink">
    <!-- trust vs promo, never both colours: -->
    <span class="absolute top-3 left-3 inline-flex items-center gap-1 rounded-[--radius-xs]
                 bg-brand-50 text-brand-700 text-2xs font-extrabold uppercase px-2 py-1">Verified local</span>
    <span class="absolute top-3 right-3 rounded-[--radius-xs] bg-accent-amber-soft
                 text-accent-amber text-2xs font-bold uppercase px-2 py-1">Sponsored</span>
  </div>
  <div class="p-5">
    <h3 class="text-h4 font-semibold text-ink">Shop name</h3>
    <!-- THE single trust line (bench mandate): -->
    <p class="mt-1 text-sm text-muted flex items-center gap-2 flex-wrap">
      <span class="text-ink font-semibold">★ 4.6</span>
      <span>(38 reviews)</span><span aria-hidden>·</span>
      <span>0.4 mi</span><span aria-hidden>·</span>
      <span class="text-success font-semibold">Open now</span>
    </p>
    <p class="mt-3 inline-flex rounded-[--radius-xs] bg-brand-50 text-brand-700
              text-xs font-bold px-2.5 py-1">25% off everything</p>
  </div>
</article>
```

The "★ rating (N reviews) · distance · Open/Closed" trust line is the one card contract
the benchmarks ask for (`01-bench-marketplaces.md` item 2; `02-ux-consumer.md` P2-9).
When `reviews_count < 3`, show "New" instead of a star number (`01-bench-marketplaces.md`
item 4). The app's existing `.hcard`/`.row` already match this shape and gradient
(`linear-gradient(135deg,var(--accent),var(--ink))`); they only need the trust-line and
the heart-on-card.

### Badges / trust seals

| Badge | Classes | Meaning |
|---|---|---|
| Verified local | `bg-brand-50 text-brand-700 ring-1 ring-brand-200 rounded-[--radius-xs] text-2xs font-extrabold uppercase px-2 py-1` + tick icon | Earned (`onboarded`) |
| Sponsored | `bg-accent-amber-soft text-accent-amber rounded-[--radius-xs] text-2xs font-bold uppercase px-2 py-1` | Paid |
| Live / Open now | `bg-success-soft text-success rounded-[--radius-xs] text-2xs font-bold px-2 py-1` with a pulsing dot | Real-time |
| Offer tag | `bg-brand-50 text-brand-700` (`.otag`); `.hot` -> `bg-error-soft text-error`; `.seasonal` -> `bg-warn-soft text-warn` | Maps the existing `.otag` variants verbatim |
| Soon / coming | `bg-sand-200 text-muted rounded-pill text-2xs font-bold px-2.5 py-1` | Disabled/future |

The big-trust seal is the existing `<x-seal>` (dark/light/mono) - now the ONLY brand
artifact; promote it from the site footer to the window sticker (`01-bench-services.md`
item 11) and the verified marker. Its colours already match this palette exactly
(`#0a0a0a`, `#059669`, `#6ee7b7`, `#047857`).

### Navigation

Desktop (site + CRM + portal): sticky glass header (`--glass-bg`, `--shadow-lg`,
`border-b border-line`), wordmark left, nav-pill links centre/left, primary CTA right.
Active link: `bg-brand-50 text-brand-700 font-semibold rounded-pill px-3 py-1.5`
(unifies the portal's `text-emerald-700 bg-emerald-50` and the site's `text-emerald`).
Inactive: `text-muted hover:text-ink hover:bg-sand-100`.

Mobile bottom-nav (the app `.tabbar`): keep the loved glass pill bar - 3-5 destinations,
icon-over-label, fixed, `env(safe-area-inset-bottom)` respected. Tokenise its values:
`bg: color-mix(in srgb, var(--color-sand-50) 68%, transparent)` + `blur(26px)`,
`border: 1px solid var(--color-line)`, `rounded-[--radius-lg]`, `--shadow-md`. Active
tab: `text-brand-600 bg-brand-600/13` (exact current `.tab.on`). Add a Wallet/My-codes
tab so redeemed offers persist (`01-bench-marketplaces.md` item 8, `02-ux-consumer.md`
P2-28).

### Empty states

Pattern: centred, muted icon, one-line headline, one-line subtext, ONE action button -
never a dead string. Replace "No offers match here." / "No results."
(`02-ux-consumer.md` P1-22):

```html
<div class="text-center py-16 px-6">
  <div class="mx-auto w-12 h-12 rounded-full bg-sand-100 grid place-items-center text-muted">…</div>
  <p class="mt-4 text-h4 font-semibold text-ink">No offers match those filters</p>
  <p class="mt-1 text-sm text-muted">Try widening your distance, or browse everything nearby.</p>
  <button class="btn btn-secondary mt-4">Clear filters</button>
</div>
```

The Saved-tab empty ("Tap the heart on any shop to save it.") and the marketing-category
empty are already correct models - generalise them.

### Loading skeletons

Replace the 6s "Can't reach Locolie" splash and bare spinners with skeletons matching the
listing card so the app feels instant (`02-ux-consumer.md` P1-30):

```html
<div class="rounded-[--radius-md] bg-white border border-line overflow-hidden">
  <div class="aspect-[16/10] bg-sand-200 animate-pulse"></div>
  <div class="p-5 space-y-2">
    <div class="h-4 w-2/3 bg-sand-200 rounded animate-pulse"></div>
    <div class="h-3 w-1/2 bg-sand-100 rounded animate-pulse"></div>
  </div>
</div>
```

`animate-pulse`, `prefers-reduced-motion` disables it (the app already guards motion at
`home.blade.php:197`).

### Toasts / errors

A single toast component to replace every `alert()` (`02-ux-consumer.md` P1-20,
`createOffer`/`redeem`):

```html
<div class="fixed bottom-24 inset-x-4 z-50 mx-auto max-w-sm rounded-[--radius-md]
            bg-ink text-white shadow-xl px-4 py-3 text-sm flex items-center gap-3">
  <span class="w-2 h-2 rounded-full bg-success"></span> <!-- success/warn/error dot -->
  <span>Code revealed - show it at the till.</span>
</div>
```

Variants by dot/border: success `--color-success`, warn `--color-warn`, error
`--color-error`. Errors inside forms use inline field errors (see Inputs), not toasts.

---

## 7. Migration note - what to replace, and where

1. **Kill CDN Tailwind + CDN Inter; ship the Vite build.** Remove
   `<script src="https://cdn.tailwindcss.com...">` and the inline `tailwind.config`
   blocks from `site/layout.blade.php` (line ~35), `portal/layout.blade.php` (line ~12),
   `business/layout.blade.php` (lines 7-9), `customer/layout.blade.php`. Replace with
   `@vite(['resources/css/app.css','resources/js/app.js'])`. Self-host Inter for the
   Capacitor offline build (`02-ux-consumer.md` P1-30/P1-37, `02-ux-business.md` P2-1).
2. **Paste the section-9 `@theme` block into `resources/css/app.css`**, replacing the
   current 9-line file (only `--font-sans` exists today).
3. **One neutral system.** Find/replace cool slate in `portal/layout`, `business/layout`,
   `customer/layout`: `bg-slate-50` -> `bg-sand-50`, `text-slate-800` -> `text-ink`,
   `text-slate-600/500` -> `text-muted`, `border-slate-200` -> `border-line`,
   body `#f8fafc` -> `--color-sand-50`. Replace site warm-greys: `#737373` -> `--color-muted`,
   `#e5e5e5`/`hair` -> `--color-line`, `#f5f5f5` -> `--color-sand-100`, legal `#262626`
   -> `--color-muted-strong` (`03-current-design.md` section 3).
4. **One green.** Replace stock-Tailwind emerald utilities in the portals
   (`bg-emerald-50`, `text-emerald-700`, `border-emerald-200`) with the `brand-*` ramp.
   `text-emerald` (site) and `.gl-glass` emerald both resolve to `--color-brand-600`.
   Unify the two `gradient-text` definitions (site `40%`, customer `45%`) to one
   `linear-gradient(120deg, var(--color-brand-600), var(--color-brand-500) 40%, var(--color-brand-700))`.
5. **Kill the rogue blue.** In `site/_phone.blade.php`, change the "Sponsored" promo card
   `from-[#2563eb] to-[#1e40af]` to `bg-accent-amber-soft text-accent-amber`
   (`03-current-design.md` section 3).
6. **One wordmark + one seal.** Extract the inline SVG pin into a single
   `<x-wordmark>` component (consolidating `$pin`, `$ppin`, `.brand-pin`, `$mpin`,
   `_phone`'s pin - `03-current-design.md` section 4) at one em-height. Keep `<x-seal>`
   as the only trust artifact; reuse it on `/s/{secret}` sticker.
7. **One button.** Replace bespoke button utility-soup with the `.btn` variants
   (section 6). Portal/CRM `rounded-lg` buttons -> `rounded-full` pills. App `.cta-btn`
   already matches Primary-brand; alias it.
8. **Radius + shadow tokens.** Replace `rounded-3xl/2xl/xl/lg/md/[2.7rem]/[6px]` with
   `--radius-*`; replace the five bespoke box-shadows with `--shadow-*` (both already
   chosen to match existing app values, so the app barely moves).
9. **Glass once.** Delete `.gl-glass` (portal); point it and `.glass-card` at the shared
   `--glass-*` tokens.
10. **Housekeeping the design touches:** unify Alpine to one CDN/Vite source (jsdelivr vs
    unpkg, `03-current-design.md` section 7); set `theme-color` to `#0a0a0a` everywhere
    (customer is `#059669`); rename leftover `fl_*` / `FL_*` localStorage keys to `ll_*`.

Order of operations: (2) tokens first, then (1) build switch, then (3)/(4) neutral+green
sweeps, then (6)-(9) component extraction. The app surface (`portal/home.blade.php`)
moves least because the tokens were reverse-engineered from its existing CSS variables.

---

## 8. The app's CSS-variable bridge

`portal/home.blade.php` themes via `--accent`, `--ink`, `--accent-soft`, `--bg`, `--text`,
`--muted`, `--line`, `--cta`, `--cta-text`, `--surface-2`, `--radius-sm`, `--sage`. Map
them onto the unified tokens so the app inherits the system without rewriting its CSS, and
delete the 8-palette theme cycler (`themes` object, `02-ux-consumer.md` P2-38):

```
--accent: var(--color-brand-600);  --accent-soft: var(--color-brand-50);
--ink: var(--color-ink);           --bg: var(--color-surface);
--text: var(--color-ink);          --muted: var(--color-muted);
--line: var(--color-line);         --surface-2: var(--color-sand-50);
--cta: var(--color-ink);           --cta-text: #ffffff;
--sage: var(--color-brand-700);    --radius-sm: 12px;
```

---

## 9. Ready-to-paste `@theme` block for `resources/css/app.css`

```css
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../views';

@theme {
  /* Fonts */
  --font-sans: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  --font-mono: 'JetBrains Mono', ui-monospace, 'SF Mono', Menlo, monospace;

  /* Brand green ramp (anchor #059669) */
  --color-brand-50:  #ecfdf5;
  --color-brand-100: #d1fae5;
  --color-brand-200: #a7f3d0;
  --color-brand-300: #6ee7b7;
  --color-brand-400: #34d399;
  --color-brand-500: #10b981;
  --color-brand-600: #059669;
  --color-brand-700: #047857;
  --color-brand-800: #065f46;
  --color-brand-900: #064e3b;

  /* Ink + warm sand neutrals (one system) */
  --color-ink:          #0a0a0a;
  --color-ink-soft:     #1c1b19;
  --color-sand-50:      #faf9f6;
  --color-sand-100:     #f4f2ec;
  --color-sand-200:     #e9e6dd;
  --color-sand-300:     #dcd8cc;
  --color-muted:        #6b675e;
  --color-muted-strong: #44413a;
  --color-line:         #e6e3da;
  --color-surface:      #ffffff;
  --color-surface-2:    #faf9f6;

  /* Accent (paid/sponsored only) */
  --color-accent-amber:      #d97706;
  --color-accent-amber-soft: #fef3c7;

  /* Semantic */
  --color-success:      #15803d;
  --color-success-soft: #dcfce7;
  --color-warn:         #b45309;
  --color-warn-soft:    #fef3c7;
  --color-error:        #dc2626;
  --color-error-soft:   #fee2e2;
  --color-info:         #0369a1;
  --color-info-soft:    #e0f2fe;

  /* Type scale (font-size / line-height) */
  --text-2xs:     0.656rem;  --text-2xs--line-height: 1.4;
  --text-xs:      0.75rem;   --text-xs--line-height: 1.45;
  --text-sm:      0.875rem;  --text-sm--line-height: 1.5;
  --text-base:    1rem;      --text-base--line-height: 1.55;
  --text-h4:      1.125rem;  --text-h4--line-height: 1.3;
  --text-h3:      1.375rem;  --text-h3--line-height: 1.25;
  --text-h2:      1.875rem;  --text-h2--line-height: 1.15;
  --text-h1:      2.5rem;    --text-h1--line-height: 1.1;
  --text-display: 3.5rem;    --text-display--line-height: 1.04;

  /* Radius scale */
  --radius-xs:   8px;
  --radius-sm:   12px;
  --radius-md:   16px;
  --radius-lg:   22px;
  --radius-pill: 9999px;

  /* Elevation scale */
  --shadow-xs:   0 1px 2px rgba(15,26,34,.05);
  --shadow-sm:   0 1px 2px rgba(15,26,34,.04), 0 8px 22px rgba(15,26,34,.06);
  --shadow-md:   0 8px 18px rgba(15,26,34,.10), 0 20px 44px rgba(15,26,34,.12);
  --shadow-lg:   0 18px 50px -18px rgba(0,0,0,.22);
  --shadow-xl:   0 30px 70px -25px rgba(0,0,0,.45);

  /* Container widths */
  --w-prose:   48rem;
  --w-content: 72rem;
  --w-wide:    80rem;
}

/* Brand-tinted primary-button glow (color-mix, not a static token) */
@utility shadow-brand {
  box-shadow: 0 6px 16px color-mix(in srgb, var(--color-brand-600) 34%, transparent);
}

/* Shared glass (one recipe, replaces .glass-card and .gl-glass) */
@utility glass {
  background: rgba(255,255,255,.7);
  backdrop-filter: blur(20px) saturate(160%);
  -webkit-backdrop-filter: blur(20px) saturate(160%);
  border: 1px solid rgba(255,255,255,.7);
  box-shadow: var(--shadow-lg);
}

/* Brand gradient text (one definition, replaces the two divergent ones) */
@utility gradient-text {
  background: linear-gradient(120deg, var(--color-brand-600), var(--color-brand-500) 40%, var(--color-brand-700));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

/* One focus ring, every surface (generalises the site-only rule) */
@layer base {
  a:focus-visible, button:focus-visible, [role="button"]:focus-visible {
    outline: 2px solid var(--color-brand-600);
    outline-offset: 2px;
  }
  ::selection { background: color-mix(in srgb, var(--color-brand-600) 20%, transparent); }
  body { background: var(--color-sand-50); color: var(--color-ink); }
}
```
