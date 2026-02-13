<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'slug',
        'code',
        'duration',
        'total_hours',
        'schedule',
        'description',
        'requirements',
        'fees_breakdown',
        'delivery_mode',
        'what_you_will_learn',
        'cover_image',
        'level',
        'featured',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relationship with Department
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Relationship with Intakes
     */
    public function intakes()
    {
        return $this->hasMany(CourseIntakes::class);
    }

    /**
     * Accessor for cover image URL
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return null;
    }

    /**
     * Scope for active courses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get or generate monthly intakes for this course
     */
    public function getMonthlyIntakes($yearsAhead = 2)
    {
        // First, ensure we have monthly intakes generated
        $this->generateMonthlyIntakes($yearsAhead);

        // Return the intakes
        return $this->intakes()
            ->where('is_active', true)
            ->orderBy('year', 'asc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')")
            ->get();
    }

    /**
     * Generate monthly intakes for the next X years
     */
    public function generateMonthlyIntakes($yearsAhead = 2)
    {
        $months = ['January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        // Generate for current year + next X years
        for ($year = $currentYear; $year <= $currentYear + $yearsAhead; $year++) {
            // For current year, start from current month
            // For future years, start from January
            $startMonth = ($year == $currentYear) ? $currentMonth : 1;

            for ($month = $startMonth; $month <= 12; $month++) {
                $monthName = $months[$month - 1];

                // Check if intake already exists for this month/year
                $existingIntake = $this->intakes()
                    ->where('month', $monthName)
                    ->where('year', $year)
                    ->first();

                if (!$existingIntake) {
                    // Calculate application deadline (15th of previous month)
                    $deadlineMonth = $month - 1;
                    $deadlineYear = $year;

                    if ($deadlineMonth < 1) {
                        $deadlineMonth = 12;
                        $deadlineYear = $year - 1;
                    }

                    $applicationDeadline = Carbon::create($deadlineYear, $deadlineMonth, 15);

                    // Create the monthly intake
                    $this->intakes()->create([
                        'month' => $monthName,
                        'year' => $year,
                        'application_deadline' => $applicationDeadline,
                        'notes' => 'Monthly intake - Applications close on the 15th of previous month',
                        'is_active' => true,
                        'created_by' => auth()->id() ?? 1,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]);
                }
            }
        }
    }

    /**
     * Get upcoming monthly intakes (next 12 months)
     */
    public function getUpcomingMonthlyIntakes($limit = 12)
    {
        $this->generateMonthlyIntakes();

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $months = ['January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];

        return $this->intakes()
            ->where('is_active', true)
            ->where(function($query) use ($currentYear, $currentMonth, $months) {
                $query->where('year', '>', $currentYear)
                      ->orWhere(function($q) use ($currentYear, $currentMonth, $months) {
                          $q->where('year', $currentYear)
                            ->whereRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') >= ?", [$currentMonth]);
                      });
            })
            ->orderBy('year', 'asc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')")
            ->limit($limit)
            ->get();
    }

    /**
     * Get current month's intake
     */
    public function getCurrentIntakeAttribute()
    {
        $currentMonth = Carbon::now()->format('F');
        $currentYear = Carbon::now()->year;

        return $this->intakes()
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get next available intake
     */
    public function getNextIntakeAttribute()
    {
        $intakes = $this->getUpcomingMonthlyIntakes(1);
        return $intakes->first();
    }

    /**
     * Check if course accepts monthly applications
     */
    public function getHasMonthlyIntakesAttribute(): bool
    {
        return true; // All courses have monthly intakes
    }

    /**
     * Get formatted delivery modes
     */
    public function getDeliveryModesArrayAttribute()
    {
        if (empty($this->delivery_mode)) {
            return [];
        }
        return array_map('trim', explode(',', $this->delivery_mode));
    }

    /**
     * Check if course has specific delivery mode
     */
    public function hasDeliveryMode($mode): bool
    {
        return in_array($mode, $this->delivery_modes_array);
    }
}
