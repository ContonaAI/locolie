<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Jobs\PublishSocialPostJob;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\Social\SocialPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Social media control centre (team area, /portal/social): a marketing calendar
 * of post drafts per platform, a handles / accounts manager, and the OAuth
 * connect/disconnect flow. Direct API publishing is wired up but only goes live
 * once each platform's developer app is approved - until then a post can be
 * drafted + scheduled and the publisher reports "not connected".
 */
class SocialController extends Controller
{
    public function __construct(protected SocialPublisher $publisher) {}

    /** Marketing calendar: month grid of scheduled / posted posts. */
    public function calendar(Request $request)
    {
        // Anchor month from ?month=YYYY-MM, defaulting to the current month.
        $month = $this->resolveMonth($request->query('month'));
        $gridStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // Calendar entries: anything with a date (scheduled_at or posted_at) in range.
        $posts = SocialPost::whereBetween('scheduled_at', [$gridStart, $gridEnd])
            ->orWhereBetween('posted_at', [$gridStart, $gridEnd])
            ->orderBy('scheduled_at')
            ->get();

        $byDay = $posts->groupBy(fn (SocialPost $p) => optional($p->scheduled_at ?? $p->posted_at)->toDateString());

        // Build the week-by-week grid of days.
        $weeks = collect();
        $cursor = $gridStart->copy();
        while ($cursor <= $gridEnd) {
            $week = collect();
            for ($i = 0; $i < 7; $i++) {
                $week->push([
                    'date' => $cursor->copy(),
                    'inMonth' => $cursor->month === $month->month,
                    'isToday' => $cursor->isToday(),
                    'posts' => $byDay->get($cursor->toDateString(), collect()),
                ]);
                $cursor->addDay();
            }
            $weeks->push($week);
        }

        return view('portal.social.calendar', [
            'month' => $month,
            'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'weeks' => $weeks,
            'accounts' => SocialAccount::all()->keyBy('platform'),
            'platforms' => SocialAccount::PLATFORMS,
            'ideas' => SocialPost::where('status', 'idea')->latest()->limit(10)->get(),
        ]);
    }

    /** New-post form. */
    public function create(Request $request)
    {
        return view('portal.social.edit', [
            'post' => new SocialPost(['status' => 'draft', 'platforms' => []]),
            'date' => $request->query('date'),
            'accounts' => SocialAccount::all()->keyBy('platform'),
            'platforms' => SocialAccount::PLATFORMS,
            'statuses' => SocialPost::STATUSES,
        ]);
    }

