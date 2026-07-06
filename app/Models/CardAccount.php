<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CardAccount extends Model
{
    protected $fillable = [
        'high_school_student_id',  // CHANGED from student_id
        'account_number',
        'card_number',
        'qr_code',
        'qr_token',
        'qr_generated_at',
        'balance',
        'total_funded',
        'total_spent',
        'is_active',
        'is_locked',
        'is_blocked',
        'blocked_reason',
        'blocked_at',
        'blocked_by',
        'daily_limit',
        'per_transaction_limit',
        'low_balance_threshold',
        'minimum_balance',
        'today_spent',
        'today_transactions',
        'today_first_used_at',
        'last_used_at',
        'last_funded_at',
        'student_name',
        'student_photo',
        'student_class',
        'student_admission_number',
        'issued_at',
        'issued_by',
        'expiry_date',
        'notes'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_funded' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'daily_limit' => 'decimal:2',
        'per_transaction_limit' => 'decimal:2',
        'low_balance_threshold' => 'decimal:2',
        'minimum_balance' => 'decimal:2',
        'today_spent' => 'decimal:2',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
        'is_blocked' => 'boolean',
        'qr_generated_at' => 'datetime',
        'blocked_at' => 'datetime',
        'today_first_used_at' => 'datetime',
        'last_used_at' => 'datetime',
        'last_funded_at' => 'datetime',
        'issued_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    /**
     * Get the high school student that owns the card
     */
    public function student(): BelongsTo  // CHANGED from student to highSchoolStudent
    {
        return $this->belongsTo(HighSchoolStudent::class, 'high_school_student_id');
    }

    /**
     * Get all transactions for this card
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CardTransaction::class);
    }

    /**
     * Get daily usage records
     */
    public function dailyUsage(): HasMany
    {
        return $this->hasMany(CardDailyUsage::class);
    }

    /**
     * Get funding requests
     */
    public function fundingRequests(): HasMany
    {
        return $this->hasMany(CardFundingRequest::class);
    }

    /**
     * Get audit logs
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(CardAuditLog::class);
    }

    /**
     * Generate account number: 7722609#ADNO
     */
    public static function generateAccountNumber(HighSchoolStudent $student): string  // CHANGED from Student to HighSchoolStudent
    {
        $admissionNumber = $student->admission_number ?? 'UNKNOWN';
        return '7722609#' . $admissionNumber;
    }

    /**
     * Generate card number
     */
    public static function generateCardNumber(): string
    {
        $year = date('Y');
        $lastCard = self::orderBy('id', 'desc')->first();
        $sequence = $lastCard ? intval(substr($lastCard->card_number, -4)) + 1 : 1;
        return 'CARD' . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate QR token
     */
    public static function generateQrToken(): string
    {
        return Str::random(32) . '-' . time();
    }

    /**
     * Check if card can make a purchase
     */
    public function canMakePurchase($amount): array
    {
        if (!$this->is_active) {
            return ['allowed' => false, 'reason' => 'Card is deactivated'];
        }

        if ($this->is_locked) {
            return ['allowed' => false, 'reason' => 'Card is locked'];
        }

        if ($this->is_blocked) {
            return ['allowed' => false, 'reason' => 'Card is blocked: ' . ($this->blocked_reason ?? 'Contact school')];
        }

        if ($this->balance < $amount) {
            return ['allowed' => false, 'reason' => 'Insufficient balance. Available: KES ' . number_format($this->balance, 2)];
        }

        if ($amount > $this->per_transaction_limit) {
            return ['allowed' => false, 'reason' => 'Amount exceeds transaction limit of KES ' . number_format($this->per_transaction_limit, 2)];
        }

        $remainingDaily = $this->daily_limit - $this->today_spent;
        if ($amount > $remainingDaily) {
            return ['allowed' => false, 'reason' => 'Daily limit exceeded. Remaining: KES ' . number_format($remainingDaily, 2)];
        }

        if ($this->balance - $amount < $this->minimum_balance) {
            return ['allowed' => false, 'reason' => 'Cannot go below minimum balance of KES ' . number_format($this->minimum_balance, 2)];
        }

        return ['allowed' => true];
    }

    /**
     * Get QR code URL
     */
    public function getQrUrlAttribute(): ?string
    {
        if ($this->qr_code) {
            return asset('storage/' . $this->qr_code);
        }
        return null;
    }

    /**
     * Get encrypted QR data for scanning
     */
    public function getEncryptedQrData(): array
    {
        return [
            'id' => encrypt($this->account_number),
            'token' => $this->qr_token,
            'timestamp' => now()->timestamp
        ];
    }
}
