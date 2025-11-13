<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'thumbnail',
        'demo_video',
        'description',
    ];
    public function sections()
{
    return $this->hasMany(CourseSection::class)->orderBy('order');
}
public function quizzes()
{
    return $this->belongsToMany(Quiz::class, 'course_section_quiz', 'course_id', 'quiz_id');
}

}
