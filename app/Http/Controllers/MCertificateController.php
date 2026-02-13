<?php

namespace App\Http\Controllers;

use App\Models\MCertificate;
use App\Models\MCertificateTemplate;
use App\Models\MStudent;
use App\Models\MEnrollment;
use App\Models\MCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class MCertificateController extends Controller
{
    public function index()
    {
        return view('ktvtc.mschool.certificates.certificate');
    }

    public function getAssignableStudents()
    {
        try {
            $students = MStudent::with(['enrollments' => function ($q) {
                $q->where('status', 'completed')->with('course');
            }])
            ->where('is_active', true)
            ->get()
            ->map(function ($student) {
                $enrollments = $student->enrollments->filter(function ($enrollment) {
                    return !MCertificate::where('enrollment_id', $enrollment->enrollment_id)->exists();
                });

                if ($enrollments->isEmpty()) return null;

                return [
                    'student_id' => $student->student_id,
                    'full_name' => $student->first_name . ' ' . $student->last_name,
                    'student_code' => $student->student_code ?? 'N/A',
                    'enrollments' => $enrollments->map(function ($enrollment) {
                        return [
                            'enrollment_id' => $enrollment->enrollment_id,
                            'course_id' => $enrollment->course_id,
                            'course_name' => $enrollment->course->course_name ?? 'Unknown Course',
                            'completion_date' => $enrollment->completion_date ? $enrollment->completion_date->format('Y-m-d') : null,
                        ];
                    }),
                ];
            })->filter()->values();

            return response()->json($students);

        } catch (\Exception $e) {
            \Log::error('Error loading assignable students: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load students'], 500);
        }
    }
public function getIssuedCertificates()
{
    try {
        $certificates = \App\Models\MCertificate::with(['student', 'course', 'template'])
            ->orderBy('issue_date', 'desc')
            ->get()
            ->map(function ($cert) {
                return [
                    'certificate_id' => $cert->certificate_id,
                    'certificate_number' => $cert->certificate_number,
                    'student_name' => $cert->student->first_name . ' ' . $cert->student->last_name,
                    'course_name' => $cert->course->course_name,
                    'certificate_type' => $cert->template->template_name ?? 'N/A',
                    'issue_date' => $cert->issue_date ? $cert->issue_date->format('Y-m-d') : null,
                    'pdf_url' => $cert->generated_pdf_path ? asset('storage/' . $cert->generated_pdf_path) : null,
                ];
            });

        return response()->json(['data' => $certificates]);
    } catch (\Exception $e) {
        \Log::error('Error fetching issued certificates: '.$e->getMessage());
        return response()->json(['error' => 'Failed to fetch issued certificates', 'message' => $e->getMessage()], 500);
    }
}

    public function assignCertificates(Request $request)
    {
        $request->validate([
            'assignments' => 'required|array',
            'assignments.*.student_id' => 'required|exists:m_students,student_id',
            'assignments.*.enrollment_id' => 'required|exists:m_enrollments,enrollment_id',
            'assignments.*.course_id' => 'required|exists:m_courses,course_id',
            'assignments.*.template_types' => 'required|array',
            'assignments.*.template_types.*' => 'in:completion,participation,achievement,recognition',
        ]);

        DB::beginTransaction();
        $issued = [];
        $errors = [];

        foreach ($request->assignments as $assignment) {
            $student = MStudent::find($assignment['student_id']);
            $course = MCourse::find($assignment['course_id']);
            $enrollment = MEnrollment::find($assignment['enrollment_id']);

            foreach ($assignment['template_types'] as $templateType) {
                try {
                    // Fetch template by type
                    $template = MCertificateTemplate::where('template_type', $templateType)->first();
                    if (!$template) {
                        $errors[] = "Template not found: $templateType";
                        continue;
                    }

                    // Skip if certificate exists
                    $exists = MCertificate::where([
                        'student_id' => $student->student_id,
                        'course_id' => $course->course_id,
                        'template_id' => $template->template_id
                    ])->exists();

                    if ($exists) {
                        $errors[] = "Certificate already exists for {$student->first_name} ({$templateType})";
                        continue;
                    }

                    // Generate certificate number
                    $certNumber = 'CERT-' . strtoupper(substr($templateType, 0, 4)) . '-' . date('Ymd') . '-' . rand(1000, 9999);

                    // Create certificate
                    $certificate = MCertificate::create([
                        'template_id' => $template->template_id,
                        'student_id' => $student->student_id,
                        'enrollment_id' => $enrollment->enrollment_id,
                        'course_id' => $course->course_id,
                        'certificate_number' => $certNumber,
                        'issue_date' => now(),
                        'created_by' => auth()->id(),
                    ]);

                    // Ensure folder
                    Storage::disk('public')->makeDirectory('certificates');

                    // Generate PDF
                    $pdfPath = $this->generateCertificatePDF($certificate, $student, $course, $template);
                    $certificate->generated_pdf_path = $pdfPath;
                    $certificate->save();

                    $issued[] = $certificate;

                } catch (\Exception $e) {
                    $errors[] = "Error for {$student->first_name} ({$templateType}): " . $e->getMessage();
                    \Log::error("Certificate generation failed for student {$student->student_id}: " . $e->getMessage());
                }
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'issued_count' => count($issued),
            'errors' => $errors,
            'message' => count($issued) . ' certificates issued successfully'
        ]);
    }

    private function generateCertificatePDF($certificate, $student, $course, $template)
    {
        $pdf = new Fpdi();
        $pdf->AddPage();

        $templatePath = storage_path('app/public/' . $template->template_file);
        if (!file_exists($templatePath)) {
            throw new \Exception("Template file not found: " . $template->template_file);
        }

        $tplId = $pdf->setSourceFile($templatePath);
        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId, 0, 0, null, null, true);

        // Add student & course
        $pdf->SetFont('Helvetica', '', 16);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY($template->name_x, $template->name_y);
        $pdf->Cell(0, 0, $student->first_name . ' ' . $student->last_name, 0, 0, 'L');

        $pdf->SetXY($template->course_x, $template->course_y);
        $pdf->Cell(0, 0, $course->course_name, 0, 0, 'L');

        // Save PDF
        $filename = 'certificates/' . $certificate->certificate_id . '.pdf';
        $storagePath = storage_path('app/public/' . $filename);
        $pdf->Output($storagePath, 'F');

        return $filename;
    }

    public function preview($id)
    {
        $certificate = MCertificate::with(['student', 'course', 'template'])->findOrFail($id);

        return response()->json([
            'student_name' => $certificate->student->first_name . ' ' . $certificate->student->last_name,
            'course_name' => $certificate->course->course_name,
            'certificate_type' => $certificate->template->template_name,
            'certificate_number' => $certificate->certificate_number,
            'issue_date' => $certificate->issue_date->format('d/m/Y'),
            'pdf_url' => $certificate->generated_pdf_path ? asset('storage/' . $certificate->generated_pdf_path) : null,
            'download_url' => route('certificates.download', $certificate->certificate_id),
        ]);
    }

    public function download($id)
    {
        $certificate = MCertificate::findOrFail($id);
        $pdfPath = $certificate->generated_pdf_path;

        if (!$pdfPath || !Storage::disk('public')->exists($pdfPath)) {
            return back()->with('error', 'Certificate PDF not found.');
        }

        return Storage::disk('public')->download($pdfPath, $certificate->certificate_number . '.pdf');
    }
}
