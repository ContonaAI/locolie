<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Services\PushService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PushController extends Controller
{
    public function __construct(protected PushService $push) {}

    /** A shopper's browser registers its web-push subscription. */
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:600'],
            'keys' => ['nullable', 'array'],
            'category_prefs' => ['nullable', 'array'],
        ]);

        $this->push->subscribe($data);

        return response()->json(['ok' => true]);
    }

    /**
     * A native iOS / Android app registers (or refreshes) its push token. Same
     * call from either platform; the platform field routes it to APNs or FCM
     * when the Messaging Studio broadcasts.
     */
    public function registerDevice(Request $request)
    {
        $data = $request->validate([
            'platform' => ['required', Rule::in(['web', 'ios', 'android'])],
            'token' => ['required', 'string', 'max:600'],
            'app_version' => ['nullable', 'string', 'max:40'],
            'locale' => ['nullable', 'string', 'max:20'],
            'topics' => ['nullable', 'array'],
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()?->id,
                'platform' => $data['platform'],
                'app_version' => $data['app_version'] ?? null,
                'locale' => $data['locale'] ?? null,
                'topics' => $data['topics'] ?? null,
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    /** A device unregisters (logout / uninstall / token rotation). */
    public function unregisterDevice(string $token)
    {
        DeviceToken::where('token', $token)->delete();

        return response()->json(['ok' => true]);
    }
}
