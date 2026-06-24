# Audit 00 - Native app shell + JSON API + /app experience

Scope: surface #2. How the Capacitor iOS app loads the web, what `/app` renders, every
`routes/api.php` endpoint, push registration, and local->prod sync. All paths absolute.
Read in full: `capacitor.config.json`, `package.json`, `www/index.html`, `routes/api.php`,
`app/Http/Controllers/Api/{Browse,Business,Push,Redemption,Sync}Controller.php`,
`PortalController@mobile`, `resources/views/portal/home.blade.php` (the rendered view),
`public/sw.js`, `app/Http/Middleware/VerifySyncToken.php`, `app/Console/Commands/SyncPush.php`,
`config/sync.php`, and the iOS shell (`ios/App/App/{AppDelegate.swift,Info.plist,capacitor.config.json,public/index.html}`).

---

## 1. How the Capacitor app loads the web

`capacitor.config.json`: `appId com.locolie.app`, `appName Locolie`, `webDir www`.
The key fact: the native shell does NOT run a local bundle. `server.url` points at a
**remote URL** -

```
"server": { "url": "https://roger-ferulaceous-nonpermissively.ngrok-free.dev/app", "cleartext": false }
```

So the iOS app is a thin WebView that loads the live `/app` page over HTTPS. `webDir: www`
only matters as the bundled fallback shown before the remote loads.

- `www/index.html` (and the identical `ios/App/App/public/index.html`) is a static splash:
  a "Locolie" wordmark, a spinner, and copy "Loading local offers near you...". After 6s with
  no remote takeover it flips to offline state: "Can't reach Locolie right now. Check your
  connection and try again." plus a "Try again" reload button. It is NOT the app - just a
  pre-load shell while the WebView fetches `server.url`.
- `allowNavigation`: `*.ngrok-free.dev`, `locolie.com`, `*.locolie.com`.
- iOS: `contentInset: always`, `backgroundColor #0a0a0a`. SplashScreen 1200ms, no spinner.
  PushNotifications presentationOptions `badge, sound, alert`.
- `package.json` Capacitor plugins installed: `@capacitor/app, browser, geolocation,
  push-notifications, splash-screen, status-bar` (v8). No app framework dep - the "app" is
  Blade + Alpine + inline JS served from Laravel.

### FLAG (broken/fragile): the native app is hard-wired to a personal ngrok tunnel
`server.url` is a free ngrok dev URL (`roger-ferulaceous-nonpermissively.ngrok-free.dev`).
A shipped iOS build pointing at an ephemeral tunnel will white-screen the moment the tunnel
is down (which is the normal state). This must be `https://locolie.com/app` for any real build.
The bundled `ios/App/App/capacitor.config.json` carries the same ngrok URL, so a fresh
`npx cap sync` would re-bake it in.

### FLAG (build hygiene): `www/` is not a real built app
`resources/js/app.js` is literally a single `//` comment. There is no compiled SPA in `www/`
- only `index.html` (the splash). The whole application lives in
`resources/views/portal/home.blade.php` (1270 lines, Alpine `goLocalApp()` + inline `<script>`),
served by Laravel. `npm run build` (Vite) builds CSS/JS for the Blade pages, not the `www`
shell. So "the mobile app is a Capacitor wrapper around the same web build" is true only in
the sense that it loads the same `/app` URL - there is no separate front-end bundle.

---

## 2. What `/app` renders

Route (`routes/web.php:40`): `Route::get('/app', [PortalController::class,'mobile'])->name('app')`.
`/m` 301-redirects to `/app` preserving query (`web.php:42`).

`PortalController@mobile` (`PortalController.php:29`) returns `view('portal.home', [...])` with:
`mockupCount 0`, `mapsKey`, `mapsId`, `vapidKey` (config), `solo => true`, and
`soloRole => $request->query('as')==='business' ? 'business':'shopper'`.

