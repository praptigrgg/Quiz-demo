<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'assignable_id',
        'assignable_type',
        'assigned_at',

        // ⭐ TIMER ADDED
        'timer',
    ];

    // Polymorphic relation to Quiz or LiveSet
    public function assignable()
    {
        return $this->morphTo();
    }

    // Meeting responses
    public function responses()
    {
        return $this->hasMany(MeetingResponse::class);
    }

    protected $casts = [
        'assigned_at' => 'datetime',

        // ⭐ CAST TIMER (ensures integer)
        'timer' => 'integer',
    ];
}
