<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Appointment;
use App\Models\User;

class CreateAppointmentEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance. haaaaamzaaaaaaa event
     */
    public Appointment $appointment;
    public User $user;
    public string $deviceToken;

    /**
     * Create a new event instance.
     */
    public function __construct(Appointment $appointment, User $user, string $deviceToken)
    {
        $this->appointment = $appointment;
        $this->user = $user;
        $this->deviceToken = $deviceToken;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
