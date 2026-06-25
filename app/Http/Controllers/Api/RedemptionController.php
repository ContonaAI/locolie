<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Offer;
use App\Models\Redemption;
use App\Models\Subscription;
use App\Services\RedemptionService;
use Illuminate\Http\Request;

class RedemptionController extends Controller
{
    public function __construct(protected RedemptionService $service) {}

    /** Customer reveals a code for an offer. */
    public function redeem(Request $request, Offer $offer)
    {
        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:80'],
            'customer_email' => ['nullable', 'email', 'max:160'],
            'customer_phone' => ['nullable', 'string', 'max:32'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            'sms_opt_in' => ['nullable', 'boolean'],
        ]);

        abort_unless($offer->status === 'active', 422, 'This offer is no longer active.');
        abort_if($offer->isSoldOut(), 422, 'Sorry, this offer has sold out.');

        $offer->loadMissing('business');
        $redemption = $this->service->createForOffer(
            $offer,
            $data['customer_name'] ?? null,
            email: $data['customer_email'] ?? null,
            optIn: (bool) ($data['marketing_opt_in'] ?? false),
        );

        // Sync the shopper's marketing/SMS consent into the subscription ledger.
        if (! empty($data['customer_email'])) {
            Subscription::setTopic($data['customer_email'], 'offers', (bool) ($data['marketing_opt_in'] ?? false), [
                'source' => 'redemption',
                'phone' => $data['customer_phone'] ?? null,
                'ip_address' => $request->ip(),
            ]);
            if (array_key_exists('sms_opt_in', $data) && ! empty($data['customer_phone'])) {
                Subscription::setTopic($data['customer_email'], 'sms_alerts', (bool) $data['sms_opt_in'], [
                    'source' => 'redemption',
                    'phone' => $data['customer_phone'],
                    'ip_address' => $request->ip(),
                ]);
            }
        }

        return response()->json([
            'code' => $redemption->code,
            'status' => $redemption->status,
            'expires_at' => $redemption->expires_at?->toIso8601String(),
            'ttl_seconds' => (int) now()->diffInSeconds($redemption->expires_at, false),
            'business' => $offer->business?->name,
            'badge' => $offer->badge,
            'offer' => $offer->title,
        ], 201);
    }

    /** Retailer verifies a code at the till. */
    public function verify(Request $request)
    {
        $data = $request->validate([
            'secret' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
            'spend' => ['nullable', 'numeric', 'min:0', 'max:100000'], // optional £ spent, for spend-based loyalty
        ]);

        $business = Business::where('owner_secret', $data['secret'])->firstOr(fn () => abort(403, 'Invalid business key.'));

        $spendPence = (int) round((float) ($data['spend'] ?? 0) * 100);
        $result = $this->service->verify($business, $data['code'], $spendPence);

        // Surface any loyalty rewards the customer just unlocked, so the till can read them out.
        $earned = collect($result['loyalty']['earned'] ?? [])
            ->map(fn ($r) => ['code' => $r->code, 'label' => $r->label])->values();

        return response()->json([
            'ok' => $result['ok'],
            'message' => $result['message'],
            'offer' => $result['redemption']?->offer?->title,
            'loyalty_earned' => $earned,
        ], $result['ok'] ? 200 : 422);
    }

    /** Customer polls a code's status (wallet / live countdown). */
    public function show(string $code)
    {
        $redemption = Redemption::where('code', $code)->latest('id')->firstOr(fn () => abort(404));

        return [
            'code' => $redemption->code,
            'status' => $redemption->status,
            'expires_at' => $redemption->expires_at?->toIso8601String(),
            'ttl_seconds' => (int) now()->diffInSeconds($redemption->expires_at, false),
        ];
    }
}
