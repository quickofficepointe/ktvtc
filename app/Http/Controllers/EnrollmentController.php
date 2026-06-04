<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use App\Models\Campus;
use Illuminate\Http\Request;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EnrollmentController extends Controller
{
    public function sendFeeReminders(Request $request)
{
    $request->validate([
        'enrollment_ids' => 'required|array',
        'enrollment_ids.*' => 'exists:enrollments,id',
        'template' => 'required|in:standard,urgent,friendly,custom',
        'custom_message' => 'required_if:template,custom|nullable|string'
    ]);

    $enrollments = Enrollment::with('student')
        ->whereIn('id', $request->enrollment_ids)
        ->get();

    $smsService = new SmsService();
    $successCount = 0;
    $failed = [];
    $results = [];

    foreach ($enrollments as $enrollment) {
        // Check if student has phone number
        if (!$enrollment->student || !$enrollment->student->phone) {
            $failed[] = [
                'id' => $enrollment->id,
                'name' => $enrollment->student_name,
                'reason' => 'No phone number on file'
            ];
            continue;
        }

        // Check if there's a balance
        if ($enrollment->balance <= 0) {
            $failed[] = [
                'id' => $enrollment->id,
                'name' => $enrollment->student_name,
                'reason' => 'No balance owing (Fully paid: KES ' . number_format($enrollment->amount_paid, 2) . ')'
            ];
            continue;
        }

        // Generate personalized message
        $message = $this->generateFeeReminderMessage($enrollment, $request->template, $request->custom_message);

        // Send SMS
        $result = $smsService->sendSingleSms($enrollment->student->phone, $message);

        if ($result['success']) {
            $successCount++;
            $results[] = [
                'name' => $enrollment->student_name,
                'phone' => $enrollment->student->phone,
                'balance' => $enrollment->balance,
                'status' => 'sent'
            ];
        } else {
            $failed[] = [
                'id' => $enrollment->id,
                'name' => $enrollment->student_name,
                'reason' => $result['message'] ?? 'SMS sending failed'
            ];
        }

        // Optional: Add small delay to avoid rate limiting
        usleep(100000); // 0.1 second delay between messages
    }

    return response()->json([
        'success' => $successCount > 0,
        'sent_count' => $successCount,
        'total_count' => $enrollments->count(),
        'failed_count' => count($failed),
        'failed' => $failed,
        'results' => $results,
        'message' => "Sent {$successCount} of {$enrollments->count()} fee reminders"
    ]);
}

/**
 * Send single fee reminder to one student
 */
public function sendSingleFeeReminder(Enrollment $enrollment)
{
    if (!$enrollment->student) {
        return redirect()->back()->with('error', 'Student record not found');
    }

    if (!$enrollment->student->phone) {
        return redirect()->back()->with('error', 'Student has no phone number on file');
    }

    if ($enrollment->balance <= 0) {
        return redirect()->back()->with('error', 'This student has no outstanding balance (Balance: KES ' . number_format($enrollment->balance, 2) . ')');
    }

    $smsService = new SmsService();
    $message = $this->generateFeeReminderMessage($enrollment, 'standard');

    $result = $smsService->sendSingleSms($enrollment->student->phone, $message);

    if ($result['success']) {
        return redirect()->back()->with('success', 'Fee reminder sent successfully to ' . $enrollment->student_name);
    }

    return redirect()->back()->with('error', 'Failed to send reminder: ' . ($result['message'] ?? 'Unknown error'));
}

/**
 * Generate fee reminder message based on template
 */
private function generateFeeReminderMessage($enrollment, $template, $customMessage = null)
{
    $studentName = $enrollment->student_name;
    $balance = number_format($enrollment->balance, 2);
    $paymentLink = 'www.ktvtc.ac.ke/pay';
    $courseName = $enrollment->course_name;
    $studentNumber = $enrollment->student_number;
    $totalFees = number_format($enrollment->total_fees, 2);
    $amountPaid = number_format($enrollment->amount_paid, 2);

    // For custom template
    if ($template === 'custom' && $customMessage) {
        $message = str_replace(
            ['{name}', '{balance}', '{link}', '{course}', '{student_number}', '{total_fees}', '{paid}'],
            [$studentName, $balance, $paymentLink, $courseName, $studentNumber, $totalFees, $amountPaid],
            $customMessage
        );
        // Ensure message isn't too long (max 1600 chars for SMS)
        return substr($message, 0, 1600);
    }

    // Pre-defined templates
   // Pre-defined templates with M-Pesa Paybill
switch ($template) {
    case 'urgent':
        return "URGENT: Dear {$studentName}, your fee balance of KES {$balance} for {$courseName} is now overdue. Kindly clear the balance immediately. M-Pesa Paybill: 522533, Account Number: 7664166. Forward payment confirmation to +254790148509. KTVTC Admin.";

    case 'friendly':
        return "Hello {$studentName}! Friendly reminder: Your outstanding balance for {$courseName} is KES {$balance}. You've paid KES {$amountPaid} of KES {$totalFees}. M-Pesa Paybill: 522533, Account: 7664166. Forward confirmation to +254790148509. Thank you for choosing KTVTC.";

    case 'standard':
    default:
        return "Dear {$studentName}, this is a reminder that you have an outstanding fee balance of KES {$balance} for {$courseName}. Kindly clear the balance on or before Friday, 1st May 2026. M-Pesa Paybill: 522533, Account Number: 7664166. Please forward your payment confirmation to +254790148509 once done. Thank you. KTVTC.";
}
}

/**
 * Bulk send reminders to all students with balance
 */
public function sendBulkBalanceReminders(Request $request)
{
    $request->validate([
        'status' => 'nullable|string',
        'course_id' => 'nullable|exists:courses,id',
        'min_balance' => 'nullable|numeric|min:0',
        'template' => 'required|in:standard,urgent,friendly,custom',
        'custom_message' => 'required_if:template,custom|nullable|string'
    ]);

    $query = Enrollment::with('student')
        ->whereHas('student', function($q) {
            $q->whereNotNull('phone');
        })
        ->hasBalance(); // Using the scope from your model

    // Apply additional filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('course_id')) {
        $query->where('course_id', $request->course_id);
    }

    if ($request->filled('min_balance') && $request->min_balance > 0) {
        $query->whereRaw('total_fees - amount_paid >= ?', [$request->min_balance]);
    }

    $enrollments = $query->get();

    if ($enrollments->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No students found with outstanding balances'
        ]);
    }

    $smsService = new SmsService();
    $successCount = 0;
    $failed = [];

    foreach ($enrollments as $enrollment) {
        $message = $this->generateFeeReminderMessage($enrollment, $request->template, $request->custom_message);
        $result = $smsService->sendSingleSms($enrollment->student->phone, $message);

        if ($result['success']) {
            $successCount++;
        } else {
            $failed[] = [
                'name' => $enrollment->student_name,
                'reason' => $result['message'] ?? 'Failed'
            ];
        }

        usleep(100000); // 0.1 second delay
    }

    return response()->json([
        'success' => $successCount > 0,
        'sent_count' => $successCount,
        'total_count' => $enrollments->count(),
        'failed_count' => count($failed),
        'failed' => $failed,
        'message' => "Bulk reminder sent to {$successCount} students"
    ]);
}

