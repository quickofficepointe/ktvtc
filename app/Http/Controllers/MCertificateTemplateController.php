<?php

namespace App\Http\Controllers;

use App\Models\MCertificateTemplate;
use App\Models\MCourse;
use App\Models\MobileSchool;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MCertificateTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = MCertificateTemplate::with(['course', 'mobileSchool', 'createdBy'])
            ->latest()
            ->get();

        $courses = MCourse::where('is_active', true)->get();
        $mobileSchools = MobileSchool::where('is_active', true)->get();

        return view('ktvtc.mschool.certificates.template', compact('templates', 'courses', 'mobileSchools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'template_code' => 'required|string|max:100|unique:m_certificate_templates,template_code',
            'description' => 'nullable|string',
            'template_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'template_type' => 'required|in:course_completion,achievement,participation,excellence,custom',
            'course_id' => 'nullable|exists:m_courses,course_id',
            'mobile_school_id' => 'nullable|exists:mobile_schools,id',
            'dynamic_fields' => 'required|array',
            'dynamic_fields.*.field_name' => 'required|string',
            'dynamic_fields.*.x_position' => 'required|numeric',
            'dynamic_fields.*.y_position' => 'required|numeric',
            'dynamic_fields.*.font_size' => 'required|numeric',
            'dynamic_fields.*.font_family' => 'required|string',
            'layout_config' => 'nullable|array',
            'styling' => 'nullable|array',
            'watermark_text' => 'nullable|string|max:255',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'has_qr_code' => 'boolean',
            'qr_code_position' => 'nullable|in:bottom_right,bottom_left,top_right,top_left,center',
            'signature_line1' => 'nullable|string|max:255',
            'signature_image1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature_line2' => 'nullable|string|max:255',
            'signature_image2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'validity_months' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'auto_generate' => 'boolean',
            'requires_approval' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Handle file uploads
            if ($request->hasFile('template_file')) {
                $templateFile = $request->file('template_file');
                $templateFileName = 'templates/' . time() . '_' . $templateFile->getClientOriginalName();
                $templateFile->storeAs('public/certificates', $templateFileName);
                $validated['template_file'] = $templateFileName;
            }

            if ($request->hasFile('background_image')) {
                $bgImage = $request->file('background_image');
                $bgImageName = 'backgrounds/' . time() . '_' . $bgImage->getClientOriginalName();
                $bgImage->storeAs('public/certificates', $bgImageName);
                $validated['background_image'] = $bgImageName;
            }

            if ($request->hasFile('signature_image1')) {
                $sig1Image = $request->file('signature_image1');
                $sig1ImageName = 'signatures/' . time() . '_sig1_' . $sig1Image->getClientOriginalName();
                $sig1Image->storeAs('public/certificates', $sig1ImageName);
                $validated['signature_image1'] = $sig1ImageName;
            }

            if ($request->hasFile('signature_image2')) {
                $sig2Image = $request->file('signature_image2');
                $sig2ImageName = 'signatures/' . time() . '_sig2_' . $sig2Image->getClientOriginalName();
                $sig2Image->storeAs('public/certificates', $sig2ImageName);
                $validated['signature_image2'] = $sig2ImageName;
            }

            // Convert arrays to JSON
            $validated['dynamic_fields'] = json_encode($validated['dynamic_fields']);
            if (isset($validated['layout_config'])) {
                $validated['layout_config'] = json_encode($validated['layout_config']);
            }
            if (isset($validated['styling'])) {
                $validated['styling'] = json_encode($validated['styling']);
            }

            // Add created by
            $validated['created_by'] = auth()->id();

            $template = MCertificateTemplate::create($validated);

            DB::commit();

            return redirect()->route('certificate-templates.index')
                ->with('success', 'Certificate template created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded files if any
            if (isset($templateFileName)) {
                Storage::delete('public/certificates/' . $templateFileName);
            }
            if (isset($bgImageName)) {
                Storage::delete('public/certificates/' . $bgImageName);
            }
            if (isset($sig1ImageName)) {
                Storage::delete('public/certificates/' . $sig1ImageName);
            }
            if (isset($sig2ImageName)) {
                Storage::delete('public/certificates/' . $sig2ImageName);
            }

            return redirect()->back()
                ->with('error', 'Failed to create certificate template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MCertificateTemplate $certificateTemplate)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'template_code' => 'required|string|max:100|unique:m_certificate_templates,template_code,' . $certificateTemplate->template_id . ',template_id',
            'description' => 'nullable|string',
            'template_file' => 'nullable|file|mimes:pdf|max:10240',
            'template_type' => 'required|in:course_completion,achievement,participation,excellence,custom',
            'course_id' => 'nullable|exists:m_courses,course_id',
            'mobile_school_id' => 'nullable|exists:mobile_schools,id',
            'dynamic_fields' => 'required|array',
            'dynamic_fields.*.field_name' => 'required|string',
            'dynamic_fields.*.x_position' => 'required|numeric',
            'dynamic_fields.*.y_position' => 'required|numeric',
            'dynamic_fields.*.font_size' => 'required|numeric',
            'dynamic_fields.*.font_family' => 'required|string',
            'layout_config' => 'nullable|array',
            'styling' => 'nullable|array',
            'watermark_text' => 'nullable|string|max:255',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'has_qr_code' => 'boolean',
            'qr_code_position' => 'nullable|in:bottom_right,bottom_left,top_right,top_left,center',
            'signature_line1' => 'nullable|string|max:255',
            'signature_image1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature_line2' => 'nullable|string|max:255',
            'signature_image2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'validity_months' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'auto_generate' => 'boolean',
            'requires_approval' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Handle file uploads
            if ($request->hasFile('template_file')) {
                // Delete old file
                if ($certificateTemplate->template_file) {
                    Storage::delete('public/certificates/' . $certificateTemplate->template_file);
                }

                $templateFile = $request->file('template_file');
                $templateFileName = 'templates/' . time() . '_' . $templateFile->getClientOriginalName();
                $templateFile->storeAs('public/certificates', $templateFileName);
                $validated['template_file'] = $templateFileName;
            }

            if ($request->hasFile('background_image')) {
                // Delete old file
                if ($certificateTemplate->background_image) {
                    Storage::delete('public/certificates/' . $certificateTemplate->background_image);
                }

                $bgImage = $request->file('background_image');
                $bgImageName = 'backgrounds/' . time() . '_' . $bgImage->getClientOriginalName();
                $bgImage->storeAs('public/certificates', $bgImageName);
                $validated['background_image'] = $bgImageName;
            }

            if ($request->hasFile('signature_image1')) {
                // Delete old file
                if ($certificateTemplate->signature_image1) {
                    Storage::delete('public/certificates/' . $certificateTemplate->signature_image1);
                }

                $sig1Image = $request->file('signature_image1');
                $sig1ImageName = 'signatures/' . time() . '_sig1_' . $sig1Image->getClientOriginalName();
                $sig1Image->storeAs('public/certificates', $sig1ImageName);
                $validated['signature_image1'] = $sig1ImageName;
            }

            if ($request->hasFile('signature_image2')) {
                // Delete old file
                if ($certificateTemplate->signature_image2) {
                    Storage::delete('public/certificates/' . $certificateTemplate->signature_image2);
                }

                $sig2Image = $request->file('signature_image2');
                $sig2ImageName = 'signatures/' . time() . '_sig2_' . $sig2Image->getClientOriginalName();
                $sig2Image->storeAs('public/certificates', $sig2ImageName);
                $validated['signature_image2'] = $sig2ImageName;
            }

            // Convert arrays to JSON
            $validated['dynamic_fields'] = json_encode($validated['dynamic_fields']);
            if (isset($validated['layout_config'])) {
                $validated['layout_config'] = json_encode($validated['layout_config']);
            }
            if (isset($validated['styling'])) {
                $validated['styling'] = json_encode($validated['styling']);
            }

            // Add updated by
            $validated['updated_by'] = auth()->id();

            $certificateTemplate->update($validated);

            DB::commit();

            return redirect()->route('certificate-templates.index')
                ->with('success', 'Certificate template updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update certificate template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MCertificateTemplate $certificateTemplate)
    {
        try {
            DB::beginTransaction();

            // Check if template has certificates
            if ($certificateTemplate->certificates()->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot delete template because it has associated certificates. Please delete the certificates first.');
            }

            // Delete associated files
            if ($certificateTemplate->template_file) {
                Storage::delete('public/certificates/' . $certificateTemplate->template_file);
            }
            if ($certificateTemplate->background_image) {
                Storage::delete('public/certificates/' . $certificateTemplate->background_image);
            }
            if ($certificateTemplate->signature_image1) {
                Storage::delete('public/certificates/' . $certificateTemplate->signature_image1);
            }
            if ($certificateTemplate->signature_image2) {
                Storage::delete('public/certificates/' . $certificateTemplate->signature_image2);
            }

            $certificateTemplate->delete();

            DB::commit();

            return redirect()->route('certificate-templates.index')
                ->with('success', 'Certificate template deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete certificate template: ' . $e->getMessage());
        }
    }
}
