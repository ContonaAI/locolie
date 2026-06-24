# Audit 03 - Current design system / visual language

Scope read in full: `resources/css/app.css`, `resources/js/app.js`, and every layout/shared
component: `site/layout`, `portal/layout`, `business/layout`, `customer/layout`,
`site/legal/layout`, `site/_pricing`, `site/_phone`, `components/seal`.

Headline: there is no single design system. There is one strong, well-developed visual
language on the public marketing site (`site/layout`), and three thinner, separately-authored
variants (portal, business, customer) that share only the brand colour and the wordmark.
Worse, the Tailwind v4 / Vite pipeline the repo is set up for is not actually used by any
rendered page.

---

## 1. The Tailwind v4 / Vite setup is dead on arrival

`resources/css/app.css` is the entire "real" stylesheet and it is 10 lines:

```css
@import 'tailwindcss';
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, ...;
}
```

`resources/js/app.js` is literally one line: `//` (empty).

Problems:

- The ONLY design token defined in `@theme` is `--font-sans: 'Instrument Sans'`. But NOT ONE
  layout uses the Vite build or Instrument Sans. Every layout instead loads **Inter** from the
  Google Fonts CDN and sets `font-family: 'Inter'` inline. So the project's single declared
  token is contradicted everywhere it renders. Instrument Sans never loads.
- Every layout pulls Tailwind from the **CDN play script** (`<script src="https://cdn.tailwindcss.com">`),
  not the compiled Vite build. `site/layout` and `business/layout` and `customer/layout` use
  `cdn.tailwindcss.com`; `portal/layout` uses `cdn.tailwindcss.com?plugins=typography`. This
  means: no JIT purge, no shared config, and the play CDN is explicitly not for production.
- Brand tokens (`ink`, `emerald`, `emerald.soft`, `muted`, `hair`, `borderRadius.card`) are
  redefined ad-hoc inside `<script>tailwind.config=...</script>` blocks per layout, NOT in
  `@theme`. So `app.css` and the actually-rendered token set have diverged completely.

This is the single biggest issue: the codebase's stated design system (Vite + `@theme` +
Instrument Sans) and its rendered design system (CDN Tailwind + inline config + Inter) are two
different things.

---

## 2. Fonts and type

| Surface | Font loaded | Weights | Source |
|---|---|---|---|
| `app.css` `@theme` (unused) | Instrument Sans | n/a | Vite |
| `site/layout` | Inter | 400-900 | Google CDN |
| `business/layout` | Inter | 400-900 | Google CDN |
| `customer/layout` | Inter | 400-900 | Google CDN |
| `portal/layout` | Inter + JetBrains Mono + Instrument Serif | Inter 400-900 | Google CDN |

So three fonts are declared in `@theme` and the layouts (Instrument Sans, Inter, plus portal
adds JetBrains Mono via `.mono` and Instrument Serif italic). Only Inter actually drives body
text. The portal is the only surface with a mono (`.mono { font-family:'JetBrains Mono' }`) and
a serif, neither of which exists elsewhere.

Type scale is entirely ad-hoc Tailwind utilities, no scale tokens. Headings range from
`text-3xl ... sm:text-4xl` (legal `<h1>`) down to one-off pixel sizes in `_phone`
(`text-[13px]`, `text-[11px]`, `text-[9px]`, `text-[8px]`, `text-[7px]`) and `legal/layout`
inline CSS (`font-size: 15.5px`, `1.35rem`, `1.05rem`, `14px`). Weights cluster on
`font-extrabold`/`800` for the wordmark and headings, `font-semibold`/`600` for nav, `font-bold`
for emphasis.

---

## 3. Colour palette - two parallel systems

There are effectively TWO neutral systems and they do not match.

**Marketing site (`site/layout`, `site/legal/layout`, `_pricing`, `_phone`):** a custom warm-
grey/black system.
- Ink (near-black text/buttons): `#0a0a0a` (named `ink`, also `--ink`)
- Muted text: `#737373` (named `muted`)
- Hairline borders: `#e5e5e5` (named `hair`)
- Surfaces: white `#ffffff`, footer `#f5f5f5`, legal callouts `#f9f9f9` and `#f0f0f0` ("Soon" pill)
- Legal prose text: `#262626` (yet another grey, inline)

