<?php

namespace App\Http\Controllers;

use App\Services\Messaging\EmailChannel;
use App\Services\Messaging\MessagingService;
use App\Services\Messaging\PushChannel;
use App\Services\Messaging\SmsChannel;

/**
 * Read-only "Go-live / Setup" status page for the internal team portal.
 *
 * Reports which messaging + payment integrations are configured (live) vs still
 * running in demo mode, and the exact env vars + steps to switch each one on.
 *
 * This controller ONLY READS config. It never prints secret values - for each
 * credential it reports a boolean ("is this set") so the page is safe to share
 * with the team without leaking keys.
 */
class SetupController extends Controller
{
    public function index(MessagingService $messaging)
    {
        /** @var EmailChannel $email */
        $email = $messaging->channel('email');
        /** @var SmsChannel $sms */
        $sms = $messaging->channel('sms');
        /** @var PushChannel $push */
        $push = $messaging->channel('push');

        $groups = [
            'email' => $this->emailGroup($email),
            'sms' => $this->smsGroup($sms),
            'push' => $this->pushGroup($push),
            'billing' => $this->billingGroup(),
            'infrastructure' => $this->infrastructureGroup(),
        ];

        // Flat list of every integration item for the launch checklist.
        $items = collect($groups)
            ->flatMap(fn ($group) => $group['items'])
            ->values()
            ->all();

        return view('portal.setup', [
            'groups' => $groups,
            'items' => $items,
            'overview' => $messaging->overview(),
            'live_count' => collect($items)->where('status', 'live')->count(),
            'total_count' => count($items),
        ]);
    }

    /* ── Email ──────────────────────────────────────────────────────────── */

    protected function emailGroup(EmailChannel $email): array
    {
        $gmail = filled(config('services.google.gmail_refresh_token'));
        $resend = filled(config('services.resend.key'));
        $postmark = filled(config('services.postmark.key'));
        $ses = filled(config('services.ses.key')) && filled(config('services.ses.secret'));
        $default = (string) config('mail.default');
        $realTransport = filled($default) && ! in_array($default, ['log', 'array'], true);

        $items = [
            $this->item(
                key: 'email_gmail',
                name: 'Email via Gmail / Google Workspace',
                status: $gmail ? 'live' : 'demo',
                what: 'Sends branded campaign + transactional email from a Google account using OAuth, so mail comes from your own domain.',
                vars: [
                    'GOOGLE_GMAIL_CLIENT_ID' => 'OAuth client id from Google Cloud Console',
                    'GOOGLE_GMAIL_CLIENT_SECRET' => 'OAuth client secret',
                    'GOOGLE_GMAIL_REFRESH_TOKEN' => 'Refresh token from the consent flow (this is what flips email live)',
                    'GOOGLE_GMAIL_FROM' => 'The from address, e.g. hello@locolie.com',
                ],
                steps: [
                    'In Google Cloud Console create an OAuth 2.0 Client (type: Web application) and enable the Gmail API.',
                    'Add the client id + secret to the env, then run the consent flow to obtain a refresh token.',
                    'Paste the refresh token and from address into the env and clear config cache.',
                ],
                doc: 'https://console.cloud.google.com/apis/credentials',
            ),
            $this->item(
                key: 'email_resend',
                name: 'Email via Resend',
                status: $resend ? 'live' : 'demo',
                what: 'Modern transactional email API. Set the key and the default mailer to "resend" to deliver via Resend.',
                vars: [
                    'RESEND_API_KEY' => 'API key from the Resend dashboard',
                    'MAIL_MAILER' => 'Set to "resend" to make it the default transport',
                    'MAIL_FROM_ADDRESS' => 'Verified sender address',
                ],
                steps: [
                    'Create a Resend account and verify your sending domain.',
                    'Generate an API key and add it as RESEND_API_KEY.',
                    'Set MAIL_MAILER=resend (or rely on the Resend key alone) and clear config cache.',
                ],
                doc: 'https://resend.com/docs',
            ),
            $this->item(
                key: 'email_smtp',
                name: 'Email via SMTP / SES / Postmark',
                status: ($realTransport || $postmark || $ses) ? 'live' : 'demo',
                what: 'Any standard SMTP host or supported transport (Mailgun, Postmark, SES relay, your own server) set as the default mailer.',
                vars: [
                    'MAIL_MAILER' => 'smtp, ses, postmark - anything other than log/array',
                    'MAIL_HOST' => 'SMTP host',
                    'MAIL_PORT' => 'SMTP port (e.g. 587)',
                    'MAIL_USERNAME' => 'SMTP username',
                    'MAIL_PASSWORD' => 'SMTP password',
                    'POSTMARK_API_KEY' => 'Only if using the postmark transport',
                    'AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY' => 'Only if using the ses transport',
                ],
                steps: [
                    'Pick a transport and set MAIL_MAILER to it (currently "'.($default ?: 'log').'").',
                    'Fill the matching host/credentials env vars for that transport.',
                    'Set MAIL_FROM_ADDRESS and clear config cache.',
                ],
                doc: 'https://laravel.com/docs/mail',
            ),
        ];

        return [
            'label' => 'Email',
            'blurb' => 'Branded, responsive campaign and transactional email. Any one of the options below makes email live.',
            'channel_live' => $email->connected(),
            'channel_provider' => $email->activeProvider(),
            'items' => $items,
        ];
    }

