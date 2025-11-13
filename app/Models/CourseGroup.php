<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'order',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
