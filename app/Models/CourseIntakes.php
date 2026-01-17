<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseIntakes extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'course_intakes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'application_deadline' => 'datetime',
        'is_active' => 'boolean',
        'year' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
  

    /**
     * Get the course that owns the intake.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the user who created the intake.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the intake.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include active intakes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include upcoming intakes.
     */
    public function scopeUpcoming($query)
    {
        return $query->where(function($q) {
            $q->whereNull('application_deadline')
              ->orWhere('application_deadline', '>=', now());
        });
    }

    /**
     * Scope a query to order by year and month.
     */
    public function scopeOrderByIntake($query)
    {
        return $query->orderBy('year', 'desc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')");
    }

    /**
     * Check if the intake is currently open for applications.
     */
    public function getIsApplicationOpenAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->application_deadline === null) {
            return true;
        }

        return now()->lte($this->application_deadline);
    }

    /**
     * Get the full intake period as a string.
     */
    public function getIntakePeriodAttribute(): string
    {
        return "{$this->month} {$this->year}";
    }

    /**
     * Get the days remaining until application deadline.
     */
    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (!$this->application_deadline) {
            return null;
        }

        return now()->diffInDays($this->application_deadline, false);
    }

    /**
     * Get the application status.
     */
    public function getApplicationStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'Closed';
        }

        if ($this->is_application_open) {
            $days = $this->days_until_deadline;
            if ($days !== null && $days <= 7) {
                return 'Closing Soon';
            }
            return 'Open';
        }

        return 'Closed';
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->application_status) {
            'Open' => 'green',
            'Closing Soon' => 'orange',
            'Closed' => 'red',
            default => 'gray'
        };
    }

    /**
     * Boot function for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
