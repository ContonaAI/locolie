<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Redemption;
use App\Models\Subscription;
use App\Support\QrSvg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * The marketing suite for retailers: an annotated "how it works" walkthrough,
 * the printable in-store QR poster, and the public scan-to-join capture flow.
 *
 * The flow this controller serves end to end:
 *   1. Retailer prints a QR poster ({@see poster}) and displays it in store.
 *   2. A shopper scans it -> lands on a branded opt-in page ({@see capture}).
 *   3. They opt in ({@see store}) -> we record consent (Subscription) and add
 *      them to the shop's first-party customer list (Redemption row).
 *   4. The retailer builds + sends campaigns in the Messaging studio, and sees
 *      engagement in Reports.
 *
 * Auth-only pages are scoped to the signed-in business via the `business` guard;
 * the capture pages are public (the shopper is not logged in).
 */
class MarketingController extends Controller
{
    protected function business(): Business
    {
        return Auth::guard('business')->user();
    }

    /** Absolute URL a shopper reaches by scanning the in-store QR. */
    public static function captureUrl(Business $business): string
    {
        return route('marketing.capture', $business->slug);
    }

    // ── Retailer: the "How your marketing works" walkthrough ──────────────────

    public function index()
    {
        $business = $this->business();
        $captureUrl = self::captureUrl($business);

        return view('business.marketing', [
            'business' => $business,
            'captureUrl' => $captureUrl,
            'qrSvg' => QrSvg::make($captureUrl, 200, '#0a0a0a'),
            'stats' => $this->captureStats($business),
            'sampleList' => $this->sampleList($business),
            'sampleCampaigns' => $this->sampleCampaigns($business),
        ]);
    }

    /** Printable, self-contained QR poster for the shop window / counter. */
    public function poster()
    {
        $business = $this->business();
        $captureUrl = self::captureUrl($business);

        return view('business.marketing-poster', [
            'business' => $business,
            'captureUrl' => $captureUrl,
            'qrSvg' => QrSvg::make($captureUrl, 320, '#0a0a0a'),
        ]);
    }

    // ── Public: scan -> capture landing -> opt in ─────────────────────────────

    /** The page a shopper lands on after scanning the QR (no login). */
    public function capture(Business $business)
    {
        $captureUrl = self::captureUrl($business);

        return view('capture.join', [
            'business' => $business,
            'captureUrl' => $captureUrl,
            'qrSvg' => QrSvg::make($captureUrl, 120, '#0a0a0a'),
            'program' => $business->loyaltyProgram,
        ]);
    }

