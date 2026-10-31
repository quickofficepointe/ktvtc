<?php

namespace App\Http\Controllers;

use App\Models\MCourse;
use App\Models\MStudent;
use App\Models\MEnrollment;
use App\Models\MExam;
use App\Models\MSubject;
use App\Models\MobileSchool;
use App\Models\MCourseCategories;
use App\Models\MAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MschoolController extends Controller
{
    /**
     * Mobile school overview: students, enrollments, attendance and certificates.
     */
    public function dashboard(\App\Services\Dashboards\RoleDashboards $dashboards)
    {
        return view('ktvtc.mschool.dashboard', $dashboards->mschool());
    }

}
