<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        return view('ktvtc.students.dashboard', [
            'isApproved' => $user->is_approved,
        ]);
    }
}
