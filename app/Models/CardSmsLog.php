<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardSmsLog extends Model
{
    protected $fillable = [
        'high_school_student_id',  // CHANGED from student_id
        'contact_id',
        'phone_number',
        'message',
        'direction',
        'response_type',
        'parsed_amount',
        'status',
        'provider_response',
        'provider_message_id',
        'funding_request_id',
        'metadata',
        'error_message',
        'sent_at',
        'delivered_at',
        'received_at'
    ];

    protected $casts = [
        'parsed_amount' => 'decimal:2',
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * Get the high school student
     */
    public function student(): BelongsTo  // CHANGED from student to highSchoolStudent
    {
        return $this->belongsTo(HighSchoolStudent::class, 'high_school_student_id');
    }

    /**
     * Get the contact
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(StudentContact::class);
    }

    /**
     * Get the funding request
     */
    public function fundingRequest(): BelongsTo
    {
        return $this->belongsTo(CardFundingRequest::class);
    }

    /**
     * Log outgoing SMS
     */
    public static function logOutgoing($phoneNumber, $message, $studentId = null, $contactId = null)
    {
        return self::create([
            'phone_number' => $phoneNumber,
            'message' => $message,
            'direction' => 'outgoing',
            'status' => 'pending',
            'high_school_student_id' => $studentId,
            'contact_id' => $contactId,
            'sent_at' => now()
        ]);
    }

    /**
     * Log incoming SMS
     */
    public static function logIncoming($phoneNumber, $message)
    {
        // Parse message to determine response type
        $upperMessage = strtoupper(trim($message));
        $responseType = 'other';
        $parsedAmount = null;

        if (strpos($upperMessage, 'FUND') === 0) {
            $responseType = 'fund';
            $parts = explode(' ', $upperMessage);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $parsedAmount = (float) $parts[1];
            }
        } elseif ($upperMessage === 'BALANCE') {
            $responseType = 'balance';
        } elseif ($upperMessage === 'HELP') {
            $responseType = 'help';
        } elseif ($upperMessage === 'STOP') {
            $responseType = 'stop';
        } elseif ($upperMessage === 'START') {
            $responseType = 'start';
        }

        return self::create([
            'phone_number' => $phoneNumber,
            'message' => $message,
            'direction' => 'incoming',
            'response_type' => $responseType,
            'parsed_amount' => $parsedAmount,
            'status' => 'received',
            'received_at' => now()
        ]);
    }
}
