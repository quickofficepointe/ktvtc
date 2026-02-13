<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Campus;
use App\Models\Application;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentsExport;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Display a listing of students (Admin).
     */
    public function adminindex(Request $request)
    {
        $user = auth()->user();

        $query = Student::query()
            ->with(['campus', 'application'])
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

        // Statistics
        $totalStudents = (clone $query)->count();
        $activeStudents = (clone $query)->where('status', 'active')->count();
        $graduatedStudents = (clone $query)->where('status', 'graduated')->count();
        $historicalStudents = (clone $query)->where('status', 'historical')->count();
        $requiresCleanup = (clone $query)->where('requires_cleanup', true)->count();

        // Status breakdown for chart
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

        // Campuses based on role
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

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        $user = auth()->user();

        // Campus selection based on role
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        // Get accepted applications NOT already linked to a student
        $applications = Application::where('status', 'accepted')
            ->whereDoesntHave('student')
            ->orderBy('application_number')
            ->get();

        $courses = Course::orderBy('name')->get();

        $intakes = [
            'january', 'february', 'march', 'april', 'may', 'june',
            'july', 'august', 'september', 'october', 'november', 'december'
        ];

        return view('ktvtc.admin.students.create', compact('campuses', 'applications', 'courses', 'intakes'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        // Dynamic validation based on creation type
        $rules = [
            'application_id' => 'nullable|exists:applications,id',
            'registration_type' => 'required|in:manual_entry,online_application',
        ];

        // If NOT from application, validate required fields
        if (!$request->filled('application_id')) {
            $rules['first_name'] = 'required|string|max:100';
            $rules['last_name'] = 'required|string|max:100';
            $rules['campus_id'] = 'required|exists:campuses,id';
            $rules['student_category'] = 'required|in:regular,alumnus,staff_child,sponsored,scholarship';
        }

        // Common validation rules
        $rules = array_merge($rules, [
            'middle_name' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:150|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|max:30|unique:students,id_number',
            'id_type' => 'nullable|in:id,birth_certificate,passport',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'county' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:50',
            'next_of_kin_name' => 'nullable|string|max:150',
            'next_of_kin_phone' => 'nullable|string|max:20',
            'next_of_kin_relationship' => 'nullable|string|max:50',
            'next_of_kin_address' => 'nullable|string',
            'next_of_kin_email' => 'nullable|email|max:150',
            'next_of_kin_id_number' => 'nullable|string|max:30',
            'emergency_contact_name' => 'nullable|string|max:150',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:50',
            'emergency_contact_phone_alt' => 'nullable|string|max:20',
            'education_level' => 'nullable|string|max:100',
            'school_name' => 'nullable|string|max:200',
            'graduation_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'mean_grade' => 'nullable|string|max:10',
            'kcse_index_number' => 'nullable|string|max:30',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'blood_group' => 'nullable|string|max:5',
            'special_needs' => 'nullable|string',
            'disability_type' => 'nullable|string|max:50',
            'tshirt_size' => 'nullable|string|max:10',
            'remarks' => 'nullable|string',
            'student_category' => 'nullable|in:regular,alumnus,staff_child,sponsored,scholarship',
            'status' => 'nullable|in:active,inactive,graduated,dropped,suspended,alumnus,prospective,historical',
            'registration_date' => 'nullable|date',
            'student_number' => 'nullable|string|max:50|unique:students,student_number',
            'legacy_student_code' => 'nullable|string|max:50',
            'legacy_code' => 'nullable|string|max:50',

            // Documents - only for manual entry
            'id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'passport_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'education_certificates' => 'nullable|file|mimes:pdf|max:5120',
            'other_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // CASE 1: CREATING FROM ACCEPTED APPLICATION
            if ($request->filled('application_id')) {
                $application = Application::findOrFail($request->application_id);

                // DIRECT 1:1 MAPPING - Application to Student
                $data = [
                    // Institution & Links
                    'campus_id' => $application->campus_id,
                    'application_id' => $application->id,
                    'student_number' => $this->generateStudentNumber(),

                    // Personal Information
                    'title' => $request->title,
                    'first_name' => $application->first_name,
                    'last_name' => $application->last_name,
                    'middle_name' => $application->middle_name,
                    'email' => $application->email,
                    'phone' => $application->phone,
                    'id_number' => $application->id_number,
                    'id_type' => $application->id_type,
                    'date_of_birth' => $application->date_of_birth,
                    'gender' => $application->gender,
                    'marital_status' => $request->marital_status,

                    // Contact Information
                    'address' => $application->address,
                    'city' => $application->city,
                    'county' => $application->county,
                    'postal_code' => $application->postal_code,
                    'country' => $application->country ?? 'Kenya',

                    // Next of Kin (from emergency contact)
                    'next_of_kin_name' => $application->emergency_contact_name,
                    'next_of_kin_phone' => $application->emergency_contact_phone,
                    'next_of_kin_relationship' => $application->emergency_contact_relationship,
                    'next_of_kin_address' => $application->address,
                    'next_of_kin_email' => $request->next_of_kin_email,
                    'next_of_kin_id_number' => $request->next_of_kin_id_number,

                    // Emergency Contact
                    'emergency_contact_name' => $application->emergency_contact_name,
                    'emergency_contact_phone' => $application->emergency_contact_phone,
                    'emergency_contact_relationship' => $application->emergency_contact_relationship,
                    'emergency_contact_phone_alt' => $request->emergency_contact_phone_alt,

                    // Education Background
                    'education_level' => $application->education_level,
                    'school_name' => $application->school_name,
                    'graduation_year' => $application->graduation_year,
                    'mean_grade' => $application->mean_grade,
                    'kcse_index_number' => $request->kcse_index_number,

                    // Medical & Special Needs
                    'medical_conditions' => $request->medical_conditions,
                    'allergies' => $request->allergies,
                    'blood_group' => $request->blood_group,
                    'special_needs' => $application->special_needs,
                    'disability_type' => $request->disability_type,

                    // Documents - Copy paths from application
                    'id_document_path' => $application->id_document,
                    'passport_photo_path' => $application->passport_photo,
                    'education_certificates_path' => $application->education_certificates,
                    'other_documents' => null,

                    // Additional Info
                    'tshirt_size' => $request->tshirt_size,
                    'remarks' => "Created from accepted application: {$application->application_number}",
                    'student_category' => $request->student_category ?? 'regular',

                    // Status
                    'status' => $request->status ?? 'active',
                    'registration_type' => 'online_application',
                    'registration_date' => $request->registration_date ?? now(),
                    'last_activity_date' => null,

                    // Legacy fields - not from application
                    'legacy_student_code' => null,
                    'legacy_code' => null,
                    'import_batch' => null,
                    'import_notes' => null,
                    'requires_cleanup' => false,
                ];
            }
            // CASE 2: MANUAL ENTRY
            else {
                $data = $request->except(['id_document', 'passport_photo', 'education_certificates', 'other_documents']);

                // Generate student number if not provided
                if (empty($data['student_number'])) {
                    $data['student_number'] = $this->generateStudentNumber();
                }

                // Set registration type
                $data['registration_type'] = 'manual_entry';

                // Set registration date if not provided
                if (empty($data['registration_date'])) {
                    $data['registration_date'] = now();
                }

                // Set defaults
                $data['requires_cleanup'] = false;
                $data['last_activity_date'] = null;

                // Handle document uploads for manual entry
                if ($request->hasFile('id_document')) {
                    $data['id_document_path'] = $request->file('id_document')
                        ->store('students/documents/id', 'public');
                }

                if ($request->hasFile('passport_photo')) {
                    $data['passport_photo_path'] = $request->file('passport_photo')
                        ->store('students/photos', 'public');
                }

                if ($request->hasFile('education_certificates')) {
                    $data['education_certificates_path'] = $request->file('education_certificates')
                        ->store('students/documents/education', 'public');
                }

                if ($request->hasFile('other_documents')) {
                    $otherDocs = [];
                    foreach ($request->file('other_documents') as $file) {
                        $path = $file->store('students/documents/other', 'public');
                        $otherDocs[] = [
                            'name' => $file->getClientOriginalName(),
                            'path' => $path,
                            'uploaded_at' => now()->toDateTimeString()
                        ];
                    }
                    $data['other_documents'] = json_encode($otherDocs);
                }
            }

            // Create the student
            $student = Student::create($data);

            // If from application, update the application with student_id
            if (!empty($data['application_id'])) {
                $application = Application::find($data['application_id']);
                if ($application) {
                    $application->update(['student_id' => $student->id]);
                }
            }

            DB::commit();

            return redirect()->route('admin.tvet.students.index')
                ->with('success', 'Student created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create student: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        $student->load(['campus', 'application.course']);

        // Parse other_documents if it's JSON
        if ($student->other_documents && is_string($student->other_documents)) {
            $student->other_documents = json_decode($student->other_documents, true);
        }

        return view('ktvtc.admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified student.
     */
 /**
 * Show the form for editing the specified student.
 */
public function edit(Student $student)
{
    $user = auth()->user();

    // Campus selection based on role
    if ($user->role == 2) {
        $campuses = Campus::orderBy('name')->get();
    } else {
        $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
    }

    // Get applications that are NOT linked to any student
    // We don't include the current application because it's already linked
    $applications = Application::whereDoesntHave('student')
        ->orderBy('application_number')
        ->get();

    // Parse other_documents if it's JSON
    if ($student->other_documents && is_string($student->other_documents)) {
        $student->other_documents = json_decode($student->other_documents, true);
    }

    return view('ktvtc.admin.students.edit', compact('student', 'campuses', 'applications'));
}

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:150|unique:students,email,' . $student->id,
            'phone' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|max:30|unique:students,id_number,' . $student->id,
            'id_type' => 'nullable|in:id,birth_certificate,passport',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|string|max:20',
            'campus_id' => 'nullable|exists:campuses,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'county' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:50',
            'next_of_kin_name' => 'nullable|string|max:150',
            'next_of_kin_phone' => 'nullable|string|max:20',
            'next_of_kin_relationship' => 'nullable|string|max:50',
            'next_of_kin_address' => 'nullable|string',
            'next_of_kin_email' => 'nullable|email|max:150',
            'next_of_kin_id_number' => 'nullable|string|max:30',
            'emergency_contact_name' => 'nullable|string|max:150',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:50',
            'emergency_contact_phone_alt' => 'nullable|string|max:20',
            'education_level' => 'nullable|string|max:100',
            'school_name' => 'nullable|string|max:200',
            'graduation_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'mean_grade' => 'nullable|string|max:10',
            'kcse_index_number' => 'nullable|string|max:30',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'blood_group' => 'nullable|string|max:5',
            'special_needs' => 'nullable|string',
            'disability_type' => 'nullable|string|max:50',
            'tshirt_size' => 'nullable|string|max:10',
            'remarks' => 'nullable|string',
            'student_category' => 'nullable|in:regular,alumnus,staff_child,sponsored,scholarship',
            'status' => 'nullable|in:active,inactive,graduated,dropped,suspended,alumnus,prospective,historical',
            'registration_date' => 'nullable|date',
            'student_number' => 'nullable|string|max:50|unique:students,student_number,' . $student->id,
            'legacy_student_code' => 'nullable|string|max:50',

            // Documents
            'id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'passport_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'education_certificates' => 'nullable|file|mimes:pdf|max:5120',
            'other_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->except(['id_document', 'passport_photo', 'education_certificates', 'other_documents']);

            // Handle document uploads
            if ($request->hasFile('id_document')) {
                if ($student->id_document_path) {
                    Storage::disk('public')->delete($student->id_document_path);
                }
                $data['id_document_path'] = $request->file('id_document')
                    ->store('students/documents/id', 'public');
            }

            if ($request->hasFile('passport_photo')) {
                if ($student->passport_photo_path) {
                    Storage::disk('public')->delete($student->passport_photo_path);
                }
                $data['passport_photo_path'] = $request->file('passport_photo')
                    ->store('students/photos', 'public');
            }

            if ($request->hasFile('education_certificates')) {
                if ($student->education_certificates_path) {
                    Storage::disk('public')->delete($student->education_certificates_path);
                }
                $data['education_certificates_path'] = $request->file('education_certificates')
                    ->store('students/documents/education', 'public');
            }

            if ($request->hasFile('other_documents')) {
                $existingDocs = $student->other_documents ?
                    (is_string($student->other_documents) ? json_decode($student->other_documents, true) : $student->other_documents) : [];

                foreach ($request->file('other_documents') as $file) {
                    $path = $file->store('students/documents/other', 'public');
                    $existingDocs[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
                $data['other_documents'] = json_encode($existingDocs);
            }

            $student->update($data);

            DB::commit();

            return redirect()->route('admin.tvet.students.index')
                ->with('success', 'Student updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update student: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        DB::beginTransaction();

        try {
            // Delete associated files
            if ($student->id_document_path) {
                Storage::disk('public')->delete($student->id_document_path);
            }
            if ($student->passport_photo_path) {
                Storage::disk('public')->delete($student->passport_photo_path);
            }
            if ($student->education_certificates_path) {
                Storage::disk('public')->delete($student->education_certificates_path);
            }

            if ($student->other_documents) {
                $docs = is_string($student->other_documents) ?
                    json_decode($student->other_documents, true) : $student->other_documents;
                if (is_array($docs)) {
                    foreach ($docs as $doc) {
                        if (isset($doc['path'])) {
                            Storage::disk('public')->delete($doc['path']);
                        }
                    }
                }
            }

            $student->delete();

            DB::commit();

            return redirect()->route('admin.tvet.students.index')
                ->with('success', 'Student deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete student: ' . $e->getMessage());
        }
    }

    /**
     * Activate a student.
     */
    public function activate(Student $student)
    {
        $student->update(['status' => 'active']);
        return redirect()->back()->with('success', 'Student activated successfully.');
    }

    /**
     * Suspend a student.
     */
    public function suspend(Student $student)
    {
        $student->update(['status' => 'suspended']);
        return redirect()->back()->with('success', 'Student suspended successfully.');
    }

    /**
     * Archive a student (soft delete).
     */
    public function archive(Student $student)
    {
        $student->delete();
        return redirect()->route('admin.tvet.students.index')
            ->with('success', 'Student archived successfully.');
    }

    /**
     * Display student details.
     */
    public function details(Student $student)
    {
        $student->load(['campus', 'application.course']);

        if ($student->other_documents && is_string($student->other_documents)) {
            $student->other_documents = json_decode($student->other_documents, true);
        }

        return view('ktvtc.admin.students.details', compact('student'));
    }

    /**
     * Show the form for editing student details.
     */
    public function editDetails(Student $student)
    {
        if ($student->other_documents && is_string($student->other_documents)) {
            $student->other_documents = json_decode($student->other_documents, true);
        }

        return view('ktvtc.admin.students.details-edit', compact('student'));
    }

    /**
     * Update student details.
     */
    public function updateDetails(Request $request, Student $student)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|max:30',
            'id_type' => 'nullable|in:id,birth_certificate,passport',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'county' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $student->update($request->only([
            'first_name', 'last_name', 'middle_name', 'title', 'email', 'phone',
            'id_number', 'id_type', 'date_of_birth', 'gender', 'marital_status',
            'address', 'city', 'county', 'postal_code', 'country'
        ]));

        return redirect()->route('admin.tvet.students.details', $student)
            ->with('success', 'Student details updated successfully.');
    }

    // ============ BULK ACTIONS ============

    public function bulkActivate(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $count = Student::whereIn('id', $request->student_ids)
            ->update(['status' => 'active']);

        return redirect()->back()
            ->with('success', "{$count} students activated successfully.");
    }

    public function bulkSuspend(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $count = Student::whereIn('id', $request->student_ids)
            ->update(['status' => 'suspended']);

        return redirect()->back()
            ->with('success', "{$count} students suspended successfully.");
    }

    public function bulkArchive(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $count = Student::whereIn('id', $request->student_ids)->count();
        Student::whereIn('id', $request->student_ids)->delete();

        return redirect()->back()
            ->with('success', "{$count} students archived successfully.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        DB::beginTransaction();

        try {
            $students = Student::whereIn('id', $request->student_ids)->get();
            $count = 0;

            foreach ($students as $student) {
                // Delete associated files
                if ($student->id_document_path) {
                    Storage::disk('public')->delete($student->id_document_path);
                }
                if ($student->passport_photo_path) {
                    Storage::disk('public')->delete($student->passport_photo_path);
                }
                if ($student->education_certificates_path) {
                    Storage::disk('public')->delete($student->education_certificates_path);
                }

                $student->forceDelete();
                $count++;
            }

            DB::commit();

            return redirect()->back()
                ->with('success', "{$count} students permanently deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete students: ' . $e->getMessage());
        }
    }

    // ============ IMPORT/EXPORT ============
public function export(Request $request)
{
    $request->validate([
        'format' => 'nullable|in:xlsx,csv',
        'status' => 'nullable|string',
        'campus_id' => 'nullable|exists:campuses,id',
    ]);

    $format = $request->get('format', 'xlsx');
    $fileName = 'students_export_' . now()->format('Y-m-d_H-i-s') . '.' . $format;

    return Excel::download(new StudentsExport($request), $fileName);
}

    public function import()
    {
        $user = auth()->user();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        return view('ktvtc.admin.students.import', compact('campuses'));
    }

  public function importProcess(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        'batch_name' => 'nullable|string|max:50',
        'campus_id' => 'nullable|exists:campuses,id',
    ]);

    $user = auth()->user();

    // For non-admin users, force their campus_id
    if ($user->role != 2) {
        $request->merge(['campus_id' => $user->campus_id]);
    }

    try {
        $import = new StudentsImport($request->batch_name, $request->campus_id);
        Excel::import($import, $request->file('file'));

        $stats = $import->getImportStats();

        return redirect()->route('admin.tvet.students.index')
            ->with('success', "Import completed: {$stats['imported']} imported, {$stats['errors']} errors, {$stats['warnings']} warnings.")
            ->with('import_stats', $stats);

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Import failed: ' . $e->getMessage());
    }
}

    // ============ REPORTS ============

    public function enrollmentReport(Request $request)
    {
        $user = auth()->user();

        $query = Student::query()
            ->select(DB::raw('DATE(registration_date) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($user->role != 2) {
            $query->where('campus_id', $user->campus_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('registration_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('registration_date', '<=', $request->date_to);
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        $enrollmentData = $query->get();

        $totalEnrolled = (clone $query)->count();
        $enrolledThisMonth = (clone $query)->whereMonth('registration_date', now()->month)
            ->whereYear('registration_date', now()->year)
            ->count();
        $enrolledThisYear = (clone $query)->whereYear('registration_date', now()->year)
            ->count();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        return view('ktvtc.admin.students.reports.enrollment', compact(
            'enrollmentData',
            'totalEnrolled',
            'enrolledThisMonth',
            'enrolledThisYear',
            'campuses'
        ));
    }

    public function demographicsReport(Request $request)
    {
        $user = auth()->user();

        $query = Student::query();

        if ($user->role != 2) {
            $query->where('campus_id', $user->campus_id);
        }

        $genderDistribution = (clone $query)->select('gender', DB::raw('COUNT(*) as count'))
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->get();

        $ageRanges = [
            'under_18' => (clone $query)->whereDate('date_of_birth', '>', now()->subYears(18))->count(),
            '18_25' => (clone $query)->whereBetween('date_of_birth', [now()->subYears(25), now()->subYears(18)])->count(),
            '26_35' => (clone $query)->whereBetween('date_of_birth', [now()->subYears(35), now()->subYears(26)])->count(),
            '36_45' => (clone $query)->whereBetween('date_of_birth', [now()->subYears(45), now()->subYears(36)])->count(),
            '46_plus' => (clone $query)->whereDate('date_of_birth', '<=', now()->subYears(46))->count(),
        ];

        $countyDistribution = (clone $query)->select('county', DB::raw('COUNT(*) as count'))
            ->whereNotNull('county')
            ->groupBy('county')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $categoryDistribution = (clone $query)->select('student_category', DB::raw('COUNT(*) as count'))
            ->groupBy('student_category')
            ->get();

        $statusDistribution = (clone $query)->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $totalStudents = (clone $query)->count();

        return view('ktvtc.admin.students.reports.demographics', compact(
            'genderDistribution',
            'ageRanges',
            'countyDistribution',
            'categoryDistribution',
            'statusDistribution',
            'totalStudents'
        ));
    }

    public function performanceReport(Request $request)
    {
        $user = auth()->user();

        $query = Student::query();

        if ($user->role != 2) {
            $query->where('campus_id', $user->campus_id);
        }

        $studentCount = (clone $query)->count();
        $activeStudents = (clone $query)->where('status', 'active')->count();
        $graduatedStudents = (clone $query)->where('status', 'graduated')->count();
        $droppedStudents = (clone $query)->where('status', 'dropped')->count();

        $retentionRate = $studentCount > 0
            ? round(($activeStudents + $graduatedStudents) / $studentCount * 100, 2)
            : 0;

        $graduationRate = $studentCount > 0
            ? round($graduatedStudents / $studentCount * 100, 2)
            : 0;

        return view('ktvtc.admin.students.reports.performance', compact(
            'studentCount',
            'activeStudents',
            'graduatedStudents',
            'droppedStudents',
            'retentionRate',
            'graduationRate'
        ));
    }

    // ============ HELPER METHODS ============

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
}
