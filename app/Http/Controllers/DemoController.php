<?php

namespace App\Http\Controllers;

use App\Models\Business;

/**
 * Public "how offers work" demo. Shows the full discount + redemption experience
 * with SAMPLE data, regardless of the offers_public master switch - so we can
 * show retailers exactly how their offer would look and redeem, even while the
 * live site runs as a no-discount directory.
 */
class DemoController extends Controller
{
    public function index()
    {
        // A real, recognisable local business to make the demo concrete, plus a
        // clearly-fictional sample offer (never persisted).
        $business = Business::live()->whereNotNull('photos')->inRandomOrder()->first()
            ?? Business::whereNotNull('photos')->first();

        $offer = (object) [
            'badge' => '20% OFF',
            'title' => 'Example: 20% off your first visit',
            'description' => 'A sample offer to show how discounts appear and redeem on locolie. Real offers are set by each business.',
            'terms' => 'One use per customer. Show the code in store. Sample offer for demonstration only.',
        ];

        return view('site.demo', [
            'business' => $business,
            'offer' => $offer,
            'sampleCode' => 'LOCO-'.strtoupper(\Illuminate\Support\Str::random(4)),
        ]);
    }
}