/**
 * Get students eligible for fee reminders (for AJAX)
 */
public function getEligibleForReminder(Request $request)
{
    $query = Enrollment::with('student')
        ->hasBalance()
        ->whereHas('student', function($q) {
            $q->whereNotNull('phone');
        });

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('student_name', 'like', "%{$search}%")
              ->orWhere('student_number', 'like', "%{$search}%");
        });
    }

    $enrollments = $query->limit(50)->get();

    return response()->json([
        'success' => true,
        'count' => $enrollments->count(),
        'students' => $enrollments->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->student_name,
                'student_number' => $e->student_number,
                'balance' => $e->balance,
                'course' => $e->course_name,
                'phone' => $e->student->phone
            ];
        })
    ]);
}
    /**
     * ============ INDEX ============
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Enrollment::with(['student', 'course', 'campus'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            });

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('intake_month')) {
            $query->where('intake_month', $request->intake_month);
        }

        if ($request->filled('intake_year')) {
            $query->where('intake_year', $request->intake_year);
        }

        if ($request->filled('student_type')) {
            $query->where('student_type', $request->student_type);
        }

        if ($request->filled('sponsorship_type')) {
            $query->where('sponsorship_type', $request->sponsorship_type);
        }

        if ($request->filled('requires_external_exam')) {
            $query->where('requires_external_exam', $request->requires_external_exam === 'yes');
        }

        if ($request->filled('exam_body')) {
            $query->where('exam_body', $request->exam_body);
        }

        if ($request->filled('enrollment_date_from')) {
            $query->whereDate('enrollment_date', '>=', $request->enrollment_date_from);
        }

        if ($request->filled('enrollment_date_to')) {
            $query->whereDate('enrollment_date', '<=', $request->enrollment_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('course_name', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%")
                  ->orWhere('legacy_code', 'like', "%{$search}%");
            });
        }

        // Statistics for cards
        $totalEnrollments = (clone $query)->count();
        $activeEnrollments = (clone $query)->where('status', 'active')->count();
        $completedEnrollments = (clone $query)->where('status', 'completed')->count();
        $pendingPayment = (clone $query)->where('balance', '>', 0)->count();

        // Uses ExamRegistration table
        $requiresExamRegistration = (clone $query)
            ->where('requires_external_exam', true)
            ->whereDoesntHave('examRegistrations', function($q) {
                $q->whereIn('status', ['registered', 'submitted', 'completed']);
            })
            ->count();

        // Status breakdown for chart
        $statusBreakdown = [
            'active' => (clone $query)->where('status', 'active')->count(),
            'graduated' => (clone $query)->where('status', 'graduated')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'dropped' => (clone $query)->where('status', 'dropped')->count(),
            'suspended' => (clone $query)->where('status', 'suspended')->count(),
            'deferred' => (clone $query)->where('status', 'deferred')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
        ];

        // Exam body breakdown (using the exam_body field directly)
        $examBodyBreakdown = [];
        $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA']; // Fixed list of exam bodies
        foreach ($examBodies as $body) {
            $examBodyBreakdown[$body] = (clone $query)
                ->where('exam_body', $body)
                ->count();
        }

        // Intake breakdown
        $currentYear = date('Y');
        $intakeBreakdown = [
            'January' => (clone $query)->where('intake_month', 'January')->where('intake_year', $currentYear)->count(),
            'May' => (clone $query)->where('intake_month', 'May')->where('intake_year', $currentYear)->count(),
            'September' => (clone $query)->where('intake_month', 'September')->where('intake_year', $currentYear)->count(),
        ];

        $enrollments = $query->orderBy('created_at', 'desc')->paginate(15);

        // Filter dropdown data
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $courses = Course::orderBy('name')->get();

        $intakeMonths = ['January', 'February', 'March', 'April', 'May', 'June',
                         'July', 'August', 'September', 'October', 'November', 'December'];
        $intakeYears = range(date('Y') - 2, date('Y') + 1);
        $studentTypes = ['new', 'continuing', 'alumnus', 'transfer'];
        $sponsorshipTypes = ['self', 'sponsored', 'government', 'scholarship', 'company'];
        $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA'];
        $statuses = ['active', 'graduated', 'completed', 'dropped', 'suspended', 'deferred', 'pending'];

        return view('ktvtc.admin.enrollments.index', compact(
            'user',
            'enrollments',
            'campuses',
            'courses',
            'examBodies',
            'intakeMonths',
            'intakeYears',
            'studentTypes',
            'sponsorshipTypes',
            'statuses',
            'totalEnrollments',
            'activeEnrollments',
            'completedEnrollments',
            'pendingPayment',
            'requiresExamRegistration',
            'statusBreakdown',
            'examBodyBreakdown',
            'intakeBreakdown'
        ));
    }

    /**
     * ============ CREATE FORM ============
     */
    public function create()
    {
        $user = auth()->user();

        // Get students based on role
        $students = Student::when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            })
            ->orderBy('first_name')
            ->get();

        // Get courses
        $courses = Course::orderBy('name')->get();

        // Get campuses based on role
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $intakeMonths = ['January', 'February', 'March', 'April', 'May', 'June',
                         'July', 'August', 'September', 'October', 'November', 'December'];
        $intakeYear = date('Y');
        $studyModes = ['full_time', 'part_time', 'evening', 'weekend', 'online'];
        $studentTypes = ['new', 'continuing', 'alumnus', 'transfer'];
        $sponsorshipTypes = ['self', 'sponsored', 'government', 'scholarship', 'company'];
        $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA'];

        return view('ktvtc.admin.enrollments.create', compact(
            'students',
            'courses',
            'campuses',
            'intakeMonths',
            'intakeYear',
            'studyModes',
            'studentTypes',
            'sponsorshipTypes',
            'examBodies'
        ));
    }

    /**
     * ============ STORE ============
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'student_name' => 'required|string|max:255',
            'student_number' => 'nullable|string|max:50',
            'course_name' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:50',
            'intake_year' => 'required|integer|min:2000|max:' . (date('Y') + 2),
            'intake_month' => 'required|string|max:20',
            'enrollment_date' => 'nullable|date',
            'study_mode' => 'required|in:full_time,part_time,evening,weekend,online',
            'student_type' => 'required|in:new,continuing,alumnus,transfer',
            'sponsorship_type' => 'required|in:self,sponsored,government,scholarship,company',
            'duration_months' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'actual_end_date' => 'nullable|date',
            'total_fees' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,graduated,completed,dropped,suspended,deferred,pending',
            'requires_external_exam' => 'boolean',
            'exam_body' => 'nullable|in:KNEC,CDACC,NITA,TVETA',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->all();

            // Calculate balance
            $data['balance'] = ($data['total_fees'] ?? 0) - ($data['amount_paid'] ?? 0);

            // Set is_active based on status
            $data['is_active'] = in_array($data['status'], ['active', 'pending']);

            // Create enrollment
            $enrollment = Enrollment::create($data);

            DB::commit();

            return redirect()->route('admin.enrollments.show', $enrollment)
                ->with('success', 'Enrollment created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create enrollment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ SHOW ============
     */
  public function show(Enrollment $enrollment)
{
    $enrollment->load(['student', 'course', 'campus', 'payments', 'examRegistrations']);

    // Get payments separately to use in the view
    $payments = $enrollment->payments; // This is already loaded via load('payments')

    return view('ktvtc.admin.enrollments.show', compact('enrollment', 'payments'));
}

    /**
     * ============ EDIT ============
     */
    public function edit(Enrollment $enrollment)
    {
        $user = auth()->user();

        // Get students based on role
        $students = Student::when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            })
            ->orderBy('first_name')
            ->get();

        // Get courses
        $courses = Course::orderBy('name')->get();

        // Get campuses based on role
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $intakeMonths = ['January', 'February', 'March', 'April', 'May', 'June',
                         'July', 'August', 'September', 'October', 'November', 'December'];
        $studyModes = ['full_time', 'part_time', 'evening', 'weekend', 'online'];
        $studentTypes = ['new', 'continuing', 'alumnus', 'transfer'];
        $sponsorshipTypes = ['self', 'sponsored', 'government', 'scholarship', 'company'];
        $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA'];
        $statuses = ['active', 'graduated', 'completed', 'dropped', 'suspended', 'deferred', 'pending'];

        return view('ktvtc.admin.enrollments.edit', compact(
            'enrollment',
            'students',
            'courses',
            'campuses',
            'intakeMonths',
            'studyModes',
            'studentTypes',
            'sponsorshipTypes',
            'examBodies',
            'statuses'
        ));
    }

    /**
     * ============ UPDATE ============
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'campus_id' => 'nullable|exists:campuses,id',
            'student_name' => 'required|string|max:255',
            'student_number' => 'nullable|string|max:50',
            'course_name' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:50',
            'intake_year' => 'required|integer|min:2000|max:' . (date('Y') + 2),
            'intake_month' => 'required|string|max:20',
            'enrollment_date' => 'nullable|date',
            'study_mode' => 'required|in:full_time,part_time,evening,weekend,online',
            'student_type' => 'required|in:new,continuing,alumnus,transfer',
            'sponsorship_type' => 'required|in:self,sponsored,government,scholarship,company',
            'duration_months' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'actual_end_date' => 'nullable|date',
            'total_fees' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,graduated,completed,dropped,suspended,deferred,pending',
            'requires_external_exam' => 'boolean',
            'exam_body' => 'nullable|in:KNEC,CDACC,NITA,TVETA',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->all();

            // Calculate balance
            $data['balance'] = ($data['total_fees'] ?? 0) - ($data['amount_paid'] ?? 0);

            // Set is_active based on status
            $data['is_active'] = in_array($data['status'], ['active', 'pending']);

            $enrollment->update($data);

            DB::commit();

            return redirect()->route('enrollments.show', $enrollment)
                ->with('success', 'Enrollment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update enrollment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ DESTROY ============
     */
    public function destroy(Enrollment $enrollment)
    {
        DB::beginTransaction();

        try {
            // Check if there are any payments
            if ($enrollment->payments()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete enrollment with payments.');
            }

            $enrollmentNumber = $enrollment->enrollment_number;
            $enrollment->delete();

            DB::commit();

            return redirect()->route('enrollments.index')
                ->with('success', "Enrollment {$enrollmentNumber} deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete enrollment: ' . $e->getMessage());
        }
    }

    /**
     * ============ STATUS ACTIONS ============
     */
    public function activate(Enrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'active',
            'is_active' => true
        ]);

        return redirect()->back()
            ->with('success', 'Enrollment activated successfully.');
    }

    public function suspend(Enrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'suspended',
            'is_active' => false
        ]);

        return redirect()->back()
            ->with('success', 'Enrollment suspended successfully.');
    }

    public function complete(Enrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'completed',
            'is_active' => false,
            'actual_end_date' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Enrollment marked as completed.');
    }

    public function defer(Enrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'deferred',
            'is_active' => false
        ]);

        return redirect()->back()
            ->with('success', 'Enrollment deferred successfully.');
    }

    /**
     * ============ API ENDPOINTS ============
     */
    public function getByStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        $enrollments = Enrollment::where('student_id', $request->student_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'student_name' => $enrollment->student_name,
                    'student_number' => $enrollment->student_number,
                    'course' => $enrollment->course_name,
                    'intake' => $enrollment->intake_month . ' ' . $enrollment->intake_year,
                    'status' => $enrollment->status,
                    'total_fee' => $enrollment->total_fees,
                    'paid' => $enrollment->amount_paid,
                    'balance' => $enrollment->balance,
                ];
            });

        return response()->json($enrollments);
    }

    /**
     * ============ HELPER METHODS ============
     */
    private function generateEnrollmentNumber($year = null)
    {
        $year = $year ?? date('Y');
        $prefix = 'ENR';

        $lastEnrollment = Enrollment::where('enrollment_number', 'LIKE', "{$prefix}/{$year}/%")
            ->orderBy('enrollment_number', 'desc')
            ->first();

        if ($lastEnrollment) {
            $parts = explode('/', $lastEnrollment->enrollment_number);
            $lastNumber = (int) end($parts);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "{$prefix}/{$year}/{$newNumber}";
    }

    /**
     * ============ EXPORT ============
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        $query = Enrollment::with(['student', 'course', 'campus'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('intake_year')) {
            $query->where('intake_year', $request->intake_year);
        }

        if ($request->filled('intake_month')) {
            $query->where('intake_month', $request->intake_month);
        }

        $enrollments = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'enrollments_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');

        // Add headers
        fputcsv($handle, [
            'Enrollment Number',
            'Student Name',
            'Student Number',
            'Course',
            'Intake',
            'Campus',
            'Exam Body',
            'Status',
            'Total Fee',
            'Amount Paid',
            'Balance',
            'Enrollment Date',
        ]);

        // Add data
        foreach ($enrollments as $enrollment) {
            fputcsv($handle, [
                $enrollment->enrollment_number ?? 'N/A',
                $enrollment->student_name,
                $enrollment->student_number ?? 'N/A',
                $enrollment->course_name,
                $enrollment->intake_month . ' ' . $enrollment->intake_year,
                $enrollment->campus->name ?? 'N/A',
                $enrollment->exam_body ?? 'N/A',
                ucfirst($enrollment->status),
                $enrollment->total_fees,
                $enrollment->amount_paid,
                $enrollment->balance,
                $enrollment->enrollment_date ? $enrollment->enrollment_date->format('Y-m-d') : 'N/A',
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
