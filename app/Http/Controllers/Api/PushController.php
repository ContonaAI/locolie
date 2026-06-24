<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PushService;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function __construct(protected PushService $push) {}

    /** A shopper's browser registers its web-push subscription. */
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:600'],
            'keys' => ['nullable', 'array'],
            'category_prefs' => ['nullable', 'array'],
        ]);

        $this->push->subscribe($data);

        return response()->json(['ok' => true]);
    }
}