    /* ── SMS ────────────────────────────────────────────────────────────── */

    protected function smsGroup(SmsChannel $sms): array
    {
        $readiness = $sms->readiness();

        $catalogue = [
            'twilio' => [
                'name' => 'SMS via Twilio',
                'what' => 'Global SMS leader, wired for real HTTP delivery. The recommended option.',
                'vars' => [
                    'TWILIO_ACCOUNT_SID' => 'Account SID from the Twilio console',
                    'TWILIO_AUTH_TOKEN' => 'Auth token from the Twilio console',
                    'TWILIO_FROM' => 'A Twilio phone number or messaging service sender',
                ],
                'steps' => [
                    'Create a Twilio account and buy / verify a sending number.',
                    'Copy the Account SID and Auth Token from the console dashboard.',
                    'Add all three env vars and clear config cache - delivery goes live immediately.',
                ],
                'doc' => 'https://www.twilio.com/docs/sms',
            ],
            'vonage' => [
                'name' => 'SMS via Vonage (Nexmo)',
                'what' => 'Competitive UK + EU routes. Credentials enable it (live HTTP send not yet wired - counts as sent).',
                'vars' => [
                    'VONAGE_API_KEY' => 'API key from the Vonage dashboard',
                    'VONAGE_API_SECRET' => 'API secret',
                    'VONAGE_FROM' => 'Sender id or number',
                ],
                'steps' => [
                    'Create a Vonage account and note the API key + secret.',
                    'Add all three env vars and clear config cache.',
                ],
                'doc' => 'https://developer.vonage.com/messaging/sms/overview',
            ],
            'messagebird' => [
                'name' => 'SMS via MessageBird / Bird',
                'what' => 'Strong European coverage. Credentials enable it (live HTTP send not yet wired - counts as sent).',
                'vars' => [
                    'MESSAGEBIRD_ACCESS_KEY' => 'Access key from the Bird dashboard',
                    'MESSAGEBIRD_ORIGINATOR' => 'Sender id or number',
                ],
                'steps' => [
                    'Create a Bird (MessageBird) account and generate a live access key.',
                    'Add both env vars and clear config cache.',
                ],
                'doc' => 'https://docs.bird.com/',
            ],
            'plivo' => [
                'name' => 'SMS via Plivo',
                'what' => 'Low-cost high-volume SMS. Credentials enable it (live HTTP send not yet wired - counts as sent).',
                'vars' => [
                    'PLIVO_AUTH_ID' => 'Auth ID from the Plivo console',
                    'PLIVO_AUTH_TOKEN' => 'Auth token',
                    'PLIVO_FROM' => 'A Plivo number',
                ],
                'steps' => [
                    'Create a Plivo account and buy a number.',
                    'Add all three env vars and clear config cache.',
                ],
                'doc' => 'https://www.plivo.com/docs/sms/',
            ],
            'aws_sns' => [
                'name' => 'SMS via AWS SNS',
                'what' => 'Pay-as-you-go SMS on AWS. Credentials enable it (live HTTP send not yet wired - counts as sent).',
                'vars' => [
                    'AWS_ACCESS_KEY_ID' => 'AWS access key (shared with SES)',
                    'AWS_SECRET_ACCESS_KEY' => 'AWS secret key',
                    'AWS_DEFAULT_REGION' => 'Region for SNS (e.g. eu-west-1)',
                ],
                'steps' => [
                    'Create an IAM user with SNS publish permissions.',
                    'Add the access key, secret and region and clear config cache.',
                ],
                'doc' => 'https://docs.aws.amazon.com/sns/latest/dg/sns-mobile-phone-number-as-subscriber.html',
            ],
            'clicksend' => [
                'name' => 'SMS via ClickSend',
                'what' => 'UK-friendly, simple per-message billing. Credentials enable it (live HTTP send not yet wired - counts as sent).',
                'vars' => [
                    'CLICKSEND_USERNAME' => 'ClickSend account username',
                    'CLICKSEND_API_KEY' => 'API key from ClickSend',
                    'CLICKSEND_FROM' => 'Sender id or number',
                ],
                'steps' => [
                    'Create a ClickSend account and find your API key under API Credentials.',
                    'Add all three env vars and clear config cache.',
                ],
                'doc' => 'https://developers.clicksend.com/docs/rest/v3/',
            ],
        ];

        $items = [];
        foreach ($catalogue as $slug => $meta) {
            $items[] = $this->item(
                key: 'sms_'.$slug,
                name: $meta['name'],
                status: ($readiness[$slug] ?? false) ? 'live' : 'demo',
                what: $meta['what'],
                vars: $meta['vars'],
                steps: $meta['steps'],
                doc: $meta['doc'],
            );
        }

        return [
            'label' => 'SMS',
            'blurb' => 'Text offers straight to a phone. Configure any one provider to go live; Twilio is fully wired for real delivery.',
            'channel_live' => $sms->connected(),
            'channel_provider' => $sms->activeProvider(),
            'items' => $items,
        ];
    }

