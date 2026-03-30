<?php

namespace App\Http\Controllers;

use App\Models\ExamRegistration;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class ExamRegistrationController extends Controller
{
    /**
     * Display a listing of exam registrations.
     */
 /**
 * Display a listing of exam registrations.
 */
public function index(Request $request)
{
    $user = auth()->user();

    $query = ExamRegistration::with([
            'enrollment.student',
            'enrollment.course',
            'registrar'
        ])
        ->when($user->role != 2, function ($q) use ($user) {
            return $q->whereHas('enrollment', function($sq) use ($user) {
                $sq->where('campus_id', $user->campus_id);
            });
        });

    // Apply filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('exam_body')) {
        $query->where('exam_body', $request->exam_body);
    }

    if ($request->filled('exam_type')) {
        $query->where('exam_type', 'like', "%{$request->exam_type}%");
    }

    if ($request->filled('enrollment_id')) {
        $query->where('enrollment_id', $request->enrollment_id);
    }

    if ($request->filled('student_id')) {
        $query->whereHas('enrollment', function($q) use ($request) {
            $q->where('student_id', $request->student_id);
        });
    }

    if ($request->filled('registration_date_from')) {
        $query->whereDate('registration_date', '>=', $request->registration_date_from);
    }

    if ($request->filled('registration_date_to')) {
        $query->whereDate('registration_date', '<=', $request->registration_date_to);
    }

    if ($request->filled('exam_date_from')) {
        $query->whereDate('exam_date', '>=', $request->exam_date_from);
    }

    if ($request->filled('exam_date_to')) {
        $query->whereDate('exam_date', '<=', $request->exam_date_to);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('registration_number', 'like', "%{$search}%")
              ->orWhere('index_number', 'like', "%{$search}%")
              ->orWhere('certificate_number', 'like', "%{$search}%")
              ->orWhereHas('enrollment.student', function ($sq) use ($search) {
                  $sq->where('first_name', 'like', "%{$search}%")
                     ->orWhere('last_name', 'like', "%{$search}%")
                     ->orWhere('student_number', 'like', "%{$search}%");
              })
              ->orWhereHas('enrollment.course', function ($sq) use ($search) {
                  $sq->where('name', 'like', "%{$search}%")
                     ->orWhere('code', 'like', "%{$search}%");
              });
        });
    }

    // Statistics
    $totalRegistrations = (clone $query)->count();

    $statusBreakdown = [
        'pending' => (clone $query)->where('status', 'pending')->count(),
        'registered' => (clone $query)->where('status', 'registered')->count(),
        'active' => (clone $query)->where('status', 'active')->count(),
        'completed' => (clone $query)->where('status', 'completed')->count(),
        'failed' => (clone $query)->where('status', 'failed')->count(),
    ];

    // Exam body breakdown - USING THE DIRECT exam_body FIELD
    $examBodyBreakdown = [];
    $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA', 'OTHER']; // Fixed list
    foreach ($examBodies as $body) {
        $examBodyBreakdown[$body] = (clone $query)
            ->where('exam_body', $body)
            ->count();
    }

    $examRegistrations = $query->orderBy('created_at', 'desc')->paginate(15);

    // Filter dropdown data - USING DIRECT VALUES
    $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA', 'OTHER'];
    $examTypes = $this->getExamTypes(); // Get distinct exam types from database

    $students = Student::orderBy('first_name')
        ->when($user->role != 2, function ($q) use ($user) {
            return $q->where('campus_id', $user->campus_id);
        })
        ->get();

    $enrollments = Enrollment::with(['student', 'course'])
        ->where('requires_external_exam', true)
        ->when($user->role != 2, function ($q) use ($user) {
            return $q->where('campus_id', $user->campus_id);
        })
        ->orderBy('created_at', 'desc')
        ->get();

    $statuses = ['pending', 'registered', 'active', 'completed', 'failed'];

    // ADD THIS: Get campuses for filter dropdown
    if ($user->role == 2) {
        $campuses = \App\Models\Campus::orderBy('name')->get();
    } else {
        $campuses = \App\Models\Campus::where('id', $user->campus_id)->orderBy('name')->get();
    }

    return view('ktvtc.admin.exam-registrations.index', compact(
        'examRegistrations',
        'examBodies',
        'examTypes',
        'students',
        'enrollments',
        'statuses',
        'totalRegistrations',
        'statusBreakdown',
        'examBodyBreakdown',
        'campuses'  // ADD THIS
    ));
}

    /**
     * Get distinct exam types from the database
     */
    private function getExamTypes()
    {
        return ExamRegistration::select('exam_type')
            ->whereNotNull('exam_type')
            ->distinct()
            ->orderBy('exam_type')
            ->pluck('exam_type');
    }

    /**
     * Show the form for creating a new exam registration.
     */
