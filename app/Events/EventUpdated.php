<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EventUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(public $event)
    {
        $this->message = "Event '{$event->title}' has been updated";


        // Log the data being broadcasted
        Log::info('EventUpdated Constructor:', [
            'event_id' => $event->id,
            'message' => $this->message,
            'channel' => 'private-event-participants.' . $event->id
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     *  \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return
            new PrivateChannel('event-participants.' . $this->event->id);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message
        ];
    }
}
