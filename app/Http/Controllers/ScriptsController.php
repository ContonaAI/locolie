<?php

namespace App\Http\Controllers;

use App\Support\HeadScripts;
use Illuminate\Http\Request;

/**
 * Admin-managed custom <head> scripts (analytics, pixels, verification tags).
 * Saved from the Settings page; rendered live across the site with no redeploy.
 */
class ScriptsController extends Controller
{
    public function save(Request $request)
    {
        $request->validate(['head_scripts' => ['nullable', 'string', 'max:20000']]);

        HeadScripts::set($request->input('head_scripts'));

        return back()->with('status', 'Head scripts saved - live across the site now.');
    }
}
