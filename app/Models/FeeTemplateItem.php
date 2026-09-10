<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_template_id',
        'fee_category_id',
        'item_name',
        'description',
        'amount',
        'quantity',
        'applicable_terms',
        'is_required',
        'is_refundable',
        'due_day_offset',
        'is_advance_payment',
        'sort_order',
        'is_visible_to_student',
        'campus_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_required' => 'boolean',
        'is_refundable' => 'boolean',
        'is_advance_payment' => 'boolean',
        'is_visible_to_student' => 'boolean',
    ];

    // Relationships
    public function feeTemplate()
    {
        return $this->belongsTo(CourseFeeTemplate::class, 'fee_template_id');
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    // Methods
    public function appliesToTerm($termNumber)
    {
        if ($this->applicable_terms === 'all') {
            return true;
        }

        $terms = explode(',', $this->applicable_terms);
        return in_array($termNumber, $terms);
    }

    public function getTotalAmount()
    {
        return $this->amount * $this->quantity;
    }

    public function isTuition()
    {
        return $this->feeCategory->code === 'TUITION';
    }

    public function isExamination()
    {
        return $this->feeCategory->code === 'EXAMINATION';
    }
}
