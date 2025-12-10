<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    // If you are using created_at / updated_at columns, keep this (default is true)
    public $timestamps = true;

    // Allow these fields to be mass-assigned
    protected $fillable = [
        'folder',
        'file_name',
        'user_name',
        'course_id',
        'serial_no',
        'user_id',
        'course',
        'template_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
