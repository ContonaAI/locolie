<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Campaign;
use App\Models\DeviceToken;
use App\Models\MessageTemplate;
use App\Models\PushSubscription;
use App\Models\Redemption;
use App\Services\Messaging\MessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Messaging Studio shell: the channel overview, per-brand identity (logo,
 * colour, sender names) and the generic connect/disconnect flow for providers.
 * Channel-specific compose/preview/send lives in the per-channel controllers
 * under App\Http\Controllers\Messaging.
 */
class MessagingController extends Controller
{
    public function __construct(protected MessagingService $messaging) {}

    /** Studio home: connection state per channel, brands, recent activity. */
    public function studio(Request $request)
    {
        return view('portal.messaging.studio', [
            'overview' => $this->messaging->overview(),
            'channels' => config('messaging.channels'),
            'businesses' => Business::where('onboarded', true)->orderBy('name')->get(),
            'campaigns' => Campaign::with('business')->latest('id')->limit(12)->get(),
            'templates' => MessageTemplate::latest('id')->get(),
            'stats' => [
                'email_audience' => Business::whereNotNull('owner_email')->count(),
                'sms_audience' => Redemption::whereNotNull('customer_phone')->where('sms_opt_in', true)->count(),
                'push_audience' => PushSubscription::count() + DeviceToken::count(),
                'sent' => Campaign::sum('sent_count'),
            ],
        ]);
    }

    /** Save a brand's identity used to make every message bespoke. */
    public function saveBrand(Request $request, Business $business)
    {
        $data = $request->validate([
            'brand_color' => ['nullable', 'string', 'regex:/^#?[0-9A-Fa-f]{3,8}$/'],
            'email_from_name' => ['nullable', 'string', 'max:80'],
            'reply_to_email' => ['nullable', 'email', 'max:160'],
            'sms_sender_id' => ['nullable', 'string', 'max:11', 'regex:/^[A-Za-z0-9 ]+$/'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        if (! empty($data['brand_color']) && ! str_starts_with($data['brand_color'], '#')) {
            $data['brand_color'] = '#'.$data['brand_color'];
        }

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store("brands/{$business->id}", 'public');
        }
        unset($data['logo']);

        $business->update(array_filter($data, fn ($v) => $v !== null));

        return back()->with('status', "Brand identity saved for {$business->name}.");
    }

    /** Connect (or demo-connect) a provider for a channel. */
    public function connect(Request $request)
    {
        $data = $request->validate([
            'channel' => ['required', Rule::in(['email', 'sms', 'push'])],
            'provider' => ['required', 'string', 'max:40'],
            'label' => ['nullable', 'string', 'max:120'],
        ]);

        // Non-secret display config only; real secrets belong in env.
        $config = collect($request->input('config', []))
            ->map(fn ($v) => is_string($v) ? Str::limit($v, 6, '••••') : $v)
            ->all();

        $this->messaging->connect($data['channel'], $data['provider'], $config, $data['label'] ?? null);

        $providerLabel = config("messaging.channels.{$data['channel']}.providers.{$data['provider']}.label", $data['provider']);

        return back()->with('status', "{$providerLabel} connected for ".ucfirst($data['channel']).' (demo).');
    }

    public function disconnect(Request $request)
    {
        $data = $request->validate([
            'channel' => ['required', Rule::in(['email', 'sms', 'push'])],
            'provider' => ['required', 'string', 'max:40'],
        ]);

        $this->messaging->disconnect($data['channel'], $data['provider']);

        return back()->with('status', 'Provider disconnected.');
    }
}
