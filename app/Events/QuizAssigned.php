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

    public $quizTitle;

    public function __construct($quizTitle)
    {
        $this->quizTitle = $quizTitle;
    }

    public function broadcastOn()
    {
        return new Channel('zoom-chat');
    }

    public function broadcastAs()
    {
        return 'QuizAssigned';
    }
}
