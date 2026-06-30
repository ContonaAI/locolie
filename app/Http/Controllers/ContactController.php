<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/** Public Contact page + simple contact form handler. */
class ContactController extends Controller
{
    /** All enquiries route to a single inbox for now. */
    public const INBOX = 'info@locolie.com';

    /** Render the Contact page. */
    public function index()
    {
        return view('site.contact');
    }

    /**
     * Handle the contact form: email the enquiry to the shared inbox.
     *
     * Uses the app's configured mailer (defaults to the "log" driver locally,
     * so this never errors before SMTP is wired up). Falls back gracefully if
     * delivery throws, so the visitor always sees a friendly confirmation.
     */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'topic' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $topic = match ($data['topic'] ?? 'general') {
            'marketing' => 'Marketing',
            'sales' => 'Sales',
            default => 'General enquiry',
        };

        try {
            Mail::raw($data['message'], function ($mail) use ($data, $topic) {
                $mail->to(self::INBOX)
                    ->replyTo($data['email'], $data['name'])
                    ->subject('['.$topic.'] Contact form - '.$data['name']);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('contact_error', 'Sorry, something went wrong sending your message. Please email us directly at '.self::INBOX.'.');
        }

        return back()->with('contact_sent', true);
    }
}
