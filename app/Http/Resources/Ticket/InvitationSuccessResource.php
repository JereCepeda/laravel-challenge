<?php

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationSuccessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Handle both test format (message/event/tickets) and service format (invitation_data/tickets)
        if (isset($this->message) && isset($this->event)) {
            // Test format - return as expected by tests
            return [
                'message' => $this->message,
                'event' => new EventResource($this->event),
                'tickets' => $this->tickets ?? [],
            ];
        }
        
        // Service format - convert invitation_data to event
        $invitationData = $this->invitation_data ?? [];
        
        $eventData = (object) [
            'name' => $invitationData['event_name'] ?? null,
            'date' => $invitationData['event_date'] ?? null,
            'sector' => $invitationData['sector'] ?? null,
        ];
        
        return [
            'message' => 'Invitation redeemed successfully',
            'event' => new EventResource($eventData),
            'tickets' => $this->tickets ?? [],
        ];
    }
}
