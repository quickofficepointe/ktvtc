<?php

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
     * Log an action
     */
    public static function log(
        $cardAccountId,
        $actionType,
        $description,
        $oldValue = null,
        $newValue = null,
        $metadata = null
    ) {
        return self::create([
            'card_account_id' => $cardAccountId,
            'action_type' => $actionType,
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
}
