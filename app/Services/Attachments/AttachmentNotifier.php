<?php

namespace App\Services\Attachments;

use App\Models\Student;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

/**
 * SMS for the attachment (industrial placement) process. A failed SMS never
 * stops the decision being saved.
 */
class AttachmentNotifier
{
    public function student(?Student $student, string $message): void
    {
        $this->send($student?->phone, $student ? "Dear {$student->first_name}, {$message} KTVTC" : null);
    }

    public function trainer(?User $trainer, string $message): void
    {
        $this->send($trainer?->phone_number, $trainer ? "Dear {$trainer->name}, {$message} KTVTC" : null);
    }

    private function send(?string $phone, ?string $message): void
    {
        if (!$phone || !$message) {
            return;
        }
        try {
            app(SmsService::class)->sendSingleSms($phone, $message);
        } catch (\Throwable $e) {
            Log::warning('Attachment SMS failed', ['phone' => $phone, 'error' => $e->getMessage()]);
        }
    }
}
