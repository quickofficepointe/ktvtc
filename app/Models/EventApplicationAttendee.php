<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventApplicationAttendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_application_id',
        'name',
        'school',
        'age',
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    /**
     * Get the event application for this attendee
     */
    public function eventApplication(): BelongsTo
    {
        return $this->belongsTo(EventApplication::class);
    }
}
