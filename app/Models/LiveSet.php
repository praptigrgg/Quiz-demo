<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveSet extends Model
{
    protected $fillable = ['title', 'description', 'timer', 'created_by'];

    public function questions()
    {
        return $this->hasMany(LiveSetQuestion::class);
    }

    public function meetingAssignments()
    {
        return $this->morphMany(MeetingAssignment::class, 'assignable');
    }
}
