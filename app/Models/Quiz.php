<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'course_id',
        'quizTitle',
        'quizSlug',
        'quizDescription',
        'quizDuration',
        'quizTotalMarks',
        'quizPassMark',
        'correctAnswerMarks',
        'negativeMarking',
        'pricingType',
        'quizThumbnail',
        'valid_days_after_enrollment',
        'normal_price',
        'discount_price',
        'discount_start_date',
        'discount_end_date',
        'is_one_time',
        'is_live_quiz',
        'live_start_time',
        'live_end_time',
        'is_quiz_group',
        'quiz_groups',
        'enableReview',
        'showInListQuiz',
        'is_publish',
        'created_by',
    ];

    protected $casts = [
        'quiz_groups' => 'array',
        'is_one_time' => 'boolean',
        'is_live_quiz' => 'boolean',
        'is_quiz_group' => 'boolean',
        'enableReview' => 'boolean',
        'showInListQuiz' => 'boolean',
        'is_publish' => 'boolean',
        'discount_start_date' => 'date',
        'discount_end_date' => 'date',
        'live_start_time' => 'datetime',
        'live_end_time' => 'datetime',
        'normal_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

  public function questions()
{
    return $this->hasMany(QuizQuestion::class, 'quiz_id');
}
public function getGroupsAttribute()
{
    return $this->quiz_groups ?? [];
}
public function courses()
{
    return $this->belongsToMany(Course::class, 'course_section_quiz', 'quiz_id', 'course_id');
}



}
