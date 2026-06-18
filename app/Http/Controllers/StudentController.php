<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Campus;
use App\Models\Application;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentsExport;
use Carbon\Carbon;
use App\Services\SmsService;

class StudentController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function dashboard()
    {
        $user = auth()->user();

        $totalFees = 0;
        $totalPaid = 0;
        $totalBalance = 0;
        $enrollments = collect();
        $recentPayments = collect();
        $student = null;
        $enrollmentCount = 0;

        $student = $user->student;

        if ($student) {
            $enrollments = Enrollment::where('student_id', $student->id)
                ->with(['course', 'payments'])
                ->get();

            $totalFees = $enrollments->sum('total_fees');
            $totalPaid = $enrollments->sum('amount_paid');
            $totalBalance = $enrollments->sum('balance');
            $enrollmentCount = $enrollments->count();

            $recentPayments = FeePayment::where('student_id', $student->id)
                ->with('enrollment.course')
                ->orderBy('payment_date', 'desc')
                ->limit(5)
                ->get();
        }

        return view('ktvtc.students.dashboard', compact(
            'enrollments',
            'totalFees',
            'totalPaid',
            'totalBalance',
            'recentPayments',
            'student',
            'enrollmentCount'
        ));
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Student::query()
            ->with(['campus', 'application'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where('campus_id', $user->campus_id);
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('student_category')) {
            $query->where('student_category', $request->student_category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('legacy_student_code', 'like', "%{$search}%")
                  ->orWhere('id_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('registration_date_from')) {
            $query->whereDate('registration_date', '>=', $request->registration_date_from);
        }

        if ($request->filled('registration_date_to')) {
            $query->whereDate('registration_date', '<=', $request->registration_date_to);
        }

        if ($request->filled('requires_cleanup')) {
            $query->where('requires_cleanup', $request->requires_cleanup === 'yes');
        }

        $totalStudents = (clone $query)->count();
        $activeStudents = (clone $query)->where('status', 'active')->count();
        $graduatedStudents = (clone $query)->where('status', 'graduated')->count();
        $historicalStudents = (clone $query)->where('status', 'historical')->count();
        $requiresCleanup = (clone $query)->where('requires_cleanup', true)->count();

        $statusBreakdown = [
            'active' => (clone $query)->where('status', 'active')->count(),
            'inactive' => (clone $query)->where('status', 'inactive')->count(),
            'graduated' => (clone $query)->where('status', 'graduated')->count(),
            'dropped' => (clone $query)->where('status', 'dropped')->count(),
            'suspended' => (clone $query)->where('status', 'suspended')->count(),
            'alumnus' => (clone $query)->where('status', 'alumnus')->count(),
            'prospective' => (clone $query)->where('status', 'prospective')->count(),
            'historical' => (clone $query)->where('status', 'historical')->count(),
        ];

        $genderBreakdown = [
            'male' => (clone $query)->where('gender', 'male')->count(),
            'female' => (clone $query)->where('gender', 'female')->count(),
            'other' => (clone $query)->where('gender', 'other')->count(),
        ];

        $categoryBreakdown = [
            'regular' => (clone $query)->where('student_category', 'regular')->count(),
            'alumnus' => (clone $query)->where('student_category', 'alumnus')->count(),
            'staff_child' => (clone $query)->where('student_category', 'staff_child')->count(),
            'sponsored' => (clone $query)->where('student_category', 'sponsored')->count(),
            'scholarship' => (clone $query)->where('student_category', 'scholarship')->count(),
        ];

        $students = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $applications = Application::orderBy('application_number')->get();

        return view('ktvtc.admin.students.index', compact(
            'user',
            'students',
            'campuses',
            'applications',
            'totalStudents',
            'activeStudents',
            'graduatedStudents',
            'historicalStudents',
            'requiresCleanup',
            'statusBreakdown',
            'genderBreakdown',
            'categoryBreakdown'
        ));
    }

    public function create()
    {
        $campuses = Campus::orderBy('name')->get();
        $courses = Course::orderBy('name')->get();
        $applications = Application::whereIn('status', ['accepted', 'pending'])
            ->orderBy('application_number')
            ->get();

        return view('ktvtc.admin.students.create', compact('campuses', 'courses', 'applications'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'campus_id' => 'nullable|exists:campuses,id',
            'status' => 'nullable|string',
            'student_category' => 'nullable|string',
            'application_id' => 'nullable|exists:applications,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $studentNumber = $this->generateStudentNumber();

        $student = Student::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'student_number' => $studentNumber,
            'email' => $request->email,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'campus_id' => $request->campus_id,
            'status' => $request->status ?? 'active',
            'registration_date' => now(),
            'student_category' => $request->student_category ?? 'regular',
            'application_id' => $request->application_id,
            'id_number' => $request->id_number,
            'address' => $request->address,
            'city' => $request->city,
            'county' => $request->county,
            'postal_code' => $request->postal_code,
            'country' => $request->country ?? 'Kenya',
            'next_of_kin_name' => $request->next_of_kin_name,
            'next_of_kin_relationship' => $request->next_of_kin_relationship,
            'next_of_kin_phone' => $request->next_of_kin_phone,
            'next_of_kin_email' => $request->next_of_kin_email,
            'next_of_kin_id_number' => $request->next_of_kin_id_number,
            'next_of_kin_address' => $request->next_of_kin_address,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_relationship' => $request->emergency_contact_relationship,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_phone_alt' => $request->emergency_contact_phone_alt,
            'education_level' => $request->education_level,
            'school_name' => $request->school_name,
            'graduation_year' => $request->graduation_year,
            'mean_grade' => $request->mean_grade,
            'kcse_index_number' => $request->kcse_index_number,
            'blood_group' => $request->blood_group,
            'tshirt_size' => $request->tshirt_size,
            'disability_type' => $request->disability_type,
            'medical_conditions' => $request->medical_conditions,
            'allergies' => $request->allergies,
            'special_needs' => $request->special_needs,
            'remarks' => $request->remarks,
        ]);

        $this->handleDocumentUploads($request, $student);
        $this->createUserFromStudent($student);

        return redirect()->route('admin.students.index')
            ->with('success', "Student created successfully. Student Number: {$studentNumber}");
    }

    public function show($id)
    {
        $student = Student::with(['campus', 'application', 'enrollments.course', 'enrollments.payments'])->findOrFail($id);
        return view('ktvtc.admin.students.show', compact('student'));
    }

    public function edit($id)
    {
        $student = Student::with(['campus', 'application'])->findOrFail($id);
        $campuses = Campus::orderBy('name')->get();
        $applications = Application::whereIn('status', ['accepted', 'pending'])
            ->orderBy('application_number')
            ->get();

        return view('ktvtc.admin.students.edit', compact('student', 'campuses', 'applications'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'student_number' => 'nullable|string|unique:students,student_number,' . $id,
            'email' => 'nullable|email|unique:students,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|unique:students,id_number,' . $id,
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'status' => 'nullable|string',
            'campus_id' => 'nullable|exists:campuses,id',
            'application_id' => 'nullable|exists:applications,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldStudentNumber = $student->student_number;

        $student->update($request->all());
        $this->handleDocumentUploads($request, $student);

        $newStudentNumber = $student->student_number;
        $studentNumberChanged = $oldStudentNumber !== $newStudentNumber;

        if ($studentNumberChanged && $newStudentNumber) {
            $this->syncStudentPassword($student, $newStudentNumber);
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student updated successfully' .
                ($studentNumberChanged ? ' and password synced with new student number.' : ''));
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        $user = User::where('student_id', $student->id)->first();
        if ($user) {
            $user->delete();
        }

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    public function activate($id)
    {
        $student = Student::findOrFail($id);
        $student->status = 'active';
        $student->save();

        $user = User::where('student_id', $student->id)->first();
        if ($user) {
            $user->is_active = true;
            $user->is_approved = true;
            $user->save();
        }

        return redirect()->back()->with('success', 'Student activated successfully.');
    }

    public function suspend($id)
    {
        $student = Student::findOrFail($id);
        $student->status = 'suspended';
        $student->save();

        $user = User::where('student_id', $student->id)->first();
        if ($user) {
            $user->is_active = false;
            $user->is_approved = false;
            $user->save();
        }

        return redirect()->back()->with('success', 'Student suspended successfully.');
    }

    public function archive($id)
    {
        $student = Student::findOrFail($id);
        $student->status = 'historical';
        $student->save();

        return redirect()->back()->with('success', 'Student archived successfully.');
    }

    public function details($id)
    {
        $student = Student::findOrFail($id);
        return view('ktvtc.admin.students.details', compact('student'));
    }

    public function editDetails($id)
    {
        $student = Student::findOrFail($id);
        return view('ktvtc.admin.students.edit-details', compact('student'));
    }

    public function updateDetails(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'emergency_contact_relationship' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $student->update($request->all());

        return redirect()->route('admin.students.details', $student->id)
            ->with('success', 'Student details updated successfully.');
    }

    public function bulkActivate(Request $request)
    {
        $studentIds = $request->student_ids;
        Student::whereIn('id', $studentIds)->update(['status' => 'active']);

        $students = Student::whereIn('id', $studentIds)->get();
        foreach ($students as $student) {
            $user = User::where('student_id', $student->id)->first();
            if ($user) {
                $user->is_active = true;
                $user->is_approved = true;
                $user->save();
            }
        }

        return redirect()->back()->with('success', count($studentIds) . ' students activated successfully.');
    }

    public function bulkSuspend(Request $request)
    {
        $studentIds = $request->student_ids;
        Student::whereIn('id', $studentIds)->update(['status' => 'suspended']);

        $students = Student::whereIn('id', $studentIds)->get();
        foreach ($students as $student) {
            $user = User::where('student_id', $student->id)->first();
            if ($user) {
                $user->is_active = false;
                $user->is_approved = false;
                $user->save();
            }
        }

        return redirect()->back()->with('success', count($studentIds) . ' students suspended successfully.');
    }

    public function bulkArchive(Request $request)
    {
        $studentIds = $request->student_ids;
        Student::whereIn('id', $studentIds)->update(['status' => 'historical']);

        return redirect()->back()->with('success', count($studentIds) . ' students archived successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $studentIds = $request->student_ids;

        $students = Student::whereIn('id', $studentIds)->get();
        foreach ($students as $student) {
            $user = User::where('student_id', $student->id)->first();
            if ($user) {
                $user->delete();
            }
        }

        Student::whereIn('id', $studentIds)->delete();

        return redirect()->back()->with('success', count($studentIds) . ' students deleted successfully.');
    }

    public function export()
    {
        return Excel::download(new StudentsExport, 'students_' . date('Y-m-d') . '.xlsx');
    }

    public function importView()
    {
        return view('ktvtc.admin.students.import');
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new StudentsImport, $request->file('file'));
            return redirect()->route('admin.students.index')
                ->with('success', 'Students imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing students: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ];

        $columns = [
            'first_name', 'last_name', 'middle_name', 'email', 'phone',
            'gender', 'date_of_birth', 'campus_id', 'status', 'student_category'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function enrollmentReport()
    {
        $data = Enrollment::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ktvtc.admin.students.reports.enrollment', compact('data'));
    }

    public function demographicsReport()
    {
        $genderData = Student::select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->get();

        $statusData = Student::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        return view('ktvtc.admin.students.reports.demographics', compact('genderData', 'statusData'));
    }

    public function search(Request $request)
    {
        $search = $request->get('q');
        $students = Student::where('first_name', 'like', "%{$search}%")
            ->orWhere('last_name', 'like', "%{$search}%")
            ->orWhere('student_number', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'student_number']);

        return response()->json($students);
    }

    public function getForSelect(Request $request)
    {
        $search = $request->get('q');
        $students = Student::where('first_name', 'like', "%{$search}%")
            ->orWhere('last_name', 'like', "%{$search}%")
            ->orWhere('student_number', 'like', "%{$search}%")
            ->limit(20)
            ->get(['id', 'first_name', 'last_name', 'student_number']);

        return response()->json($students);
    }

    public function getEnrollments($id)
    {
        $student = Student::findOrFail($id);
        $enrollments = $student->enrollments()->with('course')->get();

        return response()->json($enrollments);
    }

    // ============================================
    // STUDENT NUMBER FIX & PASSWORD SYNC METHODS
    // ============================================

    public function fixStudentNumber(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'student_number' => 'required|string|unique:students,student_number,' . $id,
        ]);

        $oldNumber = $student->student_number;
        $newNumber = $request->student_number;

        $student->student_number = $newNumber;
        $student->save();

        $this->syncStudentPassword($student, $newNumber);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Student number fixed: {$oldNumber} → {$newNumber}. Password and SMS sent.",
                'old_number' => $oldNumber,
                'new_number' => $newNumber
            ]);
        }

        return redirect()->back()
            ->with('success', "Student number fixed: {$oldNumber} → {$newNumber}. Password and SMS sent.");
    }

    public function bulkSyncStudentNumbers(Request $request)
    {
        $studentIds = $request->student_ids;
        $newStudentNumberPrefix = $request->prefix ?? 'STU';

        $results = [
            'total' => count($studentIds),
            'updated' => 0,
            'failed' => 0,
            'sms_sent' => 0,
            'details' => []
        ];

        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);

            if (!$student) {
                $results['failed']++;
                $results['details'][] = "Student ID {$studentId} not found";
                continue;
            }

            try {
                $newNumber = $this->generateStudentNumberWithPrefix($newStudentNumberPrefix);
                $oldNumber = $student->student_number;

                $student->student_number = $newNumber;
                $student->save();

                $this->syncStudentPassword($student, $newNumber);

                $results['updated']++;
                $results['sms_sent']++;
                $results['details'][] = "Student {$student->full_name}: {$oldNumber} → {$newNumber}";

            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = "Student {$student->full_name}: Error - {$e->getMessage()}";
            }
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'results' => $results
            ]);
        }

        return redirect()->route('admin.students.index')
            ->with('success', "Bulk sync completed: {$results['updated']} updated, {$results['failed']} failed.");
    }

    public function syncPassword(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $studentNumber = $student->student_number ?? $student->legacy_student_code;

        if (!$studentNumber) {
            return redirect()->back()->with('error', 'Student has no student number.');
        }

        $this->syncStudentPassword($student, $studentNumber);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Password synced with student number: {$studentNumber}",
                'student_number' => $studentNumber
            ]);
        }

        return redirect()->back()
            ->with('success', "Password synced with student number: {$studentNumber}");
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    private function syncStudentPassword(Student $student, $studentNumber)
    {
        $user = User::where('student_id', $student->id)->first();

        if (!$user) {
            $user = User::where('username', $student->legacy_student_code ?? $studentNumber)
                ->orWhere('email', $student->email)
                ->first();
        }

        if ($user) {
            $newPassword = strtoupper($studentNumber);
            $user->password = Hash::make($newPassword);

            if ($user->username == $student->legacy_student_code || $user->username == $studentNumber) {
                $user->username = $studentNumber;
            }

            if (!$user->email || $user->email == $student->legacy_student_code . '@student.ktvtc.ac.ke') {
                $user->email = $student->email ?? strtolower($studentNumber) . '@student.ktvtc.ac.ke';
            }

            $user->save();
            $this->sendPasswordUpdateSms($student, $newPassword);

            \Log::info("Password synced for student", [
                'student_id' => $student->id,
                'student_number' => $studentNumber,
                'user_id' => $user->id
            ]);
        } else {
            $this->createUserFromStudent($student);
            $user = User::where('student_id', $student->id)->first();
            if ($user) {
                $newPassword = strtoupper($studentNumber);
                $user->password = Hash::make($newPassword);
                $user->save();
                $this->sendPasswordUpdateSms($student, $newPassword);
            }
        }
    }

    private function createUserFromStudent(Student $student): ?User
    {
        $existingUser = User::where('student_id', $student->id)->first();
        if ($existingUser) {
            return $existingUser;
        }

        $studentNumber = $student->student_number ?? $student->legacy_student_code;
        if (!$studentNumber) {
            return null;
        }

        $defaultPassword = strtoupper($studentNumber);
        $email = $student->email ?? strtolower($studentNumber) . '@student.ktvtc.ac.ke';
        $name = $student->full_name ?? trim($student->first_name . ' ' . $student->last_name);

        return User::create([
            'student_id' => $student->id,
            'name' => $name,
            'username' => $studentNumber,
            'email' => $email,
            'phone_number' => $student->phone,
            'bio' => $student->remarks ?? 'Student account automatically created',
            'role' => 5,
            'is_verified' => true,
            'is_active' => true,
            'is_approved' => true,
            'password' => Hash::make($defaultPassword),
            'email_verified_at' => now(),
        ]);
    }

    private function sendPasswordUpdateSms(Student $student, $newPassword)
    {
        try {
            $fullName = $student->full_name ?? trim($student->first_name . ' ' . $student->last_name);
            $phone = $student->phone;

            if (!$phone) {
                \Log::warning("No phone number for student: {$student->id}");
                return ['success' => false, 'message' => 'No phone number'];
            }

            $message = "Dear {$fullName},\n\n";
            $message .= "Your KTVTC student account password has been updated.\n";
            $message .= "Your new password is: {$newPassword}\n\n";
            $message .= "Please login using your student number and this password.\n";
            $message .= "For security, you will be required to change your password after login.\n\n";
            $message .= "Student Number: {$student->student_number}\n";
            $message .= "Login URL: " . url('/login') . "\n\n";
            $message .= "Thank you,\nKTVTC Team";

            $result = $this->smsService->sendSingleSms($phone, $message);

            if ($result['success']) {
                \Log::info("Password update SMS sent to {$phone}", [
                    'student_id' => $student->id,
                    'student_number' => $student->student_number
                ]);
            } else {
                \Log::error("Failed to send password update SMS to {$phone}", [
                    'student_id' => $student->id,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            \Log::error("Password update SMS exception: " . $e->getMessage(), [
                'student_id' => $student->id
            ]);

            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage()
            ];
        }
    }

    private function generateStudentNumber()
    {
        $prefix = 'STU';
        $year = date('Y');
        $month = date('m');

        $lastStudent = Student::where('student_number', 'LIKE', "{$prefix}/{$year}/{$month}/%")
            ->orderBy('student_number', 'desc')
            ->first();

        if ($lastStudent) {
            $parts = explode('/', $lastStudent->student_number);
            $lastNumber = (int) end($parts);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}/{$year}/{$month}/{$newNumber}";
    }

    private function generateStudentNumberWithPrefix($prefix = 'STU')
    {
        $year = date('Y');
        $month = date('m');

        $lastStudent = Student::where('student_number', 'LIKE', "{$prefix}/{$year}/{$month}/%")
            ->orderBy('student_number', 'desc')
            ->first();

        if ($lastStudent) {
            $parts = explode('/', $lastStudent->student_number);
            $lastNumber = (int) end($parts);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}/{$year}/{$month}/{$newNumber}";
    }

    private function handleDocumentUploads(Request $request, Student $student)
    {
        if ($request->hasFile('id_document')) {
            if ($student->id_document_path) {
                Storage::delete($student->id_document_path);
            }
            $path = $request->file('id_document')->store('student_documents', 'public');
            $student->id_document_path = $path;
        }

        if ($request->hasFile('passport_photo')) {
            if ($student->passport_photo_path) {
                Storage::delete($student->passport_photo_path);
            }
            $path = $request->file('passport_photo')->store('student_documents', 'public');
            $student->passport_photo_path = $path;
        }

        if ($request->hasFile('education_certificates')) {
            if ($student->education_certificates_path) {
                Storage::delete($student->education_certificates_path);
            }
            $path = $request->file('education_certificates')->store('student_documents', 'public');
            $student->education_certificates_path = $path;
        }

        if ($request->hasFile('other_documents')) {
            if ($student->other_documents) {
                $oldDocuments = json_decode($student->other_documents, true) ?? [];
                foreach ($oldDocuments as $oldDoc) {
                    Storage::delete($oldDoc);
                }
            }

            $documents = [];
            foreach ($request->file('other_documents') as $file) {
                $path = $file->store('student_documents', 'public');
                $documents[] = $path;
            }
            $student->other_documents = json_encode($documents);
        }

        $student->save();
    }
}
