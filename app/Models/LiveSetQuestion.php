<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveSetQuestion extends Model
{
    protected $fillable = ['live_set_id', 'questionType', 'questionText', 'isMandatory'];

    public function liveSet()
    {
        return $this->belongsTo(LiveSet::class);
    }

    public function options()
    {
        return $this->hasMany(LiveSetOption::class, 'question_id');
    }

    public function responses()
    {
        return $this->morphMany(MeetingResponse::class, 'questionable');
    }
    
}
