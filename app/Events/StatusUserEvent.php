<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use phpDocumentor\Reflection\Types\Boolean;

class StatusUserEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $state,$id_user,$userCur;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($state,$id_user,$userCur)
    {
        $this->id_user = $id_user;
        $this->state = $state;
        $this->userCur = $userCur;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('Room.Chat.'.$this->id_user);
    }

    public function broadcastAs()
    {
        return "StatusEvent";
    }
    public function broadcastWith(): array
    {
        return [
            "id_user" => $this->userCur,
            "status" => $this->state
        ];
    }
}
