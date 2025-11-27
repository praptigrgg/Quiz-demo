<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomSet extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'course_id',
        'pricingType', 'valid_days_after_enrollment',
        'normal_price', 'discount_price', 'discount_start_date',
        'discount_end_date', 'enrollCount', 'duration',
        'totalMarks', 'passMark', 'marks_per_correct',
        'negative_marking', 'is_one_time', 'is_live',
        'live_start_time', 'live_end_time', 'has_groups',
        'groups', 'thumbnail', 'selectedThumbnailId',
        'enable_review', 'is_published', 'show_in_list',
        'created_by'
    ];

    protected $casts = [
        'groups' => 'array',
        'is_published' => 'boolean',
        'is_live' => 'boolean',
        'has_groups' => 'boolean',
    ];

    // Relationships
    public function questions()
    {
        return $this->hasMany(CustomSetQuestion::class, 'set_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
