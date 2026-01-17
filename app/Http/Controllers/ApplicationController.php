<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Course;
use App\Models\CourseIntakes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    /**
     * Display application form
     */
  public function index(Request $request)
{
    $courseId = $request->query('course_id');
    $course = null;
    $intakes = collect();

    if ($courseId) {
        $course = Course::with(['department', 'intakes' => function($query) {
            $query->active() // Uses the scopeActive from CourseIntakes model
                  ->upcoming() // Uses the scopeUpcoming from CourseIntakes model
                  ->orderByIntake(); // Uses the scopeOrderByIntake from CourseIntakes model
        }])->find($courseId);

        if ($course) {
            $intakes = $course->intakes;
        }
    }

    $courses = Course::active()->orderBy('name', 'asc')->get();

    return view('ktvtc.website.application.index', compact('course', 'courses', 'intakes'));
}

    /**
     * Store a new application
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Course Information
            'course_id' => 'required|exists:courses,id',
            'intake_period' => 'required|string',
            'study_mode' => 'required|in:full_time,part_time,evening,weekend',

            // Personal Information
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'id_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',

            // Contact Information
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',

            // Education Background
            'education_level' => 'required|string|max:255',
            'school_name' => 'required|string|max:255',
            'graduation_year' => 'required|integer|min:1950|max:' . date('Y'),
            'mean_grade' => 'required|string|max:10',
            'application_type' => 'required|in:new,transfer,continuing',

            // Documents
            'id_document' => 'required|file|mimes:pdf|max:2048',
            'education_certificates' => 'required|file|mimes:pdf|max:5120',
            'passport_photo' => 'required|file|mimes:jpg,jpeg,png|max:1024',

            // Emergency Contact
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:255',

            // Special Needs
            'special_needs' => 'nullable|string|max:500',
        ]);

        try {
            // Generate unique application number
            $applicationNumber = 'APP-' . date('Y') . '-' . Str::random(6) . '-' . Str::random(4);

            // Handle file uploads
            $idDocumentPath = $request->file('id_document')->store('applications/documents', 'public');
            $educationCertificatesPath = $request->file('education_certificates')->store('applications/certificates', 'public');
            $passportPhotoPath = $request->file('passport_photo')->store('applications/photos', 'public');

            // Create application
            $application = Application::create([
                // Course Information
                'course_id' => $validated['course_id'],
                'intake_period' => $validated['intake_period'],
                'study_mode' => $validated['study_mode'],

                // Personal Information
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'id_number' => $validated['id_number'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],

                // Contact Information
                'address' => $validated['address'],
                'city' => $validated['city'],
                'county' => $validated['county'],
                'postal_code' => $validated['postal_code'],

                // Education Background
                'education_level' => $validated['education_level'],
                'school_name' => $validated['school_name'],
                'graduation_year' => $validated['graduation_year'],
                'mean_grade' => $validated['mean_grade'],
                'application_type' => $validated['application_type'],

                // Documents
                'id_document' => $idDocumentPath,
                'education_certificates' => $educationCertificatesPath,
                'passport_photo' => $passportPhotoPath,

                // Emergency Contact
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
                'emergency_contact_relationship' => $validated['emergency_contact_relationship'],

                // Special Needs
                'special_needs' => $validated['special_needs'],

                // Application Tracking
                'application_number' => $applicationNumber,
                'submitted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Send confirmation email (you can implement this later)
            // $this->sendConfirmationEmail($application);

            return redirect()->route('application.success', $application->id)
                           ->with('success', 'Application submitted successfully!');

        } catch (\Exception $e) {
            // Clean up uploaded files if application creation fails
            if (isset($idDocumentPath) && Storage::disk('public')->exists($idDocumentPath)) {
                Storage::disk('public')->delete($idDocumentPath);
            }
            if (isset($educationCertificatesPath) && Storage::disk('public')->exists($educationCertificatesPath)) {
                Storage::disk('public')->delete($educationCertificatesPath);
            }
            if (isset($passportPhotoPath) && Storage::disk('public')->exists($passportPhotoPath)) {
                Storage::disk('public')->delete($passportPhotoPath);
            }

            return back()->with('error', 'There was an error submitting your application. Please try again.')
                       ->withInput();
        }
    }

    /**
     * Display application success page
     */
    public function success($id)
    {
        $application = Application::with('course')->findOrFail($id);
        return view('ktvtc.website.application.success', compact('application'));
    }

    /**
     * Get course details for AJAX
     */
    public function getCourseDetails($id)
    {
        $course = Course::with(['department', 'intakes' => function($query) {
            $query->where('is_active', true)
                  ->where('application_deadline', '>=', now())
                  ->orderBy('year', 'desc')
                  ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')");
        }])->findOrFail($id);

        return response()->json([
            'course' => $course,
            'intakes' => $course->intakes
        ]);
    }

    /**
     * Display all applications (for admin)
     */
    public function list(Request $request)
    {
        $applications = Application::with('course')
            ->latest()
            ->paginate(20);
$courses= Course::active()->orderBy('name', 'asc')->get();
        return view('ktvtc.admin.applications.index', compact( 'courses','applications'));
    }

    /**
     * Show specific application (for admin)
     */
    public function show($id)
    {
        $application = Application::with('course')->findOrFail($id);
        return view('ktvtc.admin.applications.show', compact('application'));
    }

    /**
     * Update application status (for admin)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,under_review,accepted,rejected,waiting_list'
        ]);

        $application = Application::findOrFail($id);
        $application->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Application status updated successfully.');
    }
}
