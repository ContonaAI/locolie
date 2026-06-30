<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Support\QrSvg;

/**
 * In-app, on-brand retailer onboarding deck (about five slides) that walks a new
 * retailer through how locolie works: welcome, claim + set up, the free loyalty
 * scheme, marketing to their own customers (with the privacy promise), and going
 * live. Viewable online (shareable) and printable to a clean PDF from the browser.
 *
 * Uses a real, recognisable local business to make the screenshots feel like the
 * product rather than placeholders. Marketing keeps the focus - we never expose
 * public discounting here, so the offers kill-switch is respected.
 */
class OnboardingDeckController extends Controller
{
    public function deck()
    {
        // A real local business with a photo makes the deck feel like the product.
        $business = Business::live()->whereNotNull('photos')->inRandomOrder()->first()
            ?? Business::whereNotNull('photos')->first();

        // The deck's scannable QR points at the website signup (retailer join page),
        // so anyone viewing or printing the deck can scan straight through to sign up.
        $signupUrl = route('business.join');

        return view('site.onboarding.deck', [
            'business' => $business,
            'signupUrl' => $signupUrl,
            'signupQr' => QrSvg::make($signupUrl, 360, '#0a0a0a'),
        ]);
    }
}
