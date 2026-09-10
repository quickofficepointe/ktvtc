<?php
// app/Models/CardAuditLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardAuditLog extends Model
{
    protected $fillable = [
        'card_account_id',
        'action_type',
        'description',
        'old_value',
        'new_value',
        'metadata',
        'performed_by',
        'performed_by_type',
        'ip_address',
        'user_agent',
        'device_id',
        'status',
        'error_message'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the card account
     */
    public function cardAccount(): BelongsTo
    {
        return $this->belongsTo(CardAccount::class);
    }

    /**
     * Get the high school student (via card account)
     */
    public function student()
    {
        return $this->hasOneThrough(
            HighSchoolStudent::class,
            CardAccount::class,
            'id',
            'id',
            'card_account_id',
            'high_school_student_id'
        );
    }

    /**
     * Get the performer
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * ✅ FIX: Map action types to ENUM values
     */
    protected static function mapActionType(string $actionType): string
    {
        $map = [
            // Funding
            'funding_initiated' => 'fund',
            'funding_completed' => 'fund',
            'funding_failed' => 'fund',
            'funding_requested' => 'fund',
            'funding_retry' => 'fund',
            
            // Card lifecycle
            'card_created' => 'create',
            'card_issued' => 'create',
            'card_activated' => 'create',
            
            // Card status
            'card_locked' => 'lock',
            'card_unlocked' => 'unlock',
            'card_blocked' => 'block',
            'card_unblocked' => 'unblock',
            
            // Balance
            'balance_adjusted' => 'balance_adjust',
            'balance_added' => 'balance_adjust',
            'balance_deducted' => 'balance_adjust',
            
            // Transactions
            'purchase_made' => 'purchase',
            'purchase_approved' => 'purchase',
            'purchase_declined' => 'purchase',
            'purchase_failed' => 'purchase',
            
            // QR Code
            'qr_generated' => 'create',
            'qr_printed' => 'create',
            
            // User actions
            'user_login' => 'create',
            'user_logout' => 'create',
            'user_created' => 'create',
            'user_updated' => 'create',
            
            // System
            'system_error' => 'create',
            'system_warning' => 'create',
            
            // Default fallback
            'default' => 'create',
        ];

        return $map[$actionType] ?? $map['default'];
    }

    /**
     * Log an action - ✅ FIXED: Uses valid ENUM values
     */
    public static function log(
        $cardAccountId,
        $actionType,
        $description,
        $oldValue = null,
        $newValue = null,
        $metadata = null
    ) {
        // ✅ Map to valid ENUM value
        $validAction = self::mapActionType($actionType);
        
        return self::create([
            'card_account_id' => $cardAccountId,
            'action_type' => $validAction,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'metadata' => $metadata,
            'performed_by' => auth()->id(),
            'performed_by_type' => auth()->check() ? 'admin' : 'system',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => 'success'
        ]);
    }

    /**
     * Log an error
     */
    public static function logError(
        $cardAccountId,
        $actionType,
        $errorMessage,
        $metadata = null
    ) {
        $validAction = self::mapActionType($actionType);
        
        return self::create([
            'card_account_id' => $cardAccountId,
            'action_type' => $validAction,
            'description' => $errorMessage,
            'metadata' => $metadata,
            'performed_by' => auth()->id(),
            'performed_by_type' => auth()->check() ? 'admin' : 'system',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Get human readable action label
     */
    public function getActionLabelAttribute(): string
    {
        $labels = [
            'create' => 'Created',
            'fund' => 'Funding',
            'purchase' => 'Purchase',
            'lock' => 'Locked',
            'unlock' => 'Unlocked',
            'block' => 'Blocked',
            'unblock' => 'Unblocked',
            'limit_change' => 'Limit Changed',
            'balance_adjust' => 'Balance Adjusted',
            'refund' => 'Refund',
            'reversal' => 'Reversal',
            'pin_reset' => 'PIN Reset',
            'delete' => 'Deleted',
        ];
        
        return $labels[$this->action_type] ?? $this->action_type;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'success' => 'green',
            'failed' => 'red',
            'pending' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Scope for funding actions
     */
    public function scopeFunding($query)
    {
        return $query->where('action_type', 'fund');
    }

    /**
     * Scope for purchase actions
     */
    public function scopePurchases($query)
    {
        return $query->where('action_type', 'purchase');
    }

    /**
     * Scope for status changes
     */
    public function scopeStatusChanges($query)
    {
        return $query->whereIn('action_type', ['lock', 'unlock', 'block', 'unblock']);
    }
}