<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\JsonResource;

class UsedTicketDataTableResource extends JsonResource
{
    /**
     * Transform the resource into an array for DataTables.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ticket_code' => $this->ticket_code, // Frontend usa 'ticket_code' ahora
            'event_name' => $this->event_name,
            'event_date' => $this->event_date?->format('Y-m-d'),
            'sector' => $this->sector
        ];
    }
}