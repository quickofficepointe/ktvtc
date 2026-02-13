<?php

namespace App\Http\Controllers;

use App\Models\MExamResult;
use App\Models\MExam;
use App\Models\MStudent;
use App\Models\MEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MExamResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    // Get ALL exam results without any conditions
    $results = MExamResult::with(['exam', 'student', 'enrollment.course'])
        ->orderBy('created_at', 'desc')
        ->orderBy('exam_id')
        ->get();

    // Get ALL exams (including unpublished and inactive)
    $exams = MExam::orderBy('exam_name')
        ->get();

    // Get ALL students (including inactive)
    $students = MStudent::orderBy('first_name')
        ->orderBy('last_name')
        ->get();

    // Get ALL enrollments (including inactive)
    $enrollments = MEnrollment::with(['student', 'course'])
        ->orderBy('enrollment_date', 'desc')
        ->get();

    return view('ktvtc.mschool.exam.examresults', compact(
        'results', 'exams', 'students', 'enrollments'
    ));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:m_exams,exam_id',
            'student_id' => 'required|exists:m_students,student_id',
            'enrollment_id' => 'required|exists:m_enrollments,enrollment_id',
            'marks_obtained' => 'required|numeric|min:0',
            'total_marks' => 'required|numeric|min:0',
            'percentage' => 'required|numeric|min:0|max:100',
            'grade' => 'nullable|string|max:5',
            'grade_point' => 'nullable|numeric|min:0|max:4',
            'remarks' => 'nullable|string|max:500',
            'status' => 'required|in:pending,graded,absent,cheated,special_case',
            'attempt_number' => 'required|integer|min:1',
            'attempt_date' => 'nullable|date',
            'time_taken_minutes' => 'nullable|integer|min:1',
            'section_marks' => 'nullable|array',
            'question_wise_marks' => 'nullable|array',
            'grading_notes' => 'nullable|string',
            'is_absent' => 'boolean',
            'is_retake' => 'boolean',
            'is_supplementary' => 'boolean',
            'absent_reason' => 'nullable|string|max:500',
            'class_rank' => 'nullable|integer|min:1',
            'total_students' => 'nullable|integer|min:1',
            'class_average' => 'nullable|numeric|min:0|max:100',
        ]);

        // Validate that marks obtained don't exceed total marks
        if ($validated['marks_obtained'] > $validated['total_marks']) {
            return redirect()->back()
                ->with('error', 'Marks obtained cannot exceed total marks.')
                ->withInput();
        }

        // Validate that percentage matches calculated percentage
        $calculatedPercentage = ($validated['marks_obtained'] / $validated['total_marks']) * 100;
        if (abs($validated['percentage'] - $calculatedPercentage) > 0.1) {
            return redirect()->back()
                ->with('error', 'Percentage does not match the calculated value based on marks.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Check for duplicate result (same exam, student, and attempt)
            $existingResult = MExamResult::where('exam_id', $validated['exam_id'])
                ->where('student_id', $validated['student_id'])
                ->where('attempt_number', $validated['attempt_number'])
                ->exists();

            if ($existingResult) {
                return redirect()->back()
                    ->with('error', 'A result for this exam, student, and attempt number already exists.')
                    ->withInput();
            }

            // Auto-calculate grade if not provided
            if (empty($validated['grade']) && $validated['status'] === 'graded') {
                $validated['grade'] = $this->calculateGrade($validated['percentage']);
            }

            // Auto-calculate grade point if not provided
            if (empty($validated['grade_point']) && $validated['status'] === 'graded' && !empty($validated['grade'])) {
                $validated['grade_point'] = $this->calculateGradePoint($validated['grade']);
            }

            // Set graded information if status is graded
            if ($validated['status'] === 'graded') {
                $validated['graded_by'] = Auth::id();
                $validated['graded_at'] = now();
            }

            // Handle absent status
            if ($validated['is_absent']) {
                $validated['status'] = 'absent';
                $validated['marks_obtained'] = 0;
                $validated['percentage'] = 0;
            }

            // Add created by information
            $validated['created_by'] = Auth::id();

            $result = MExamResult::create($validated);

            // Update class statistics if provided
            if ($request->has('class_rank') || $request->has('total_students') || $request->has('class_average')) {
                $this->updateClassStatistics($result->exam_id);
            }

            DB::commit();

            return redirect()->route('exam-results.index')
                ->with('success', 'Exam result created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create exam result: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MExamResult $examResult)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:m_exams,exam_id',
            'student_id' => 'required|exists:m_students,student_id',
            'enrollment_id' => 'required|exists:m_enrollments,enrollment_id',
            'marks_obtained' => 'required|numeric|min:0',
            'total_marks' => 'required|numeric|min:0',
            'percentage' => 'required|numeric|min:0|max:100',
            'grade' => 'nullable|string|max:5',
            'grade_point' => 'nullable|numeric|min:0|max:4',
            'remarks' => 'nullable|string|max:500',
            'status' => 'required|in:pending,graded,absent,cheated,special_case',
            'attempt_number' => 'required|integer|min:1',
            'attempt_date' => 'nullable|date',
            'time_taken_minutes' => 'nullable|integer|min:1',
            'section_marks' => 'nullable|array',
            'question_wise_marks' => 'nullable|array',
            'grading_notes' => 'nullable|string',
            'is_absent' => 'boolean',
            'is_retake' => 'boolean',
            'is_supplementary' => 'boolean',
            'absent_reason' => 'nullable|string|max:500',
            'class_rank' => 'nullable|integer|min:1',
            'total_students' => 'nullable|integer|min:1',
            'class_average' => 'nullable|numeric|min:0|max:100',
        ]);

        // Validate that marks obtained don't exceed total marks
        if ($validated['marks_obtained'] > $validated['total_marks']) {
            return redirect()->back()
                ->with('error', 'Marks obtained cannot exceed total marks.')
                ->withInput();
        }

        // Validate that percentage matches calculated percentage
        $calculatedPercentage = ($validated['marks_obtained'] / $validated['total_marks']) * 100;
        if (abs($validated['percentage'] - $calculatedPercentage) > 0.1) {
            return redirect()->back()
                ->with('error', 'Percentage does not match the calculated value based on marks.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Check for duplicate result (same exam, student, and attempt, excluding current)
            $existingResult = MExamResult::where('exam_id', $validated['exam_id'])
                ->where('student_id', $validated['student_id'])
                ->where('attempt_number', $validated['attempt_number'])
                ->where('result_id', '!=', $examResult->result_id)
                ->exists();

            if ($existingResult) {
                return redirect()->back()
                    ->with('error', 'Another result for this exam, student, and attempt number already exists.')
                    ->withInput();
            }

            // Auto-calculate grade if not provided and status is graded
            if (empty($validated['grade']) && $validated['status'] === 'graded') {
                $validated['grade'] = $this->calculateGrade($validated['percentage']);
            }

            // Auto-calculate grade point if not provided
            if (empty($validated['grade_point']) && $validated['status'] === 'graded' && !empty($validated['grade'])) {
                $validated['grade_point'] = $this->calculateGradePoint($validated['grade']);
            }

            // Update graded information if status changed to graded
            if ($validated['status'] === 'graded' && $examResult->status !== 'graded') {
                $validated['graded_by'] = Auth::id();
                $validated['graded_at'] = now();
            }

            // Handle absent status
            if ($validated['is_absent']) {
                $validated['status'] = 'absent';
                $validated['marks_obtained'] = 0;
                $validated['percentage'] = 0;
            }

            // Add updated by information
            $validated['updated_by'] = Auth::id();

            $examResult->update($validated);

            // Update class statistics if provided
            if ($request->has('class_rank') || $request->has('total_students') || $request->has('class_average')) {
                $this->updateClassStatistics($examResult->exam_id);
            }

            DB::commit();

            return redirect()->route('exam-results.index')
                ->with('success', 'Exam result updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update exam result: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MExamResult $examResult)
    {
        try {
            DB::beginTransaction();

            $examId = $examResult->exam_id;
            $examResult->delete();

            // Update class statistics after deletion
            $this->updateClassStatistics($examId);

            DB::commit();

            return redirect()->route('exam-results.index')
                ->with('success', 'Exam result deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('exam-results.index')
                ->with('error', 'Failed to delete exam result: ' . $e->getMessage());
        }
    }

    /**
     * Calculate grade based on percentage
     */
    private function calculateGrade($percentage)
    {
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 60) return 'C';
        if ($percentage >= 50) return 'D';
        return 'F';
    }

    /**
     * Calculate grade point based on grade
     */
    private function calculateGradePoint($grade)
    {
        $gradePoints = [
            'A' => 4.0,
            'B' => 3.0,
            'C' => 2.0,
            'D' => 1.0,
            'F' => 0.0
        ];

        return $gradePoints[$grade] ?? 0.0;
    }

    /**
     * Update class statistics for an exam
     */
    private function updateClassStatistics($examId)
    {
        $results = MExamResult::where('exam_id', $examId)
            ->where('status', 'graded')
            ->where('is_absent', false)
            ->get();

        if ($results->isEmpty()) {
            return;
        }

        $totalStudents = $results->count();
        $classAverage = $results->avg('percentage');

        // Calculate ranks
        $rankedResults = $results->sortByDesc('percentage')->values();

        foreach ($rankedResults as $index => $result) {
            $result->update([
                'class_rank' => $index + 1,
                'total_students' => $totalStudents,
                'class_average' => $classAverage
            ]);
        }
    }

    /**
     * Get results by exam
     */
    public function getResultsByExam($examId)
    {
        $results = MExamResult::with(['student', 'enrollment'])
            ->where('exam_id', $examId)
            ->orderBy('marks_obtained', 'desc')
            ->get();

        return response()->json($results);
    }

    /**
     * Get results by student
     */
    public function getResultsByStudent($studentId)
    {
        $results = MExamResult::with(['exam', 'exam.course', 'exam.subject'])
            ->where('student_id', $studentId)
            ->orderBy('exam_date', 'desc')
            ->get();

        return response()->json($results);
    }

    /**
     * Bulk update results
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.result_id' => 'required|exists:m_exam_results,result_id',
            'results.*.marks_obtained' => 'required|numeric|min:0',
            'results.*.grade' => 'nullable|string|max:5',
            'results.*.status' => 'required|in:pending,graded,absent,cheated,special_case',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['results'] as $resultData) {
                $result = MExamResult::find($resultData['result_id']);

                if ($result) {
                    $updateData = [
                        'marks_obtained' => $resultData['marks_obtained'],
                        'status' => $resultData['status'],
                        'updated_by' => Auth::id()
                    ];

                    // Calculate percentage
                    $updateData['percentage'] = ($resultData['marks_obtained'] / $result->total_marks) * 100;

                    // Auto-calculate grade if not provided
                    if (empty($resultData['grade']) && $resultData['status'] === 'graded') {
                        $updateData['grade'] = $this->calculateGrade($updateData['percentage']);
                        $updateData['grade_point'] = $this->calculateGradePoint($updateData['grade']);
                    } elseif (!empty($resultData['grade'])) {
                        $updateData['grade'] = $resultData['grade'];
                        $updateData['grade_point'] = $this->calculateGradePoint($resultData['grade']);
                    }

                    // Set graded information if status changed to graded
                    if ($resultData['status'] === 'graded' && $result->status !== 'graded') {
                        $updateData['graded_by'] = Auth::id();
                        $updateData['graded_at'] = now();
                    }

                    $result->update($updateData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Results updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark student as absent
     */
    public function markAbsent(Request $request, MExamResult $examResult)
    {
        try {
            $validated = $request->validate([
                'absent_reason' => 'nullable|string|max:500'
            ]);

            $examResult->update([
                'is_absent' => true,
                'status' => 'absent',
                'marks_obtained' => 0,
                'percentage' => 0,
                'absent_reason' => $validated['absent_reason'],
                'updated_by' => Auth::id()
            ]);

            return redirect()->route('exam-results.index')
                ->with('success', 'Student marked as absent successfully!');

        } catch (\Exception $e) {
            return redirect()->route('exam-results.index')
                ->with('error', 'Failed to mark student as absent: ' . $e->getMessage());
        }
    }

    /**
     * Get exam statistics
     */
    public function statistics()
    {
        $totalResults = MExamResult::count();
        $gradedResults = MExamResult::where('status', 'graded')->count();
        $pendingResults = MExamResult::where('status', 'pending')->count();
        $absentResults = MExamResult::where('is_absent', true)->count();

        return [
            'total' => $totalResults,
            'graded' => $gradedResults,
            'pending' => $pendingResults,
            'absent' => $absentResults,
        ];
    }

    /**
     * Export results to PDF
     */
    public function exportToPdf($examId)
    {
        $exam = MExam::with(['course', 'subject'])->findOrFail($examId);
        $results = MExamResult::with(['student'])
            ->where('exam_id', $examId)
            ->where('status', 'graded')
            ->orderBy('marks_obtained', 'desc')
            ->get();

        // You would typically use a PDF library like DomPDF or TCPDF here
        // This is a placeholder for the PDF export functionality

        return response()->json([
            'exam' => $exam,
            'results' => $results,
            'message' => 'PDF export functionality would be implemented here'
        ]);
    }
}
