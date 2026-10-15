<?php

namespace App\Http\Controllers;

use App\Models\CardAccount;
use App\Services\CardQrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CardBulkPdfController extends Controller
{
    protected CardQrCodeService $qrService;

    public function __construct(CardQrCodeService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Bulk ID Card PDF generator.
     *
     * mode:
     *   - fronts      → 10 cards per A4 page
     *   - backs       → 10 backs per A4 page
     *   - front_back  → 5 students per A4 page (front + back side-by-side)
     */
    public function download(Request $request)
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(600);

        $mode  = $request->get('mode', 'fronts');
        $scope = $request->get('scope', 'all');

        // -------- Build query --------
        $query = CardAccount::with('student')->where('is_active', true);

        if ($scope === 'class' && $request->filled('class')) {
            $query->where('student_class', $request->class);
        } elseif ($scope === 'selected' && $request->filled('card_ids')) {
            $query->whereIn('id', (array) $request->card_ids);
        }

        $cards = $query->orderBy('student_class')->orderBy('student_name')->get();

        if ($cards->isEmpty()) {
            abort(404, 'No cards found.');
        }

        // -------- Ensure every card has a QR --------
        foreach ($cards as $card) {
            if (!$card->qr_code || !Storage::disk('public')->exists($card->qr_code)) {
                if ($card->student) {
                    $this->qrService->generateQrCode($card, $card->student);
                    $card->refresh();
                }
            }
        }

        // -------- Preload + resize images as base64 --------
        $cards->each(function ($card) {
            // QR: 300x300 target (matches generated size)
            $card->qr_base64 = $this->imageToBase64(
                $card->qr_code ? public_path('storage/' . $card->qr_code) : null,
                300, 300
            );

            // Photo: 300x300 square target — prevents giant embedded images
            $photoPath = $card->student && $card->student->profile_picture
                ? public_path('storage/' . $card->student->profile_picture)
                : public_path('images/default/avatar.png');

            $card->photo_base64 = $this->imageToBase64($photoPath, 300, 300);
        });

        // -------- Pick template --------
        $view = match ($mode) {
            'backs'      => 'ktvtc.finance.high-school.cards.pdf-bulk-backs',
            'front_back' => 'ktvtc.finance.high-school.cards.pdf-bulk-front-back',
            default      => 'ktvtc.finance.high-school.cards.pdf-bulk-fronts',
        };

        $pdf = Pdf::loadView($view, [
            'cards'     => $cards,
            'logo'      => $this->imageToBase64(public_path('images/logo/main-logo.png'), 200, 200),
            'header_bg' => $this->fileToBase64(public_path('images/id-card/svg-bg.png')),
            'footer_bg' => $this->fileToBase64(public_path('images/id-card/footer-bg.png')),
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'dpi'                  => 96,       // ✅ critical — was 300
            'defaultFont'          => 'sans-serif',
            'isFontSubsettingEnabled' => true,
        ]);

        $filename = 'ID-Cards-' . ucfirst($mode) . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Return a base64 data URI, optionally resizing to max WxH to keep the PDF small.
     */
    protected function imageToBase64(?string $path, ?int $maxWidth = null, ?int $maxHeight = null): ?string
    {
        if (!$path || !file_exists($path)) {
            return null;
        }

        // If it's an SVG, don't try to process with GD
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = mime_content_type($path) ?: 'image/png';

        if ($ext === 'svg' || $mime === 'image/svg+xml') {
            return 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($path));
        }

        // Try to resize with GD if available and a max is given
        if ($maxWidth && $maxHeight && function_exists('imagecreatefromstring')) {
            $resized = $this->resizeImage($path, $maxWidth, $maxHeight);
            if ($resized !== null) {
                return $resized;
            }
        }

        // Fallback: return as-is
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    /**
     * Resize an image to fit within $maxW x $maxH, preserving aspect ratio.
     * Returns a PNG data URI or null if GD can't process it.
     */
    protected function resizeImage(string $path, int $maxW, int $maxH): ?string
    {
        try {
            $data = file_get_contents($path);
            if ($data === false) return null;

            $src = @imagecreatefromstring($data);
            if (!$src) return null;

            $srcW = imagesx($src);
            $srcH = imagesy($src);

            if ($srcW <= 0 || $srcH <= 0) {
                imagedestroy($src);
                return null;
            }

            // Calculate target dimensions (fit inside box, preserve aspect)
            $ratio = min($maxW / $srcW, $maxH / $srcH);

            // Don't upscale
            if ($ratio > 1) {
                $ratio = 1;
            }

            $newW = (int) round($srcW * $ratio);
            $newH = (int) round($srcH * $ratio);

            $dst = imagecreatetruecolor($newW, $newH);

            // Preserve transparency
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);

            imagecopyresampled(
                $dst, $src,
                0, 0, 0, 0,
                $newW, $newH,
                $srcW, $srcH
            );

            ob_start();
            imagepng($dst, null, 8);   // PNG compression level 8
            $output = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);

            return 'data:image/png;base64,' . base64_encode($output);

        } catch (\Throwable $e) {
            \Log::warning('resizeImage failed: ' . $e->getMessage(), ['path' => $path]);
            return null;
        }
    }

    /**
     * Plain base64 for backgrounds (no resizing — usually decorative and small).
     */
    protected function fileToBase64(?string $path): ?string
    {
        if (!$path || !file_exists($path)) {
            return null;
        }
        $mime = mime_content_type($path) ?: 'image/png';

        // Some systems store SVG with .png extension — detect via content
        $content = file_get_contents($path);
        if ($content !== false && strpos(trim($content), '<svg') === 0) {
            return 'data:image/svg+xml;base64,' . base64_encode($content);
        }

        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }
}