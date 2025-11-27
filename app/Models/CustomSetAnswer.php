<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomSetAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'optionText',
        'subjectiveAnswer',
        'isCorrect',
    ];

    // Relationship to question
    public function question()
    {
        return $this->belongsTo(CustomSetQuestion::class, 'question_id');
    }
}
