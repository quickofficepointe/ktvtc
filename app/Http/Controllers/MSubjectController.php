<?php

namespace App\Http\Controllers;

use App\Models\MSubject;
use App\Models\MCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MSubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subjects = MSubject::with(['course', 'prerequisite'])
            ->orderBy('is_active', 'desc')
            ->orderBy('sort_order')
            ->orderBy('subject_name')
            ->get();

        $courses = MCourse::where('is_active', true)
            ->orderBy('course_name')
            ->get();

        $prerequisites = MSubject::where('is_active', true)
            ->orderBy('subject_name')
            ->get();

        return view('ktvtc.mschool.subject.index', compact('subjects', 'courses', 'prerequisites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_name' => 'required|string|max:255',
            'subject_code' => 'nullable|string|max:50|unique:m_subjects,subject_code',
            'description' => 'nullable|string',
            'course_id' => 'nullable|exists:m_courses,course_id',
            'credit_hours' => 'nullable|integer|min:0',
            'duration_weeks' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_core' => 'boolean',
            'prerequisite_subject_id' => 'nullable|exists:m_subjects,subject_id',
            'exam_weight' => 'required|integer|min:0|max:100',
            'assignment_weight' => 'required|integer|min:0|max:100',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'syllabus_file' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
        ]);

        // Validate that exam and assignment weights sum to 100
        if (($validated['exam_weight'] + $validated['assignment_weight']) !== 100) {
            return redirect()->back()
                ->with('error', 'Exam weight and assignment weight must sum to 100%.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                $imagePath = $request->file('cover_image')->store('subjects/cover-images', 'public');
                $validated['cover_image'] = $imagePath;
            }

            // Handle syllabus file upload
            if ($request->hasFile('syllabus_file')) {
                $syllabusPath = $request->file('syllabus_file')->store('subjects/syllabus', 'public');
                $validated['syllabus_file'] = $syllabusPath;
            }

            $subject = MSubject::create($validated);

            DB::commit();

            return redirect()->route('subjects.index')
                ->with('success', 'Subject created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create subject: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subject = MSubject::findOrFail($id);

        $validated = $request->validate([
            'subject_name' => 'required|string|max:255',
            'subject_code' => 'nullable|string|max:50|unique:m_subjects,subject_code,' . $subject->subject_id . ',subject_id',
            'description' => 'nullable|string',
            'course_id' => 'nullable|exists:m_courses,course_id',
            'credit_hours' => 'nullable|integer|min:0',
            'duration_weeks' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_core' => 'boolean',
            'prerequisite_subject_id' => 'nullable|exists:m_subjects,subject_id',
            'exam_weight' => 'required|integer|min:0|max:100',
            'assignment_weight' => 'required|integer|min:0|max:100',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'syllabus_file' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
        ]);

        // Validate that exam and assignment weights sum to 100
        if (($validated['exam_weight'] + $validated['assignment_weight']) !== 100) {
            return redirect()->back()
                ->with('error', 'Exam weight and assignment weight must sum to 100%.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                // Delete old image if exists
                if ($subject->cover_image) {
                    Storage::disk('public')->delete($subject->cover_image);
                }

                $imagePath = $request->file('cover_image')->store('subjects/cover-images', 'public');
                $validated['cover_image'] = $imagePath;
            }

            // Handle syllabus file upload
            if ($request->hasFile('syllabus_file')) {
                // Delete old syllabus file if exists
                if ($subject->syllabus_file) {
                    Storage::disk('public')->delete($subject->syllabus_file);
                }

                $syllabusPath = $request->file('syllabus_file')->store('subjects/syllabus', 'public');
                $validated['syllabus_file'] = $syllabusPath;
            }

            $subject->update($validated);

            DB::commit();

            return redirect()->route('subjects.index')
                ->with('success', 'Subject updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update subject: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subject = MSubject::findOrFail($id);

        try {
            DB::beginTransaction();

            // Check if subject is used as prerequisite for other subjects
            if ($subject->requiredBy()->exists()) {
                return redirect()->route('subjects.index')
                    ->with('error', 'Cannot delete subject. It is set as prerequisite for other subjects. Please update those subjects first.');
            }

            // Check if subject has course associations
            if ($subject->courses()->exists()) {
                return redirect()->route('subjects.index')
                    ->with('error', 'Cannot delete subject. It is associated with courses. Please remove the associations first.');
            }

            // Delete cover image if exists
            if ($subject->cover_image) {
                Storage::disk('public')->delete($subject->cover_image);
            }

            // Delete syllabus file if exists
            if ($subject->syllabus_file) {
                Storage::disk('public')->delete($subject->syllabus_file);
            }

            $subject->delete();

            DB::commit();

            return redirect()->route('subjects.index')
                ->with('success', 'Subject deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('subjects.index')
                ->with('error', 'Failed to delete subject: ' . $e->getMessage());
        }
    }
}