So `/app` reuses the internal team `portal.home` mockup view but in **solo mode**: CSS at
`home.blade.php:269+` (`.solo`/`.gl-solo`) strips the phone-frame chrome and renders ONE
full-bleed responsive app. Default = shopper; `/app?as=business` = the business CRM front.
Both run in the same Alpine component.

The app (`goLocalApp()`, `home.blade.php:811`) provides, all client-side over the JSON API:
- Shopper: category tree browse (parent -> sub -> sub-trade), search, filters (distance,
  rating, offer %, sale type, open-now), "For you" by saved prefs, featured row, business
  profile, Google map (`mapsKey`), favourites, in-app QR scanner, reveal-code redemption with
  a live countdown, and a notifications drawer. State persists in `localStorage`
  (`gl_customer, gl_biz, gl_secret, gl_favs, gl_notifs, gl_theme,` etc.).
- Business (`?as=business`): self-serve signup (Google Places search), publish/remove offers,
  redemption stats, and a verify-code-at-till panel (also drivable by the QR scanner).
- It seeds a "Guest" customer on first load so the app shows immediately (`init`, line 945),
  and includes a theme switcher + brand-name cycler left over from the internal mockup tool.

API base in JS: `fetch('/api'+path, ...)` with header `ngrok-skip-browser-warning: true`
(`home.blade.php:928`) - a tell that this is run through ngrok.

---

## 3. JSON API (`routes/api.php`)

Header comment states the design: stateless, browsing public, retailer actions scoped by a
per-business owner secret, "Sanctum token auth is a thin future add for native iOS." There is
NO Sanctum / `auth:` middleware on any route today - the only guard is `sync.token` on `/sync/*`.

### Browse (public, no auth)
| Method/Route | Controller | Purpose | Request | Response |
|---|---|---|---|---|
| GET `/api/categories` | `BrowseController@categories` | Full category tree (parents->children x3). Falls back to flat list if `Category::supportsHierarchy()` is false (parent_id migration not run). | none | `[{id,name,slug,children[]}]` |
| GET `/api/businesses` | `@businesses` | Live businesses with active offers, ranked (paid placement, offer count, rating). | query `postcode` (outward-code prefix `like`), `category` (matches slug at self/parent/grandparent), `q` (name/description like) | array of `present()` shape (below) |
| GET `/api/businesses/{business:slug}` | `@business` | One business, full detail. | route slug | `present(full:true)` |
| GET `/api/businesses/by-token/{token}` | `@byToken` | Resolve window-sticker `qr_token` -> business (in-app scanner). 404 if none. | route token | `present(full:true)` |

`present()` returns: `id,name,slug,category,category_slug,category_parent(_slug),cat_slugs[]
(ancestor slugs),postcode,lat,lng,hours,rating,reviews_count,image (photos[0]),featured,plan,
distance,offers[{id,title,badge,terms,discount_type,sale_type,remaining}]`. `full` adds
`description,address,phone,website,reviews`.

### Retailer (owner-secret scoped - the only "auth")
| Method/Route | Controller | Purpose | Auth | Notes |
|---|---|---|---|---|
| GET `/api/places/search?q=` | `BusinessController@placesSearch` | Live Google Places search for signup box. | none | `q` min2 max120 |
| POST `/api/businesses` | `@register` | Self-serve signup; if `place_id` given, fills listing from Google Places (name,addr,coords,rating,photo); records `terms_accepted` consent + subscription topics. | none | returns `{business, owner_secret, qr_token}` (201) |
| GET `/api/businesses/secret/{secret}/offers` | `@offers` | List this biz's offers. | secret in URL | `resolve()` 403 if bad |
| POST `/api/businesses/secret/{secret}/offers` | `@storeOffer` | Publish offer (title,badge,terms,sale_type,quantity). | secret in URL | |
| DELETE `/api/businesses/secret/{secret}/offers/{offer}` | `@destroyOffer` | Remove offer; `abort_unless` offer belongs to biz (403). | secret in URL | |
| GET `/api/businesses/secret/{secret}/redemptions` | `@redemptions` | Dashboard counts + last 10 redeemed. | secret in URL | |

