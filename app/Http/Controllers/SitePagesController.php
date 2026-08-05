<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SitePagesController extends Controller
{
    public function now(): View
    {
        return view('site.now');
    }

    public function about(): View
    {
        return view('site.about');
    }
}
