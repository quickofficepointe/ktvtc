<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'exam_body_id',
        'name',
        'code',
        'level',
        'duration_months',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'level' => 'integer',
        'duration_months' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function examBody()
    {
        return $this->belongsTo(ExamBody::class);
    }

    public function examRegistrations()
    {
        return $this->hasMany(ExamRegistration::class);
    }

    public function feeTemplates()
    {
        return $this->hasMany(CourseFeeTemplate::class);
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

    public function scopeByExamBody($query, $examBodyId)
    {
        return $query->where('exam_body_id', $examBodyId);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->examBody->name . ' - ' . $this->name;
    }
}
