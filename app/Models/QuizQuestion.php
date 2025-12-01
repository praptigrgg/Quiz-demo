<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $table = 'quizzes_questions';

    protected $fillable = [
        'quiz_id',
        'questionText',
        'questionType',
        'isMandatory',
        'explanation',
        'isQuestionShuffle',
        'isAnswerShuffle',
        'group_name',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
     protected $guarded = [];

    public function options()
    {
        return $this->hasMany(QuizOption::class, 'question_id');
    }
    
    // Responses
    public function responses()
    {
        return $this->morphMany(MeetingResponse::class, 'questionable');
        // 'questionable' is the polymorphic name in meeting_responses
    }
}
