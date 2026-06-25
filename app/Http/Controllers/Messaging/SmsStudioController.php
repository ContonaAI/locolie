<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Redemption;
use App\Services\Messaging\MessagingService;
use App\Services\Messaging\SmsChannel;
use Illuminate\Http\Request;

/**
 * SMS vertical of the Messaging Studio: compose, live phone-mockup preview,
 * test send and campaign send to opted-in redemption phones. Degrades to demo
 * (logged + counted) whenever no real provider is configured - never 500s.
 */
class SmsStudioController extends Controller
{
    public function __construct(protected MessagingService $messaging) {}

    /** Compose studio: brands, channel state, default preview, audience. */
    public function index(Request $request)
    {
        /** @var SmsChannel $channel */
        $channel = $this->messaging->channel('sms');
        $businesses = Business::orderBy('name')->get();

        $defaultMessage = ['body' => '2 for 1 cocktails tonight at The Anchor. Show this text at the bar.'];

        return view('portal.messaging.sms', [
            'channel' => $channel,
            'businesses' => $businesses,
            'providers' => $channel->providers(),
            'readiness' => $channel->readiness(),
            'activeProvider' => $channel->activeProvider(),
            'connection' => $channel->connection(),
            'defaultPreview' => $channel->previewData($defaultMessage, $businesses->first()),
            'audienceCount' => $this->audienceQuery()->count(),
        ]);
    }

    /** Server-rendered preview partial for the live mockup / AJAX refresh. */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'url' => ['nullable', 'string', 'max:300'],
            'business_id' => ['nullable', 'integer', 'exists:businesses,id'],
        ]);

        /** @var SmsChannel $channel */
        $channel = $this->messaging->channel('sms');
        $brand = ! empty($data['business_id']) ? Business::find($data['business_id']) : null;

        $preview = $channel->previewData([
            'body' => $data['body'] ?? '',
            'url' => $data['url'] ?? '',
        ], $brand);

        return response()->json([
            'html' => view('messaging.previews.sms', ['preview' => $preview])->render(),
            'preview' => $preview,
        ]);
    }

    /** Send a single test SMS to one phone number. */
    public function test(Request $request)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'url' => ['nullable', 'string', 'max:300'],
            'phone' => ['required', 'string', 'max:32'],
            'business_id' => ['nullable', 'integer', 'exists:businesses,id'],
        ]);

        $brand = ! empty($data['business_id']) ? Business::find($data['business_id']) : null;
        $message = ['body' => $data['body'], 'url' => $data['url'] ?? null];

        $result = $this->messaging->dispatch('sms', $message, [
            ['phone' => $data['phone'], 'name' => 'Test'],
        ], $brand);

        return back()->with('status', "Test SMS to {$data['phone']} ({$result->status}). {$result->note}");
    }

    /** Send a campaign to every opted-in redemption phone (optionally scoped to a brand). */
    public function send(Request $request)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'url' => ['nullable', 'string', 'max:300'],
            'business_id' => ['nullable', 'integer', 'exists:businesses,id'],
        ]);

        $brand = ! empty($data['business_id']) ? Business::find($data['business_id']) : null;
        $message = ['body' => $data['body'], 'url' => $data['url'] ?? null];

        // Carry the email too so the consent layer can drop anyone who has
        // unsubscribed from SMS alerts, on top of the redemption opt-in flag.
        $recipients = $this->audienceQuery($data['business_id'] ?? null)
            ->get(['customer_phone', 'customer_name', 'customer_email'])
            ->map(fn ($r) => array_filter([
                'phone' => $r->customer_phone,
                'name' => $r->customer_name ?: 'Customer',
                'email' => $r->customer_email,
            ]))
            ->all();

        $result = $this->messaging->dispatch('sms', $message, $recipients, $brand, [
            'scheduled_at' => $request->input('scheduled_at'),
        ]);

        return back()->with('status', "SMS to opted-in phones: {$result->note}");
    }

    /**
     * Opted-in SMS audience query. When scoped to a business, only redemptions
     * for that brand's offers are included.
     */
    protected function audienceQuery(?int $businessId = null)
    {
        $q = Redemption::whereNotNull('customer_phone')->where('sms_opt_in', true);

        if ($businessId) {
            $q->whereHas('offer', fn ($o) => $o->where('business_id', $businessId));
        }

        return $q;
    }
}