**Portal / business / customer:** Tailwind's default cool **slate** scale instead.
- `portal/layout`: `text-slate-800` body, `slate-900/600/500/400/200/100/50`, surface
  `#f8fafc` + emerald/teal radial mesh.
- `business/layout`: `bg-slate-50`, `text-slate-800`, `border-slate-200`, `slate-900/600/500/400`.
- `customer/layout`: `bg-slate-50`, `text-slate-800`, `border-slate-200`.

So the marketing site uses neutral grey (`#737373`, `#e5e5e5`) while every logged-in surface
uses cool slate (`#64748b`, `#e2e8f0`, `#f8fafc`). Same brand, two different greys.

**Brand green is itself inconsistent:**
- Site config names `emerald` = `#059669` and `emerald.soft` = `#d1fae5`.
- Portal/business use Tailwind's **default** emerald palette for backgrounds/text:
  `bg-emerald-50`, `text-emerald-700`, `border-emerald-200`, `text-emerald-800` - none of which
  is `#059669` or `#d1fae5`. So "emerald" means a custom hex on the site and the stock Tailwind
  ramp in the portal. Active nav is `text-emerald-700 bg-emerald-50` in portal/business vs
  `text-emerald` (= `#059669`) on the site/legal nav.
- `gradient-text` differs by file: `site/layout` is `linear-gradient(120deg,#059669,#10b981 40%,#047857)`;
  `customer/layout` is the same colours but at `45%` not `40%`. Near-duplicate, not shared.
- Selection colour: site/customer use `#05966933`; portal uses `rgba(5,150,105,.18)`. Same
  intent, two encodings.

**A rogue blue.** `_phone` has a "Sponsored" promo card in **blue**
`bg-gradient-to-r from-[#2563eb] to-[#1e40af]` - the only blue in the entire system, with no
token and no rationale, sitting inside an otherwise emerald/ink mock.

---

## 4. The wordmark / brand pin - 4 different implementations

The "locolie" wordmark with map-pin "o"s is reimplemented per surface instead of being one
shared component:

1. `site/layout`: inline SVG pin (`$pin`) wrapped in `<span class="wordmark">`, with
   `drop-shadow(0 1px 3px rgba(5,150,105,.4))`, full path `M12 1.6C7.3 1.6...`.
2. `portal/layout`: a separate `$ppin` SVG, height `0.84em` (site uses `0.92em`), same path.
3. `business/layout` and `customer/layout`: yet another inline copy of the same SVG at
   `0.9em`, plus a CSS `.brand-pin` (a rotated rounded square `border-radius:50% 50% 50% 0`)
   that is a totally different shape from the SVG pin and is the version `customer/layout`
   actually defines.
4. `_phone`: a third pin form, an inline rotated `<span>` (`$mpin`).
5. `components/seal.blade.php`: the proper, reusable brand artifact (`<x-seal>`) with `dark`/
   `light`/`mono` variants - the one genuinely systematised piece, but used only in the site
   footer.

Same logo, 4-5 hand-maintained encodings at 3 different em-heights. Drift is guaranteed.

---

## 5. Buttons - shape splits cleanly by surface

- **Site / customer / pricing:** fully rounded **pills** (`rounded-full`). Primary =
  `bg-ink text-white hover:bg-emerald` (e.g. "Launch app", "Accept all"). Secondary =
  `border border-hair ... hover:bg-black/[0.04]` or `hover:border-ink`. Pricing CTA highlight
  inverts to `bg-emerald text-white hover:bg-ink`. Padding ~`px-4/5 py-2/2.5/3`.
- **Portal / business:** **rounded-lg** rectangles (`rounded-lg`), nav pills
  `px-3 py-1.5 rounded-lg`, primary actions are slate/emerald-tinted, not ink-black pills.

So a primary button is a black pill on the public site and a small emerald-tinted
`rounded-lg` chip in the back office. No shared button class anywhere - every button is bespoke
utility soup.

---

## 6. Cards, radius, shadows, badges

