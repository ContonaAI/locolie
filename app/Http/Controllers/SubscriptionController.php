<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

/**
 * Login-free subscription management. The preference centre and one-click
 * unsubscribe are reached through SIGNED links (emailed to the contact), so a
 * person can manage consent without an account — and links can't be forged.
 *
 * A person can also land on /preferences with no signature and look up their
 * own email; we then email them a signed link rather than exposing anyone's
 * preferences to an unauthenticated guesser.
 */
class SubscriptionController extends Controller
{
    private array $topics;

    public function __construct()
    {
        $this->topics = Subscription::TOPICS;
    }

    /** Preference centre. Editable only when the link is validly signed. */
    public function preferences(Request $request)
    {
        $signed = $request->hasValidSignature();
        $email = $signed ? strtolower(trim((string) $request->query('email'))) : null;

        return view('site.subscriptions.preferences', [
            'topics' => $this->topics,
            'email' => $email,
            'signed' => $signed,
            'statusMap' => Subscription::statusMap($email),
        ]);
    }

    /** Save preferences. Requires a valid signature (proves email ownership). */
    public function update(Request $request)
    {
        abort_unless($request->hasValidSignature(), 403, 'This link has expired. Please request a fresh one.');

        $data = $request->validate([
            'email' => ['required', 'email', 'max:160'],
            'topics' => ['array'],
            'topics.*' => ['boolean'],
        ]);

        $email = strtolower(trim($data['email']));
        $checked = $data['topics'] ?? [];

        foreach (array_keys($this->topics) as $topic) {
            Subscription::setTopic(
                $email,
                $topic,
                (bool) ($checked[$topic] ?? false),
                ['source' => 'preference_centre', 'ip_address' => $request->ip()],
            );
        }

        return redirect(Subscription::preferencesUrl($email))
            ->with('saved', true);
    }

    /**
     * One-click unsubscribe (signed). Without a `topic` it unsubscribes from
     * everything; with one, just that topic. Handles both the GET a person
     * clicks and the POST that RFC 8058 "List-Unsubscribe-Post" clients send.
     */
    public function unsubscribe(Request $request)
    {
        abort_unless($request->hasValidSignature(), 403, 'This unsubscribe link has expired.');

        $email = strtolower(trim((string) $request->query('email')));
        $topic = $request->query('topic');

        if ($topic && isset($this->topics[$topic])) {
            Subscription::setTopic($email, $topic, false, ['source' => 'one_click', 'ip_address' => $request->ip()]);
        } else {
            $topic = null;
            Subscription::unsubscribeAll($email, ['source' => 'one_click', 'ip_address' => $request->ip()]);
        }

        // One-click POST clients just need a 200 with no body.
        if ($request->isMethod('post')) {
            return response()->noContent();
        }

        return view('site.subscriptions.unsubscribed', [
            'email' => $email,
            'topic' => $topic,
            'topicLabel' => $topic ? Subscription::topicLabel($topic) : null,
            'manageUrl' => Subscription::preferencesUrl($email),
        ]);
    }
}
