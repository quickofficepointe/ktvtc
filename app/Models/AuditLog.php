<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->morphTo();
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public function scopeForUser($query, $userType, $userId)
    {
        return $query->where('user_type', $userType)
                     ->where('user_id', $userId);
    }

    public function scopeForAuditable($query, $auditableType, $auditableId)
    {
        return $query->where('auditable_type', $auditableType)
                     ->where('auditable_id', $auditableId);
    }

    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
