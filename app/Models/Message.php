<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'seen_by', // admin id who first saw the message
        'action_taken', // optional: e.g., responded, forwarded
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];

  // Accessor for action badge color
    public function getActionBadgeAttribute()
    {
        return match($this->action) {
            'responded' => 'success',
            'pending' => 'warning',
            'ignored' => 'secondary',
            default => 'light'
        };
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'seen_by');
    }
}
