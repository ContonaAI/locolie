<?php

namespace App\Services;

use App\Models\Business;

/**
 * Stripe billing for paid plans. Scaffolded to be correct-shaped now and live
 * the moment STRIPE_SECRET (+ optional price IDs) are set — callers don't change.
 *
 * Without keys, configured() is false and the CRM/dashboard just sets the plan
 * directly (MVP "free at launch" behaviour).
 */
class BillingService
{
    public function configured(): bool
    {
        return (bool) config('services.stripe.secret') && class_exists(\Stripe\StripeClient::class);
    }

    protected function client(): \Stripe\StripeClient
    {
        return new \Stripe\StripeClient(config('services.stripe.secret'));
    }

    /** A hosted Stripe Checkout URL for upgrading this business to $plan, or null. */
    public function checkoutUrl(Business $business, string $plan, string $successUrl, string $cancelUrl): ?string
    {
        if (! $this->configured() || ! in_array($plan, ['featured', 'premium'], true)) {
            return null;
        }

        $cfg = Business::PLANS[$plan];
        $priceId = config("services.stripe.prices.{$plan}");

        $lineItem = $priceId
            ? ['price' => $priceId, 'quantity' => 1]
            : [
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'gbp',
                    'recurring' => ['interval' => 'month'],
                    'unit_amount' => $cfg['price'] * 100,
                    'product_data' => ['name' => "locolie {$cfg['label']} plan"],
                ],
            ];

        $session = $this->client()->checkout->sessions->create([
            'mode' => 'subscription',
            'line_items' => [$lineItem],
            'customer_email' => $business->owner_email,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => ['business_id' => $business->id, 'plan' => $plan],
        ]);

        return $session->url;
    }
}
