<?php

namespace App\Http\Controllers;

use App\Models\Business;
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
        $demo = Business::where('owner_email', 'demo@golocal.test')->first();

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
        return \App\Models\Redemption::whereHas('offer', fn ($q) => $q->where('business_id', $business->id))->get();
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

    /** Email the captured (opted-in) customers — the marketing channel. */
    public function emailCustomers(Request $request)
    {
        $business = Auth::guard('business')->user();
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $recipients = $this->customersFor($business)->where('opt_in', true)->count();
        \App\Models\Campaign::create([
            'business_id' => $business->id,
            'channel' => 'email',
            'subject' => $data['subject'],
            'body' => $data['body'],
            'sent_count' => $recipients,
        ]);

        return back()->with('status', "Email queued to {$recipients} opted-in customers.");
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
    public function upgrade(Request $request, \App\Services\BillingService $billing)
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