**Border-radius is all over the place** - no scale:
- Site declares one token: `borderRadius.card = 18px` (`rounded-card`), used only in `_pricing`.
- Otherwise: `rounded-full` (nav, pills), `rounded-3xl` (mobile menu, cookie bar, glass-card),
  `rounded-2xl` (dropdowns, legal callout, phone cards), `rounded-xl` (portal cards, alerts,
  category tiles), `rounded-lg` (portal nav/buttons), `rounded-[2.7rem]` (phone frame),
  `rounded-md` (phone badges), `rounded-[6px]` (focus outline). Six-plus radii, one token.

**Shadows** are bespoke per use, no scale:
- `glass-card`: `0 18px 50px -18px rgba(0,0,0,0.22), inset 0 1px 0 rgba(255,255,255,0.6)`
- `card-hover:hover`: `0 24px 50px -20px rgba(0,0,0,.25)`
- `_phone` frame: `0 30px 70px -25px rgba(0,0,0,0.55)`
- portal header: `shadow-[0_1px_0_rgba(0,0,0,.02)]`
- Plus utility `shadow-sm/lg/xl/2xl` and `shadow-xl shadow-emerald/10` (pricing highlight).

**Cards:** site uses glassmorphism (`.glass`, `.glass-card`, `.glass-dark`, blur 14-20px,
`saturate 140-180%`, translucent white borders `rgba(255,255,255,0.6-0.7)`). Portal has its own
`.gl-glass` (`rgba(255,255,255,.72)` blur 14px) - same idea, separate class. Business/customer
have NO glass; they use flat `bg-white border border-slate-200`. So three different card
treatments: glass (site), gl-glass (portal), flat slate (business/customer).

**Badges/pills** are inconsistent: "Live" pill = `bg-emerald-soft text-emerald` on site;
"Most popular" = `bg-emerald text-white` (pricing); status banners = `bg-emerald-50
border-emerald-200 text-emerald-800` (portal/business). Offer badges in `_phone` =
`bg-emerald text-white` (rounded-md). "Soon" pill = `bg-[#f0f0f0] text-muted`. No badge token.

**Inputs:** there is no shared input style and effectively no inputs in these layout files; the
only field-like element is the static search box in `_phone` (`bg-white/10 rounded-xl`). Form
input styling is presumably ad-hoc in page templates (not in scope here) - flag as undefined.

---

## 7. Other inconsistencies worth noting

- **Alpine.js** loaded from two different CDNs: `cdn.jsdelivr.net/npm/alpinejs` (site,
  business, customer) vs `unpkg.com/alpinejs` (portal).
- **theme-color** meta differs: site/portal `#0a0a0a`, customer `#059669`.
- **Container widths** are per-surface and untokenised: site `max-w-7xl`, portal `max-w-[1400px]`,
  business `max-w-6xl`, customer `max-w-lg`, legal `max-w-3xl`.
- **localStorage key prefixes** are mixed brand-era: `fl_lang`, `fl_place` (and `window.flTranslate`,
  `FL_CITIES`) vs `ll_cookie_consent` - leftover "FL" vs "LL" naming, not "locolie".
- Legal page styling is a self-contained inline `<style>` block (`.legal-prose`) with its own
  hardcoded greys (`#262626`, `#0a0a0a`, `#e5e5e5`, `#f5f5f5`) rather than Tailwind typography
  utilities, even though the portal already loads the `typography` plugin.

---

## Token inventory (as actually rendered, for the unified system)

- Brand green: `#059669` (primary), soft `#d1fae5`, gradient stops `#10b981`, `#047857`,
  mesh blobs `#6ee7b7`/`#a7f3d0`/`#bbf7d0`.
- Ink/near-black: `#0a0a0a`.
- Greys in play: `#737373` (muted), `#e5e5e5` (hairline), `#262626`, `#f5f5f5`, `#f9f9f9`,
  `#f0f0f0`, plus the entire Tailwind `slate` ramp (`#f8fafc`...`#0f172a`).
- Rogue: blue `#2563eb`/`#1e40af` (phone sponsored card only).
- Font: Inter (rendered) / Instrument Sans (declared, unused) + JetBrains Mono + Instrument
  Serif (portal only).
- Radii: token `18px` (card) + ad-hoc full/3xl/2xl/xl/lg/md/6px/2.7rem.
- Focus ring (the one consistent good pattern): `outline: 2px solid var(--emerald);
  outline-offset: 3px` on `:focus-visible` (site only - not in portal/business/customer).
