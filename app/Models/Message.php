<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory;
    // Remove SoftDeletes since it's not in your migration

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'first_seen_by',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationship with admin who first saw the message
    public function firstSeenBy()
    {
        return $this->belongsTo(User::class, 'first_seen_by');
    }

    // Accessor for status badge color
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'viewed' => 'info',
            'replied' => 'success',
            'resolved' => 'primary',
            'archived' => 'secondary',
            default => 'light'
        };
    }

    // Accessor for status label
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    // Scope for pending messages
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for viewed messages
    public function scopeViewed($query)
    {
        return $query->where('status', 'viewed');
    }

    // Scope for replied messages
    public function scopeReplied($query)
    {
        return $query->where('status', 'replied');
    }

    // Scope for resolved messages
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
