<?php

namespace App\Http\Controllers;

use App\Models\MEnrollment;
use App\Models\MStudent;
use App\Models\MCourse;
use App\Models\MobileSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MEnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enrollments = MEnrollment::with(['student', 'course', 'mobileSchool'])
            ->orderBy('enrollment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $students = MStudent::where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $courses = MCourse::where('is_active', true)
            ->orderBy('course_name')
            ->get();

        $mobileSchools = MobileSchool::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get current academic year
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        $academicYear = "{$currentYear}/{$nextYear}";

        return view('ktvtc.mschool.enrollments.index', compact('enrollments', 'students', 'courses', 'mobileSchools', 'academicYear'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:m_students,student_id',
            'course_id' => 'required|exists:m_courses,course_id',
            'mobile_school_id' => 'nullable|exists:mobile_schools,id',
            'enrollment_date' => 'required|date',
            'start_date' => 'nullable|date|after_or_equal:enrollment_date',
            'end_date' => 'nullable|date|after:start_date',
            'completion_date' => 'nullable|date|after:enrollment_date',
            'status' => 'required|in:pending,active,completed,cancelled,suspended',
            'progress' => 'nullable|numeric|min:0|max:100',
            'current_semester' => 'nullable|integer|min:1|max:8',
            'total_fees' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid,overdue',
            'academic_year' => 'required|string|max:20',
            'semester' => 'nullable|string|max:20',
            'batch' => 'nullable|string|max:50',
            'certificate_number' => 'nullable|string|unique:m_enrollments,certificate_number',
            'certificate_issue_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Validate paid amount doesn't exceed total fees
        if ($validated['paid_amount'] > $validated['total_fees']) {
            return redirect()->back()
                ->with('error', 'Paid amount cannot exceed total fees.')
                ->withInput();
        }

        // Check for duplicate enrollment (same student, course, and academic year)
        $existing = MEnrollment::where('student_id', $validated['student_id'])
            ->where('course_id', $validated['course_id'])
            ->where('academic_year', $validated['academic_year'])
            ->exists();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'This student is already enrolled in the selected course for the specified academic year.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate enrollment code if not provided
            if (empty($validated['enrollment_code'])) {
                $validated['enrollment_code'] = $this->generateEnrollmentCode();
            }

            // Auto-calculate payment status based on amounts
            if (empty($validated['payment_status'])) {
                $validated['payment_status'] = $this->calculatePaymentStatus(
                    $validated['total_fees'] ?? 0,
                    $validated['paid_amount'] ?? 0
                );
            }

            // Add tracking information
            $validated['created_by'] = auth()->id();
            $validated['ip_address'] = $request->ip();
            $validated['user_agent'] = $request->userAgent();

            $enrollment = MEnrollment::create($validated);

            DB::commit();

            return redirect()->route('enrollments.index')
                ->with('success', 'Enrollment created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create enrollment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $enrollment = MEnrollment::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'required|exists:m_students,student_id',
            'course_id' => 'required|exists:m_courses,course_id',
            'mobile_school_id' => 'nullable|exists:mobile_schools,id',
            'enrollment_date' => 'required|date',
            'start_date' => 'nullable|date|after_or_equal:enrollment_date',
            'end_date' => 'nullable|date|after:start_date',
            'completion_date' => 'nullable|date|after:enrollment_date',
            'status' => 'required|in:pending,active,completed,cancelled,suspended',
            'progress' => 'nullable|numeric|min:0|max:100',
            'current_semester' => 'nullable|integer|min:1|max:8',
            'total_fees' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid,overdue',
            'academic_year' => 'required|string|max:20',
            'semester' => 'nullable|string|max:20',
            'batch' => 'nullable|string|max:50',
            'certificate_number' => 'nullable|string|unique:m_enrollments,certificate_number,' . $enrollment->enrollment_id . ',enrollment_id',
            'certificate_issue_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Validate paid amount doesn't exceed total fees
        if ($validated['paid_amount'] > $validated['total_fees']) {
            return redirect()->back()
                ->with('error', 'Paid amount cannot exceed total fees.')
                ->withInput();
        }

        // Check for duplicate enrollment (excluding current record)
        $existing = MEnrollment::where('student_id', $validated['student_id'])
            ->where('course_id', $validated['course_id'])
            ->where('academic_year', $validated['academic_year'])
            ->where('enrollment_id', '!=', $enrollment->enrollment_id)
            ->exists();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'This student is already enrolled in the selected course for the specified academic year.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Auto-calculate payment status based on amounts if not explicitly set
            if (empty($validated['payment_status'])) {
                $validated['payment_status'] = $this->calculatePaymentStatus(
                    $validated['total_fees'] ?? 0,
                    $validated['paid_amount'] ?? 0
                );
            }

            // Update tracking information
            $validated['updated_by'] = auth()->id();
            $validated['ip_address'] = $request->ip();
            $validated['user_agent'] = $request->userAgent();

            $enrollment->update($validated);

            DB::commit();

            return redirect()->route('enrollments.index')
                ->with('success', 'Enrollment updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update enrollment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $enrollment = MEnrollment::findOrFail($id);

        try {
            DB::beginTransaction();

            // Check if enrollment has certificate issued
            if ($enrollment->certificate_number) {
                return redirect()->route('enrollments.index')
                    ->with('error', 'Cannot delete enrollment. A certificate has been issued. Please revoke the certificate first.');
            }

            $enrollment->delete();

            DB::commit();

            return redirect()->route('enrollments.index')
                ->with('success', 'Enrollment deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('enrollments.index')
                ->with('error', 'Failed to delete enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique enrollment code
     */
    private function generateEnrollmentCode()
    {
        $prefix = 'ENR';
        $year = date('Y');

        do {
            $random = Str::upper(Str::random(6));
            $code = $prefix . $year . $random;
        } while (MEnrollment::where('enrollment_code', $code)->exists());

        return $code;
    }

    /**
     * Calculate payment status based on amounts
     */
    private function calculatePaymentStatus($totalFees, $paidAmount)
    {
        if ($totalFees == 0) {
            return 'paid';
        }

        if ($paidAmount == 0) {
            return 'pending';
        }

        if ($paidAmount >= $totalFees) {
            return 'paid';
        }

        if ($paidAmount > 0) {
            return 'partial';
        }

        return 'pending';
    }
}
