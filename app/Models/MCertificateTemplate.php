<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MCertificateTemplate extends Model
{
    use HasFactory;

    protected $table = 'm_certificate_templates';
    protected $primaryKey = 'template_id';

    protected $fillable = [
        'template_name',
        'template_type',
        'template_file',
        'name_x',
        'name_y',
        'course_x',
        'course_y'
    ];

    // Relationships
    public function certificates()
    {
        return $this->hasMany(MCertificate::class, 'template_id', 'template_id');
    }

    // Helper methods
    public function getPositions()
    {
        return [
            'name' => ['x' => $this->name_x, 'y' => $this->name_y],
            'course' => ['x' => $this->course_x, 'y' => $this->course_y]
        ];
    }

    public function getTypeName()
    {
        return ucfirst($this->template_type);
    }
}
