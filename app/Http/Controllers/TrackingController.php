<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Email open + click tracking. Tokens are encrypted (campaign id + recipient
 * email, and for clicks the target URL), so they cannot be forged or read. The
 * counters feed measured engagement into the reporting suite.
 */
class TrackingController extends Controller
{
    /** Transparent 1x1 GIF returned by the open pixel. */
    private const PIXEL = "GIF89a\x01\x00\x01\x00\x80\x00\x00\xff\xff\xff\x00\x00\x00!\xf9\x04\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;";

    public function open(Request $request)
    {
        if ($data = $this->decode($request->query('t'))) {
            $this->recordOpen($data['c'] ?? null, $data['e'] ?? null);
        }

        return response(self::PIXEL, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    public function click(Request $request)
    {
        $data = $this->decode($request->query('t'));
        $target = $data['u'] ?? null;

        if ($data) {
            // A click implies an open, so count both.
            $this->recordOpen($data['c'] ?? null, $data['e'] ?? null);
            if (! empty($data['c'])) {
                Campaign::where('id', $data['c'])->increment('clicks');
            }
        }

        // Only redirect to safe absolute http(s) URLs; otherwise home.
        if ($target && preg_match('#^https?://#i', $target)) {
            return redirect()->away($target);
        }

        return redirect('/');
    }

    private function recordOpen(?int $campaignId, ?string $email): void
    {
        if (! $campaignId || ! ($campaign = Campaign::find($campaignId))) {
            return;
        }

        // Count one open per distinct recipient (so opens == unique opens). When
        // we cannot identify the recipient, fall back to a raw increment.
        if (! $email) {
            $campaign->increment('opens');

            return;
        }

        $openedBy = $campaign->opened_by ?? [];
        if (! in_array($email, $openedBy, true)) {
            $openedBy[] = $email;
            $campaign->forceFill([
                'opened_by' => $openedBy,
                'opens' => $campaign->opens + 1,
            ])->save();
        }
    }

    private function decode(?string $token): ?array
    {
        if (! $token) {
            return null;
        }
        try {
            return json_decode(Crypt::decryptString($token), true) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** Build the encrypted tracking token used in emails. */
    public static function token(int $campaignId, string $email, ?string $url = null): string
    {
        return Crypt::encryptString(json_encode(array_filter([
            'c' => $campaignId,
            'e' => $email,
            'u' => $url,
        ])));
    }
}