`resolve()` = `Business::where('owner_secret',$secret)->firstOr(fn()=>abort(403))`.

### Push
| Method/Route | Controller | Purpose | Request |
|---|---|---|---|
| POST `/api/push/subscribe` | `PushController@subscribe` | Browser web-push (VAPID) subscription. | `endpoint(req), keys[], category_prefs[]` -> `PushService::subscribe` |
| POST `/api/devices/register` | `@registerDevice` | Native iOS/Android device token (APNs/FCM). `DeviceToken::updateOrCreate` on token. | `platform(web|ios|android), token(req), app_version, locale, topics[]`; `user_id` from `$request->user()` (always null - no auth) |
| DELETE `/api/devices/{token}` | `@unregisterDevice` | Delete device token (logout/uninstall). | route token |

### Redemption
| Method/Route | Controller | Purpose | Request | Response |
|---|---|---|---|---|
| POST `/api/offers/{offer}/redeem` | `RedemptionController@redeem` | Customer reveals a code. Aborts if offer not active (422) or sold out (422). Syncs marketing/SMS consent into `Subscription`. | `customer_name,customer_email,customer_phone,marketing_opt_in,sms_opt_in` (all nullable) | `{code,status,expires_at,ttl_seconds,business,badge,offer}` (201) |
| POST `/api/redemptions/verify` | `@verify` | Retailer verifies a 6-char code at till. | `secret(req), code(size:6)` | `{ok,message,offer}` (200/422) |
| GET `/api/redemptions/{code}` | `@show` | Customer polls code status (live countdown). Latest row by code, 404 if none. | route code | `{code,status,expires_at,ttl_seconds}` |

### Sync (token-guarded) - see section 5.

---

## 4. Push registration (the actual wiring)

`subscribePush()` (`home.blade.php:995`) branches:
- If `window.Capacitor?.isNativePlatform?.()` -> `registerNativePush()`.
- Else web-push: `Notification.requestPermission()`, register `/sw.js`,
  `pushManager.subscribe({applicationServerKey: vapidKey})`, POST to `/api/push/subscribe`
  with `{endpoint,keys,category_prefs:this.form.prefs}`.

`registerNativePush()` (line 1016): gets `Capacitor.Plugins.PushNotifications`, binds a
`registration` listener that POSTs `{platform, token, topics:this.prefs}` to
`/api/devices/register`, then `requestPermissions()` + `register()`.

`public/sw.js` (`golocal-v2`): network-first app-shell cache, never caches `/api`, handles
`push` (showNotification) and `notificationclick`.

### FLAG (half-built): native push will not deliver on iOS
- `ios/App/App/AppDelegate.swift` is the stock Capacitor template. It has NO
  `didRegisterForRemoteNotificationsWithDeviceToken` / `didFailToRegister` / `didReceive`
  forwarding to `NotificationCenter`. The `@capacitor/push-notifications` plugin relies on
  those AppDelegate hooks to surface the APNs token to the `registration` JS listener; without
  them, `Push.register()` never fires `registration`, so `/api/devices/register` is never
  called from a real device.
- `ios/App/App/Info.plist` (62 lines) has `NSLocationWhenInUseUsageDescription` but NO
  `aps-environment` entitlement and NO `UIBackgroundModes -> remote-notification`. APNs is not
  enabled for this build.
- There is a `PushService` referenced for web push, but no server-side APNs/FCM sender was in
  scope here; `DeviceToken` rows are stored but the broadcast side is "when the Messaging
  Studio broadcasts" (per the controller docblock) - i.e. not wired in this surface.
