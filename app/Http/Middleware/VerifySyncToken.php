<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySyncToken
{
    /**
     * Guard the sync API with the shared secret token. Rejects any request whose
     * Bearer token doesn't match config('sync.token'). Constant-time compared.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('sync.token');

        if (blank($expected)) {
            return response()->json(['message' => 'Sync is not configured (no SYNC_TOKEN set).'], 503);
        }

        if (! hash_equals($expected, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Invalid or missing sync token.'], 401);
        }

        return $next($request);
    }
}
