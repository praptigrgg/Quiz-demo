<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class QuizAssigned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $quiz;
    public $meetingId;

    public function __construct($quiz, $meetingId)
    {
        $this->quiz = $quiz;
        $this->meetingId = $meetingId;
    }

    public function broadcastOn()
    {
        return new Channel('zoom-meeting.' . $this->meetingId);
    }

    public function broadcastAs()
    {
        return 'QuizAssigned';
    }
}