- `public/sw.js` still hardcodes the OLD `/m` URL in `install` cache list, `fetch` fallback,
  and `notificationclick` default. `/m` now just 301s to `/app`, so the offline fallback and
  notification-tap landing are a redirect hop (works, but stale).

---

## 5. Local -> production sync

Design (`SyncController` docblock): one-way, idempotent, upsert on natural keys, never deletes
prod-only data. Guarded by `sync.token` middleware.

- `config/sync.php`: `token = env('SYNC_TOKEN')`, `target = env('SYNC_TARGET', 'https://locolie.com')`.
- `VerifySyncToken` (`app/Http/Middleware/VerifySyncToken.php`): 503 if no token configured,
  401 if Bearer token != `config('sync.token')`, compared with `hash_equals` (constant-time). Good.
- Endpoints under `Route::middleware('sync.token')->prefix('sync')`:
  - GET `/api/sync/status` -> `{categories,businesses,offers,images,last_sync}` counts.
  - POST `/api/sync/data` -> upserts categories (match `slug`), businesses (match
    `google_place_id` else `slug`, `Business::withoutEvents`), offers (match `business+title`);
    sets `Cache::forever('sync.last_at')`.
  - POST `/api/sync/image` -> stores one photo under `storage/app/public/biz/`. Path is
    regex-sanitised to `^biz/[A-Za-z0-9._-]+$` (blocks traversal). Good.
- Command: `php artisan sync:push` (`SyncPush.php`) - snapshots remote, builds payload from
  local DB (categories/businesses/offers via fillable, mapping ids to slugs/place_ids), POSTs
  to `{target}/api/sync/data`, then uploads each `biz/*` photo to `/api/sync/image`
  (unless `--skip-images`). `--target` overrides destination.

Assessment: sync is the most complete and the only properly-secured part of the API.

---

## 6. Security / correctness flags (consolidated)

1. **owner_secret travels in the URL path** (`/api/businesses/secret/{secret}/offers`,
   `/redemptions`, etc.). It is a bearer-equivalent capability that lands in web-server access
   logs, ngrok logs, proxy logs, and browser history. The header comment even calls it the
   scoping mechanism. It is also persisted in `localStorage` as `gl_secret` and replayed on
   every `loadBiz()`. Move it to an `Authorization` header or a real Sanctum token. (Verify is
   slightly better - secret is in the POST body, not URL - but it is the same plaintext secret.)
2. **No auth on the whole API except sync.** `register`, `redeem`, `placesSearch`,
   `devices/register` are fully open. `placesSearch` proxies Google Places with only `q`
   validation - an open, unthrottled, billable proxy (no rate limit / no `throttle` middleware
   seen on `routes/api.php`). Abuse = Google bill.
3. **Anyone can sign up any Google business** via POST `/api/businesses` with a `place_id`;
   the response hands back that business's `owner_secret` + `qr_token`. There is no email
   verification or ownership proof, so a stranger can claim a real local business and control
   its offers/redemptions. (Prototype, but worth flagging as a launch blocker.)
4. **Native shell points at an ephemeral ngrok tunnel** (section 1) - app is non-functional
   when the tunnel is down; ships a personal dev URL.
5. **iOS push is not actually wired** (section 4): stock AppDelegate, no APNs entitlement,
   no background mode. JS registration path exists but the OS token never reaches it.
6. **Stale `/m` references** baked into `public/sw.js` (cache list, offline fallback,
   notification landing) after the rename to `/app`.
7. **`www/` is a splash only**; `resources/js/app.js` is empty. The "built web app" is the
   server-rendered Blade `portal.home`. Fine architecturally, but `webDir: www` implies a real
   offline bundle that does not exist - native offline = the 6s "can't reach Locolie" screen.
8. **Prototype data leaks into prod shapes**: `BrowseController` returns a `fakeDistance()`
   pseudo-distance and synthesises sample Google reviews (`reviews()`) when a business has none.
   The app cannot distinguish real vs fabricated reviews.