    /* ── Push ───────────────────────────────────────────────────────────── */

    protected function pushGroup(PushChannel $push): array
    {
        $vapid = filled(config('services.vapid.public')) && filled(config('services.vapid.private'));
        $minishlinkExists = class_exists(\Minishlink\WebPush\WebPush::class);
        $webLive = $vapid && $minishlinkExists;

        $fcm = filled(config('services.fcm.project_id')) && filled(config('services.fcm.credentials'));
        $apns = filled(config('services.apns.key_id'))
            && filled(config('services.apns.team_id'))
            && filled(config('services.apns.auth_key'));

        $items = [
            $this->item(
                key: 'push_web',
                name: 'Web Push (VAPID)',
                status: $webLive ? 'live' : 'demo',
                what: 'Browser notifications, no third party. Needs a self-generated VAPID keypair and the web-push package installed.',
                vars: [
                    'VAPID_PUBLIC_KEY' => 'Public key of the VAPID keypair',
                    'VAPID_PRIVATE_KEY' => 'Private key of the VAPID keypair',
                    'VAPID_SUBJECT' => 'mailto: or URL identifying the sender (defaults to APP_URL)',
                ],
                steps: [
                    'Generate a VAPID keypair (e.g. with the web-push CLI or an online generator).',
                    'Ensure the minishlink/web-push composer package is installed.',
                    'Add the public + private keys to the env and clear config cache.',
                ],
                doc: 'https://github.com/web-push-libs/web-push-php',
                note: $minishlinkExists ? null : 'The minishlink/web-push package is not installed yet - install it before web push can go live.',
            ),
            $this->item(
                key: 'push_fcm',
                name: 'Firebase Cloud Messaging (Android)',
                status: $fcm ? 'live' : 'demo',
                what: 'Native Android (and web fallback) push via Firebase. Live HTTP send is stubbed pending the mobile app.',
                vars: [
                    'FCM_PROJECT_ID' => 'Firebase project id',
                    'FCM_CREDENTIALS' => 'Path to the service-account JSON file',
                ],
                steps: [
                    'In the Firebase console create a project and generate a service-account key (JSON).',
                    'Place the JSON on the server and set FCM_CREDENTIALS to its path.',
                    'Set FCM_PROJECT_ID and clear config cache.',
                ],
                doc: 'https://firebase.google.com/docs/cloud-messaging',
            ),
            $this->item(
                key: 'push_apns',
                name: 'Apple Push (APNs, iOS)',
                status: $apns ? 'live' : 'demo',
                what: 'Native iOS push. Live HTTP/2 + JWT send is stubbed pending the mobile app.',
                vars: [
                    'APNS_KEY_ID' => 'Key id of the APNs auth key',
                    'APNS_TEAM_ID' => 'Apple developer team id',
                    'APNS_BUNDLE_ID' => 'App bundle identifier',
                    'APNS_AUTH_KEY' => 'Path to the .p8 auth key file',
                ],
                steps: [
                    'In the Apple Developer portal create an APNs auth key (.p8) and note its key id.',
                    'Place the .p8 on the server and set APNS_AUTH_KEY to its path.',
                    'Set the key id, team id and bundle id, then clear config cache.',
                ],
                doc: 'https://developer.apple.com/documentation/usernotifications',
            ),
        ];

        return [
            'label' => 'Push',
            'blurb' => 'Notifications to browsers and the future iOS + Android apps. Web push can go live today; FCM / APNs land with the apps.',
            'channel_live' => $push->connected(),
            'channel_provider' => $push->activeProvider(),
            'items' => $items,
        ];
    }

