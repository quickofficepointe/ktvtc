<?php

namespace App\Http\Controllers;

use App\Models\StudentDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check if it's an AJAX request
        if ($request->ajax()) {
            $query = User::where('role', 'student')
                ->with(['details'])
                ->orderBy('created_at', 'desc');

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%")
                        ->orWhereHas('details', function ($q) use ($search) {
                            $q->where('national_id_number', 'like', "%{$search}%")
                                ->orWhere('personal_phone', 'like', "%{$search}%")
                                ->orWhere('next_of_kin_name', 'like', "%{$search}%");
                        });
                });
            }

            // Apply status filter
            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'complete':
                        $query->whereHas('details', function ($q) {
                            $q->whereRaw("JSON_LENGTH(documents_uploaded) = JSON_LENGTH(JSON_REMOVE(documents_uploaded, '$.*.?(@.uploaded = false)'))");
                        });
                        break;
                    case 'incomplete':
                        $query->whereHas('details', function ($q) {
                            $q->whereRaw("JSON_LENGTH(documents_uploaded) != JSON_LENGTH(JSON_REMOVE(documents_uploaded, '$.*.?(@.uploaded = false)'))");
                        });
                        break;
                    case 'attachment':
                        $query->whereHas('details', function ($q) {
                            $q->where('industrial_attachment_status', 'ongoing');
                        });
                        break;
                    case 'alumni':
                        $query->whereHas('details', function ($q) {
                            $q->where('is_alumni', true);
                        });
                        break;
                    case 'employed':
                        $query->whereHas('details', function ($q) {
                            $q->where('is_employed', true);
                        });
                        break;
                }
            }

            // Apply sponsorship filter
            if ($request->filled('sponsorship')) {
                $query->whereHas('details', function ($q) use ($request) {
                    $q->where('sponsorship_type', $request->sponsorship);
                });
            }

            $students = $query->paginate(20);

            // Add additional data for the response
            foreach ($students as $student) {
                if ($student->details) {
                    $student->details->append(['documents_completion_percentage', 'has_complete_documents']);
                }
            }

            return response()->json($students);
        }

        // Get statistics for the dashboard
        $totalStudents = User::where('role', 'student')->count();
        $completeProfiles = StudentDetail::hasCompleteDocuments()->count();
        $activeAttachment = StudentDetail::activeIndustrialAttachment()->count();
        $alumniCount = StudentDetail::alumni()->count();

        // Get all sponsorship types for filter
        $sponsorshipTypes = [
            'self' => 'Self Sponsored',
            'parent' => 'Parent/Guardian',
            'spouse' => 'Spouse',
            'employer' => 'Employer',
            'government' => 'Government',
            'county' => 'County Government',
            'helb' => 'HELB',
            'scholarship' => 'Scholarship',
            'bursary' => 'Bursary',
            'other' => 'Other'
        ];

        return view('ktvtc.admin.students.index', compact(
            'totalStudents',
            'completeProfiles',
            'activeAttachment',
            'alumniCount',
            'sponsorshipTypes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all students without details for selection
        $students = User::where('role', 'student')
            ->whereDoesntHave('details')
            ->get();

        return view('admin.student-details.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:users,id|unique:student_details,student_id',

            // Personal Identification
            'national_id_number' => 'nullable|string|max:20|unique:student_details,national_id_number',
            'passport_number' => 'nullable|string|max:20|unique:student_details,passport_number',
            'birth_certificate_number' => 'nullable|string|max:20',
            'kra_pin' => 'nullable|string|max:20',
            'nssf_number' => 'nullable|string|max:20',
            'nhif_number' => 'nullable|string|max:20',

            // Contact Details
            'personal_email' => 'nullable|email|unique:student_details,personal_email',
            'personal_phone' => 'required|string|max:15',
            'alternative_phone' => 'nullable|string|max:15',
            'postal_address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'town' => 'nullable|string|max:100',
            'county' => 'nullable|string|max:100',

            // Next of Kin
            'next_of_kin_name' => 'required|string|max:255',
            'next_of_kin_relationship' => 'required|string|max:50',
            'next_of_kin_phone' => 'required|string|max:15',
            'next_of_kin_email' => 'nullable|email',
            'next_of_kin_address' => 'required|string',
            'next_of_kin_id_number' => 'nullable|string|max:20',
            'next_of_kin_occupation' => 'nullable|string|max:100',

            // Emergency Contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:50',
            'emergency_contact_phone' => 'required|string|max:15',
            'emergency_contact_alternate_phone' => 'nullable|string|max:15',
            'emergency_contact_address' => 'nullable|string',

            // Education Background
            'secondary_school_name' => 'required|string|max:255',
            'secondary_school_completion_year' => 'required|integer|min:1950|max:' . date('Y'),
            'kcse_index_number' => 'nullable|string|max:20',
            'kcse_mean_grade' => 'nullable|string|max:2',
            'secondary_school_address' => 'nullable|string',

            'primary_school_name' => 'nullable|string|max:255',
            'primary_school_completion_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'knec_index_primary' => 'nullable|string|max:20',
            'primary_school_address' => 'nullable|string',

            // Previous Education
            'previous_institution' => 'nullable|string|max:255',
            'previous_course' => 'nullable|string|max:255',
            'previous_start_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'previous_end_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'previous_qualification' => 'nullable|string|max:100',
            'transfer_reason' => 'nullable|string',

            // Employment Details
            'is_employed' => 'boolean',
            'employer_name' => 'nullable|required_if:is_employed,true|string|max:255',
            'employer_address' => 'nullable|string',
            'employer_phone' => 'nullable|string|max:15',
            'job_title' => 'nullable|string|max:100',
            'employment_duration' => 'nullable|string|max:50',
            'employer_email' => 'nullable|email',

            // Medical Information
            'blood_group' => 'nullable|string|max:3',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_illnesses' => 'nullable|string',
            'disabilities' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'doctor_name' => 'nullable|string|max:255',
            'doctor_phone' => 'nullable|string|max:15',
            'doctor_address' => 'nullable|string',
            'medical_insurance_provider' => 'nullable|string|max:100',
            'medical_insurance_number' => 'nullable|string|max:50',

            // Sponsorship Details
            'sponsorship_type' => 'required|in:self,parent,spouse,employer,government,county,helb,scholarship,bursary,other',
            'sponsor_name' => 'nullable|required_if:sponsorship_type,!=,self|string|max:255',
            'sponsor_id_number' => 'nullable|string|max:20',
            'sponsor_phone' => 'nullable|required_if:sponsorship_type,!=,self|string|max:15',
            'sponsor_email' => 'nullable|email',
            'sponsor_address' => 'nullable|string',
            'sponsor_relationship' => 'nullable|string|max:50',
            'sponsor_occupation' => 'nullable|string|max:100',

            // Bank Details
            'bank_name' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_branch' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create initial documents array
            $documents = [
                'id_copy' => ['uploaded' => false, 'verified' => false],
                'kcse_certificate' => ['uploaded' => false, 'verified' => false],
                'passport_photo' => ['uploaded' => false, 'verified' => false],
                'medical_certificate' => ['uploaded' => false, 'verified' => false],
                'next_of_kin_id' => ['uploaded' => false, 'verified' => false],
            ];

            // Add sponsorship letter if not self-sponsored
            if ($request->sponsorship_type !== 'self') {
                $documents['sponsorship_letter'] = ['uploaded' => false, 'verified' => false];
            }

            // Create student details
            $studentDetail = StudentDetail::create(array_merge(
                $request->except(['_token', '_method']),
                [
                    'created_by' => auth()->id(),
                    'documents_uploaded' => $documents,
                ]
            ));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student details created successfully.',
                'data' => $studentDetail
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $student = User::with(['details'])->findOrFail($id);

        // Load additional data if needed
        $student->details->append([
            'age',
            'is_minor',
            'full_address',
            'documents_completion_percentage',
            'has_complete_documents',
            'industrial_attachment_duration',
            'is_attachment_active'
        ]);

        // Check if it's an AJAX request
        if (request()->ajax()) {
            return response()->json([
                'student' => $student,
                'details' => $student->details
            ]);
        }

        return view('admin.student-details.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $student = User::with(['details'])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'student' => $student,
                'details' => $student->details
            ]);
        }

        return view('admin.student-details.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            // Personal Identification
            'national_id_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('student_details')->ignore($studentDetail->id)
            ],
            'passport_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('student_details')->ignore($studentDetail->id)
            ],
            'birth_certificate_number' => 'nullable|string|max:20',
            'kra_pin' => 'nullable|string|max:20',
            'nssf_number' => 'nullable|string|max:20',
            'nhif_number' => 'nullable|string|max:20',

            // Contact Details
            'personal_email' => [
                'nullable',
                'email',
                Rule::unique('student_details')->ignore($studentDetail->id)
            ],
            'personal_phone' => 'required|string|max:15',
            'alternative_phone' => 'nullable|string|max:15',
            'postal_address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'town' => 'nullable|string|max:100',
            'county' => 'nullable|string|max:100',

            // Next of Kin
            'next_of_kin_name' => 'required|string|max:255',
            'next_of_kin_relationship' => 'required|string|max:50',
            'next_of_kin_phone' => 'required|string|max:15',
            'next_of_kin_email' => 'nullable|email',
            'next_of_kin_address' => 'required|string',
            'next_of_kin_id_number' => 'nullable|string|max:20',
            'next_of_kin_occupation' => 'nullable|string|max:100',

            // Emergency Contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:50',
            'emergency_contact_phone' => 'required|string|max:15',
            'emergency_contact_alternate_phone' => 'nullable|string|max:15',
            'emergency_contact_address' => 'nullable|string',

            // Education Background
            'secondary_school_name' => 'required|string|max:255',
            'secondary_school_completion_year' => 'required|integer|min:1950|max:' . date('Y'),
            'kcse_index_number' => 'nullable|string|max:20',
            'kcse_mean_grade' => 'nullable|string|max:2',
            'secondary_school_address' => 'nullable|string',

            'primary_school_name' => 'nullable|string|max:255',
            'primary_school_completion_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'knec_index_primary' => 'nullable|string|max:20',
            'primary_school_address' => 'nullable|string',

            // Previous Education
            'previous_institution' => 'nullable|string|max:255',
            'previous_course' => 'nullable|string|max:255',
            'previous_start_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'previous_end_year' => 'nullable|integer|min:1950|max:' . date('Y'),
            'previous_qualification' => 'nullable|string|max:100',
            'transfer_reason' => 'nullable|string',

            // Employment Details
            'is_employed' => 'boolean',
            'employer_name' => 'nullable|required_if:is_employed,true|string|max:255',
            'employer_address' => 'nullable|string',
            'employer_phone' => 'nullable|string|max:15',
            'job_title' => 'nullable|string|max:100',
            'employment_duration' => 'nullable|string|max:50',
            'employer_email' => 'nullable|email',

            // Medical Information
            'blood_group' => 'nullable|string|max:3',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_illnesses' => 'nullable|string',
            'disabilities' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'doctor_name' => 'nullable|string|max:255',
            'doctor_phone' => 'nullable|string|max:15',
            'doctor_address' => 'nullable|string',
            'medical_insurance_provider' => 'nullable|string|max:100',
            'medical_insurance_number' => 'nullable|string|max:50',

            // Sponsorship Details
            'sponsorship_type' => 'required|in:self,parent,spouse,employer,government,county,helb,scholarship,bursary,other',
            'sponsor_name' => 'nullable|required_if:sponsorship_type,!=,self|string|max:255',
            'sponsor_id_number' => 'nullable|string|max:20',
            'sponsor_phone' => 'nullable|required_if:sponsorship_type,!=,self|string|max:15',
            'sponsor_email' => 'nullable|email',
            'sponsor_address' => 'nullable|string',
            'sponsor_relationship' => 'nullable|string|max:50',
            'sponsor_occupation' => 'nullable|string|max:100',

            // Bank Details
            'bank_name' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_branch' => 'nullable|string|max:100',

            // Notes
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update student details
            $studentDetail->update(array_merge(
                $request->except(['_token', '_method']),
                [
                    'updated_by' => auth()->id(),
                    'last_updated_at' => now(),
                ]
            ));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student details updated successfully.',
                'data' => $studentDetail
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        DB::beginTransaction();
        try {
            $studentDetail->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student details deleted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sponsorship data for a student
     */
    public function getSponsorship($id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        return response()->json([
            'sponsorship_type' => $studentDetail->sponsorship_type,
            'sponsor_name' => $studentDetail->sponsor_name,
            'sponsor_id_number' => $studentDetail->sponsor_id_number,
            'sponsor_phone' => $studentDetail->sponsor_phone,
            'sponsor_email' => $studentDetail->sponsor_email,
            'sponsor_address' => $studentDetail->sponsor_address,
        ]);
    }

    /**
     * Update sponsorship for a student
     */
    public function updateSponsorship(Request $request, $id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'sponsorship_type' => 'required|in:self,parent,spouse,employer,government,county,helb,scholarship,bursary,other',
            'sponsor_name' => 'nullable|required_if:sponsorship_type,!=,self|string|max:255',
            'sponsor_id_number' => 'nullable|string|max:20',
            'sponsor_phone' => 'nullable|required_if:sponsorship_type,!=,self|string|max:15',
            'sponsor_email' => 'nullable|email',
            'sponsor_address' => 'nullable|string',
            'sponsor_relationship' => 'nullable|string|max:50',
            'sponsor_occupation' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $studentDetail->updateSponsorship(
                $request->sponsorship_type,
                $request->sponsor_name,
                $request->sponsor_id_number,
                $request->sponsor_phone
            );

            // Update additional fields if provided
            if ($request->filled('sponsor_email')) {
                $studentDetail->sponsor_email = $request->sponsor_email;
            }
            if ($request->filled('sponsor_address')) {
                $studentDetail->sponsor_address = $request->sponsor_address;
            }
            if ($request->filled('sponsor_relationship')) {
                $studentDetail->sponsor_relationship = $request->sponsor_relationship;
            }
            if ($request->filled('sponsor_occupation')) {
                $studentDetail->sponsor_occupation = $request->sponsor_occupation;
            }

            $studentDetail->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sponsorship updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sponsorship: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get medical information for a student
     */
    public function getMedical($id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        return response()->json([
            'blood_group' => $studentDetail->blood_group,
            'medical_conditions' => $studentDetail->medical_conditions,
            'allergies' => $studentDetail->allergies,
            'chronic_illnesses' => $studentDetail->chronic_illnesses,
            'disabilities' => $studentDetail->disabilities,
            'special_needs' => $studentDetail->special_needs,
            'doctor_name' => $studentDetail->doctor_name,
            'doctor_phone' => $studentDetail->doctor_phone,
            'doctor_address' => $studentDetail->doctor_address,
            'medical_insurance_provider' => $studentDetail->medical_insurance_provider,
            'medical_insurance_number' => $studentDetail->medical_insurance_number,
        ]);
    }

    /**
     * Update medical information for a student
     */
    public function updateMedical(Request $request, $id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'blood_group' => 'nullable|string|max:3',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_illnesses' => 'nullable|string',
            'disabilities' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'doctor_name' => 'nullable|string|max:255',
            'doctor_phone' => 'nullable|string|max:15',
            'doctor_address' => 'nullable|string',
            'medical_insurance_provider' => 'nullable|string|max:100',
            'medical_insurance_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $studentDetail->update($request->all());
            $studentDetail->updated_by = auth()->id();
            $studentDetail->last_updated_at = now();
            $studentDetail->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Medical information updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update medical information: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents management page for a student
     */
    public function getDocuments($id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();
        $requiredDocuments = $studentDetail->getRequiredDocuments();

        $documents = $studentDetail->documents_uploaded ?? [];

        return view('admin.student-details.documents', compact('studentDetail', 'requiredDocuments', 'documents'));
    }

    /**
     * Update document status for a student
     */
    public function updateDocument(Request $request, $id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'document' => 'required|string',
            'uploaded' => 'required|boolean',
            'verified' => 'boolean',
            'path' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $studentDetail->updateDocumentStatus(
                $request->document,
                $request->uploaded,
                $request->path,
                $request->verified ?? false
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document status updated successfully.',
                'completion_percentage' => $studentDetail->documents_completion_percentage
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign industrial attachment to a student
     */
    public function assignAttachment(Request $request, $id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'company' => 'required|string|max:255',
            'supervisor' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'nullable|email',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $studentDetail->recordIndustrialAttachment(
                $request->company,
                $request->supervisor,
                $request->phone,
                $request->email,
                $request->start_date,
                $request->end_date,
                $request->address
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Industrial attachment assigned successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign industrial attachment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete industrial attachment for a student
     */
    public function completeAttachment($id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        DB::beginTransaction();
        try {
            $studentDetail->completeIndustrialAttachment();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Industrial attachment marked as completed.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete industrial attachment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Issue workshop equipment to a student
     */
    public function issueWorkshopEquipment(Request $request, $id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'tool_kit' => 'required|string|max:255',
            'protective_clothing' => 'required|string|max:255',
            'clothing_size' => 'nullable|string|max:10',
            'safety_date' => 'nullable|date',
            'safety_certificate' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $studentDetail->issueWorkshopEquipment(
                $request->tool_kit,
                $request->protective_clothing,
                $request->clothing_size
            );

            // Update safety training date if provided
            if ($request->filled('safety_date')) {
                $studentDetail->workshop_safety_training_date = $request->safety_date;
            }

            // Update safety certificate if provided
            if ($request->filled('safety_certificate') && $request->safety_certificate) {
                $studentDetail->updateDocumentStatus('workshop_safety_certificate', true);
            }

            $studentDetail->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workshop equipment issued successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to issue workshop equipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return workshop equipment from a student
     */
    public function returnToolKit(Request $request, $id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'return_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $studentDetail->returnToolKit($request->return_date);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tool kit returned successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to return tool kit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark student as alumni
     */
    public function markAsAlumni(Request $request, $id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'graduation_year' => 'required|integer|min:2000|max:' . date('Y'),
            'employment_status' => 'nullable|string|max:50',
            'employer' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:100',
            'starting_salary' => 'nullable|numeric|min:0',
            'alumni_membership' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $studentDetail->markAsAlumni($request->graduation_year);

            // Update employment information if provided
            if ($request->filled('employment_status') && $request->employment_status === 'employed') {
                $studentDetail->updateEmploymentAfterGraduation(
                    $request->employer,
                    $request->job_title,
                    $request->starting_salary
                );
            }

            // Update alumni membership
            if ($request->filled('alumni_membership') && !$request->alumni_membership) {
                $studentDetail->is_alumni = false;
                $studentDetail->alumni_membership_date = null;
            }

            $studentDetail->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student marked as alumni successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as alumni: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add extracurricular activity
     */
    public function addExtracurricular(Request $request, $id)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:hobby,sport,club,leadership,award,volunteer',
            'activity' => 'required|string|max:255',
            'position' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:2000|max:' . date('Y'),
            'achievements' => 'nullable|string',
            'certificate' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $extracurriculars = $studentDetail->extracurricular_activities ?? [];
            $extracurriculars[] = [
                'type' => $request->type,
                'activity' => $request->activity,
                'position' => $request->position,
                'year' => $request->year,
                'achievements' => $request->achievements,
                'certificate' => $request->certificate,
                'added_by' => auth()->id(),
                'added_at' => now(),
            ];

            $studentDetail->extracurricular_activities = $extracurriculars;
            $studentDetail->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Extracurricular activity added successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add extracurricular activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update extracurricular activity
     */
    public function updateExtracurricular(Request $request, $id, $activityIndex)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:hobby,sport,club,leadership,award,volunteer',
            'activity' => 'required|string|max:255',
            'position' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:2000|max:' . date('Y'),
            'achievements' => 'nullable|string',
            'certificate' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $extracurriculars = $studentDetail->extracurricular_activities ?? [];

            if (isset($extracurriculars[$activityIndex])) {
                $extracurriculars[$activityIndex] = [
                    'type' => $request->type,
                    'activity' => $request->activity,
                    'position' => $request->position,
                    'year' => $request->year,
                    'achievements' => $request->achievements,
                    'certificate' => $request->certificate,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ];

                $studentDetail->extracurricular_activities = $extracurriculars;
                $studentDetail->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Extracurricular activity updated successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity not found.'
                ], 404);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update extracurricular activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove extracurricular activity
     */
    public function removeExtracurricular($id, $activityIndex)
    {
        $studentDetail = StudentDetail::where('student_id', $id)->firstOrFail();

        DB::beginTransaction();
        try {
            $extracurriculars = $studentDetail->extracurricular_activities ?? [];

            if (isset($extracurriculars[$activityIndex])) {
                array_splice($extracurriculars, $activityIndex, 1);
                $studentDetail->extracurricular_activities = $extracurriculars;
                $studentDetail->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Extracurricular activity removed successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity not found.'
                ], 404);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove extracurricular activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import student details from Excel/CSV
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,xlsx,xls|max:5120',
            'import_type' => 'required|in:new,update,replace',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $importType = $request->import_type;

            // Process the import file
            // This would typically use Laravel Excel package
            // For now, we'll return a placeholder response

            $importedCount = 0;
            $updatedCount = 0;
            $errors = [];

            // Simulate import processing
            DB::beginTransaction();

            // Your actual import logic would go here
            // Example:
            // $import = new StudentDetailsImport($importType);
            // $import->import($file);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully.',
                'data' => [
                    'imported' => $importedCount,
                    'updated' => $updatedCount,
                    'errors' => $errors,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export student details
     */
    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:csv,excel,pdf',
            'fields' => 'nullable|array',
            'student_ids' => 'nullable|string', // comma-separated IDs
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $query = User::where('role', 'student')
                ->with(['details'])
                ->orderBy('created_at', 'desc');

            // Filter by student IDs if provided
            if ($request->filled('student_ids')) {
                $ids = explode(',', $request->student_ids);
                $query->whereIn('id', $ids);
            }

            // Apply filters if provided
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'complete':
                        $query->whereHas('details', function ($q) {
                            $q->where('documents_completion_percentage', 100);
                        });
                        break;
                    case 'incomplete':
                        $query->whereHas('details', function ($q) {
                            $q->where('documents_completion_percentage', '<', 100);
                        });
                        break;
                    case 'alumni':
                        $query->whereHas('details', function ($q) {
                            $q->where('is_alumni', true);
                        });
                        break;
                }
            }

            $students = $query->get();

            // Prepare export data
            $exportData = [];

            foreach ($students as $student) {
                $details = $student->details;

                $row = [
                    'Student ID' => $student->student_id,
                    'Name' => $student->name,
                    'Email' => $student->email,
                    'Phone' => $student->phone,
                    'Date of Birth' => $student->date_of_birth,
                    'Gender' => $student->gender,
                    'National ID' => $details->national_id_number ?? '',
                    'Personal Phone' => $details->personal_phone ?? '',
                    'Personal Email' => $details->personal_email ?? '',
                    'Sponsorship Type' => $details->sponsorship_type ?? '',
                    'Sponsor Name' => $details->sponsor_name ?? '',
                    'Secondary School' => $details->secondary_school_name ?? '',
                    'KCSE Grade' => $details->kcse_mean_grade ?? '',
                    'Employment Status' => $details->is_employed ? 'Employed' : 'Not Employed',
                    'Employer Name' => $details->employer_name ?? '',
                    'Alumni Status' => $details->is_alumni ? 'Yes' : 'No',
                    'Documents Completion' => $details->documents_completion_percentage ?? 0,
                    'Attachment Status' => $details->industrial_attachment_status ?? 'None',
                    'Created At' => $student->created_at,
                    'Last Updated' => $details->last_updated_at ?? '',
                ];

                $exportData[] = $row;
            }

            // Generate filename
            $filename = 'student_details_export_' . date('Y-m-d_H-i-s') . '.' . $request->format;

            // Export based on format
            switch ($request->format) {
                case 'csv':
                    return $this->exportToCsv($exportData, $filename);
                case 'excel':
                    return $this->exportToExcel($exportData, $filename);
                case 'pdf':
                    return $this->exportToPdf($exportData, $filename);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // Add headers
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
            }

            // Add data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel (placeholder)
     */
    private function exportToExcel($data, $filename)
    {
        // This would typically use Laravel Excel package
        // For now, we'll export as CSV
        return $this->exportToCsv($data, str_replace('.xlsx', '.csv', $filename));
    }

    /**
     * Export to PDF (placeholder)
     */
    private function exportToPdf($data, $filename)
    {
        // This would typically use DomPDF or similar package
        // For now, we'll export as CSV
        return $this->exportToCsv($data, str_replace('.pdf', '.csv', $filename));
    }

    /**
     * Get student statistics
     */
    public function statistics()
    {
        try {
            $totalStudents = User::where('role', 'student')->count();

            $genderStats = User::where('role', 'student')
                ->select('gender', DB::raw('count(*) as count'))
                ->groupBy('gender')
                ->get()
                ->pluck('count', 'gender');

            $sponsorshipStats = StudentDetail::select('sponsorship_type', DB::raw('count(*) as count'))
                ->groupBy('sponsorship_type')
                ->get()
                ->pluck('count', 'sponsorship_type');

            $documentStats = [
                'complete' => StudentDetail::hasCompleteDocuments()->count(),
                'incomplete' => StudentDetail::whereHas('user', function($q) {
                    $q->where('role', 'student');
                })->whereRaw('JSON_LENGTH(documents_uploaded) != JSON_LENGTH(JSON_REMOVE(documents_uploaded, \'$.*.?(@.uploaded = false)\'))')
                ->count(),
            ];

            $attachmentStats = [
                'ongoing' => StudentDetail::activeIndustrialAttachment()->count(),
                'completed' => StudentDetail::where('industrial_attachment_status', 'completed')->count(),
                'none' => StudentDetail::whereNull('industrial_attachment_status')
                    ->orWhere('industrial_attachment_status', 'pending')
                    ->count(),
            ];

            $alumniStats = [
                'alumni' => StudentDetail::alumni()->count(),
                'employed_alumni' => StudentDetail::alumni()->where('is_employed', true)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'total_students' => $totalStudents,
                    'gender_distribution' => $genderStats,
                    'sponsorship_distribution' => $sponsorshipStats,
                    'document_status' => $documentStats,
                    'attachment_status' => $attachmentStats,
                    'alumni_stats' => $alumniStats,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update students
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'action' => 'required|in:update_sponsorship,update_documents,assign_attachment,mark_alumni,send_notification',
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $studentIds = $request->student_ids;
            $action = $request->action;
            $data = $request->data;

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($studentIds as $studentId) {
                try {
                    switch ($action) {
                        case 'update_sponsorship':
                            $this->handleBulkSponsorshipUpdate($studentId, $data);
                            break;
                        case 'update_documents':
                            $this->handleBulkDocumentUpdate($studentId, $data);
                            break;
                        case 'assign_attachment':
                            $this->handleBulkAttachmentAssign($studentId, $data);
                            break;
                        case 'mark_alumni':
                            $this->handleBulkMarkAlumni($studentId, $data);
                            break;
                        case 'send_notification':
                            $this->handleBulkNotification($studentId, $data);
                            break;
                    }
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'student_id' => $studentId,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk update completed.',
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle bulk sponsorship update
     */
    private function handleBulkSponsorshipUpdate($studentId, $data)
    {
        $studentDetail = StudentDetail::where('student_id', $studentId)->first();

        if (!$studentDetail) {
            throw new \Exception('Student details not found');
        }

        $studentDetail->update($data);
        $studentDetail->updated_by = auth()->id();
        $studentDetail->last_updated_at = now();
        $studentDetail->save();
    }

    /**
     * Handle bulk document update
     */
    private function handleBulkDocumentUpdate($studentId, $data)
    {
        $studentDetail = StudentDetail::where('student_id', $studentId)->first();

        if (!$studentDetail) {
            throw new \Exception('Student details not found');
        }

        foreach ($data as $document => $status) {
            $studentDetail->updateDocumentStatus(
                $document,
                $status['uploaded'] ?? false,
                $status['path'] ?? null,
                $status['verified'] ?? false
            );
        }
    }

    /**
     * Handle bulk attachment assignment
     */
    private function handleBulkAttachmentAssign($studentId, $data)
    {
        $studentDetail = StudentDetail::where('student_id', $studentId)->first();

        if (!$studentDetail) {
            throw new \Exception('Student details not found');
        }

        $studentDetail->recordIndustrialAttachment(
            $data['company'] ?? '',
            $data['supervisor'] ?? '',
            $data['phone'] ?? '',
            $data['email'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['address'] ?? null
        );
    }

    /**
     * Handle bulk mark as alumni
     */
    private function handleBulkMarkAlumni($studentId, $data)
    {
        $studentDetail = StudentDetail::where('student_id', $studentId)->first();

        if (!$studentDetail) {
            throw new \Exception('Student details not found');
        }

        $studentDetail->markAsAlumni($data['graduation_year'] ?? date('Y'));

        if (isset($data['employment_status']) && $data['employment_status'] === 'employed') {
            $studentDetail->updateEmploymentAfterGraduation(
                $data['employer'] ?? null,
                $data['job_title'] ?? null,
                $data['starting_salary'] ?? null
            );
        }
    }

    /**
     * Handle bulk notification
     */
    private function handleBulkNotification($studentId, $data)
    {
        $student = User::find($studentId);

        if (!$student) {
            throw new \Exception('Student not found');
        }

        // Send notification logic here
        // This would typically use Laravel Notification or Email system
        // For now, just log it
        \Log::info('Bulk notification sent to student', [
            'student_id' => $studentId,
            'notification_type' => $data['type'] ?? 'general',
            'subject' => $data['subject'] ?? '',
            'message' => $data['message'] ?? '',
        ]);
    }

    /**
     * Get student dashboard data
     */
    public function dashboardData()
    {
        try {
            // Recent students with incomplete profiles
            $recentIncomplete = User::where('role', 'student')
                ->whereHas('details', function($q) {
                    $q->whereRaw('JSON_LENGTH(documents_uploaded) != JSON_LENGTH(JSON_REMOVE(documents_uploaded, \'$.*.?(@.uploaded = false)\'))');
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'email', 'student_id', 'created_at']);

            // Upcoming attachment endings
            $upcomingAttachments = StudentDetail::where('industrial_attachment_status', 'ongoing')
                ->where('industrial_attachment_end_date', '>=', now())
                ->where('industrial_attachment_end_date', '<=', now()->addDays(30))
                ->with('user:id,name,email')
                ->orderBy('industrial_attachment_end_date')
                ->limit(10)
                ->get();

            // Document statistics
            $documentStats = DB::table('student_details')
                ->select(DB::raw('
                    COUNT(*) as total,
                    SUM(CASE WHEN JSON_LENGTH(documents_uploaded) = JSON_LENGTH(JSON_REMOVE(documents_uploaded, \'$.*.?(@.uploaded = false)\')) THEN 1 ELSE 0 END) as complete,
                    SUM(CASE WHEN JSON_LENGTH(documents_uploaded) != JSON_LENGTH(JSON_REMOVE(documents_uploaded, \'$.*.?(@.uploaded = false)\')) THEN 1 ELSE 0 END) as incomplete
                '))
                ->first();

            // Alumni by year
            $alumniByYear = StudentDetail::where('is_alumni', true)
                ->select('graduation_year', DB::raw('COUNT(*) as count'))
                ->groupBy('graduation_year')
                ->orderBy('graduation_year', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'recent_incomplete' => $recentIncomplete,
                    'upcoming_attachments' => $upcomingAttachments,
                    'document_stats' => $documentStats,
                    'alumni_by_year' => $alumniByYear,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }
}
