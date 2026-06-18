<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use App\Models\Campus;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\SmsService;

class EnrollmentController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index(Request $request)
    {
        $query = Enrollment::with(['student', 'course', 'campus']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('intake_year')) {
            $query->where('intake_year', $request->intake_year);
        }

        if ($request->filled('intake_month')) {
            $query->where('intake_month', $request->intake_month);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('course_name', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'paid') {
                $query->whereRaw('total_fees <= amount_paid');
            } elseif ($request->payment_status === 'partial') {
                $query->whereRaw('total_fees > amount_paid AND amount_paid > 0');
            } elseif ($request->payment_status === 'unpaid') {
                $query->where('amount_paid', 0);
            }
        }

        if ($request->filled('exam_required')) {
            $query->where('requires_external_exam', $request->exam_required === 'yes');
        }

        $enrollments = $query->orderBy('created_at', 'desc')->paginate(15);

        // Stats
        $totalEnrollments = Enrollment::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();
        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        $pendingPayment = Enrollment::whereRaw('total_fees > amount_paid')->count();
        $requiresExamRegistration = Enrollment::where('requires_external_exam', true)->count();

        // Data for filters
        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        $courses = Course::orderBy('name')->get();
        $campuses = Campus::orderBy('name')->get();

        // Get intake years
        $intakeYears = Enrollment::select('intake_year')
            ->distinct()
            ->orderBy('intake_year', 'desc')
            ->pluck('intake_year')
            ->toArray();

        if (empty($intakeYears)) {
            $currentYear = date('Y');
            $intakeYears = range($currentYear - 5, $currentYear + 1);
        }

        return view('ktvtc.admin.enrollments.index', compact(
            'enrollments',
            'totalEnrollments',
            'activeEnrollments',
            'completedEnrollments',
            'pendingPayment',
            'requiresExamRegistration',
            'students',
            'courses',
            'campuses',
            'intakeYears'
        ));
    }

    public function create()
    {
        $students = Student::where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $courses = Course::select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        $campuses = Campus::orderBy('name')->get();

        return view('ktvtc.admin.enrollments.create', compact('students', 'courses', 'campuses'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'total_fees' => 'required|numeric|min:0',
            'intake_year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'intake_month' => 'required|string',
            'enrollment_date' => 'required|date',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:active,graduated,completed,dropped,suspended,pending',
            'study_mode' => 'required|string|in:full_time,part_time,evening,weekend,online',
            'student_type' => 'required|string|in:new,continuing,alumnus,transfer',
            'sponsorship_type' => 'required|string|in:self,sponsored,government,scholarship,company',
            'duration_months' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $student = Student::findOrFail($request->student_id);
        $course = Course::findOrFail($request->course_id);

        $enrollment = Enrollment::create([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'campus_id' => $request->campus_id,
            'student_name' => $student->full_name,
            'student_number' => $student->student_number,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'total_fees' => $request->total_fees,
            'amount_paid' => $request->amount_paid ?? 0,
            'intake_year' => $request->intake_year,
            'intake_month' => $request->intake_month,
            'enrollment_date' => $request->enrollment_date,
            'start_date' => $request->start_date,
            'expected_end_date' => $request->expected_end_date,
            'status' => $request->status,
            'study_mode' => $request->study_mode,
            'student_type' => $request->student_type,
            'sponsorship_type' => $request->sponsorship_type,
            'duration_months' => $request->duration_months,
            'remarks' => $request->remarks,
            'is_active' => $request->status === 'active',
        ]);

        return redirect()->route('admin.enrollments.index')
            ->with('success', "Enrollment created successfully for {$enrollment->student_name}.");
    }

    public function show($id)
    {
        $enrollment = Enrollment::with(['student', 'course', 'campus', 'payments'])->findOrFail($id);
        return view('ktvtc.admin.enrollments.show', compact('enrollment'));
    }

    public function edit($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        $courses = Course::orderBy('name')->get();
        $campuses = Campus::orderBy('name')->get();

        return view('ktvtc.admin.enrollments.edit', compact('enrollment', 'students', 'courses', 'campuses'));
    }

    public function update(Request $request, $id)
    {
        $enrollment = Enrollment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'total_fees' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'intake_year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'intake_month' => 'required|string',
            'enrollment_date' => 'required|date',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:active,graduated,completed,dropped,suspended,pending',
            'study_mode' => 'required|string|in:full_time,part_time,evening,weekend,online',
            'student_type' => 'required|string|in:new,continuing,alumnus,transfer',
            'sponsorship_type' => 'required|string|in:self,sponsored,government,scholarship,company',
            'duration_months' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $student = Student::findOrFail($request->student_id);
        $course = Course::findOrFail($request->course_id);

        $enrollment->update([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'campus_id' => $request->campus_id,
            'student_name' => $student->full_name,
            'student_number' => $student->student_number,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'total_fees' => $request->total_fees,
            'amount_paid' => $request->amount_paid ?? $enrollment->amount_paid,
            'intake_year' => $request->intake_year,
            'intake_month' => $request->intake_month,
            'enrollment_date' => $request->enrollment_date,
            'start_date' => $request->start_date,
            'expected_end_date' => $request->expected_end_date,
            'status' => $request->status,
            'study_mode' => $request->study_mode,
            'student_type' => $request->student_type,
            'sponsorship_type' => $request->sponsorship_type,
            'duration_months' => $request->duration_months,
            'remarks' => $request->remarks,
            'is_active' => $request->status === 'active',
        ]);

        return redirect()->route('admin.enrollments.index')
            ->with('success', "Enrollment updated successfully.");
    }

    public function destroy($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->delete();

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Enrollment deleted successfully.');
    }

    public function activate($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->status = 'active';
        $enrollment->is_active = true;
        $enrollment->save();

        return redirect()->back()->with('success', 'Enrollment activated successfully.');
    }

    public function suspend($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->status = 'suspended';
        $enrollment->is_active = false;
        $enrollment->save();

        return redirect()->back()->with('success', 'Enrollment suspended successfully.');
    }

    public function complete($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->status = 'completed';
        $enrollment->actual_end_date = now();
        $enrollment->save();

        return redirect()->back()->with('success', 'Enrollment completed successfully.');
    }

    public function defer($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->status = 'deferred';
        $enrollment->is_active = false;
        $enrollment->save();

        return redirect()->back()->with('success', 'Enrollment deferred successfully.');
    }

    public function registerExam($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->requires_external_exam = true;
        $enrollment->save();

        return redirect()->back()->with('success', 'Student registered for external exam.');
    }

    public function bulkActivate(Request $request)
    {
        $ids = $request->enrollment_ids;
        Enrollment::whereIn('id', $ids)->update(['status' => 'active', 'is_active' => true]);

        return redirect()->back()->with('success', count($ids) . ' enrollments activated.');
    }

    public function bulkComplete(Request $request)
    {
        $ids = $request->enrollment_ids;
        Enrollment::whereIn('id', $ids)->update(['status' => 'completed', 'actual_end_date' => now()]);

        return redirect()->back()->with('success', count($ids) . ' enrollments completed.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->enrollment_ids;
        Enrollment::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', count($ids) . ' enrollments deleted.');
    }

    public function export()
    {
        return redirect()->back()->with('success', 'Export functionality coming soon.');
    }

    public function enrollmentReport()
    {
        $data = Enrollment::with(['student', 'course'])->get();
        return view('ktvtc.admin.enrollments.report', compact('data'));
    }

    public function financialReport()
    {
        $data = Enrollment::with(['student', 'course', 'payments'])->get();
        return view('ktvtc.admin.enrollments.financial-report', compact('data'));
    }

    public function getByStudent(Request $request)
    {
        $studentId = $request->student_id;
        $enrollments = Enrollment::where('student_id', $studentId)->with('course')->get();
        return response()->json($enrollments);
    }

    public function getStats(Request $request)
    {
        $stats = [
            'total' => Enrollment::count(),
            'active' => Enrollment::where('status', 'active')->count(),
            'completed' => Enrollment::where('status', 'completed')->count(),
            'graduated' => Enrollment::where('status', 'graduated')->count(),
            'has_balance' => Enrollment::whereRaw('total_fees > amount_paid')->count(),
            'fully_paid' => Enrollment::whereRaw('total_fees <= amount_paid')->count(),
        ];
        return response()->json($stats);
    }

    public function sendFeeReminders(Request $request)
    {
        $enrollmentIds = $request->enrollment_ids;
        $template = $request->template ?? 'standard';
        $customMessage = $request->custom_message ?? '';

        if (empty($enrollmentIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No students selected.'
            ], 400);
        }

        $enrollments = Enrollment::whereIn('id', $enrollmentIds)
            ->with(['student'])
            ->get();

        $sentCount = 0;
        $failedCount = 0;
        $failed = [];

        foreach ($enrollments as $enrollment) {
            if (!$enrollment->student) {
                $failedCount++;
                $failed[] = [
                    'name' => $enrollment->student_name ?? 'Unknown',
                    'reason' => 'No student record found'
                ];
                continue;
            }

            if (!$enrollment->student->phone) {
                $failedCount++;
                $failed[] = [
                    'name' => $enrollment->student->full_name,
                    'reason' => 'No phone number'
                ];
                continue;
            }

            $balance = $enrollment->total_fees - $enrollment->amount_paid;
            $name = $enrollment->student->full_name;

            if ($template === 'custom' && !empty($customMessage)) {
                $message = $this->parseMessage($customMessage, $name, $balance, $enrollment);
            } else {
                $message = $this->generateTemplateMessage($template, $name, $balance, $enrollment);
            }

            $result = $this->smsService->sendSingleSms($enrollment->student->phone, $message);

            if ($result['success']) {
                $sentCount++;
            } else {
                $failedCount++;
                $failed[] = [
                    'name' => $name,
                    'reason' => $result['message'] ?? 'SMS sending failed'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'total_count' => $enrollments->count(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'failed' => $failed
        ]);
    }

    private function generateTemplateMessage($template, $name, $balance, $enrollment)
    {
        $balanceFormatted = number_format($balance, 2);
        $link = 'www.ktvtc.ac.ke/pay';
        $course = $enrollment->course_name ?? 'course';
        $studentNumber = $enrollment->student_number ?? 'N/A';
        $totalFees = number_format($enrollment->total_fees, 2);
        $paid = number_format($enrollment->amount_paid, 2);

        switch ($template) {
            case 'urgent':
                return "URGENT: Dear {$name}, your fee balance of KES {$balanceFormatted} is now overdue. Please clear your fees immediately to avoid interruption. Pay via {$link} or visit the finance office. KTVTC Admin.";
            case 'friendly':
                return "Hello {$name}! Friendly reminder: Your outstanding balance is KES {$balanceFormatted}. Pay conveniently at {$link}. Thank you for choosing KTVTC.";
            case 'standard':
            default:
                return "Dear {$name}, your current fee balance is KES {$balanceFormatted}. Please clear your fees promptly. Pay online: {$link}. Thank you. KTVTC.";
        }
    }

    private function parseMessage($message, $name, $balance, $enrollment)
    {
        $replacements = [
            '{name}' => $name,
            '{balance}' => number_format($balance, 2),
            '{link}' => 'www.ktvtc.ac.ke/pay',
            '{course}' => $enrollment->course_name ?? 'course',
            '{student_number}' => $enrollment->student_number ?? 'N/A',
            '{total_fees}' => number_format($enrollment->total_fees, 2),
            '{paid}' => number_format($enrollment->amount_paid, 2),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    public function sendSingleFeeReminder($id)
    {
        $enrollment = Enrollment::findOrFail($id);

        if (!$enrollment->student || !$enrollment->student->phone) {
            return redirect()->back()->with('error', 'Student has no phone number.');
        }

        $balance = $enrollment->total_fees - $enrollment->amount_paid;
        $message = "Dear {$enrollment->student->full_name},\n\n";
        $message .= "This is a reminder that you have an outstanding balance of KES " . number_format($balance, 2) . " for your enrollment in {$enrollment->course_name}.\n\n";
        $message .= "Please settle your fees to avoid any interruptions.\n\n";
        $message .= "Thank you,\nKTVTC Team";

        $result = $this->smsService->sendSingleSms($enrollment->student->phone, $message);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Fee reminder sent successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to send fee reminder: ' . $result['message']);
        }
    }

    public function sendBulkBalanceReminders(Request $request)
    {
        $ids = $request->enrollment_ids;
        $enrollments = Enrollment::whereIn('id', $ids)->with(['student'])->get();

        $sent = 0;
        $failed = 0;

        foreach ($enrollments as $enrollment) {
            if ($enrollment->student && $enrollment->student->phone) {
                $balance = $enrollment->total_fees - $enrollment->amount_paid;
                $message = "Dear {$enrollment->student->full_name},\n\n";
                $message .= "This is a reminder that you have an outstanding balance of KES " . number_format($balance, 2) . " for your enrollment in {$enrollment->course_name}.\n\n";
                $message .= "Please settle your fees to avoid any interruptions.\n\n";
                $message .= "Thank you,\nKTVTC Team";

                $result = $this->smsService->sendSingleSms($enrollment->student->phone, $message);
                if ($result['success']) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        }

        return redirect()->back()->with('success', "Bulk reminders sent: {$sent} sent, {$failed} failed.");
    }

    public function getEligibleForReminder()
    {
        $enrollments = Enrollment::whereRaw('total_fees > amount_paid')
            ->where('status', 'active')
            ->with(['student'])
            ->get(['id', 'student_id', 'student_name', 'total_fees', 'amount_paid']);

        return response()->json($enrollments);
    }
}