    /* ── Billing ────────────────────────────────────────────────────────── */

    protected function billingGroup(): array
    {
        $stripe = filled(config('services.stripe.key')) && filled(config('services.stripe.secret'));

        $items = [
            $this->item(
                key: 'stripe',
                name: 'Stripe billing',
                status: $stripe ? 'live' : 'demo',
                what: 'Card payments for paid plans (Featured / Premium listings). Scaffolded - paste keys to go live.',
                vars: [
                    'STRIPE_KEY' => 'Publishable key from the Stripe dashboard',
                    'STRIPE_SECRET' => 'Secret key from the Stripe dashboard',
                    'STRIPE_PRICE_FEATURED' => 'Price id for the Featured plan',
                    'STRIPE_PRICE_PREMIUM' => 'Price id for the Premium plan',
                ],
                steps: [
                    'Create a Stripe account and grab the publishable + secret keys (test or live).',
                    'Create the Featured and Premium products/prices and copy their price ids.',
                    'Add all four env vars and clear config cache.',
                ],
                doc: 'https://dashboard.stripe.com/apikeys',
            ),
        ];

        return [
            'label' => 'Billing',
            'blurb' => 'Take payment for paid plans. Set the Stripe keys to go live.',
            'channel_live' => $stripe,
            'channel_provider' => $stripe ? 'stripe' : null,
            'items' => $items,
        ];
    }

    /* ── Infrastructure ─────────────────────────────────────────────────── */

    protected function infrastructureGroup(): array
    {
        $queue = (string) config('queue.default');
        $needsWorker = ! in_array($queue, ['sync', ''], true);

        $items = [
            $this->item(
                key: 'queue',
                name: 'Queue worker',
                status: $queue === 'sync' ? 'demo' : ($needsWorker ? 'attention' : 'demo'),
                what: 'Background jobs (sending campaigns, etc). Driver is currently "'.($queue ?: 'sync').'".'
                    .($queue === 'database' ? ' A long-running worker must be running for jobs to process.' : ''),
                vars: [
                    'QUEUE_CONNECTION' => 'sync (inline, no worker) / database / redis',
                ],
                steps: $needsWorker ? [
                    'Run the migrations so the jobs table exists (if using the database driver).',
                    'Start a worker process: php artisan queue:work --tries=3.',
                    'Keep it alive with a supervisor (systemd, Supervisor, or the host process manager).',
                ] : [
                    'Driver is "sync": jobs run inline on the request, no worker needed.',
                    'For production throughput switch QUEUE_CONNECTION to database or redis and run a worker.',
                ],
                doc: 'https://laravel.com/docs/queues',
            ),
        ];

        return [
            'label' => 'Infrastructure',
            'blurb' => 'The plumbing that makes delivery reliable in production.',
            'channel_live' => ! $needsWorker ? false : false,
            'channel_provider' => null,
            'items' => $items,
        ];
    }

    /* ── Helper ─────────────────────────────────────────────────────────── */

    /**
     * Build one integration item. $vars is [ENV_VAR => one-line description].
     * Status is 'live', 'demo' or 'attention' (needs an operational action).
     */
    protected function item(
        string $key,
        string $name,
        string $status,
        string $what,
        array $vars,
        array $steps,
        ?string $doc = null,
        ?string $note = null,
    ): array {
        return [
            'key' => $key,
            'name' => $name,
            'status' => $status,
            'what' => $what,
            'vars' => $vars,
            'steps' => $steps,
            'doc' => $doc,
            'note' => $note,
        ];
    }
}
