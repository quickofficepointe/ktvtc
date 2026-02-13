<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'frequency',
        'is_refundable',
        'is_mandatory',
        'is_taxable',
        'is_active',
        'sort_order',
        'icon',
        'color',
        'suggested_items',
        'campus_id',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'suggested_items' => 'array',
        'is_refundable' => 'boolean',
        'is_mandatory' => 'boolean',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('campus_id');
    }

    public function scopeForCampus($query, $campusId)
    {
        return $query->where(function($q) use ($campusId) {
            $q->whereNull('campus_id')
              ->orWhere('campus_id', $campusId);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    // Methods
    public function isGlobal()
    {
        return is_null($this->campus_id);
    }

    public function getSuggestedItemsList()
    {
        return $this->suggested_items ?? [];
    }

    public function isPerTerm()
    {
        return $this->frequency === 'per_term';
    }

    public function isOnce()
    {
        return $this->frequency === 'once';
    }

    public function getFrequencyText()
    {
        return match($this->frequency) {
            'once' => 'One Time',
            'per_term' => 'Per Term',
            'per_year' => 'Per Year',
            'per_month' => 'Per Month',
            'per_course' => 'Per Course',
            default => ucfirst(str_replace('_', ' ', $this->frequency)),
        };
    }
}
