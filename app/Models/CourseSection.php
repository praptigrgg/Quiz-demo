<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function quizzes()
{
    return $this->belongsToMany(Quiz::class, 'course_section_quiz');
}
public function lessons()
{
    return $this->hasMany(Lesson::class)->orderBy('order');
}

}
