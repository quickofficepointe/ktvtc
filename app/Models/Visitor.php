<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'session_id',
        'user_agent',
        'path',
        'referrer',
        'country',
        'device_type',
        'browser'
    ];

    // Scope to get unique visitors (by session or IP)
    public function scopeUniqueVisitors($query)
    {
        return $query->select('session_id')->distinct();
    }

    // Scope to get today's visitors
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // Scope to get this month's visitors
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    // Scope to get visitors by date range
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Get popular pages
    public function scopePopularPages($query, $limit = 10)
    {
        return $query->select('path', \DB::raw('COUNT(*) as visits'))
                    ->groupBy('path')
                    ->orderBy('visits', 'desc')
                    ->limit($limit);
    }
}
