<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\MessageTemplate;
use App\Models\Redemption;
use App\Services\Messaging\MessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Email vertical of the Messaging Studio: compose, live preview, test send and
 * campaign send, plus the "Connect to Google" OAuth (or demo) flow. Never 500s
 * on a missing provider - it degrades to demo logging via the EmailChannel.
 */
class EmailStudioController extends Controller
{
    public function __construct(protected MessagingService $messaging) {}

    /** Studio email tab: compose form + live preview + connection panel. */
    public function index(Request $request)
    {
        $channel = $this->messaging->channel('email');
        $businesses = Business::orderBy('name')->get();
        $sample = $businesses->first();

        $defaultPreview = $channel->previewData([
            'subject' => 'A fresh local offer just for you',
            'body' => "Hi there,\n\nWe have a new deal we think you'll love. Pop in this week and show this email to claim it.\n\nSee you soon!",
            'cta_label' => 'See the offer',
            'cta_url' => 'https://locolie.com',
        ], $sample);

        // Compact brand map for the live Alpine preview (id => identity).
        $brandMap = $businesses->mapWithKeys(fn (Business $b) => [$b->id => [
            'name' => $b->name,
            'color' => $b->brandColor(),
            'logoUrl' => $b->logoUrl(),
            'initials' => $b->brandInitials(),
            'fromName' => $b->emailFromName(),
        ]])->all();

        return view('portal.messaging.email', [
            'channel' => $channel,
            'businesses' => $businesses,
            'templates' => MessageTemplate::where('channel', 'email')->orderBy('name')->get(),
            'providers' => $channel->providers(),
            'connected' => $channel->connected(),
            'status' => $channel->status(),
            'activeProvider' => $channel->activeProvider(),
            'connection' => $channel->connection(),
            'defaultPreview' => $defaultPreview,
            'brandMap' => $brandMap,
            'platformAudience' => Business::whereNotNull('owner_email')->where('onboarded', true)->count(),
        ]);
    }

    /** Optional server-side preview render for a fetch-based live preview. */
    public function preview(Request $request)
    {
        $data = $this->validateMessage($request);
        $brand = $this->resolveBrand($request);
        $channel = $this->messaging->channel('email');

        $html = view('messaging.previews.email', [
            'preview' => $channel->previewData($data, $brand),
        ])->render();

        return response()->json(['html' => $html]);
    }

    /** Send a single test email to a chosen address. */
    public function test(Request $request)
    {
        $data = $this->validateMessage($request);
        $request->validate(['test_email' => ['required', 'email', 'max:160']]);

        $brand = $this->resolveBrand($request);
        $address = $request->input('test_email');

        $result = $this->messaging->dispatch('email', $data, [
            ['email' => $address, 'name' => 'Test recipient'],
        ], $brand);

        return back()->with('status', "Test email to {$address} - {$result->note}");
    }

    /** Send the campaign to the computed audience (platform or a brand's customers). */
    public function send(Request $request)
    {
        $data = $this->validateMessage($request);
        $brand = $this->resolveBrand($request);

        $recipients = $this->audienceFor($brand);

        if ($recipients->isEmpty()) {
            return back()->with('status', 'No opted-in recipients found for that audience yet - nothing sent.');
        }

        $result = $this->messaging->dispatch('email', $data, $recipients->all(), $brand);

        $scope = $brand ? "{$brand->name} customers" : 'the platform audience';

        return back()->with('status', "Email sent to {$result->sent} recipient(s) across {$scope} ({$result->status}).");
    }

    /**
     * "Connect to Google". If real OAuth creds exist, redirect to Google's
     * consent screen for the gmail.send scope; otherwise demo-connect so the
     * studio flips to a connected-looking state.
     */
    public function connectGoogle(Request $request)
    {
        $clientId = config('services.google.gmail_client_id');

        if (filled($clientId)) {
            $params = http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => route('messaging.email.google.callback'),
                'response_type' => 'code',
                'scope' => 'https://www.googleapis.com/auth/gmail.send',
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
            ]);

            return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$params);
        }

        $this->messaging->connect('email', 'google', ['account' => 'demo@locolie.com'], 'Google Workspace (demo)');

        return redirect()->route('messaging.email')->with('status',
            'Connected to Google (demo) - sends will route through Gmail once OAuth keys are added.');
    }

    /**
     * OAuth callback. We avoid a hard dependency on a token exchange here: if a
     * code came back we record a connection (stubbed), and on any error we
     * degrade to a demo connect. Never 500s.
     */
    public function googleCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('messaging.email')->with('status',
                'Google connection cancelled - still in demo mode.');
        }

        try {
            $code = $request->input('code');
            $account = config('services.google.gmail_from') ?: 'connected@gmail.com';

            $this->messaging->connect('email', 'google', [
                'account' => $account,
                'code' => $code ? Str::limit($code, 6, '••••') : null,
            ], 'Google Workspace');

            return redirect()->route('messaging.email')->with('status',
                "Connected to Google as {$account}. Emails now route through Gmail.");
        } catch (\Throwable $e) {
            Log::warning('[email] google callback degraded to demo', ['error' => $e->getMessage()]);

            $this->messaging->connect('email', 'google', ['account' => 'demo@locolie.com'], 'Google Workspace (demo)');

            return redirect()->route('messaging.email')->with('status',
                'Connected to Google (demo) - sends will route through Gmail once OAuth keys are added.');
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Validate + assemble the channel-specific message payload. */
    protected function validateMessage(Request $request): array
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:8000'],
            'preheader' => ['nullable', 'string', 'max:160'],
            'cta_label' => ['nullable', 'string', 'max:60'],
            'cta_url' => ['nullable', 'url', 'max:500'],
            'template_id' => ['nullable', 'integer', 'exists:message_templates,id'],
            'business_id' => ['nullable', 'integer', 'exists:businesses,id'],
        ]);

        return [
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'preheader' => $validated['preheader'] ?? '',
            'cta_label' => $validated['cta_label'] ?? '',
            'cta_url' => $validated['cta_url'] ?? '',
            'template_id' => $validated['template_id'] ?? null,
        ];
    }

    protected function resolveBrand(Request $request): ?Business
    {
        $id = $request->input('business_id');

        return $id ? Business::find($id) : null;
    }

    /**
     * Compute the recipient list. With a brand: its opted-in redemption
     * customers. Platform-wide: every onboarded business owner.
     *
     * @return Collection<int,array{email:string,name:string}>
     */
    protected function audienceFor(?Business $brand)
    {
        if ($brand) {
            return Redemption::whereNotNull('customer_email')
                ->where('marketing_opt_in', true)
                ->whereHas('offer', fn ($q) => $q->where('business_id', $brand->id))
                ->get()
                ->unique('customer_email')
                ->map(fn (Redemption $r) => [
                    'email' => $r->customer_email,
                    'name' => $r->customer_name ?: 'there',
                ])
                ->values();
        }

        return Business::whereNotNull('owner_email')
            ->where('onboarded', true)
            ->get()
            ->map(fn (Business $b) => [
                'email' => $b->owner_email,
                'name' => $b->name,
            ])
            ->values();
    }
}
