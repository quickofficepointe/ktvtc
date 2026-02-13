<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseFeeTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'course_id',
        'exam_type',
        'total_terms',
        'duration_months',
        'intake_periods',
        'total_tuition_fee',
        'total_other_fees',
        'total_amount',
        'is_default',
        'is_active',
        'is_public',
        'description',
        'notes',
        'campus_id',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'total_terms' => 'integer',
        'duration_months' => 'integer',
        'total_tuition_fee' => 'decimal:2',
        'total_other_fees' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'intake_periods' => 'array',
    ];

    /**
     * ============ RELATIONSHIPS ============
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function feeItems()
    {
        return $this->hasMany(FeeTemplateItem::class, 'fee_template_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'fee_template_id');
    }

    /**
     * ============ SCOPES ============
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByExamType($query, $examType)
    {
        return $query->where('exam_type', $examType);
    }

    public function scopeByCampus($query, $campusId)
    {
        return $query->where(function($q) use ($campusId) {
            $q->whereNull('campus_id')
              ->orWhere('campus_id', $campusId);
        });
    }

    /**
     * ============ ACCESSORS ============
     */
    public function getExamTypeLabelAttribute()
    {
        return match($this->exam_type) {
            'nita' => 'NITA',
            'cdacc' => 'CDACC',
            'school_assessment' => 'School Assessment',
            'mixed' => 'Mixed',
            default => ucfirst($this->exam_type),
        };
    }

    public function getStatusColorAttribute()
    {
        if ($this->is_default) {
            return 'purple';
        }
        return $this->is_active ? 'green' : 'gray';
    }

    public function getStatusLabelAttribute()
    {
        if ($this->is_default) {
            return 'Default';
        }
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function getFormattedTotalAttribute()
    {
        return 'KES ' . number_format($this->total_amount, 2);
    }

    public function getIntakePeriodsListAttribute()
    {
        if (is_array($this->intake_periods)) {
            return implode(', ', $this->intake_periods);
        }
        return $this->intake_periods;
    }

    /**
     * ============ METHODS ============
     */
    public function calculateTotalAmount()
    {
        $total = $this->feeItems()->sum(\DB::raw('amount * quantity'));

        $tuitionTotal = $this->feeItems()
            ->whereHas('feeCategory', function($q) {
                $q->where('code', 'TUITION');
            })
            ->sum(DB::raw('amount * quantity'));

        $otherTotal = $total - $tuitionTotal;

        $this->total_tuition_fee = $tuitionTotal;
        $this->total_other_fees = $otherTotal;
        $this->total_amount = $total;
        $this->save();

        return $total;
    }

    public function isGlobal()
    {
        return is_null($this->campus_id);
    }

    public function isDefault()
    {
        return $this->is_default;
    }

    public function makeDefault()
    {
        // Remove default from other templates for this course+exam_type
        self::where('course_id', $this->course_id)
            ->where('exam_type', $this->exam_type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true, 'is_active' => true]);

        return $this;
    }
}
