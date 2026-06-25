# locolie - Go-live / Setup runbook

locolie ships in "demo-first" mode. With no credentials configured, every
messaging channel and the billing flow still work end to end: messages are
logged and counted, payments are scaffolded. Nothing is broken and nothing is
sent for real. The moment you add the credentials below, the same code paths go
live with no code change.

This document is the practical runbook for switching each integration on. The
internal portal mirrors this at the Setup page (read-only status + the same
steps). It only ever reports whether a credential is set, never its value.

After editing the environment, always run:

```
php artisan config:clear
```

## Prerequisites

- PHP + Composer installed and `composer install` run.
- Node installed and assets built: `npm run build` (see Assets below).
- A reachable `.env` (copy from `.env.example`). Set `APP_URL` to the real URL.
- For anything that sends in the background, a queue worker (see Queue worker).

---

## Email

Email goes live as soon as any one of the following is configured. Order of
preference: Gmail OAuth, then Resend, then a generic SMTP / SES / Postmark
transport.

### Email via Gmail / Google Workspace (recommended)

Sends from your own Google account / domain via OAuth.

Env vars:

```
GOOGLE_GMAIL_CLIENT_ID=
GOOGLE_GMAIL_CLIENT_SECRET=
GOOGLE_GMAIL_REFRESH_TOKEN=
GOOGLE_GMAIL_FROM=hello@locolie.com
```

Steps:

1. In the Google Cloud Console (https://console.cloud.google.com/apis/credentials)
   create an OAuth 2.0 Client (type: Web application) and enable the Gmail API.
2. Add the client id + secret to the env.
3. Run the OAuth consent flow once to obtain a refresh token.
4. Paste `GOOGLE_GMAIL_REFRESH_TOKEN` and `GOOGLE_GMAIL_FROM`. The refresh token
   is what actually flips email live.

### Email via SMTP / Resend

Resend (https://resend.com/docs) is the simplest API option:

```
RESEND_API_KEY=
MAIL_MAILER=resend
MAIL_FROM_ADDRESS=hello@locolie.com
```

1. Create a Resend account and verify your sending domain.
2. Generate an API key, set `RESEND_API_KEY`.
3. Set `MAIL_MAILER=resend`.

For any other SMTP host (Mailgun, your own server) or a supported transport
(SES, Postmark):

```
MAIL_MAILER=smtp            # or ses, postmark - anything except log/array
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=hello@locolie.com

# Only if MAIL_MAILER=postmark
POSTMARK_API_KEY=

# Only if MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=eu-west-1
```

The default mailer is `log` out of the box (that is the demo mode). Setting it
to anything other than `log` or `array` makes email live.

---

## SMS

Configure any one provider to go live. Twilio is fully wired for real HTTP
delivery; the other providers are credential-gated and currently count sends as
delivered (live HTTP send drops in per provider later).

### Twilio (recommended, fully wired)

```
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_FROM=+44...
```

1. Create a Twilio account and buy / verify a sending number.
2. Copy the Account SID + Auth Token from the console dashboard.
3. Add all three env vars. Delivery is live immediately.

Docs: https://www.twilio.com/docs/sms

### Other providers

Each is enabled by setting its credentials:

```
# Vonage (Nexmo)
VONAGE_API_KEY=
VONAGE_API_SECRET=
VONAGE_FROM=

# MessageBird / Bird
MESSAGEBIRD_ACCESS_KEY=
MESSAGEBIRD_ORIGINATOR=

# Plivo
PLIVO_AUTH_ID=
PLIVO_AUTH_TOKEN=
PLIVO_FROM=

# AWS SNS (shares the AWS keys with SES)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=eu-west-1

# ClickSend
CLICKSEND_USERNAME=
CLICKSEND_API_KEY=
CLICKSEND_FROM=
```

---

## Push

### Web Push (VAPID) - can go live today

Browser notifications, no third party. Needs a self-generated keypair and the
`minishlink/web-push` composer package installed.

```
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:hello@locolie.com
```

1. Generate a VAPID keypair (web-push CLI or an online generator).
2. Confirm `minishlink/web-push` is installed (`composer require minishlink/web-push`).
3. Add the public + private keys.

Docs: https://github.com/web-push-libs/web-push-php

### Firebase Cloud Messaging (Android) - lands with the mobile app

```
FCM_PROJECT_ID=
FCM_CREDENTIALS=/path/to/service-account.json
```

1. In the Firebase console create a project and generate a service-account key.
2. Place the JSON on the server, point `FCM_CREDENTIALS` at it.
3. Set `FCM_PROJECT_ID`.

Docs: https://firebase.google.com/docs/cloud-messaging

### Apple Push (APNs, iOS) - lands with the mobile app

```
APNS_KEY_ID=
APNS_TEAM_ID=
APNS_BUNDLE_ID=
APNS_AUTH_KEY=/path/to/AuthKey.p8
```

1. In the Apple Developer portal create an APNs auth key (.p8), note its key id.
2. Place the .p8 on the server, point `APNS_AUTH_KEY` at it.
3. Set key id, team id and bundle id.

Docs: https://developer.apple.com/documentation/usernotifications

---

## Stripe billing

Card payments for the paid plans (Featured / Premium listings). Scaffolded -
paste keys to go live.

```
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_PRICE_FEATURED=
STRIPE_PRICE_PREMIUM=
```

1. Create a Stripe account and grab the publishable + secret keys (test or live).
2. Create the Featured and Premium products/prices, copy their price ids.
3. Add all four env vars.

Docs: https://dashboard.stripe.com/apikeys

---

## Queue worker

Background jobs (sending campaigns, etc.) run on the queue. The default
connection is `database`, which needs a long-running worker for jobs to process.

```
QUEUE_CONNECTION=database    # or redis; "sync" runs inline with no worker
```

1. If using `database`, run migrations so the jobs table exists: `php artisan migrate`.
2. Start a worker: `php artisan queue:work --tries=3`.
3. Keep it alive with a process manager (systemd, Supervisor, or the host's
   process manager). Restart it on each deploy: `php artisan queue:restart`.

For a quick demo you can set `QUEUE_CONNECTION=sync` to run jobs inline with no
worker.

Docs: https://laravel.com/docs/queues

---

## Assets

The front end is built with Vite. Build once per deploy:

```
npm install
npm run build
```

For local development: `npm run dev`.

---

## Going live checklist

- [ ] `APP_URL` set to the real domain, `APP_ENV=production`, `APP_DEBUG=false`.
- [ ] Email: one of Gmail OAuth / Resend / SMTP configured.
- [ ] SMS: at least Twilio configured (or another provider as needed).
- [ ] Push: VAPID keys set (web push). FCM / APNs when the apps ship.
- [ ] Stripe keys + price ids set.
- [ ] Queue worker running and supervised.
- [ ] `npm run build` run and assets deployed.
- [ ] `php artisan config:clear` run after the final env edit.
