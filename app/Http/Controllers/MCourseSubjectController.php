<?php

namespace App\Http\Controllers;

use App\Models\MCourseSubject;
use App\Models\MCourse;
use App\Models\MSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MCourseSubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courseSubjects = MCourseSubject::with(['course', 'subject'])
            ->orderBy('course_id')
            ->orderBy('year')
            ->orderBy('semester')
            ->orderBy('sort_order')
            ->get();

        $courses = MCourse::where('is_active', true)
            ->orderBy('course_name')
            ->get();

        $subjects = MSubject::where('is_active', true)
            ->orderBy('subject_name')
            ->get();

        return view('ktvtc.mschool.mcoursesubject.index', compact('courseSubjects', 'courses', 'subjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:m_courses,course_id',
            'subject_id' => 'required|exists:m_subjects,subject_id',
            'semester' => 'nullable|integer|min:1|max:8',
            'year' => 'nullable|integer|min:1|max:4',
            'is_compulsory' => 'boolean',
            'credit_hours' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Check for duplicate course-subject combination
        $existing = MCourseSubject::where('course_id', $validated['course_id'])
            ->where('subject_id', $validated['subject_id'])
            ->exists();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'This subject is already associated with the selected course.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $courseSubject = MCourseSubject::create($validated);

            DB::commit();

            return redirect()->route('course-subjects.index')
                ->with('success', 'Course subject association created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create course subject association: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $courseSubject = MCourseSubject::findOrFail($id);

        $validated = $request->validate([
            'course_id' => 'required|exists:m_courses,course_id',
            'subject_id' => 'required|exists:m_subjects,subject_id',
            'semester' => 'nullable|integer|min:1|max:8',
            'year' => 'nullable|integer|min:1|max:4',
            'is_compulsory' => 'boolean',
            'credit_hours' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Check for duplicate course-subject combination (excluding current record)
        $existing = MCourseSubject::where('course_id', $validated['course_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('course_subject_id', '!=', $courseSubject->course_subject_id)
            ->exists();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'This subject is already associated with the selected course.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $courseSubject->update($validated);

            DB::commit();

            return redirect()->route('course-subjects.index')
                ->with('success', 'Course subject association updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update course subject association: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $courseSubject = MCourseSubject::findOrFail($id);

        try {
            DB::beginTransaction();

            $courseSubject->delete();

            DB::commit();

            return redirect()->route('course-subjects.index')
                ->with('success', 'Course subject association deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('course-subjects.index')
                ->with('error', 'Failed to delete course subject association: ' . $e->getMessage());
        }
    }
}
