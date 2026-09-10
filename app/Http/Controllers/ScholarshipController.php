<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    public function dashboard()
    {
        return view('scholarship.dashboard');
    }
}