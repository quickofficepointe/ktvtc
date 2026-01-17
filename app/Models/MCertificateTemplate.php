<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MCertificateTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_certificate_templates';
    protected $primaryKey = 'template_id';

    protected $fillable = [
        'template_name', 'template_code', 'description', 'template_file',
        'template_type', 'course_id', 'mobile_school_id', 'dynamic_fields',
        'layout_config', 'styling', 'watermark_text', 'background_image',
        'has_qr_code', 'qr_code_position', 'signature_line1', 'signature_image1',
        'signature_line2', 'signature_image2', 'validity_months', 'is_active',
        'auto_generate', 'requires_approval', 'approver_role_id', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'dynamic_fields' => 'array',
        'layout_config' => 'array',
        'styling' => 'array',
        'is_active' => 'boolean',
        'auto_generate' => 'boolean',
        'requires_approval' => 'boolean',
        'has_qr_code' => 'boolean',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(MCourse::class, 'course_id', 'course_id');
    }

    public function mobileSchool()
    {
        return $this->belongsTo(MobileSchool::class, 'mobile_school_id');
    }

    public function certificates()
    {
        return $this->hasMany(MCertificate::class, 'template_id', 'template_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helpers
    public function getDynamicField($fieldName)
    {
        return collect($this->dynamic_fields)->firstWhere('field_name', $fieldName);
    }

    public function hasField($fieldName)
    {
        return collect($this->dynamic_fields)->contains('field_name', $fieldName);
    }
}
