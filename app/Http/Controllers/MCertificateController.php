<?php

namespace App\Http\Controllers;

use App\Models\MCertificate;
use App\Models\MCertificateTemplate;
use App\Models\MStudent;
use App\Models\MEnrollment;
use App\Models\MCourse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MCertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $certificates = MCertificate::with([
            'template',
            'student',
            'enrollment',
            'course',
            'issuedBy'
        ])->latest()->get();

        $templates = MCertificateTemplate::where('is_active', true)->get();
        $students = MStudent::where('is_active', true)->get();
        $enrollments = MEnrollment::where('is_active', true)->get();
        $courses = MCourse::where('is_active', true)->get();

        return view('ktvtc.mschool.certificates.certificate', compact(
            'certificates',
            'templates',
            'students',
            'enrollments',
            'courses'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:m_certificate_templates,template_id',
            'student_id' => 'required|exists:m_students,student_id',
            'enrollment_id' => 'required|exists:m_enrollments,enrollment_id',
            'course_id' => 'required|exists:m_courses,course_id',
            'certificate_number' => 'required|string|unique:m_certificates,certificate_number',
            'serial_number' => 'nullable|string|unique:m_certificates,serial_number',
            'certificate_data' => 'required|array',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'status' => 'required|in:draft,generated,issued,revoked,expired',
            'is_verified' => 'boolean',
            'verification_url' => 'nullable|url',
            'qr_code_data' => 'nullable|string',
            'issuance_remarks' => 'nullable|string',
            'allow_download' => 'boolean',
            'allow_sharing' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Generate verification URL if not provided
            if (empty($validated['verification_url'])) {
                $validated['verification_url'] = url('/verify-certificate/' . $validated['certificate_number']);
            }

            // Generate QR code data if not provided
            if (empty($validated['qr_code_data']) && !empty($validated['verification_url'])) {
                $validated['qr_code_data'] = $validated['verification_url'];
            }

            // Set generated at timestamp
            $validated['generated_at'] = now();

            // Set issued by if status is issued
            if ($validated['status'] === 'issued') {
                $validated['issued_by'] = auth()->id();
            }

            // Convert certificate data to JSON
            $validated['certificate_data'] = json_encode($validated['certificate_data']);

            // Add created by
            $validated['created_by'] = auth()->id();

            $certificate = MCertificate::create($validated);

            DB::commit();

            return redirect()->route('certificates.index')
                ->with('success', 'Certificate created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create certificate: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MCertificate $certificate)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:m_certificate_templates,template_id',
            'student_id' => 'required|exists:m_students,student_id',
            'enrollment_id' => 'required|exists:m_enrollments,enrollment_id',
            'course_id' => 'required|exists:m_courses,course_id',
            'certificate_number' => 'required|string|unique:m_certificates,certificate_number,' . $certificate->certificate_id . ',certificate_id',
            'serial_number' => 'nullable|string|unique:m_certificates,serial_number,' . $certificate->certificate_id . ',certificate_id',
            'certificate_data' => 'required|array',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'status' => 'required|in:draft,generated,issued,revoked,expired',
            'is_verified' => 'boolean',
            'verification_url' => 'nullable|url',
            'qr_code_data' => 'nullable|string',
            'issuance_remarks' => 'nullable|string',
            'is_revoked' => 'boolean',
            'revoked_date' => 'nullable|date|required_if:is_revoked,true',
            'revocation_reason' => 'nullable|string|required_if:is_revoked,true',
            'allow_download' => 'boolean',
            'allow_sharing' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Handle revocation
            if ($validated['is_revoked'] && !$certificate->is_revoked) {
                $validated['revoked_by'] = auth()->id();
                $validated['revoked_date'] = $validated['revoked_date'] ?? now();
            } elseif (!$validated['is_revoked'] && $certificate->is_revoked) {
                // If un-revoking, clear revocation fields
                $validated['revoked_by'] = null;
                $validated['revoked_date'] = null;
                $validated['revocation_reason'] = null;
            }

            // Handle issuance
            if ($validated['status'] === 'issued' && $certificate->status !== 'issued') {
                $validated['issued_by'] = auth()->id();
            }

            // Convert certificate data to JSON
            $validated['certificate_data'] = json_encode($validated['certificate_data']);

            // Add updated by
            $validated['updated_by'] = auth()->id();

            $certificate->update($validated);

            DB::commit();

            return redirect()->route('certificates.index')
                ->with('success', 'Certificate updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update certificate: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MCertificate $certificate)
    {
        try {
            DB::beginTransaction();

            // Delete generated PDF file if exists
            if ($certificate->generated_pdf_path && Storage::exists('public/certificates/' . $certificate->generated_pdf_path)) {
                Storage::delete('public/certificates/' . $certificate->generated_pdf_path);
            }

            $certificate->delete();

            DB::commit();

            return redirect()->route('certificates.index')
                ->with('success', 'Certificate deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete certificate: ' . $e->getMessage());
        }
    }

    /**
     * Download certificate PDF
     */
    public function download(MCertificate $certificate)
    {
        if (!$certificate->generated_pdf_path || !Storage::exists('public/certificates/' . $certificate->generated_pdf_path)) {
            return redirect()->back()->with('error', 'Certificate file not found.');
        }

        // Increment download count
        $certificate->incrementDownloadCount();

        return Storage::download('public/certificates/' . $certificate->generated_pdf_path,
            $certificate->certificate_number . '.pdf');
    }

    /**
     * Revoke certificate
     */
    public function revoke(Request $request, MCertificate $certificate)
    {
        $validated = $request->validate([
            'revocation_reason' => 'required|string|max:500',
            'revoked_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $certificate->update([
                'is_revoked' => true,
                'status' => 'revoked',
                'revocation_reason' => $validated['revocation_reason'],
                'revoked_date' => $validated['revoked_date'],
                'revoked_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('certificates.index')
                ->with('success', 'Certificate revoked successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to revoke certificate: ' . $e->getMessage());
        }
    }

    /**
     * Restore revoked certificate
     */
    public function restore(MCertificate $certificate)
    {
        try {
            DB::beginTransaction();

            $certificate->update([
                'is_revoked' => false,
                'status' => 'issued',
                'revocation_reason' => null,
                'revoked_date' => null,
                'revoked_by' => null,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('certificates.index')
                ->with('success', 'Certificate restored successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to restore certificate: ' . $e->getMessage());
        }
    }
}
