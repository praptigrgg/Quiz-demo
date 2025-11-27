<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomSetQuestion extends Model
{
    protected $fillable = [
        'set_id', 'group_name', 'questionText',
        'questionType', 'isMandatory', 'explanation',
        'shuffleQuestions', 'shuffleAnswers'
    ];

    protected $casts = [
        'isMandatory' => 'boolean',
        'shuffleQuestions' => 'boolean',
        'shuffleAnswers' => 'boolean',
    ];

    public function set()
    {
        return $this->belongsTo(CustomSet::class, 'set_id');
    }

    public function options()
    {
        return $this->hasMany(CustomSetAnswer::class, 'question_id');
    }
}
