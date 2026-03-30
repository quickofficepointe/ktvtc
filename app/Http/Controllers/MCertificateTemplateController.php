<?php

namespace App\Http\Controllers;

use App\Models\MCertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

class MCertificateTemplateController extends Controller
{
    /**
     * Display template management page
     */
    public function index()
    {
        $templates = MCertificateTemplate::withCount('certificates')->latest()->get();
        return view('ktvtc.mschool.certificates.template', compact('templates'));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        Log::info('Template store method called', ['request' => $request->except(['template_file'])]);

        $validator = Validator::make($request->all(), [
            'template_name' => 'required|string|max:255',
            'template_type' => 'required|string|in:completion,participation,achievement,recognition|unique:m_certificate_templates,template_type',
            'template_file' => 'required|file|mimes:pdf|max:5120',
            'name_x' => 'required|integer|min:0',
            'name_y' => 'required|integer|min:0',
            'course_x' => 'required|integer|min:0',
            'course_y' => 'required|integer|min:0',
        ], [
            'template_type.in' => 'Template type must be one of: completion, participation, achievement, recognition',
            'template_file.max' => 'Template file must not exceed 5MB',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()->toArray()]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $file = $request->file('template_file');
            Log::info('File uploaded', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);

            // Generate safe filename
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $safeName = Str::slug($originalName) . '_' . time() . '.' . $extension;
            $directory = 'certificate_templates';

            // Store file directly using storeAs with 'public' disk
            $filePath = $file->storeAs($directory, $safeName, 'public');

            Log::info('File stored', [
                'file_path' => $filePath,
                'relative_path' => $filePath, // This is the path relative to the 'public' disk
                'full_disk_path' => Storage::disk('public')->path($filePath)
            ]);

            if (!$filePath) {
                throw new \Exception('Failed to store template file');
            }

            // Verify file was stored - check with Storage facade
            if (!Storage::disk('public')->exists($filePath)) {
                Log::error('File verification failed', [
                    'expected_path' => $filePath,
                    'disk_path' => Storage::disk('public')->path($filePath),
                    'files_in_directory' => Storage::disk('public')->files($directory)
                ]);
                throw new \Exception('Template file was not saved properly. Storage check failed.');
            }

            // Create template record
            $template = MCertificateTemplate::create([
                'template_name' => $request->template_name,
                'template_type' => $request->template_type,
                'template_file' => $filePath, // Store the relative path
                'name_x' => $request->name_x,
                'name_y' => $request->name_y,
                'course_x' => $request->course_x,
                'course_y' => $request->course_y,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Certificate template created successfully!',
                'template' => $template
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded file if error
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            Log::error('Template creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get template for editing
     */
    public function edit(MCertificateTemplate $template)
    {
        return response()->json([
            'success' => true,
            'template' => $template
        ]);
    }

    /**
     * Update template
     */
    public function update(Request $request, MCertificateTemplate $template)
    {
        Log::info('Template update method called', [
            'template_id' => $template->template_id,
            'request' => $request->except(['template_file'])
        ]);

        $validator = Validator::make($request->all(), [
            'template_name' => 'required|string|max:255',
            'template_type' => 'required|string|in:completion,participation,achievement,recognition|unique:m_certificate_templates,template_type,' . $template->template_id . ',template_id',
            'template_file' => 'nullable|file|mimes:pdf|max:5120',
            'name_x' => 'required|integer|min:0',
            'name_y' => 'required|integer|min:0',
            'course_x' => 'required|integer|min:0',
            'course_y' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed on update', ['errors' => $validator->errors()->toArray()]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = [
                'template_name' => $request->template_name,
                'template_type' => $request->template_type,
                'name_x' => $request->name_x,
                'name_y' => $request->name_y,
                'course_x' => $request->course_x,
                'course_y' => $request->course_y,
                'updated_by' => auth()->id(),
            ];

            $oldFilePath = $template->template_file;

            // Handle file upload if new file provided
            if ($request->hasFile('template_file')) {
                $file = $request->file('template_file');
                Log::info('New file uploaded for update', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize()
                ]);

                // Generate safe filename
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $safeName = Str::slug($originalName) . '_' . time() . '.' . $extension;
                $directory = 'certificate_templates';

                // Store new file
                $newFilePath = $file->storeAs($directory, $safeName, 'public');

                if (!$newFilePath) {
                    throw new \Exception('Failed to store new template file');
                }

                // Verify file was stored
                if (!Storage::disk('public')->exists($newFilePath)) {
                    throw new \Exception('New template file was not saved properly');
                }

                $data['template_file'] = $newFilePath;
            }

            // Update template
            $template->update($data);

            // Delete old file after successful update
            if ($request->hasFile('template_file') && $oldFilePath) {
                if (Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Certificate template updated successfully!',
                'template' => $template->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Template update failed: ' . $e->getMessage(), [
                'template_id' => $template->template_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete template
     */
    public function destroy(MCertificateTemplate $template)
    {
        Log::info('Template delete method called', ['template_id' => $template->template_id]);

        DB::beginTransaction();
        try {
            // Check if template is in use
            if ($template->certificates()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete template because it has issued certificates. Please reassign or delete those certificates first.'
                ], 400);
            }

            $filePath = $template->template_file;

            // Delete template record
            $template->delete();

            // Delete associated file
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            DB::commit();

            Log::info('Template deleted successfully', ['template_id' => $template->template_id]);

            return response()->json([
                'success' => true,
                'message' => 'Certificate template deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Template deletion failed: ' . $e->getMessage(), [
                'template_id' => $template->template_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test template coordinates
     */
    public function testCoordinates(Request $request, MCertificateTemplate $template)
    {
        Log::info('Template test method called', [
            'template_id' => $template->template_id,
            'test_name' => $request->test_name,
            'test_course' => $request->test_course
        ]);

        $validator = Validator::make($request->all(), [
            'test_name' => 'required|string|max:100',
            'test_course' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify template file exists
            if (!$template->template_file) {
                throw new \Exception('Template file path is not set in database.');
            }

            if (!Storage::disk('public')->exists($template->template_file)) {
                Log::error('Template file not found', [
                    'template_file' => $template->template_file,
                    'storage_path' => Storage::disk('public')->path($template->template_file),
                    'available_files' => Storage::disk('public')->files('certificate_templates')
                ]);
                throw new \Exception('Template file not found. Please re-upload the template.');
            }

            $templatePath = Storage::disk('public')->path($template->template_file);

            // Verify PDF file is valid
            if (!file_exists($templatePath)) {
                throw new \Exception('Template file path is invalid.');
            }

            // Create PDF
            $pdf = new Fpdi();

            // Set PDF properties
            $pdf->SetCreator(config('app.name'));
            $pdf->SetAuthor(config('app.name'));
            $pdf->SetTitle('Certificate Test - ' . $template->template_name);
            $pdf->SetSubject('Certificate Template Test');

            // Import template
            $pageCount = $pdf->setSourceFile($templatePath);
            if ($pageCount < 1) {
                throw new \Exception('Invalid PDF file - no pages found.');
            }

            // Add first page from template
            $pdf->AddPage();
            $templateId = $pdf->importPage(1);
            $pdf->useTemplate($templateId, 0, 0, null, null, true);

            // Set font for text
            $pdf->SetFont('helvetica', '', 16);
            $pdf->SetTextColor(0, 0, 0);

            // Add test text - Student Name
            $pdf->SetXY($template->name_x, $template->name_y);
            $pdf->Cell(120, 10, $request->test_name, 0, 0, 'L');

            // Add test text - Course Name
            $pdf->SetXY($template->course_x, $template->course_y);
            $pdf->Cell(150, 10, $request->test_course, 0, 0, 'L');

            // Add debug info (optional)
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(128, 128, 128);
            $pdf->SetXY(10, 280);
            $pdf->Cell(0, 5, 'Template: ' . $template->template_name . ' | Test Date: ' . now()->format('Y-m-d H:i:s'), 0, 0, 'L');

            // Output PDF
            $outputName = 'test_certificate_' . Str::slug($template->template_name) . '_' . time() . '.pdf';

            return response($pdf->Output('S'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $outputName . '"')
                ->header('Cache-Control', 'private, max-age=0, must-revalidate')
                ->header('Pragma', 'public');

        } catch (\Exception $e) {
            Log::error('Template test failed: ' . $e->getMessage() . ' | Template ID: ' . $template->template_id, [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get template details for preview
     */
    public function show(MCertificateTemplate $template)
    {
        try {
            $template->loadCount('certificates');

            $fileExists = false;
            $fileSize = null;
            $fileUrl = null;

            if ($template->template_file) {
                $fileExists = Storage::disk('public')->exists($template->template_file);
                if ($fileExists) {
                    $fileSize = Storage::disk('public')->size($template->template_file);
                    $fileUrl = asset('storage/' . $template->template_file);
                }
            }

            return response()->json([
                'success' => true,
                'template' => $template,
                'file_info' => [
                    'exists' => $fileExists,
                    'size' => $fileSize ? $this->formatBytes($fileSize) : null,
                    'url' => $fileUrl,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load template details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load template details'
            ], 500);
        }
    }

    /**
     * Helper function to format bytes
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
