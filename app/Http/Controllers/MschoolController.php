<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MschoolController extends Controller
{
    //
    public function dashboard()
    {
        return view('ktvtc.mschool.dashboard');
    }
}
