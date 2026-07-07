<?php
// app/Models/Course.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'user_agent',
        // 🔒 Fee structure fields
        'fee_version',
        'fee_modified_by',
        'fee_modified_at',
        'fee_modification_reason',
        'previous_fee_structure',
        'fee_modification_approved_by',
        'fee_modification_approved_at',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'fees_breakdown' => 'array',
        'previous_fee_structure' => 'array',
        'fee_modified_at' => 'datetime',
        'fee_modification_approved_at' => 'datetime',
    ];

    /**
     * ============ RELATIONSHIPS ============
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function intakes()
    {
        return $this->hasMany(CourseIntakes::class);
    }

    public function feeModifiedBy()
    {
        return $this->belongsTo(User::class, 'fee_modified_by');
    }

    public function feeModificationApprovedBy()
    {
        return $this->belongsTo(User::class, 'fee_modification_approved_by');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * ============ FEE STRUCTURE METHODS ============
     */

    /**
     * Check if user can modify fee structure
     */
    public static function canUserModifyFees(User $user): bool
    {
        return in_array($user->role, [0, 7]); // Super Admin or Finance
    }

    /**
     * Update fee structure with audit trail
     */
    public function updateFeeStructure(array $newFees, string $reason, User $updatedBy): self
    {
        // Store previous structure
        $this->previous_fee_structure = $this->fees_breakdown;

        // Update with new structure
        $this->fees_breakdown = $newFees;

        // Increment version
        $this->fee_version = $this->incrementVersion();

        // Track modification
        $this->fee_modified_by = $updatedBy->id;
        $this->fee_modified_at = now();
        $this->fee_modification_reason = $reason;

        // Set approval status (Finance can self-approve)
        if ($updatedBy->role === 7) {
            $this->fee_modification_approved_by = $updatedBy->id;
            $this->fee_modification_approved_at = now();
        } else {
            // Website Admin changes need Finance approval
            $this->fee_modification_approved_by = null;
            $this->fee_modification_approved_at = null;
        }

        $this->save();

        // Log the change
        \Log::info('Course fee structure updated', [
            'course_id' => $this->id,
            'course_name' => $this->name,
            'old_fees' => $this->previous_fee_structure,
            'new_fees' => $newFees,
            'reason' => $reason,
            'updated_by' => $updatedBy->id,
            'updated_by_role' => $updatedBy->role,
            'version' => $this->fee_version,
        ]);

        return $this;
    }

    /**
     * Approve fee structure changes (Finance role only)
     */
    public function approveFeeStructure(User $approver): self
    {
        if ($approver->role !== 7 && $approver->role !== 0) {
            throw new \Exception('Only Finance or Super Admin can approve fee structure changes.');
        }

        $this->fee_modification_approved_by = $approver->id;
        $this->fee_modification_approved_at = now();

        $this->save();

        \Log::info('Course fee structure approved', [
            'course_id' => $this->id,
            'course_name' => $this->name,
            'approved_by' => $approver->id,
            'version' => $this->fee_version,
        ]);

        return $this;
    }

    /**
     * Check if fee structure is approved
     */
    public function isFeeStructureApproved(): bool
    {
        return $this->fee_modification_approved_by !== null &&
               $this->fee_modification_approved_at !== null;
    }

    /**
     * Check if fee structure has pending changes
     */
    public function hasPendingFeeChanges(): bool
    {
        return $this->fee_modified_by !== null &&
               $this->fee_modified_at !== null &&
               $this->fee_modification_approved_by === null;
    }

    /**
     * Increment fee version
     */
    private function incrementVersion(): string
    {
        $currentVersion = $this->fee_version ?? 'v1.0';
        $parts = explode('.', $currentVersion);

        if (count($parts) === 2) {
            $major = (int) $parts[0];
            $minor = (int) $parts[1] + 1;

            // If minor exceeds 9, increment major
            if ($minor > 9) {
                $major++;
                $minor = 0;
            }

            return 'v' . $major . '.' . $minor;
        }

        return 'v1.0';
    }

    /**
     * Get fee structure version history
     */
    public function getFeeVersionHistory(): array
    {
        return [
            'current_version' => $this->fee_version ?? 'v1.0',
            'modified_by' => $this->feeModifiedBy?->name,
            'modified_at' => $this->fee_modified_at?->toDateTimeString(),
            'approved_by' => $this->feeModificationApprovedBy?->name,
            'approved_at' => $this->fee_modification_approved_at?->toDateTimeString(),
            'has_pending_changes' => $this->hasPendingFeeChanges(),
            'is_approved' => $this->isFeeStructureApproved(),
        ];
    }

    /**
     * Get fee structure with version info
     */
    public function getFeeStructureWithVersion(): array
    {
        return [
            'fees' => $this->fees_breakdown,
            'version' => $this->fee_version ?? 'v1.0',
            'last_modified_by' => $this->feeModifiedBy?->name,
            'last_modified_at' => $this->fee_modified_at?->toDateTimeString(),
            'approved_by' => $this->feeModificationApprovedBy?->name,
            'approved_at' => $this->fee_modification_approved_at?->toDateTimeString(),
        ];
    }

    /**
     * Get the total fee from breakdown
     */
    public function getTotalFeeAttribute(): float
    {
        if (empty($this->fees_breakdown)) {
            return 0;
        }

        $total = 0;
        foreach ($this->fees_breakdown as $item) {
            if (isset($item['amount'])) {
                $total += (float) $item['amount'];
            }
        }
        return $total;
    }

    /**
     * Get fee breakdown for display
     */
    public function getFormattedFeeBreakdownAttribute(): array
    {
        if (empty($this->fees_breakdown)) {
            return [];
        }

        $formatted = [];
        foreach ($this->fees_breakdown as $key => $value) {
            if (is_array($value)) {
                $formatted[] = [
                    'label' => $key,
                    'amount' => isset($value['amount']) ? (float) $value['amount'] : 0,
                    'description' => $value['description'] ?? null,
                ];
            } else {
                $formatted[] = [
                    'label' => $key,
                    'amount' => (float) $value,
                    'description' => null,
                ];
            }
        }
        return $formatted;
    }

    /**
     * ============ SCOPES ============
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithPendingFeeChanges($query)
    {
        return $query->whereNotNull('fee_modified_by')
            ->whereNull('fee_modification_approved_by');
    }

    public function scopeApprovedFeeStructure($query)
    {
        return $query->whereNotNull('fee_modification_approved_by');
    }

    public function scopeUnapprovedFeeStructure($query)
    {
        return $query->whereNull('fee_modification_approved_by')
            ->whereNotNull('fee_modified_by');
    }

    /**
     * ============ ACCESSORS ============
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return null;
    }

    public function getDeliveryModesArrayAttribute()
    {
        if (empty($this->delivery_mode)) {
            return [];
        }
        return array_map('trim', explode(',', $this->delivery_mode));
    }

    public function getHasMonthlyIntakesAttribute(): bool
    {
        return true;
    }

    /**
     * ============ INTAKE METHODS ============
     */
    public function getAllIntakesSorted()
    {
        return $this->intakes()
            ->orderBy('year', 'desc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June',
                                 'July', 'August', 'September', 'October', 'November', 'December')")
            ->get();
    }

    public function getMonthlyIntakes($yearsAhead = 2)
    {
        $this->generateMonthlyIntakes($yearsAhead);
        return $this->intakes()
            ->where('is_active', true)
            ->orderBy('year', 'asc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June',
                                 'July', 'August', 'September', 'October', 'November', 'December')")
            ->get();
    }

    public function generateMonthlyIntakes($yearsAhead = 2)
    {
        $months = ['January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        for ($year = $currentYear; $year <= $currentYear + $yearsAhead; $year++) {
            $startMonth = ($year == $currentYear) ? $currentMonth : 1;

            for ($month = $startMonth; $month <= 12; $month++) {
                $monthName = $months[$month - 1];

                $existingIntake = $this->intakes()
                    ->where('month', $monthName)
                    ->where('year', $year)
                    ->first();

                if (!$existingIntake) {
                    $deadlineMonth = $month - 1;
                    $deadlineYear = $year;

                    if ($deadlineMonth < 1) {
                        $deadlineMonth = 12;
                        $deadlineYear = $year - 1;
                    }

                    $applicationDeadline = Carbon::create($deadlineYear, $deadlineMonth, 15);

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

    public function getUpcomingMonthlyIntakes($limit = 12)
    {
        $this->generateMonthlyIntakes();
        return $this->intakes()
            ->where('is_active', true)
            ->where('year', '>=', 2026)
            ->orderBy('year', 'asc')
            ->orderByRaw("FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June',
                                 'July', 'August', 'September', 'October', 'November', 'December')")
            ->limit($limit)
            ->get();
    }

    public function hasDeliveryMode($mode): bool
    {
        return in_array($mode, $this->delivery_modes_array);
    }
}
