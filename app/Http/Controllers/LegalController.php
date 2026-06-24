<?php

namespace App\Http\Controllers;

/** Public legal pages: Terms, Privacy and Cookies. */
class LegalController extends Controller
{
    public function terms()
    {
        return view('site.legal.terms');
    }

    public function privacy()
    {
        return view('site.legal.privacy');
    }

    public function cookies()
    {
        return view('site.legal.cookies');
    }
}