    /** Edit an existing post. */
    public function edit(SocialPost $post)
    {
        return view('portal.social.edit', [
            'post' => $post,
            'date' => null,
            'accounts' => SocialAccount::all()->keyBy('platform'),
            'platforms' => SocialAccount::PLATFORMS,
            'statuses' => SocialPost::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $post = SocialPost::create($this->validatePost($request) + [
            'created_by' => 'team',
        ]);

        $this->scheduleIfDue($post);

        return redirect()->route('social.calendar', ['month' => optional($post->scheduled_at)->format('Y-m')])
            ->with('status', 'Post saved to the calendar.');
    }

    public function update(Request $request, SocialPost $post)
    {
        $post->update($this->validatePost($request));
        $this->scheduleIfDue($post);

        return redirect()->route('social.calendar', ['month' => optional($post->scheduled_at)->format('Y-m')])
            ->with('status', 'Post updated.');
    }

    public function destroy(SocialPost $post)
    {
        $post->delete();

        return back()->with('status', 'Post removed.');
    }

    /**
     * Publish now (manual trigger). Goes live per platform that is connected;
     * otherwise reports a clear "not connected - register the app" result.
     */
    public function publishNow(SocialPost $post)
    {
        $results = $this->publisher->publish($post->refresh());

        $notes = collect($results)
            ->map(fn ($r) => SocialAccount::label($r->platform).': '.$r->note)
            ->implode(' ');

        return back()->with('status', $notes ?: 'No platforms selected on this post.');
    }

    // ── Accounts / handles manager ─────────────────────────────────────────────

    public function accounts()
    {
        return view('portal.social.accounts', [
            'accounts' => SocialAccount::all()->keyBy('platform'),
            'platforms' => SocialAccount::PLATFORMS,
        ]);
    }

    /** Save a handle / display name for a platform (no OAuth needed for this). */
    public function saveAccount(Request $request, string $platform)
    {
        abort_unless(in_array($platform, SocialAccount::PLATFORMS, true), 404);

        $data = $request->validate([
            'handle' => ['nullable', 'string', 'max:120'],
            'display_name' => ['nullable', 'string', 'max:120'],
        ]);

        SocialAccount::updateOrCreate(['platform' => $platform], $data);

        return back()->with('status', SocialAccount::label($platform).' details saved.');
    }

    /** Forget the connection + token for a platform (keeps the handle row). */
    public function disconnect(string $platform)
    {
        abort_unless(in_array($platform, SocialAccount::PLATFORMS, true), 404);

        SocialAccount::where('platform', $platform)->update([
            'connected' => false,
            'access_token' => null,
            'token_expires_at' => null,
        ]);

        return back()->with('status', SocialAccount::label($platform).' disconnected.');
    }

    // ── OAuth connect flow (scaffolded - needs the approved developer app) ─────

    /**
     * Begin OAuth for a platform. If the app's client id is configured we
     * redirect to the platform consent screen; otherwise we explain that the
     * developer app still needs registering + approving.
     */
    public function connect(string $platform)
    {
        abort_unless(in_array($platform, SocialAccount::PLATFORMS, true), 404);

        $cfg = config("services.social.{$platform}");
        $clientId = $cfg['client_id'] ?? null;

        if (blank($clientId)) {
            return redirect()->route('social.accounts')->with('status',
                SocialAccount::label($platform).' app is not registered yet - add its client id/secret to .env to enable connecting.');
        }

        $redirect = $cfg['redirect'] ?: route('social.connect.callback', $platform);
        $state = Str::random(40);
        session(["social_oauth_state_{$platform}" => $state]);

        $url = $this->authorizeUrl($platform, $clientId, $redirect, $state);

        return $url
            ? redirect()->away($url)
            : redirect()->route('social.accounts')->with('status', 'OAuth for '.SocialAccount::label($platform).' is not configured.');
    }

    /**
     * OAuth callback. Scaffolded: when the app is approved and a code comes
     * back, exchange it for a token here and store it (encrypted) on the
     * account. Until then this records the attempt and degrades gracefully -
     * it never 500s.
     */
    public function connectCallback(Request $request, string $platform)
    {
        abort_unless(in_array($platform, SocialAccount::PLATFORMS, true), 404);
        $label = SocialAccount::label($platform);

        if ($request->filled('error')) {
            return redirect()->route('social.accounts')->with('status',
                "{$label} connection cancelled.");
        }

        // CSRF: state must match what we issued.
        $expected = session()->pull("social_oauth_state_{$platform}");
        if ($expected && $request->input('state') !== $expected) {
            return redirect()->route('social.accounts')->with('status',
                "{$label} connection failed a security check - please try again.");
        }

        // The real token exchange lives here once the app is approved. We log a
        // clear pointer rather than attempting a half-configured exchange, so
        // operators know exactly what is outstanding.
        Log::info("[social] {$platform} OAuth callback received", [
            'has_code' => $request->filled('code'),
        ]);

        return redirect()->route('social.accounts')->with('status',
            "{$label} OAuth received. Token exchange goes live once the {$label} developer app is approved - see config/services.php (services.social.{$platform}).");
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    protected function validatePost(Request $request): array
    {
        $validated = $request->validate([
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => [Rule::in(SocialAccount::PLATFORMS)],
            'body' => ['required', 'string', 'max:5000'],
            'media' => ['nullable', 'string', 'max:2000'], // newline/comma separated asset paths
            'status' => ['required', Rule::in(SocialPost::STATUSES)],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        // Media textarea -> array of trimmed paths.
        $media = collect(preg_split('/[\r\n,]+/', (string) ($validated['media'] ?? '')))
            ->map(fn ($p) => trim($p))
            ->filter()
            ->values()
            ->all();

        return [
            'platforms' => array_values($validated['platforms']),
            'body' => $validated['body'],
            'media' => $media,
            'status' => $validated['status'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ];
    }

    /** Queue a publish job when a post is scheduled (delayed to its time). */
    protected function scheduleIfDue(SocialPost $post): void
    {
        if ($post->status !== 'scheduled' || ! $post->scheduled_at) {
            return;
        }

        $job = new PublishSocialPostJob($post->id);
        dispatch($post->scheduled_at->isFuture() ? $job->delay($post->scheduled_at) : $job);
    }

    protected function resolveMonth(?string $month): Carbon
    {
        try {
            return $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : Carbon::now()->startOfMonth();
        } catch (\Throwable $e) {
            return Carbon::now()->startOfMonth();
        }
    }

    /** Build the platform authorize URL for the OAuth redirect. */
    protected function authorizeUrl(string $platform, string $clientId, string $redirect, string $state): ?string
    {
        return match ($platform) {
            'facebook', 'instagram' => 'https://www.facebook.com/v19.0/dialog/oauth?'.http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirect,
                'state' => $state,
                'response_type' => 'code',
                'scope' => $platform === 'instagram'
                    ? 'instagram_basic,instagram_content_publish,pages_show_list'
                    : 'pages_manage_posts,pages_read_engagement,pages_show_list',
            ]),
            'linkedin' => 'https://www.linkedin.com/oauth/v2/authorization?'.http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirect,
                'state' => $state,
                'response_type' => 'code',
                'scope' => 'w_member_social',
            ]),
            'tiktok' => 'https://www.tiktok.com/v2/auth/authorize/?'.http_build_query([
                'client_key' => $clientId,
                'redirect_uri' => $redirect,
                'state' => $state,
                'response_type' => 'code',
                'scope' => 'video.publish',
            ]),
            default => null,
        };
    }
}
