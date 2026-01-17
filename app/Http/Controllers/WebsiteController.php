<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    //
    public function dashboard()
    {
        return view('ktvtc.website.dashboard');
    }
}
