<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;

class AuditLogCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auditLog;

    public function __construct(AuditLog $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    // In App\Events\AuditLogCreatedEvent
    public function broadcastAs()
    {
        return 'audit-log.created-event';
    }
    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        Log::info('Broadcasting to channel:', ['channel' => 'audit-logs.' . $this->auditLog->doctor_id]);
        return new PrivateChannel('audit-logs.' . $this->auditLog->doctor_id);
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        $data = [
            'id' => $this->auditLog->id,
            'action' => $this->auditLog->action,
            'description' => $this->auditLog->description,
            'old_values' => $this->auditLog->old_values,
            'new_values' => $this->auditLog->new_values,
            'created_at' => $this->auditLog->created_at->toDateTimeString(),
        ];
        Log::info('Broadcasting data:', $data);
        return $data;
    }
}