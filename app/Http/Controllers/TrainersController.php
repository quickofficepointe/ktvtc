<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrainersController extends Controller
{
    //
    public function dashboard()
    {
        return view('ktvtc.trainers.dashboard');
    }
}
