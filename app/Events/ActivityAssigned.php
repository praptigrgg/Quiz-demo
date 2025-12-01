<?php

namespace App\Events;

use App\Models\MeetingAssignment;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ActivityAssigned implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $assignment_id;
    public $assignable_type;
    public $assignable;
    public $meeting_id;

    public function __construct(MeetingAssignment $assignment)
    {
        // Load related assignable with questions + options
        $assignment->load([
            'assignable.questions.options'
        ]);

        $this->assignment_id   = $assignment->id;
        $this->assignable_type = class_basename($assignment->assignable_type);
        $this->assignable      = $assignment->assignable->toArray();
        $this->meeting_id      = $assignment->meeting_id;

        Log::info("🔥 ActivityAssigned Event Constructed", [
            'meeting_id' => $this->meeting_id,
            'assignable_type' => $this->assignable_type,
        ]);
    }

public function broadcastOn()
{
    Log::info('ActivityAssigned::broadcastOn called for meeting_id: ' . $this->meeting_id);
    return new Channel('zoom-meeting.' . $this->meeting_id);
}



    public function broadcastAs()
    {
        return 'ActivityAssigned';
    }

    public function broadcastWith()
    {
        return [
            'assignment_id'   => $this->assignment_id,
            'assignable_type' => $this->assignable_type,
            'assignable'      => $this->assignable
        ];
    }
}
