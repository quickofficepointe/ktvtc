<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'level',
        'ip_address',
        'user_agent',
        'metadata',
        'read'
    ];

    protected $casts = [
        'metadata' => 'array',
        'read' => 'boolean',
        'created_at' => 'datetime'
    ];

    /**
     * Get the user that performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread logs
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope for specific level
     */
    public function scopeLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark log as read
     */
    public function markAsRead()
    {
        $this->update(['read' => true]);
    }

    /**
     * Create a system log entry
     */
    public static function log($action, $description, $level = 'info', $userId = null, $metadata = null)
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'level' => $level,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Create an error log
     */
    public static function error($action, $description, $userId = null, $metadata = null)
    {
        return self::log($action, $description, 'error', $userId, $metadata);
    }

    /**
     * Create a warning log
     */
    public static function warning($action, $description, $userId = null, $metadata = null)
    {
        return self::log($action, $description, 'warning', $userId, $metadata);
    }

    /**
     * Create an info log
     */
    public static function info($action, $description, $userId = null, $metadata = null)
    {
        return self::log($action, $description, 'info', $userId, $metadata);
    }

    /**
     * Create a critical log
     */
    public static function critical($action, $description, $userId = null, $metadata = null)
    {
        return self::log($action, $description, 'critical', $userId, $metadata);
    }
}
