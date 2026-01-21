<?php

namespace App\Http\Resources\Ticket;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code'=>$this->ticket_code,
            'event'=>$this->event_name,
            'sector'=>$this->sector,
            'validated_at'=>$this->when($this->is_validated, $this->validated_at),
        ];
    }
}