    /** Store a captured contact with consent, then show the thank-you page. */
    public function store(Request $request, Business $business)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email_opt_in' => ['accepted'], // joining the list is the whole point
            'sms_opt_in' => ['nullable', 'boolean'],
        ], [
            'email_opt_in.accepted' => 'Please tick the box to join the list.',
        ]);

        $email = strtolower(trim($data['email']));
        $phone = $this->normalisePhone($data['phone'] ?? null);
        $smsOptIn = (bool) ($data['sms_opt_in'] ?? false) && $phone;

        // 1. Consent is the source of truth (GDPR audit trail via ConsentLog).
        Subscription::setTopic($email, 'offers', true, [
            'phone' => $phone,
            'source' => 'qr_capture',
            'ip_address' => $request->ip(),
        ]);
        if ($smsOptIn) {
            Subscription::setTopic($email, 'sms_alerts', true, [
                'phone' => $phone,
                'source' => 'qr_capture',
                'ip_address' => $request->ip(),
            ]);
        }

        // 2. Add the contact to the shop's own first-party list. A capture is a
        //    redemption row with no offer; business_id keeps it attributable.
        Redemption::create([
            'business_id' => $business->id,
            'offer_id' => optional($business->activeOffers()->first())->id,
            'customer_name' => $data['name'] ?? null,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'marketing_opt_in' => true,
            'sms_opt_in' => $smsOptIn,
            'code' => strtoupper(Str::random(6)),
            'status' => 'pending',
            'source' => 'qr_capture',
        ]);

        return redirect()
            ->route('marketing.capture.done', $business->slug)
            ->with('captured', ['name' => $data['name'] ?? null, 'sms' => $smsOptIn]);
    }

    /** Confirmation page after a successful opt-in. */
    public function done(Business $business)
    {
        return view('capture.done', [
            'business' => $business,
            'captured' => session('captured', []),
            'program' => $business->loyaltyProgram,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Real capture counts for the signed-in shop (drives the live tiles). */
    protected function captureStats(Business $business): array
    {
        $captured = Redemption::where('business_id', $business->id)
            ->where('source', 'qr_capture')
            ->whereNotNull('customer_email')
            ->get();

        return [
            'emails' => $captured->pluck('customer_email')->unique()->count(),
            'sms' => $captured->where('sms_opt_in', true)->pluck('customer_phone')->filter()->unique()->count(),
            'this_week' => $captured->where('created_at', '>=', now()->subWeek())->count(),
        ];
    }

    /**
     * A worked-example list so a prospect can see what a captured list looks
     * like before they have any real contacts. Falls back to illustrative data;
     * if the shop has real captures, those are shown instead.
     */
    protected function sampleList(Business $business): array
    {
        $real = Redemption::where('business_id', $business->id)
            ->whereNotNull('customer_email')
            ->latest('id')
            ->get()
            ->unique('customer_email')
            ->take(6)
            ->map(fn ($r) => [
                'name' => $r->customer_name ?: 'Customer',
                'email' => $r->customer_email,
                'sms' => (bool) $r->sms_opt_in,
                'joined' => optional($r->created_at)->diffForHumans() ?? 'just now',
                'real' => true,
            ])
            ->values()
            ->all();

        if (count($real) >= 3) {
            return $real;
        }

        // Illustrative sample (clearly labelled in the view).
        return [
            ['name' => 'Hannah Reid', 'email' => 'hannah.r@example.com', 'sms' => true, 'joined' => '2 hours ago', 'real' => false],
            ['name' => 'Marcus Bell', 'email' => 'm.bell@example.com', 'sms' => false, 'joined' => 'yesterday', 'real' => false],
            ['name' => 'Priya Shah', 'email' => 'priya.shah@example.com', 'sms' => true, 'joined' => '2 days ago', 'real' => false],
            ['name' => 'Tom Lawson', 'email' => 'tomlawson@example.com', 'sms' => true, 'joined' => '3 days ago', 'real' => false],
            ['name' => 'Erin Doyle', 'email' => 'erin.d@example.com', 'sms' => false, 'joined' => '4 days ago', 'real' => false],
            ['name' => 'Sam Okafor', 'email' => 's.okafor@example.com', 'sms' => true, 'joined' => 'last week', 'real' => false],
        ];
    }

    /** Worked-example "sent campaigns" with engagement, for the reports preview. */
    protected function sampleCampaigns(Business $business): array
    {
        $real = $business->campaigns()->latest('id')->take(3)->get();

        if ($real->count() >= 1) {
            return $real->map(fn ($c) => [
                'channel' => $c->channel,
                'subject' => $c->subject ?: Str::limit($c->body, 40),
                'sent' => (int) $c->sent_count,
                'opens' => (int) ($c->opens ?? 0),
                'clicks' => (int) ($c->clicks ?? 0),
                'when' => optional($c->created_at)->diffForHumans() ?? 'recently',
                'real' => true,
            ])->all();
        }

        return [
            ['channel' => 'email', 'subject' => 'This weekend: 2-for-1 on all mains', 'sent' => 248, 'opens' => 141, 'clicks' => 39, 'when' => '3 days ago', 'real' => false],
            ['channel' => 'sms', 'subject' => 'Quiet Tuesday? Free coffee with any cake, today only', 'sent' => 96, 'opens' => 0, 'clicks' => 22, 'when' => 'last week', 'real' => false],
            ['channel' => 'push', 'subject' => 'New autumn menu just dropped', 'sent' => 173, 'opens' => 0, 'clicks' => 51, 'when' => '2 weeks ago', 'real' => false],
        ];
    }

    /** Light UK-friendly phone tidy: strip spaces/punctuation, keep + and digits. */
    protected function normalisePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }
        $clean = preg_replace('/[^0-9+]/', '', $phone);

        return $clean !== '' ? $clean : null;
    }
}
