<?php

namespace App\Http\Controllers;

use App\Models\MExam;
use App\Models\MCourse;
use App\Models\MSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exams = MExam::with(['course', 'subject', 'results'])
            ->orderBy('exam_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $courses = MCourse::where('is_active', true)
            ->orderBy('course_name')
            ->get();

        $subjects = MSubject::where('is_active', true)
            ->orderBy('subject_name')
            ->get();

        $academicYear = date('Y') . '/' . (date('Y') + 1);

        return view('ktvtc.mschool.exams.index', compact('exams', 'courses', 'subjects', 'academicYear'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_name' => 'required|string|max:255',
            'exam_code' => 'nullable|string|max:50|unique:m_exams,exam_code',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:m_subjects,subject_id',
            'course_id' => 'required|exists:m_courses,course_id',
            'exam_type' => 'required|in:cat1,cat2,cat3,main_exam,assignment,practical,project,quiz,final',
            'exam_category' => 'nullable|string|max:100',
            'exam_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'duration_minutes' => 'nullable|integer|min:1',
            'total_marks' => 'required|numeric|min:0',
            'passing_marks' => 'nullable|numeric|min:0|lte:total_marks',
            'weightage' => 'nullable|numeric|min:0|max:100',
            'number_of_questions' => 'nullable|integer|min:1',
            'question_types' => 'nullable|array',
            'question_types.*' => 'in:multiple_choice,essay,practical,true_false,short_answer,matching,fill_blank,oral',
            'sections' => 'nullable|array',
            'venue' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'materials_allowed' => 'nullable|string',
            'academic_year' => 'required|string|max:20',
            'semester' => 'nullable|string|max:50',
            'term' => 'nullable|integer|min:1|max:3',
            'is_published' => 'boolean',
            'is_active' => 'boolean',
            'allow_retake' => 'boolean',
            'max_attempts' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Generate exam code if not provided
            if (empty($validated['exam_code'])) {
                $validated['exam_code'] = $this->generateExamCode($validated['exam_type']);
            }

            // Add created by information
            $validated['created_by'] = Auth::id();

            // Handle question types array
            if (isset($validated['question_types'])) {
                $validated['question_types'] = array_values($validated['question_types']);
            }

            // Handle sections if provided
            if ($request->has('sections')) {
                $validated['sections'] = $this->processSections($request->sections);
            }

            $exam = MExam::create($validated);

            DB::commit();

            return redirect()->route('exams.index')
                ->with('success', 'Exam created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create exam: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MExam $exam)
    {
        $validated = $request->validate([
            'exam_name' => 'required|string|max:255',
            'exam_code' => 'nullable|string|max:50|unique:m_exams,exam_code,' . $exam->exam_id . ',exam_id',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:m_subjects,subject_id',
            'course_id' => 'required|exists:m_courses,course_id',
            'exam_type' => 'required|in:cat1,cat2,cat3,main_exam,assignment,practical,project,quiz,final',
            'exam_category' => 'nullable|string|max:100',
            'exam_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'duration_minutes' => 'nullable|integer|min:1',
            'total_marks' => 'required|numeric|min:0',
            'passing_marks' => 'nullable|numeric|min:0|lte:total_marks',
            'weightage' => 'nullable|numeric|min:0|max:100',
            'number_of_questions' => 'nullable|integer|min:1',
            'question_types' => 'nullable|array',
            'question_types.*' => 'in:multiple_choice,essay,practical,true_false,short_answer,matching,fill_blank,oral',
            'sections' => 'nullable|array',
            'venue' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'materials_allowed' => 'nullable|string',
            'academic_year' => 'required|string|max:20',
            'semester' => 'nullable|string|max:50',
            'term' => 'nullable|integer|min:1|max:3',
            'is_published' => 'boolean',
            'is_active' => 'boolean',
            'allow_retake' => 'boolean',
            'max_attempts' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Add updated by information
            $validated['updated_by'] = Auth::id();

            // Handle question types array
            if (isset($validated['question_types'])) {
                $validated['question_types'] = array_values($validated['question_types']);
            } else {
                $validated['question_types'] = null;
            }

            // Handle sections if provided
            if ($request->has('sections')) {
                $validated['sections'] = $this->processSections($request->sections);
            }

            $exam->update($validated);

            DB::commit();

            return redirect()->route('exams.index')
                ->with('success', 'Exam updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update exam: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MExam $exam)
    {
        try {
            DB::beginTransaction();

            // Check if exam has results
            if ($exam->results()->exists()) {
                return redirect()->route('exams.index')
                    ->with('error', 'Cannot delete exam. It has associated results. Please delete the results first.');
            }

            $exam->delete();

            DB::commit();

            return redirect()->route('exams.index')
                ->with('success', 'Exam deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('exams.index')
                ->with('error', 'Failed to delete exam: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique exam code
     */
    private function generateExamCode($examType)
    {
        $prefix = strtoupper(substr($examType, 0, 3));
        $year = date('Y');
        $month = date('m');

        do {
            $random = strtoupper(\Illuminate\Support\Str::random(4));
            $code = $prefix . $year . $month . $random;
        } while (MExam::where('exam_code', $code)->exists());

        return $code;
    }

    /**
     * Process sections data
     */
    private function processSections($sections)
    {
        if (!is_array($sections)) {
            return null;
        }

        $processedSections = [];
        foreach ($sections as $section) {
            if (!empty($section['name']) && !empty($section['marks'])) {
                $processedSections[] = [
                    'name' => $section['name'],
                    'marks' => (float) $section['marks'],
                    'questions' => isset($section['questions']) ? (int) $section['questions'] : 0,
                    'description' => $section['description'] ?? null,
                ];
            }
        }

        return !empty($processedSections) ? $processedSections : null;
    }

    /**
     * Get exam statistics
     */
    public function statistics()
    {
        $totalExams = MExam::count();
        $publishedExams = MExam::where('is_published', true)->count();
        $activeExams = MExam::where('is_active', true)->count();
        $mainExams = MExam::where('exam_type', 'main_exam')->count();

        return [
            'total' => $totalExams,
            'published' => $publishedExams,
            'active' => $activeExams,
            'main_exams' => $mainExams,
        ];
    }

    /**
     * Toggle exam publication status
     */
    public function togglePublication(MExam $exam)
    {
        try {
            $exam->update([
                'is_published' => !$exam->is_published,
                'updated_by' => Auth::id()
            ]);

            $status = $exam->is_published ? 'published' : 'unpublished';

            return redirect()->route('exams.index')
                ->with('success', "Exam {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()->route('exams.index')
                ->with('error', 'Failed to update exam status: ' . $e->getMessage());
        }
    }

    /**
     * Toggle exam active status
     */
    public function toggleActive(MExam $exam)
    {
        try {
            $exam->update([
                'is_active' => !$exam->is_active,
                'updated_by' => Auth::id()
            ]);

            $status = $exam->is_active ? 'activated' : 'deactivated';

            return redirect()->route('exams.index')
                ->with('success', "Exam {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()->route('exams.index')
                ->with('error', 'Failed to update exam status: ' . $e->getMessage());
        }
    }

    /**
     * Get exams by course
     */
    public function getExamsByCourse($courseId)
    {
        $exams = MExam::with('subject')
            ->where('course_id', $courseId)
            ->where('is_published', true)
            ->where('is_active', true)
            ->orderBy('exam_date', 'desc')
            ->get();

        return response()->json($exams);
    }

    /**
     * Get exams by subject
     */
    public function getExamsBySubject($subjectId)
    {
        $exams = MExam::with('course')
            ->where('subject_id', $subjectId)
            ->where('is_published', true)
            ->where('is_active', true)
            ->orderBy('exam_date', 'desc')
            ->get();

        return response()->json($exams);
    }

    /**
     * Get upcoming exams
     */
    public function upcomingExams()
    {
        $upcomingExams = MExam::with(['course', 'subject'])
            ->where('exam_date', '>=', now())
            ->where('is_published', true)
            ->where('is_active', true)
            ->orderBy('exam_date', 'asc')
            ->limit(10)
            ->get();

        return response()->json($upcomingExams);
    }
}
