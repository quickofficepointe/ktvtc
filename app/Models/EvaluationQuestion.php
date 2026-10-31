<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationQuestion extends Model
{
    protected $fillable = ['text', 'type', 'is_required', 'is_active', 'sort_order'];

    protected $casts = ['is_required' => 'boolean', 'is_active' => 'boolean'];

    public function answers()
    {
        return $this->hasMany(TrainerEvaluationAnswer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
