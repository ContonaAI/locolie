<?php

namespace App\Http\Controllers;

use App\Models\Redemption;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * The shopper-facing "Your locolie" report - money saved, places visited and
 * local businesses supported. Reached two ways:
 *  - a shopper enters their email at /my-locolie (the entry form), or
 *  - they tap a signed link we drop into the emails/SMS we send them.
 *
 * The view itself is opened via a signed URL so the personalised report cannot
 * be guessed or scraped by changing an email in the address bar.
 */
class CustomerReportController extends Controller
{
    public function entry()
    {
        return view('customer.entry');
    }

    public function lookup(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $exists = Redemption::where('customer_email', $data['email'])->exists();
        if (! $exists) {
            return back()->withInput()->with('notfound', "We could not find any locolie activity for {$data['email']} yet. Redeem an offer and check back.");
        }

        // 30-day signed link - the same kind we embed in customer messages.
        return redirect(URL::temporarySignedRoute('customer.report', now()->addDays(30), ['email' => $data['email']]));
    }

    public function show(Request $request, ReportingService $reporting)
    {
        $email = (string) $request->query('email');

        return view('customer.report', [
            'report' => $reporting->forCustomer($email),
        ]);
    }
}
