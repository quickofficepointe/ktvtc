<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial  extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_path',
        'review',
        'rating',
        'is_approved',
        'approved_by',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
