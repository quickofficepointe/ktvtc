<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalQuizAttempt extends Model
{
    protected $fillable = ['user_id', 'student_id', 'quiz', 'score', 'total', 'answers'];

    protected $casts = ['answers' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function percent(): int
    {
        return $this->total ? (int) round($this->score * 100 / $this->total) : 0;
    }
}
