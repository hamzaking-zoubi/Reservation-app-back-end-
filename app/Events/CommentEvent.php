<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $review,$profile,$name,$path_photo;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($name,$path_photo,$review)
    {
        $this->name = $name;
        $this->path_photo = $path_photo;
        $this->review = $review;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('User.Comment.Facility.'.$this->review->id_facility);
    }
    public function broadcastAs():string
    {
        return "CommentEvent";
    }
    public function broadcastWith():array
    {
        return [
            "comment" => $this->review,
            "user" => [
                "name" => $this->name,
                "path" => $this->path_photo
            ]
        ];
    }
}
