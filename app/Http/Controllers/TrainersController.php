<?php
// app/Http/Controllers/TrainersController.php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Attachment;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainersController extends Controller
{
    // ============================================================
    // DASHBOARD
    // ============================================================

    /**
     * Trainer overview: my units, marks still to enter or submit, and feedback.
     */
    public function dashboard(\App\Services\Dashboards\RoleDashboards $dashboards)
    {
        return view('ktvtc.trainers.dashboard', $dashboards->trainer(auth()->user()));
    }


    public function students(Request $request)
    {
        $trainer   = Auth::user();
        $courseIds = $trainer->trainerCourseIds();

        $terms = AcademicTerm::orderByDesc('academic_year')
            ->orderBy('term_number')
            ->get();

        $termId = $request->input('term_id')
            ?? AcademicTerm::where('is_current', true)->value('id');

        $enrollments = Enrollment::with(['student', 'course', 'term'])
            ->forCourses($courseIds)
            ->when($termId, fn ($q) => $q->where('term_id', $termId))
            ->when($request->filled('course_id'),
                fn ($q) => $q->where('course_id', $request->course_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($sub) use ($s) {
                    $sub->where('student_name', 'like', "%{$s}%")
                        ->orWhere('student_number', 'like', "%{$s}%");
                });
            })
            ->orderBy('student_name')
            ->paginate(25)
            ->withQueryString();

        $courses = $trainer->activeCoursesAsTrainer()->orderBy('name')->get();

        return view('ktvtc.trainers.students', compact(
            'enrollments',
            'terms',
            'courses',
            'termId'
        ));
    }
}