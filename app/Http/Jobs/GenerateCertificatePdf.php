<?php

namespace App\Jobs;

use App\Models\MCertificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class GenerateCertificatePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $certificate;

    public function __construct(MCertificate $certificate)
    {
        $this->certificate = $certificate;
    }

    public function handle()
    {
        try {
            // Load the certificate with relationships
            $certificate = $this->certificate->load(['template', 'student', 'course']);

            // Get template PDF path
            $templatePath = storage_path('app/public/certificates/' . $certificate->template->template_file);

            if (!file_exists($templatePath)) {
                throw new \Exception("Template PDF not found: " . $templatePath);
            }

            // Initialize FPDI
            $pdf = new Fpdi();

            // Import the template page
            $pageCount = $pdf->setSourceFile($templatePath);
            $templateId = $pdf->importPage(1);

            // Get template size
            $size = $pdf->getTemplateSize($templateId);

            // Add page with same dimensions as template
            if ($size['width'] > $size['height']) {
                $pdf->AddPage('L', [$size['width'], $size['height']]);
            } else {
                $pdf->AddPage('P', [$size['width'], $size['height']]);
            }

            // Use the imported page
            $pdf->useTemplate($templateId);

            // Set default font
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->SetTextColor(0, 0, 0); // Black text

            // Get certificate data
            $certificateData = json_decode($certificate->certificate_data, true);

            // Get field coordinates from template
            $dynamicFields = json_decode($certificate->template->dynamic_fields, true) ?? [];

            // Fill in the fields
            foreach ($dynamicFields as $field) {
                $fieldName = $field['field_name'] ?? null;
                $x = $field['x_position'] ?? 0;
                $y = $field['y_position'] ?? 0;
                $fontSize = $field['font_size'] ?? 12;
                $fontFamily = $field['font_family'] ?? 'Helvetica';

                if ($fieldName && isset($certificateData[$fieldName])) {
                    $value = $certificateData[$fieldName];

                    // Set font
                    $pdf->SetFont($fontFamily, '', $fontSize);

                    // Insert text at coordinates
                    $pdf->SetXY($x, $y);
                    $pdf->Write(0, $value);
                }
            }

            // Save generated PDF
            $outputFilename = 'certificates/generated/' . $certificate->certificate_number . '.pdf';
            $outputPath = storage_path('app/public/' . $outputFilename);

            // Ensure directory exists
            $directory = dirname($outputPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $pdf->Output($outputPath, 'F');

            // Update certificate with PDF info
            $certificate->update([
                'generated_pdf_path' => $outputFilename,
                'generated_at' => now(),
                'file_size' => filesize($outputPath),
                'file_hash' => hash_file('sha256', $outputPath),
                'status' => $certificate->status === 'draft' ? 'generated' : $certificate->status
            ]);

        } catch (\Exception $e) {
            Log::error('PDF Generation Failed for certificate ' . $this->certificate->certificate_id . ': ' . $e->getMessage());

            // Update certificate with error status
            $this->certificate->update([
                'status' => 'draft',
                'issuance_remarks' => 'PDF Generation Failed: ' . $e->getMessage()
            ]);

            throw $e;
        }
    }
}
