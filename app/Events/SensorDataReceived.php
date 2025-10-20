<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SensorDataReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $row;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $row  // the BehavioralData model instance or array
     */
    public function __construct($row)
    {
        $this->row = $row;
    }

    /**
     * Define the channel to broadcast on.
     */
    public function broadcastOn()
    {
        // public channel named "sensor-data"
        return new Channel('sensor-data');
    }

    /**
     * Customize payload sent to clients.
     */
    public function broadcastWith()
    {
        // ensure we serialize only needed fields
        return [
            'id' => $this->row->id ?? null,
            'value' => $this->row->value ?? null,
            'client_id' => $this->row->client_id ?? null,
            'barangay' => $this->row->barangay ?? null,
            'created_at' => (string) ($this->row->created_at ?? now()),
            'metric_name' => $this->row->metric_name ?? null,
            'meta' => $this->row->meta ?? null,
        ];
    }

    /**
     * Optional: event name when broadcasting (keeps it simple)
     */
    public function broadcastAs()
    {
        return 'SensorDataReceived';
    }
}
