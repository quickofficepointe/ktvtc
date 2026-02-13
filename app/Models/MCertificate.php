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
        'template_id',
        'student_id',
        'enrollment_id',
        'course_id',
        'certificate_number',
        'generated_pdf_path',
        'issue_date',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'issue_date' => 'date'
    ];

    // Relationships - keep only what we need
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

    // Simple scopes
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeForTemplate($query, $templateId)
    {
        return $query->where('template_id', $templateId);
    }

    // Helper methods
    public function getStudentName()
    {
        return $this->student ? $this->student->first_name . ' ' . $this->student->last_name : 'Unknown';
    }

    public function getCourseName()
    {
        return $this->course ? $this->course->course_name : 'Unknown';
    }

    public function getTemplateType()
    {
        return $this->template ? $this->template->template_type : 'Unknown';
    }

    public function getTemplateName()
    {
        return $this->template ? $this->template->template_name : 'Unknown';
    }

    public function hasPdf()
    {
        return !empty($this->generated_pdf_path);
    }

    public function getPdfUrl()
    {
        return $this->generated_pdf_path ? asset('storage/' . $this->generated_pdf_path) : null;
    }

    // Check if certificate already exists for student+course+template
    public static function existsFor($studentId, $courseId, $templateId)
    {
        return self::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->where('template_id', $templateId)
            ->exists();
    }
}
