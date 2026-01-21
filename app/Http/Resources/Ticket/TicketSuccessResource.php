<?php

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\Ticket\TicketResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketSuccessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'access_granted'=>$this->access_granted,
            'message'=>$this->message,
            'ticket_info'=>new TicketResource($this->ticket),
        ];
    }
}
