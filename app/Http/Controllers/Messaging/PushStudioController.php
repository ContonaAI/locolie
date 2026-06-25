<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\Messaging\MessagingService;
use App\Services\Messaging\PushChannel;
use Illuminate\Http\Request;

/**
 * Push channel studio: compose, live preview across web + iOS + Android, send
 * test broadcasts and full broadcasts. Connection state for the three push
 * providers (web_push / fcm / apns) is rendered by the shared studio pattern.
 */
class PushStudioController extends Controller
{
    public function __construct(protected MessagingService $messaging) {}

    /** Compose + live preview screen for the push channel. */
    public function index(Request $request)
    {
        /** @var PushChannel $channel */
        $channel = $this->messaging->channel('push');
        $businesses = Business::orderBy('name')->get();

        return view('portal.messaging.push', [
            'businesses' => $businesses,
            'channel' => $channel,
            'audience' => $channel->audienceBreakdown(),
            'defaultPreview' => $channel->previewData([], $businesses->first()),
            'connected' => $channel->connected(),
            'status' => $channel->status(),
            'activeProvider' => $channel->activeProvider(),
            'providers' => config('messaging.channels.push.providers', []),
            'catalogue' => config('messaging.channels.push', []),
        ]);
    }

    /** Render the three notification mockups for the given draft (AJAX). */
    public function preview(Request $request)
    {
        $channel = $this->messaging->channel('push');
        $brand = $this->resolveBrand($request);

        $html = view('messaging.previews.push', [
            'preview' => $channel->previewData($this->message($request), $brand),
        ])->render();

        return response()->json(['html' => $html]);
    }

    /** Send a test broadcast to the current subscribers/devices. */
    public function test(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
        ]);

        $brand = $this->resolveBrand($request);
        $result = $this->messaging->dispatch('push', $this->message($request), [], $brand);

        return back()->with('status', "Test push: reached {$result->sent} device(s)/browser(s) ({$result->status}).");
    }

    /** Send the broadcast to every subscriber + device. */
    public function send(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
            'business_id' => ['nullable', 'exists:businesses,id'],
        ]);

        $brand = $this->resolveBrand($request);
        $result = $this->messaging->dispatch('push', $this->message($request), [], $brand, [
            'scheduled_at' => $request->input('scheduled_at'),
        ]);

        return back()->with('status', "Push broadcast: {$result->note}");
    }

    /** Pull the compose payload off the request into the channel message shape. */
    protected function message(Request $request): array
    {
        return [
            'title' => (string) $request->input('title', ''),
            'body' => (string) $request->input('body', ''),
            'cta_label' => (string) $request->input('cta_label', ''),
            'cta_url' => (string) $request->input('cta_url', ''),
        ];
    }

    /** The sending brand, or null for a platform-wide push. */
    protected function resolveBrand(Request $request): ?Business
    {
        $id = $request->input('business_id');

        return $id ? Business::find($id) : null;
    }
}
