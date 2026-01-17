<?php

namespace App\Http\Controllers;

use App\Models\CourseIntakes;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseIntakesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $intakes = CourseIntakes::with('course')
            ->latest()
            ->get();
        $courses = Course::all();

        return view('ktvtc.website.courseintake.index', compact('courses', 'intakes'));
    }

    /**
     * Display course intakes for public website
     */
    public function publicIndex()
    {
        $courses = Course::with(['intakes' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('year', 'desc')
                      ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')");
            }])
            ->where('is_active', true)
            ->has('intakes')
            ->orderBy('name', 'asc')
            ->get();

        return view('ktvtc.website.courseintake.courseintake', compact('courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id'            => 'required|exists:courses,id',
            'month'                => 'required|in:January,February,March,April,May,June,July,August,September,October,November,December',
            'year'                 => 'nullable|digits:4|integer|min:2000',
            'application_deadline' => 'nullable|date',
            'notes'                => 'nullable|string',
            'is_active'            => 'boolean',
        ]);

        CourseIntakes::create([
            'course_id'            => $request->course_id,
            'month'                => $request->month,
            'year'                 => $request->year,
            'application_deadline' => $request->application_deadline,
            'notes'                => $request->notes,
            'is_active'            => $request->is_active ?? true,
            'created_by'           => Auth::id(),
            'ip_address'           => $request->ip(),
            'user_agent'           => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Course intake added successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseIntakes $course_intake)
    {
        $request->validate([
            'course_id'            => 'required|exists:courses,id',
            'month'                => 'required|in:January,February,March,April,May,June,July,August,September,October,November,December',
            'year'                 => 'nullable|digits:4|integer|min:2000',
            'application_deadline' => 'nullable|date',
            'notes'                => 'nullable|string',
            'is_active'            => 'boolean',
        ]);

        $course_intake->update([
            'course_id'            => $request->course_id,
            'month'                => $request->month,
            'year'                 => $request->year,
            'application_deadline' => $request->application_deadline,
            'notes'                => $request->notes,
            'is_active'            => $request->is_active ?? true,
            'updated_by'           => Auth::id(),
            'ip_address'           => $request->ip(),
            'user_agent'           => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Course intake updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseIntakes $course_intake)
    {
        $course_intake->delete();

        return redirect()->back()->with('success', 'Course intake deleted successfully.');
    }
}
