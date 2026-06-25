<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\Request;

/**
 * Google Search Console ownership verification, managed from the admin Settings
 * page. Saves the meta-tag tokens / HTML-file names (rendered + served live), and
 * serves the googleXXXX.html verification files Google fetches at the site root.
 */
class SearchConsoleController extends Controller
{
    /** Admin: save the verification tokens + HTML filenames. */
    public function save(Request $request)
    {
        $request->validate([
            'verification_tags' => ['nullable', 'string', 'max:4000'],
            'html_files' => ['nullable', 'string', 'max:2000'],
        ]);

        Seo::setVerificationTags($request->input('verification_tags'));
        Seo::setHtmlFiles($request->input('html_files'));

        return back()->with('status', 'Search Console verification saved. Run "Verify" in Google once the changes are live.');
    }

    /**
     * Serve a Google HTML verification file at the site root, e.g.
     * /google1a2b3c.html -> "google-site-verification: google1a2b3c.html".
     * Only filenames the admin has approved are served; anything else 404s.
     */
    public function file(string $gscfile)
    {
        abort_unless(Seo::isHtmlFile($gscfile), 404);

        return response(Seo::htmlFileBody($gscfile), 200, ['Content-Type' => 'text/plain']);
    }
}
