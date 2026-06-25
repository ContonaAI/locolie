<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(protected LoyaltyService $loyalty) {}

    /**
     * Loyalty snapshot for a business (public). Pass the shopper's email to get
     * their personal progress + any rewards waiting; omit it for the scheme only.
     */
    public function progress(Request $request)
    {
        $data = $request->validate([
            'business' => ['required', 'string'],     // slug
            'email' => ['nullable', 'email'],
        ]);

        $business = Business::where('slug', $data['business'])->firstOr(fn () => abort(404));

        return response()->json($this->loyalty->snapshot($business, $data['email'] ?? null));
    }
}
