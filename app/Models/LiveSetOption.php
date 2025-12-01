<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveSetOption extends Model
{
    protected $fillable = ['question_id', 'optionText', 'isCorrect'];

    public function question() {
        return $this->belongsTo(LiveSetQuestion::class, 'question_id');
    }
}
