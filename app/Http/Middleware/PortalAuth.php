<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalAuth
{
    /**
     * Block access to the portal unless the visitor has entered the shared password.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Shared-password gate. The internal team portal exposes admin CRM, campaign
        // sends and the messaging studio, so it must not be publicly reachable.
        if (! $request->session()->get('portal_authed')) {
            return redirect()->route('portal.login')->with('intended', $request->fullUrl());
        }

        return $next($request);
    }
}
