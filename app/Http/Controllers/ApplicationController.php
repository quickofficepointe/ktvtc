<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationPayment;
use App\Models\Course;
use App\Models\Campus;
use App\Models\CourseIntakes;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Services\SmsService;

class ApplicationController extends Controller
{
    protected $smsService;

    public function __construct()
    {
        $this->smsService = new SmsService();
    }

    /**
     * Display application form with course slug support
     * SHOW ALL INTAKES (including past months)
     */
    public function index(Request $request, $course_slug = null)
    {
        $course = null;
        $intakes = collect();
        $campuses = Campus::active()->orderBy('name', 'asc')->get();

        // Priority: Route parameter (slug) > Query parameter (slug) > Query parameter (id)
        if ($course_slug) {
            $course = Course::with(['department', 'intakes' => function($query) {
                // REMOVED available() scope - show ALL intakes including past
                $query->orderBy('year', 'desc')
                      ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June',
                                           'July', 'August', 'September', 'October', 'November', 'December')");
            }])->where('slug', $course_slug)
               ->where('is_active', true)
               ->first();
        } elseif ($request->has('course_slug')) {
            $course = Course::with(['department', 'intakes' => function($query) {
                // REMOVED available() scope - show ALL intakes including past
                $query->orderBy('year', 'desc')
                      ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June',
                                           'July', 'August', 'September', 'October', 'November', 'December')");
            }])->where('slug', $request->course_slug)
               ->where('is_active', true)
               ->first();
        } elseif ($request->has('course_id')) {
            // Keep backward compatibility with ID
            $course = Course::with(['department', 'intakes' => function($query) {
                // REMOVED available() scope - show ALL intakes including past
                $query->orderBy('year', 'desc')
                      ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June',
                                           'July', 'August', 'September', 'October', 'November', 'December')");
            }])->find($request->course_id);
        }

        if ($course) {
            $intakes = $course->intakes;
        }

        $courses = Course::active()->orderBy('name', 'asc')->get();

        return view('ktvtc.website.application.index', compact('course', 'courses', 'intakes', 'campuses'));
    }

    /**
     * Store a new application with campus support
     * Modified to allow past intakes
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Campus Information
            'campus_id' => 'required|exists:campuses,id',

            // Course Information
            'course_id' => 'required|exists:courses,id',
            'intake_period' => 'required|string',
            'study_mode' => 'required|in:full_time,part_time,evening,weekend,online',

            // ID Type Information
            'id_type' => 'required|in:id,birth_certificate',
            'id_number' => 'required|string|max:20',

            // Personal Information
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
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

            // Documents - updated to accept images for ID and certificates
            'id_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'education_certificates' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'passport_photo' => 'required|file|mimes:jpg,jpeg,png|max:1024',

            // Emergency Contact
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:255',

            // Special Needs
            'special_needs' => 'nullable|string|max:500',
        ]);

        // Validate that the intake exists for this course (don't check availability)
        $selectedIntake = $validated['intake_period'];

        // Parse the selected intake period (e.g., "January 2024")
        $intakeParts = explode(' ', $selectedIntake);
        $month = $intakeParts[0] ?? null;
        $year = $intakeParts[1] ?? null;

        if (!$month || !$year) {
            return back()->with('error', 'Invalid intake period format.')
                       ->withInput();
        }

        // Check if the intake exists in the database (don't check availability)
        $intake = CourseIntakes::where('course_id', $validated['course_id'])
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$intake) {
            return back()->with('error', 'The selected intake period is not valid. Please select a valid intake period.')
                       ->withInput();
        }

        $course = Course::find($validated['course_id']);

        if (!$course) {
            return back()->with('error', 'Selected course not found.')
                       ->withInput();
        }

        try {
            // Generate unique application number
            $applicationNumber = 'APP-' . date('Y') . '-' . Str::random(6) . '-' . Str::random(4);

            // Handle file uploads
            $idDocumentPath = $request->file('id_document')->store('applications/documents', 'public');
            $educationCertificatesPath = $request->file('education_certificates')->store('applications/certificates', 'public');
            $passportPhotoPath = $request->file('passport_photo')->store('applications/photos', 'public');

            // Get campus name
            $campus = Campus::find($validated['campus_id']);
            $campusName = $campus ? $campus->name : 'Unknown Campus';

            // Create application
            $application = Application::create([
                // Campus Information
                'campus_id' => $validated['campus_id'],

                // Course Information
                'course_id' => $validated['course_id'],
                'intake_period' => $validated['intake_period'],
                'study_mode' => $validated['study_mode'],

                // ID Type Information
                'id_type' => $validated['id_type'],
                'id_number' => $validated['id_number'],

                // Personal Information
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
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
                'status' => 'pending',
            ]);

            // Send SMS to admin about new application
            $this->sendAdminApplicationNotification($application, $course, $campusName);

            // Send confirmation SMS to applicant about application submission
            $this->sendApplicantConfirmation($application, $course);

            // Redirect to payment page instead of success page
            return redirect()->route('application.payment.form', $application->id)
                           ->with('success', 'Application submitted successfully! Please complete the payment to finalize your application.');

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
     * Send SMS notification to admin about new application
     */
    private function sendAdminApplicationNotification(Application $application, Course $course, string $campusName)
    {
        try {
            $message = $this->generateAdminApplicationNotificationMessage($application, $course, $campusName);

            // Send to all admin phones
            $adminPhones = $this->smsService->getAdminPhones();
            $results = [];

            foreach ($adminPhones as $adminPhone) {
                $result = $this->smsService->sendSingleSms($adminPhone, $message);
                $results[$adminPhone] = $result;

                Log::info('Admin application notification sent', [
                    'phone' => $adminPhone,
                    'success' => $result['success'] ?? false,
                    'application_id' => $application->id,
                    'application_number' => $application->application_number
                ]);
            }

            return $results;

        } catch (\Exception $e) {
            Log::error('Failed to send admin application notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate admin notification message for new application
     */
 private function generateAdminApplicationNotificationMessage(Application $application, Course $course, string $campusName): string
{
    $fullName = $application->first_name . ' ' . $application->last_name;
    $applicationTime = $application->submitted_at->format('d/m/Y H:i');

    $message = "New application received from {$fullName} ({$application->phone}) for {$course->name} at {$campusName} campus. Application Number: {$application->application_number}. Intake: {$application->intake_period}. Mode: " . ucfirst(str_replace('_', ' ', $application->study_mode)) . ". Applied on {$applicationTime}. Registration fee of KES 500 pending. Please review in admin panel.";

    return $message;
}

    /**
     * Send confirmation SMS to applicant
     */
    private function sendApplicantConfirmation(Application $application, Course $course)
    {
        try {
            $message = $this->generateApplicantConfirmationMessage($application, $course);
            $result = $this->smsService->sendSingleSms($application->phone, $message);

            Log::info('Applicant confirmation SMS sent', [
                'phone' => $application->phone,
                'success' => $result['success'] ?? false,
                'application_id' => $application->id,
                'application_number' => $application->application_number
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to send applicant confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate applicant confirmation message
     */
private function generateApplicantConfirmationMessage(Application $application, Course $course): string
{
    $fullName = $application->first_name . ' ' . $application->last_name;
    $applicationTime = $application->submitted_at->format('d/m/Y H:i');

    $message = "Dear {$fullName}, thank you for applying to Kenswed Technical College. Your application for {$course->name} ({$application->intake_period} intake) has been received. Application Number: {$application->application_number}. To complete your application, please pay the registration fee of KES 500 via: " . route('application.payment.form', $application->id) . " Regards, Kenswed Technical College.";

    return $message;
}

    /**
     * Send payment confirmation SMS
     */
    public function sendPaymentConfirmation(Application $application, ApplicationPayment $payment)
    {
        try {
            $message = $this->generatePaymentConfirmationMessage($application, $payment);
            $result = $this->smsService->sendSingleSms($application->phone, $message);

            Log::info('Payment confirmation SMS sent', [
                'phone' => $application->phone,
                'success' => $result['success'] ?? false,
                'application_id' => $application->id,
                'receipt' => $payment->mpesa_receipt_number
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate payment confirmation message
     */
  private function generatePaymentConfirmationMessage(Application $application, ApplicationPayment $payment): string
{
    $fullName = $application->first_name . ' ' . $application->last_name;

    $message = "Dear {$fullName}, payment of KES " . number_format($payment->amount, 2) . " for application {$application->application_number} has been confirmed. M-Pesa Receipt: {$payment->mpesa_receipt_number}. Your application is now complete and under review. We will contact you soon. Thank you for choosing Kenswed Technical College.";

    return $message;
}

    /**
     * Display application success page (after payment)
     */
    public function success($id)
    {
        $application = Application::with(['course', 'campus', 'payments' => function($query) {
            $query->where('status', 'completed')->latest();
        }])->findOrFail($id);

        return view('ktvtc.website.application.success', compact('application'));
    }

    /**
     * Get course details for AJAX - SHOW ALL INTAKES including past
     */
    public function getCourseDetails($id)
    {
        $course = Course::with(['department', 'intakes' => function($query) {
            // Show ALL intakes including past
            $query->orderBy('year', 'desc')
                  ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June',
                                       'July', 'August', 'September', 'October', 'November', 'December')");
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'course' => $course,
            'intakes' => $course->intakes
        ]);
    }

    /**
     * Display all applications (for admin) with campus support
     */
    public function list(Request $request)
    {
        $query = Application::with(['course', 'campus', 'payments' => function($query) {
            $query->where('status', 'completed');
        }])->latest();

        // Filter by campus
        if ($request->has('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        // Filter by course
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            if ($request->payment_status === 'paid') {
                $query->whereHas('payments', function($q) {
                    $q->where('status', 'completed');
                });
            } elseif ($request->payment_status === 'pending') {
                $query->whereDoesntHave('payments', function($q) {
                    $q->where('status', 'completed');
                });
            }
        }

        $applications = $query->paginate(20);
        $courses = Course::active()->orderBy('name', 'asc')->get();
        $campuses = Campus::active()->orderBy('name', 'asc')->get();

        return view('ktvtc.admin.applications.index', compact('courses', 'campuses', 'applications'));
    }

    /**
     * Show specific application (for admin) with campus and payment
     */
    public function show($id)
    {
        $application = Application::with(['course', 'campus', 'payments' => function($query) {
            $query->latest();
        }])->findOrFail($id);

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
        $oldStatus = $application->status;
        $application->update([
            'status' => $request->status
        ]);

        // Send status update SMS to applicant if status changed
        if ($oldStatus !== $request->status) {
            $this->sendStatusUpdateNotification($application);
        }

        return back()->with('success', 'Application status updated successfully.');
    }

    /**
     * Send status update SMS to applicant
     */
    private function sendStatusUpdateNotification(Application $application)
    {
        try {
            $message = $this->generateStatusUpdateMessage($application);
            $result = $this->smsService->sendSingleSms($application->phone, $message);

            log::info('Status update SMS sent', [
                'phone' => $application->phone,
                'status' => $application->status,
                'success' => $result['success'] ?? false,
                'application_id' => $application->id
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to send status update: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate status update message
     */
private function generateStatusUpdateMessage(Application $application): string
{
    $fullName = $application->first_name . ' ' . $application->last_name;
    $courseName = $application->course ? $application->course->name : 'your course';

    if ($application->status === 'accepted') {
        $message = "Dear {$fullName}, congratulations! Your application for {$courseName} (App No: {$application->application_number}) has been accepted. Admission details will be sent to you shortly. Welcome to Kenswed Technical College.";
    } elseif ($application->status === 'rejected') {
        $message = "Dear {$fullName}, thank you for applying to Kenswed Technical College. We regret to inform you that your application for {$courseName} (App No: {$application->application_number}) was not successful. We wish you all the best in your future endeavors.";
    } elseif ($application->status === 'waiting_list') {
        $message = "Dear {$fullName}, your application for {$courseName} (App No: {$application->application_number}) has been placed on our waiting list. We will notify you immediately if a space becomes available. Thank you for your patience.";
    } else {
        $message = "Dear {$fullName}, your application for {$courseName} (App No: {$application->application_number}) is now {$application->status}. We will update you once a decision is made. Thank you for applying to Kenswed Technical College.";
    }

    return $message;
}
}
