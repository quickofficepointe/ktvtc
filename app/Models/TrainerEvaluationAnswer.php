<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerEvaluationAnswer extends Model
{
    protected $fillable = ['trainer_evaluation_id', 'evaluation_question_id', 'rating', 'answer'];

    public function question()
    {
        return $this->belongsTo(EvaluationQuestion::class, 'evaluation_question_id');
    }
}
