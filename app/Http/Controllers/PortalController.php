<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortalController extends Controller
{
    /**
     * Portal hub / landing page.
     */
    public function home()
    {
        return view('portal.home', [
            'mockupCount' => $this->mockupFiles()->count(),
            'mapsKey' => config('services.google.maps_key'),
            'mapsId' => config('services.google.maps_id'),
            'vapidKey' => config('services.vapid.public'),
        ]);
    }

    /**
     * Public shopper-only build of the SAME app, full-screen for mobile/iOS testing.
     */
    public function mobile(Request $request)
    {
        return view('portal.home', [
            'mockupCount' => 0,
            'mapsKey' => config('services.google.maps_key'),
            'mapsId' => config('services.google.maps_id'),
            'vapidKey' => config('services.vapid.public'),
            'solo' => true,
            'soloRole' => $request->query('as') === 'business' ? 'business' : 'shopper',
        ]);
    }

    /**
     * Render the business plan markdown.
     */
    public function businessPlan()
    {
        $path = resource_path('content/business-plan.md');

        $html = File::exists($path)
            ? Str::markdown(File::get($path), ['html_input' => 'allow'])
            : '<p>Business plan not found.</p>';

        return view('portal.business-plan', ['html' => $html]);
    }

    /**
     * Brand: logo concepts, app style directions, name exploration.
     */
    public function brand()
    {
        return view('portal.brand');
    }

    /**
     * App & brand design exploration (wrapper page with portal nav).
     */
    public function design()
    {
        return view('portal.design');
    }

    /**
     * Lightweight data/admin overview of the live prototype DB.
     */
    public function admin()
    {
        return view('portal.admin', [
            'stats' => [
                'businesses' => \App\Models\Business::count(),
                'onboarded' => \App\Models\Business::where('onboarded', true)->count(),
                'leads' => \App\Models\Business::where('onboarded', false)->count(),
                'paid' => \App\Models\Business::whereIn('plan', ['featured', 'premium'])->count(),
                'offers' => \App\Models\Offer::where('status', 'active')->count(),
                'redemptions' => \App\Models\Redemption::count(),
                'redeemed' => \App\Models\Redemption::where('status', 'redeemed')->count(),
                'push_subs' => \App\Models\PushSubscription::count(),
            ],
            'planCounts' => [
                'free' => \App\Models\Business::where('plan', 'free')->count(),
                'featured' => \App\Models\Business::where('plan', 'featured')->count(),
                'premium' => \App\Models\Business::where('plan', 'premium')->count(),
            ],
            'categories' => \App\Models\Category::orderBy('name')->get(),
            'businesses' => \App\Models\Business::with('category')
                ->withCount(['offers' => fn ($q) => $q->where('status', 'active')])
                ->orderByDesc('onboarded')->orderByDesc('priority')->orderBy('name')->get(),
            'campaigns' => \App\Models\Campaign::with('business')->latest('id')->limit(15)->get(),
            'redemptions' => \App\Models\Redemption::with('offer.business')
                ->latest('id')->limit(15)->get(),
        ]);
    }

    /**
     * Admin settings: data-sync configuration + this environment's data footprint.
     */
    public function settings()
    {
        $token = (string) config('sync.token');
        $bizDir = \Illuminate\Support\Facades\Storage::disk('public')->exists('biz')
            ? \Illuminate\Support\Facades\Storage::disk('public')->files('biz') : [];

        return view('portal.settings', [
            'sync' => [
                'configured' => filled($token),
                'token_masked' => filled($token)
                    ? substr($token, 0, 6).str_repeat('•', 10).substr($token, -4)
                    : null,
                'endpoint' => url('/api/sync'),
                'last_sync' => \Illuminate\Support\Facades\Cache::get('sync.last_at'),
                'counts' => [
                    'businesses' => \App\Models\Business::count(),
                    'offers' => \App\Models\Offer::count(),
                    'categories' => \App\Models\Category::count(),
                    'images' => count($bizDir),
                ],
            ],
            'gsc' => [
                'tags' => \App\Support\Seo::storedTags(),
                'files' => \App\Support\Seo::htmlFiles(),
                'verified' => count(\App\Support\Seo::verificationTags()) > 0 || count(\App\Support\Seo::htmlFiles()) > 0,
                'home_url' => url('/'),
                'sitemap_url' => url('/sitemap.xml'),
                'robots_url' => url('/robots.txt'),
            ],
        ]);
    }

    /** CRM: flip a business between live (onboarded) and lead. */
    public function adminToggleOnboard(\App\Models\Business $business)
    {
        $business->update([
            'onboarded' => ! $business->onboarded,
            'claimed_at' => $business->onboarded ? null : now(),
        ]);

        return back()->with('status', $business->name.($business->onboarded ? ' is now live.' : ' moved back to leads.'));
    }

    /** CRM: set a business's paid tier (priority + featured follow the plan). */
    public function adminSetPlan(Request $request, \App\Models\Business $business)
    {
        $data = $request->validate(['plan' => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Models\Business::PLANS))]]);
        $cfg = \App\Models\Business::PLANS[$data['plan']];
        $business->update(['plan' => $data['plan'], 'priority' => $cfg['priority'], 'featured' => $cfg['featured']]);

        return back()->with('status', $business->name.' set to '.$cfg['label'].'.');
    }

    /** CRM prospecting: live Google Maps search to build the onboarding list. */
    public function adminProspectSearch(Request $request, \App\Services\PlacesService $places)
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:120']]);

        $results = collect($places->search($request->input('q')))->map(function ($p) {
            $pid = data_get($p, 'place_id') ?? data_get($p, 'id');

            return array_merge((array) $p, ['already_added' => \App\Models\Business::where('google_place_id', $pid)->exists()]);
        });

        return response()->json($results);
    }

    /** CRM prospecting: add a Google place as a lead (NOT onboarded yet). */
    public function adminAddProspect(Request $request, \App\Services\PlacesService $places)
    {
        $data = $request->validate([
            'place_id' => ['required', 'string', 'max:300'],
            'category_id' => ['nullable', \Illuminate\Validation\Rule::exists('categories', 'id')],
        ]);

        $place = $places->details($data['place_id']);
        abort_unless($place, 422, 'Could not load that place.');

        $business = \App\Models\Business::updateOrCreate(
            ['google_place_id' => data_get($place, 'id')],
            [
                'name' => data_get($place, 'displayName.text', 'Unknown'),
                'category_id' => $data['category_id'] ?? \App\Models\Category::value('id'),
                'address' => data_get($place, 'formattedAddress'),
                'lat' => data_get($place, 'location.latitude'),
                'lng' => data_get($place, 'location.longitude'),
                'rating' => data_get($place, 'rating'),
                'reviews_count' => (int) data_get($place, 'userRatingCount', 0),
                'postcode' => $places->postcode(data_get($place, 'formattedAddress')) ?? 'NE1',
                'plan' => 'free',
                'onboarded' => false, // a lead until the team onboards them
                'status' => 'active',
            ]
        );

        return back()->with('status', $business->name.' added as a lead.');
    }

    /** CRM: log + "send" an email or push campaign (Stripe/VAPID wiring is scaffolded). */
    public function adminSendCampaign(Request $request, \App\Services\PushService $push)
    {
        $data = $request->validate([
            'channel' => ['required', \Illuminate\Validation\Rule::in(['email', 'push'])],
            'subject' => ['nullable', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $sent = $data['channel'] === 'push'
            ? $push->broadcast($data['subject'] ?? 'locolie', $data['body'])
            : \App\Models\Business::where('onboarded', true)->whereNotNull('owner_email')->count();

        \App\Models\Campaign::create($data + ['sent_count' => $sent]);

        return back()->with('status', ucfirst($data['channel']).' campaign queued to '.$sent.' recipients.');
    }

    /**
     * QR deep link printed on a business's window sticker.
     * Resolves the public qr_token and opens the app at that business.
     */
    public function qrRedirect(string $token)
    {
        $business = \App\Models\Business::where('qr_token', $token)->firstOr(fn () => abort(404));

        return redirect()->route('app', ['b' => $business->slug]);
    }

    /**
     * Printable window sticker for a business (owner-secret scoped).
     */
    public function sticker(string $secret)
    {
        $business = \App\Models\Business::where('owner_secret', $secret)->firstOr(fn () => abort(404));

        return view('demo.sticker', [
            'business' => $business,
            'url' => route('qr.redirect', ['token' => $business->qr_token]),
        ]);
    }

    /**
     * The standalone design HTML, served raw for the iframe.
     */
    public function designRaw()
    {
        $path = resource_path('content/app-design.html');

        abort_unless(File::exists($path), 404);

        return response(File::get($path))->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * App mockups gallery.
     */
    public function mockups()
    {
        return view('portal.mockups', ['mockups' => $this->mockupFiles()]);
    }

    /**
     * Collect uploaded mockup images from the public disk.
     */
    private function mockupFiles()
    {
        Storage::disk('public')->makeDirectory('mockups');

        return collect(Storage::disk('public')->files('mockups'))
            ->filter(fn ($f) => Str::of($f)->lower()->endsWith(['.png', '.jpg', '.jpeg', '.gif', '.webp']))
            ->map(fn ($f) => [
                'url' => Storage::url($f),
                'name' => basename($f),
            ])
            ->values();
    }

    /**
     * Handle mockup image uploads.
     */
    public function uploadMockup(Request $request)
    {
        $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['image', 'max:10240'],
        ]);

        foreach ($request->file('images') as $image) {
            $image->store('mockups', 'public');
        }

        return redirect()->route('portal.mockups')->with('status', 'Mockups uploaded.');
    }

    /**
     * Ideas board.
     */
    public function ideas()
    {
        return view('portal.ideas', [
            'ideas' => Idea::latest()->get(),
        ]);
    }

    /**
     * Store a new idea.
     */
    public function storeIdea(Request $request)
    {
        $data = $request->validate([
            'author' => ['nullable', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:160'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        Idea::create($data);

        return redirect()->route('portal.ideas')->with('status', 'Idea added.');
    }

    /**
     * Delete an idea.
     */
    public function deleteIdea(Idea $idea)
    {
        $idea->delete();

        return redirect()->route('portal.ideas')->with('status', 'Idea removed.');
    }

    /**
     * Show the password gate.
     */
    public function loginForm()
    {
        return view('portal.login');
    }

    /**
     * Validate the shared password.
     */
    public function login(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);

        if (hash_equals((string) config('portal.password'), (string) $request->input('password'))) {
            $request->session()->regenerate();
            $request->session()->put('portal_authed', true);

            return redirect()->intended(route('portal.home'));
        }

        return back()->withErrors(['password' => 'Incorrect password.']);
    }

    /**
     * Log out of the portal.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('portal_authed');

        return redirect()->route('portal.login');
    }
}
