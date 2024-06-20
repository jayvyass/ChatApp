<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSendEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $receiver_id;

    public function __construct($message)
    {
        $this->message = $message;
        $this->receiver_id = $message->receiver_id;
    }

    public function broadcastOn()
    {
        return [new PrivateChannel('chat-channel.' . $this->receiver_id)];
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'receiver_id' => $this->receiver_id,
        ];
    }
    
}
