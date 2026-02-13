<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CourseIntakes extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'course_intakes';

    protected $fillable = [
        'course_id',
        'month',
        'year',
        'application_deadline',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'application_deadline' => 'datetime',
        'is_active' => 'boolean',
        'year' => 'integer',
    ];

    /**
     * Relationship with Course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope for active intakes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for dynamically available intakes based on current date
     */
    public function scopeAvailable($query)
    {
        $now = Carbon::now();
        $currentDay = $now->day;
        $currentMonth = $now->format('F'); // Full month name
        $currentYear = $now->year;

        // Month order for sorting
        $monthOrder = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        return $query->where('is_active', true)
            ->where(function($q) use ($now, $currentDay, $currentMonth, $currentYear, $monthOrder) {
                // Get current month index
                $currentMonthIndex = array_search($currentMonth, $monthOrder);

                // If day <= 10, include current month
                if ($currentDay <= 10) {
                    $q->where(function($subQ) use ($currentYear, $currentMonthIndex, $monthOrder) {
                        // Years greater than current year
                        $subQ->where('year', '>', $currentYear)
                            ->orWhere(function($yearQ) use ($currentYear, $currentMonthIndex, $monthOrder) {
                                // Current year, months from current month onward
                                $yearQ->where('year', $currentYear);

                                // Get months from current month to December
                                $eligibleMonths = array_slice($monthOrder, $currentMonthIndex);
                                if (!empty($eligibleMonths)) {
                                    $yearQ->whereIn('month', $eligibleMonths);
                                }
                            });
                    });
                } else {
                    // If day > 10, start from next month
                    $nextMonthIndex = ($currentMonthIndex + 1) % 12;
                    $nextYear = $currentYear;
                    if ($nextMonthIndex == 0) {
                        $nextYear = $currentYear + 1;
                    }

                    $q->where(function($subQ) use ($currentYear, $nextYear, $nextMonthIndex, $monthOrder) {
                        // Years greater than next year
                        $subQ->where('year', '>', $nextYear)
                            ->orWhere(function($yearQ) use ($currentYear, $nextYear, $nextMonthIndex, $monthOrder) {
                                if ($nextYear == $currentYear) {
                                    // Same year, months from next month onward
                                    $yearQ->where('year', $currentYear);
                                    $eligibleMonths = array_slice($monthOrder, $nextMonthIndex);
                                    if (!empty($eligibleMonths)) {
                                        $yearQ->whereIn('month', $eligibleMonths);
                                    }
                                } else {
                                    // Next year, all months
                                    $yearQ->where('year', $nextYear);
                                }
                            });
                    });
                }
            })
            ->where(function($q) use ($now) {
                // Check application deadline
                $q->whereNull('application_deadline')
                  ->orWhere('application_deadline', '>=', $now);
            })
            ->orderBy('year', 'asc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')");
    }

    /**
     * Get month_year attribute
     */
    public function getMonthYearAttribute(): string
    {
        return $this->month . ' ' . $this->year;
    }

    /**
     * Get display name for intake
     */
    public function getDisplayNameAttribute(): string
    {
        $display = $this->month . ' ' . $this->year;

        if ($this->application_deadline) {
            $display .= ' (Apply by: ' . $this->application_deadline->format('M j') . ')';
        }

        return $display;
    }

    /**
     * Check if applications are open
     */
    public function getIsApplicationOpenAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->application_deadline) {
            return true;
        }

        return now()->lte($this->application_deadline);
    }

    /**
     * Get intake status
     */
    public function getStatusAttribute(): string
    {
        $currentMonth = date('F');
        $currentYear = date('Y');

        if ($this->year < $currentYear) {
            return 'past';
        }

        if ($this->year == $currentYear) {
            $months = ['January', 'February', 'March', 'April', 'May', 'June',
                      'July', 'August', 'September', 'October', 'November', 'December'];

            $currentMonthIndex = array_search($currentMonth, $months);
            $intakeMonthIndex = array_search($this->month, $months);

            if ($intakeMonthIndex < $currentMonthIndex) {
                return 'past';
            } elseif ($intakeMonthIndex == $currentMonthIndex) {
                return 'current';
            } else {
                return 'upcoming';
            }
        }

        return 'upcoming';
    }

    /**
     * Get formatted application deadline
     */
    public function getFormattedDeadlineAttribute(): ?string
    {
        return $this->application_deadline
            ? $this->application_deadline->format('M j, Y')
            : null;
    }

    /**
     * Get days until deadline
     */
    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (!$this->application_deadline) {
            return null;
        }

        return now()->diffInDays($this->application_deadline, false);
    }
}
