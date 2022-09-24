<?php

namespace App\Events;

use App\Models\chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReadMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $message;
    private $toUserSend;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message,$toUserSend)
    {
        $this->message = $message;
        $this->toUserSend = $toUserSend;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('Read.Messages.'.$this->toUserSend);
    }

    public function broadcastAs()
    {
        return "ReadMessageEvent";
    }
    public function broadcastWith(): array
    {
        return [
            "id_messages" => $this->message
        ];
    }

}
