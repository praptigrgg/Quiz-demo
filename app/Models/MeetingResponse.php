<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingResponse extends Model
{
    use HasFactory;

    // Make sure all fields you receive are fillable
    protected $fillable = [
        'meeting_assignment_id',
        'student_id',
        'questionable_id',
        'questionable_type',
        'selected_option_id',
        'subjective_answer',
        'is_correct',
        'score',
        'elapsed_time', // NEW

    ];

    // Polymorphic relation for quiz or custom questions
    public function questionable()
    {
        return $this->morphTo();
    }

    public function assignment()
    {
        return $this->belongsTo(MeetingAssignment::class, 'meeting_assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function selectedOption()
    {
        return $this->belongsTo(LiveSetOption::class, 'selected_option_id');
    }
}
