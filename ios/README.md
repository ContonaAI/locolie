# Locolie iOS app

A [Capacitor](https://capacitorjs.com) wrapper around the live Locolie web app. The
native shell loads `https://locolie.com/app` in a full-screen `WKWebView` and adds
real native capabilities (push, geolocation, splash, status bar). The website and the
iOS app share **one** backend (the Laravel JSON API) — publish an offer once, it shows
everywhere.

- **Bundle ID:** `com.locolie.app`
- **Display name:** Locolie
- **Loads:** `https://locolie.com/app` (set in `/capacitor.config.json` → `server.url`)
- **Offline fallback:** `/www/index.html` (branded "can't reach Locolie" screen)

## Project layout

```
capacitor.config.json     # appId, appName, server.url, plugin config  (repo root)
www/                      # offline fallback shell                     (repo root)
ios/App/                  # the native Xcode project
  App/Info.plist          # permission strings, display name
  App.xcodeproj           # open THIS in Xcode
```

Native plugins installed: `@capacitor/push-notifications`, `@capacitor/geolocation`,
`@capacitor/app`, `@capacitor/splash-screen`, `@capacitor/status-bar`,
`@capacitor/browser`. (QR scanning is handled by the web app's `html5-qrcode` running
in the WebView — `NSCameraUsageDescription` is set so the camera prompt works.)

## After changing config or web assets

```bash
npx cap sync ios     # copies capacitor.config.json + www into the native project
```

## Run on your own iPhone (free, no paid account)

1. `open ios/App/App.xcodeproj`
2. Plug in your iPhone, trust the Mac.
3. In Xcode: select the **App** target → **Signing & Capabilities** → set **Team** to
   your personal Apple ID (Xcode → Settings → Accounts → add your Apple ID first).
4. Pick your iPhone in the device dropdown, press **▶ Run**.
5. On the phone: Settings → General → VPN & Device Management → trust the developer cert.

> Free Apple ID builds expire after 7 days and only install on devices plugged into
> this Mac. For shareable installs you need the paid account below.

## Distribute before App Store approval — TestFlight

Requires the **Apple Developer Program** ($99/yr, enrol at developer.apple.com).

1. Set a real signing **Team** (your enrolled account) in Xcode.
2. **Product → Archive**, then **Distribute App → App Store Connect → Upload**.
3. In App Store Connect → your app → **TestFlight**:
   - **Internal testers** (your team, up to 100): builds are usable immediately, **no review**.
   - **External testers** (up to 10,000): add testers or enable a **public link**; each
     build gets one quick beta review (usually same-day).
4. Testers install Apple's **TestFlight** app and tap your invite/link.

## Submit to the App Store

1. App Store Connect → **App Store** tab: screenshots, description, keywords, support URL,
   **privacy policy URL** (required — Locolie collects customer data), and the
   App Privacy data-collection questionnaire.
2. Attach the uploaded build → **Submit for Review**. First review is typically 1-3 days.
3. Mitigate Guideline 4.2 ("minimum functionality" — Apple rejects thin web wrappers) by
   keeping native push, geolocation, and camera active so the app offers real native value.

## Native push (APNs) — remaining setup

The client + backend are wired: the web app calls `registerNativePush()` inside the
native shell, which registers with iOS and POSTs the device token to
`POST /api/devices/register` (stored in the `device_tokens` table). To actually
**deliver** pushes you still need:

1. In the Apple Developer account: create an **APNs Auth Key (.p8)**, note the Key ID
   and Team ID.
2. In Xcode → Signing & Capabilities → add the **Push Notifications** capability.
3. Server side: add an APNs provider (e.g. a PHP APNs client) to the push sender so
   `device_tokens` with `platform = ios` receive notifications. Web push (VAPID) already
   works for browsers.
