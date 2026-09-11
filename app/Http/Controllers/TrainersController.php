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

    public function dashboard()
    {
        $trainer   = Auth::user();
        $courseIds = $trainer->trainerCourseIds();

        $currentTerm = AcademicTerm::where('is_current', true)->first();

        $stats = [
            'courses'                => count($courseIds),
            'active_students'        => Enrollment::forCourses($courseIds)
                                                ->where('status', 'active')
                                                ->count(),
            'students_on_attachment' => Attachment::forCourses($courseIds)
                                                ->whereIn('status', [
                                                    'trainer_approved',
                                                    'active',
                                                ])
                                                ->count(),
            'pending_approval'       => Attachment::forCourses($courseIds)
                                                ->where('status', 'submitted')
                                                ->count(),
        ];

        $recentEnrollments = Enrollment::with(['student', 'course', 'term'])
            ->forCourses($courseIds)
            ->where('status', 'active')
            ->latest()
            ->limit(10)
            ->get();

        $recentAttachments = Attachment::with(['student', 'company'])
            ->forCourses($courseIds)
            ->whereIn('status', ['submitted', 'trainer_approved', 'needs_revision'])
            ->latest()
            ->limit(5)
            ->get();

        return view('ktvtc.trainers.dashboard', compact(
            'stats',
            'recentEnrollments',
            'recentAttachments',
            'currentTerm'
        ));
    }

    // ============================================================
    // STUDENTS PER TERM
    // ============================================================

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