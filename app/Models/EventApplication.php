<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'parent_name',
        'parent_contact',
        'parent_email',
        'mpesa_reference_number',
        'number_of_people',
        'total_amount',
        'application_status',
        'notes',
    ];

    protected $casts = [
        'number_of_people' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the event for this application
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->application_status) {
            'pending' => 'warning',
            'confirmed' => 'success',
            'cancelled' => 'danger',
            'completed' => 'info',
            'pending_payment' => 'warning',
            'payment_failed' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get the attendees for this application
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(EventApplicationAttendee::class);
    }

    /**
     * Get KCB transactions for this application
     */
    public function kcbTransactions(): HasMany
    {
        return $this->hasMany(KcbBuniTransaction::class, 'application_id');
    }

    /**
     * Get the latest KCB transaction
     */
    public function latestKcbTransaction()
    {
        return $this->hasOne(KcbBuniTransaction::class, 'application_id')->latest();
    }
}
