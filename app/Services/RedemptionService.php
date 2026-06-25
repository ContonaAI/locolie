<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Offer;
use App\Models\Redemption;

class RedemptionService
{
    /** How long a revealed code stays valid. */
    public const TTL_MINUTES = 10;

    /**
     * Create a pending redemption for an offer and return it with a fresh 6-digit code.
     */
    public function createForOffer(Offer $offer, ?string $customerName = null, ?int $userId = null, ?string $email = null, bool $optIn = false): Redemption
    {
        return Redemption::create([
            'offer_id' => $offer->id,
            'user_id' => $userId,
            'customer_name' => $customerName,
            'customer_email' => $email,
            'marketing_opt_in' => $optIn,
            'code' => $this->uniqueCode(),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);
    }

    /**
     * Verify a code on behalf of a business and mark it redeemed.
     *
     * @return array{ok: bool, message: string, redemption: ?Redemption}
     */
    public function verify(Business $business, string $code, int $spendPence = 0): array
    {
        $redemption = Redemption::with('offer')
            ->whereHas('offer', fn ($q) => $q->where('business_id', $business->id))
            ->where('code', $code)
            ->latest('id')
            ->first();

        if (! $redemption) {
            return ['ok' => false, 'message' => 'No matching code for this business.', 'redemption' => null, 'loyalty' => null];
        }

        if ($redemption->status === 'redeemed') {
            return ['ok' => false, 'message' => 'Code already redeemed.', 'redemption' => $redemption, 'loyalty' => null];
        }

        if ($redemption->isExpired()) {
            $redemption->update(['status' => 'expired']);

            return ['ok' => false, 'message' => 'Code has expired.', 'redemption' => $redemption, 'loyalty' => null];
        }

        $redemption->update(['status' => 'redeemed', 'redeemed_at' => now()]);

        // Count the redemption against a limited offer's stock.
        if ($redemption->offer && $redemption->offer->quantity !== null) {
            $redemption->offer->increment('redeemed_count');
        }

        // Accrue loyalty for this customer and award any rewards earned this visit.
        $loyalty = app(LoyaltyService::class)->recordVisit($business, $redemption->customer_email, $spendPence);

        return ['ok' => true, 'message' => 'Valid — redeemed.', 'redemption' => $redemption, 'loyalty' => $loyalty];
    }

    /** A 6-digit numeric code not currently live for another pending redemption. */
    protected function uniqueCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (
            Redemption::where('code', $code)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->exists()
        );

        return $code;
    }
}
