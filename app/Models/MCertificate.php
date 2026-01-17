<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MCertificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_certificates';
    protected $primaryKey = 'certificate_id';

    protected $fillable = [
        'template_id', 'student_id', 'enrollment_id', 'course_id',
        'certificate_number', 'serial_number', 'certificate_data',
        'generated_pdf_path', 'file_size', 'file_hash', 'issue_date',
        'expiry_date', 'generated_at', 'status', 'is_verified',
        'verification_url', 'qr_code_data', 'issued_by', 'issuance_remarks',
        'is_revoked', 'revoked_date', 'revoked_by', 'revocation_reason',
        'digital_signature', 'signature_timestamp', 'allow_download',
        'allow_sharing', 'download_count', 'view_count', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'certificate_data' => 'array',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'generated_at' => 'datetime',
        'revoked_date' => 'date',
        'is_verified' => 'boolean',
        'is_revoked' => 'boolean',
        'allow_download' => 'boolean',
        'allow_sharing' => 'boolean',
    ];

    // Relationships
    public function template()
    {
        return $this->belongsTo(MCertificateTemplate::class, 'template_id', 'template_id');
    }

    public function student()
    {
        return $this->belongsTo(MStudent::class, 'student_id', 'student_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(MEnrollment::class, 'enrollment_id', 'enrollment_id');
    }

    public function course()
    {
        return $this->belongsTo(MCourse::class, 'course_id', 'course_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // Helpers
    public function getFieldValue($fieldName)
    {
        return $this->certificate_data[$fieldName] ?? null;
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isValid()
    {
        return !$this->is_revoked && !$this->isExpired() && $this->status === 'issued';
    }

    public function incrementViewCount()
    {
        $this->view_count++;
        $this->save();
    }

    public function incrementDownloadCount()
    {
        $this->download_count++;
        $this->save();
    }
}