/**
 * Show the form for creating a new exam registration.
 */
public function create(Request $request)
{
    $user = auth()->user();

    // Get ALL students for the dropdown (based on user role)
    $students = Student::orderBy('first_name')
        ->when($user->role != 2, function ($q) use ($user) {
            return $q->where('campus_id', $user->campus_id);
        })
        ->get();

    $enrollments = Enrollment::with(['student', 'course'])
        ->where('requires_external_exam', true)
        ->whereDoesntHave('examRegistrations', function($q) {
            $q->whereIn('status', ['registered', 'active', 'completed']);
        })
        ->when($user->role != 2, function ($q) use ($user) {
            return $q->where('campus_id', $user->campus_id);
        })
        ->orderBy('created_at', 'desc')
        ->get();

    $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA', 'OTHER'];

    // If enrollment_id is provided, pre-select
    $selectedEnrollment = null;
    if ($request->filled('enrollment_id')) {
        $selectedEnrollment = Enrollment::with(['student', 'course'])
            ->find($request->enrollment_id);
    }

    return view('ktvtc.admin.exam-registrations.create', compact(
        'students',        // ← ADD THIS
        'enrollments',
        'examBodies',
        'selectedEnrollment'
    ));
}

    /**
     * Store a newly created exam registration.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enrollment_id' => 'required|exists:enrollments,id',
            'exam_body' => 'required|in:KNEC,CDACC,NITA,TVETA,OTHER',
            'exam_type' => 'required|string|max:100',
            'exam_code' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'index_number' => 'nullable|string|max:50',
            'registration_date' => 'required|date',
            'exam_date' => 'nullable|date|after_or_equal:registration_date',
            'status' => 'required|in:pending,registered,active,completed,failed',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Check if enrollment already has an active registration
            $exists = ExamRegistration::where('enrollment_id', $request->enrollment_id)
                ->whereIn('status', ['pending', 'registered', 'active'])
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->with('error', 'This enrollment already has an active exam registration.')
                    ->withInput();
            }

            $data = $request->all();
            $data['registered_by'] = auth()->id();

            $examRegistration = ExamRegistration::create($data);

            // Update enrollment's requires_external_exam flag if needed
            $enrollment = Enrollment::find($request->enrollment_id);
            if (!$enrollment->requires_external_exam) {
                $enrollment->update(['requires_external_exam' => true]);
            }

            DB::commit();

            return redirect()->route('admin.exam-registrations.show', $examRegistration)
                ->with('success', 'Exam registration created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create exam registration: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified exam registration.
     */
    public function show(ExamRegistration $examRegistration)
    {
        $examRegistration->load([
            'enrollment.student',
            'enrollment.course',
            'enrollment.campus',
            'registrar'
        ]);

        return view('ktvtc.admin.exam-registrations.show', compact('examRegistration'));
    }

    /**
     * Show the form for editing the specified exam registration.
     */
    public function edit(ExamRegistration $examRegistration)
    {
        $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA', 'OTHER'];

        return view('ktvtc.admin.exam-registrations.edit', compact('examRegistration', 'examBodies'));
    }

    /**
     * Update the specified exam registration.
     */
    public function update(Request $request, ExamRegistration $examRegistration)
    {
        $validator = Validator::make($request->all(), [
            'exam_body' => 'required|in:KNEC,CDACC,NITA,TVETA,OTHER',
            'exam_type' => 'required|string|max:100',
            'exam_code' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'index_number' => 'nullable|string|max:50',
            'registration_date' => 'required|date',
            'exam_date' => 'nullable|date|after_or_equal:registration_date',
            'status' => 'required|in:pending,registered,active,completed,failed',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $examRegistration->update($request->all());

            DB::commit();

            return redirect()->route('admin.exam-registrations.show', $examRegistration)
                ->with('success', 'Exam registration updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update exam registration: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified exam registration.
     */
    public function destroy(ExamRegistration $examRegistration)
    {
        if ($examRegistration->status === 'completed' || $examRegistration->certificate_number) {
            return redirect()->back()
                ->with('error', 'Cannot delete a completed or certified exam registration.');
        }

        $examRegistration->delete();

        return redirect()->route('admin.exam-registrations.index')
            ->with('success', 'Exam registration deleted successfully.');
    }

    /**
     * Mark as registered (add registration number)
     */
    public function markRegistered(Request $request, ExamRegistration $examRegistration)
    {
        $validator = Validator::make($request->all(), [
            'registration_number' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $examRegistration->update([
            'registration_number' => $request->registration_number,
            'status' => 'registered'
        ]);

        return redirect()->back()
            ->with('success', 'Exam registration marked as registered.');
    }

    /**
     * Enter exam results.
     */
    public function enterResults(Request $request, ExamRegistration $examRegistration)
    {
        $validator = Validator::make($request->all(), [
            'result' => 'required|in:Pass,Fail,Distinction',
            'grade' => 'nullable|string|max:10',
            'score' => 'nullable|numeric|min:0|max:100',
            'result_date' => 'required|date',
            'certificate_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = [
                'result' => $request->result,
                'grade' => $request->grade,
                'score' => $request->score,
                'result_date' => $request->result_date,
                'status' => 'completed'
            ];

            if ($request->filled('certificate_number')) {
                $data['certificate_number'] = $request->certificate_number;
                $data['certificate_issue_date'] = now();
            }

            $examRegistration->update($data);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Exam results entered successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to enter exam results: ' . $e->getMessage());
        }
    }

    /**
     * Print exam registration slip.
     */
    public function printSlip(ExamRegistration $examRegistration)
    {
        $examRegistration->load([
            'enrollment.student',
            'enrollment.course'
        ]);

        $pdf = Pdf::loadView('ktvtc.admin.exam-registrations.slip-pdf', compact('examRegistration'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('exam-slip-' . ($examRegistration->registration_number ?? 'slip') . '.pdf');
    }

    /**
     * Summary report.
     */
    public function summaryReport(Request $request)
    {
        $user = auth()->user();

        $query = ExamRegistration::with(['enrollment.course'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->whereHas('enrollment', function($sq) use ($user) {
                    $sq->where('campus_id', $user->campus_id);
                });
            });

        // Apply date filters
        if ($request->filled('date_from')) {
            $query->whereDate('registration_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('registration_date', '<=', $request->date_to);
        }

        // Total registrations
        $totalRegistrations = (clone $query)->count();

        // Status breakdown
        $statusBreakdown = [
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'registered' => (clone $query)->where('status', 'registered')->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
        ];

        // Exam body breakdown
        $examBodyBreakdown = [];
        $examBodies = ['KNEC', 'CDACC', 'NITA', 'TVETA', 'OTHER'];
        foreach ($examBodies as $body) {
            $examBodyBreakdown[$body] = (clone $query)
                ->where('exam_body', $body)
                ->count();
        }

        // Success rate
        $totalCompleted = $statusBreakdown['completed'] ?? 0;
        $totalFailed = $statusBreakdown['failed'] ?? 0;
        $totalWithResults = $totalCompleted + $totalFailed;
        $successRate = $totalWithResults > 0
            ? round(($totalCompleted / $totalWithResults) * 100, 1)
            : 0;

        return view('ktvtc.admin.exam-registrations.summary', compact(
            'totalRegistrations',
            'statusBreakdown',
            'examBodyBreakdown',
            'totalWithResults',
            'totalCompleted',
            'totalFailed',
            'successRate'
        ));
    }

    /**
     * Export registrations.
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        $query = ExamRegistration::with([
                'enrollment.student',
                'enrollment.course'
            ])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->whereHas('enrollment', function($sq) use ($user) {
                    $sq->where('campus_id', $user->campus_id);
                });
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('exam_body')) {
            $query->where('exam_body', $request->exam_body);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('registration_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('registration_date', '<=', $request->date_to);
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        $filename = 'exam-registrations-' . date('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Headers
            fputcsv($file, [
                'Student Name',
                'Student Number',
                'Course',
                'Exam Body',
                'Exam Type',
                'Registration Number',
                'Index Number',
                'Registration Date',
                'Exam Date',
                'Status',
                'Result',
                'Grade',
                'Certificate Number',
            ]);

            // Data
            foreach ($registrations as $reg) {
                fputcsv($file, [
                    $reg->enrollment->student->full_name ?? 'N/A',
                    $reg->enrollment->student->student_number ?? 'N/A',
                    $reg->enrollment->course->name ?? 'N/A',
                    $reg->exam_body ?? 'N/A',
                    $reg->exam_type ?? 'N/A',
                    $reg->registration_number ?? 'N/A',
                    $reg->index_number ?? 'N/A',
                    $reg->registration_date->format('Y-m-d'),
                    $reg->exam_date ? $reg->exam_date->format('Y-m-d') : 'N/A',
                    ucfirst($reg->status),
                    $reg->result ?? 'N/A',
                    $reg->grade ?? 'N/A',
                    $reg->certificate_number ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
