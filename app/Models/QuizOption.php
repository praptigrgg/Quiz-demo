<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizOption extends Model
{
    use HasFactory;

    // Tell Laravel to use this table
    protected $table = 'quizzes_options';

    // Fillable fields
    protected $fillable = [
        'question_id',
        'optionText',
        'isCorrect'
    ];

    // Optional: define relationship to question
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
