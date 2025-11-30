<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketValidated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public ?User $validator = null,
        public string $validationIp = 'unknown',
        public array $metadata = []
    ) {
        $this->validator = $validator ?? $ticket->validator;
        
        $this->metadata = array_merge([
            'timestamp' => now()->toISOString(),
            'user_agent' => request()->userAgent() ?? 'Unknown',
            'method' => 'api_validation',
        ], $metadata);
    }
}