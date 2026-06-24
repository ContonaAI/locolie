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
        // Password gate disabled short-term — portal is open. Auto-authorise so the
        // layout nav still renders. To re-enable, restore the redirect below.
        $request->session()->put('portal_authed', true);

        return $next($request);

        // if (! $request->session()->get('portal_authed')) {
        //     return redirect()->route('portal.login')->with('intended', $request->fullUrl());
        // }
        // return $next($request);
    }
}
