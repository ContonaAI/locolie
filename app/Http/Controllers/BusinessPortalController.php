<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Campaign;
use App\Models\DeviceToken;
use App\Models\PushSubscription;
use App\Models\Redemption;
use App\Services\BillingService;
use App\Services\Messaging\MessagingService;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Self-serve business CRM (web). Businesses log in with email + password to
 * manage their listing, offers and plan. The in-app build (/m?as=business)
 * uses the owner_secret; this is the browser equivalent.
 */
class BusinessPortalController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('business')->check()) {
            return redirect()->route('business.dashboard');
        }

        // A known demo account so the login can be shown live.
        $demo = Business::where('owner_email', 'demo@locolie.test')->first();

        return view('business.login', ['demo' => $demo]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('business')->attempt(['owner_email' => $data['email'], 'password' => $data['password']])) {
            $request->session()->regenerate();

            return redirect()->intended(route('business.dashboard'));
        }

        return back()->withErrors(['email' => 'Those details don’t match a business account.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('business')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('business.login');
    }

    public function dashboard()
    {
        $business = Auth::guard('business')->user()->load(['category', 'offers']);
        $redemptions = $this->redemptionsFor($business);

        return view('business.dashboard', [
            'business' => $business,
            'plans' => Business::PLANS,
            'customers' => $this->customersFor($business),
            'stats' => [
                'offers' => $business->offers->where('status', 'active')->count(),
                'redeemed' => $redemptions->where('status', 'redeemed')->count(),
                'pending' => $redemptions->where('status', 'pending')->count(),
            ],
        ]);
    }

    protected function redemptionsFor(Business $business)
    {
        return Redemption::whereHas('offer', fn ($q) => $q->where('business_id', $business->id))->get();
    }

    /** Distinct first-party customers captured at redemption — the retailer value prop. */
    protected function customersFor(Business $business)
    {
        return $this->redemptionsFor($business)
            ->filter(fn ($r) => $r->customer_email)
            ->groupBy('customer_email')
            ->map(fn ($g) => (object) [
                'email' => $g->first()->customer_email,
                'name' => $g->first()->customer_name ?: '—',
                'visits' => $g->count(),
                'opt_in' => (bool) $g->max('marketing_opt_in'),
                'last' => $g->max('created_at'),
            ])
            ->sortByDesc('last')->values();
    }

    /** Export the captured customer list as CSV (the data they couldn't get before). */
    public function exportCustomers()
    {
        $business = Auth::guard('business')->user();
        $customers = $this->customersFor($business);

        $csv = "Name,Email,Visits,Marketing opt-in,Last visit\n";
        foreach ($customers as $c) {
            $csv .= '"'.str_replace('"', '""', $c->name).'","'.$c->email.'",'.$c->visits.','.($c->opt_in ? 'yes' : 'no').','.optional($c->last)->toDateString()."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="golocal-customers.csv"',
        ]);
    }

    /**
     * Quick email draft — saves the subject + body as a draft Campaign against
     * the captured (opted-in) customers. This does NOT send: real branded
     * delivery happens in the Messaging Studio (messagingSend), which routes
     * through MessagingService::dispatch. Kept as a fast way to jot down a
     * campaign from the dashboard before sending it for real.
     */
    public function emailCustomers(Request $request)
    {
        $business = Auth::guard('business')->user();
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $recipients = $this->customersFor($business)->where('opt_in', true)->count();
        Campaign::create([
            'business_id' => $business->id,
            'channel' => 'email',
            'status' => 'draft',
            'subject' => $data['subject'],
            'body' => $data['body'],
            'sent_count' => 0,
        ]);

        return back()->with('status', "Draft saved. Head to Messaging to send it as branded email to your {$recipients} opted-in customers.");
    }

    /**
     * Retailer self-serve Messaging Studio - send branded email / SMS / push to
     * their own captured customers. Same channels + previews as the team studio,
     * scoped to this one brand.
     */
    public function messaging(MessagingService $messaging)
    {
        $business = Auth::guard('business')->user();
        $customers = $this->customersFor($business);

        return view('business.messaging', [
            'business' => $business,
            'overview' => $messaging->overview(),
            'channels' => config('messaging.channels'),
            'campaigns' => Campaign::where('business_id', $business->id)->latest('id')->limit(10)->get(),
            'samples' => [
                'email' => $this->sampleMessage('email'),
                'sms' => $this->sampleMessage('sms'),
                'push' => $this->sampleMessage('push'),
            ],
            'previews' => [
                'email' => $messaging->channel('email')->previewData($this->sampleMessage('email'), $business),
                'sms' => $messaging->channel('sms')->previewData($this->sampleMessage('sms'), $business),
                'push' => $messaging->channel('push')->previewData($this->sampleMessage('push'), $business),
            ],
            'audience' => [
                'email' => $customers->where('opt_in', true)->count(),
                'sms' => $this->smsCustomersFor($business)->count(),
                'push' => PushSubscription::count() + DeviceToken::count(),
            ],
        ]);
    }

    /** Save this business's own brand identity (logo, colour, sender names). */
    public function saveBrand(Request $request)
    {
        $business = Auth::guard('business')->user();
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

        return back()->with('status', 'Brand identity saved.');
    }

    /** Live preview of one channel for this brand (AJAX). */
    public function messagingPreview(Request $request, MessagingService $messaging)
    {
        $business = Auth::guard('business')->user();
        $channel = $request->validate(['channel' => ['required', Rule::in(['email', 'sms', 'push'])]])['channel'];
        $message = $request->only(['subject', 'preheader', 'body', 'title', 'cta_label', 'cta_url']);

        $preview = $messaging->channel($channel)->previewData($message, $business);

        return response()->json([
            'html' => view("messaging.previews.$channel", ['preview' => $preview])->render(),
        ]);
    }

    /** Send a branded message to this brand's own opted-in customers. */
    public function messagingSend(Request $request, MessagingService $messaging)
    {
        $business = Auth::guard('business')->user();
        $data = $request->validate([
            'channel' => ['required', Rule::in(['email', 'sms', 'push'])],
            'subject' => ['nullable', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
            'cta_label' => ['nullable', 'string', 'max:40'],
            'cta_url' => ['nullable', 'string', 'max:300'],
        ]);

        $recipients = match ($data['channel']) {
            'email' => $this->customersFor($business)->where('opt_in', true)
                ->map(fn ($c) => ['email' => $c->email, 'name' => $c->name])->all(),
            'sms' => $this->smsCustomersFor($business)
                ->map(fn ($c) => ['phone' => $c->phone, 'name' => $c->name])->all(),
            'push' => [], // broadcast to subscribed shoppers + app devices
        };

        $result = $messaging->dispatch($data['channel'], $data, $recipients, $business);

        $label = ['email' => 'Email', 'sms' => 'SMS', 'push' => 'Push'][$data['channel']];

        return back()->with('status', "{$label} sent to {$result->sent} recipients ({$result->status}).");
    }

    /** Retailer reporting suite - this brand's own performance, dynamically. */
    public function reports(Request $request, ReportingService $reporting)
    {
        $business = Auth::guard('business')->user();
        $days = (int) $request->integer('days', 30);
        $days = in_array($days, [7, 14, 30, 90], true) ? $days : 30;

        return view('business.reports', [
            'business' => $business,
            'report' => $reporting->forBusiness($business, $days),
            'days' => $days,
        ]);
    }

    /** Download the report's customer + offer tables as CSV. */
    public function reportsExport(ReportingService $reporting)
    {
        $business = Auth::guard('business')->user();
        $report = $reporting->forBusiness($business, 90);

        $csv = "Offer,Badge,Status,Redemptions,Issued,Redemption rate %,Est. value\n";
        foreach ($report['top_offers'] as $o) {
            $csv .= '"'.str_replace('"', '""', (string) $o['title']).'","'.$o['badge'].'","'.$o['status'].'",'.$o['redemptions'].','.$o['issued'].','.$o['rate'].','.$o['value']."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="locolie-report.csv"',
        ]);
    }

    /** Opted-in SMS customers for a business (distinct by phone). */
    protected function smsCustomersFor(Business $business)
    {
        return $this->redemptionsFor($business)
            ->filter(fn ($r) => $r->customer_phone && $r->sms_opt_in)
            ->groupBy('customer_phone')
            ->map(fn ($g) => (object) [
                'phone' => $g->first()->customer_phone,
                'name' => $g->first()->customer_name ?: 'Customer',
            ])->values();
    }

    /**
     * A starter message per channel, pre-filled with the brand's own live offer
     * where one exists - so the retailer can promote what is actually on in one
     * click, rather than starting from a blank box.
     */
    protected function sampleMessage(string $channel): array
    {
        $business = Auth::guard('business')->user();
        $offer = $business->activeOffers()->latest('id')->first();
        $deal = $offer
            ? trim(($offer->badge ? $offer->badge.' - ' : '').$offer->title)
            : '20% off your next visit';
        $shopUrl = url('/shop/'.$business->slug);

        return match ($channel) {
            'email' => [
                'subject' => "A treat from {$business->name}",
                'preheader' => 'Just for our regulars',
                'body' => "Thanks for being a regular at {$business->name}. Here is what is on: {$deal}. Show this email in store to claim it.",
                'cta_label' => 'See the offer',
                'cta_url' => $shopUrl,
            ],
            'sms' => ['body' => "{$business->name}: {$deal}. Show this text in store. Reply STOP to opt out."],
            'push' => ['title' => $business->name, 'body' => "{$deal} - tap to see the offer"],
        };
    }

    public function updateListing(Request $request)
    {
        $business = Auth::guard('business')->user();

        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:600'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:200'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')],
        ]);

        $business->update($data);

        return back()->with('status', 'Listing updated.');
    }

    /** Self-serve plan change. Routes through Stripe Checkout when keys are set. */
    public function upgrade(Request $request, BillingService $billing)
    {
        $business = Auth::guard('business')->user();
        $data = $request->validate(['plan' => ['required', Rule::in(array_keys(Business::PLANS))]]);

        // Paid plan + Stripe configured → hosted checkout.
        $url = $billing->checkoutUrl(
            $business,
            $data['plan'],
            route('business.upgrade.success', ['plan' => $data['plan']]),
            route('business.dashboard'),
        );
        if ($url) {
            return redirect()->away($url);
        }

        // Otherwise set the tier directly (MVP / free-at-launch).
        return $this->applyPlan($business, $data['plan']);
    }

    /** Return from Stripe Checkout — activate the plan. */
    public function upgradeSuccess(Request $request)
    {
        $business = Auth::guard('business')->user();
        $plan = $request->query('plan');
        abort_unless(array_key_exists($plan, Business::PLANS), 404);

        return $this->applyPlan($business, $plan);
    }

    protected function applyPlan(Business $business, string $plan)
    {
        $cfg = Business::PLANS[$plan];
        $business->update(['plan' => $plan, 'priority' => $cfg['priority'], 'featured' => $cfg['featured']]);

        return redirect()->route('business.dashboard')->with('status', 'You’re now on the '.$cfg['label'].' plan.');
    }
}
