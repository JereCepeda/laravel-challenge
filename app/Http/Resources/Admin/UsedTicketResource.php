<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsedTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_code' => $this->ticket_code,
            'event_name' => $this->event_name,
            'event_date' => $this->event_date?->format('Y-m-d'),
            'sector' => $this->sector,
            'validator' => [
                'id' => $this->validator?->id,
                'name' => $this->validator?->name ?? 'N/A',
                'email' => $this->validator?->email ?? 'N/A',
            ],
            'is_validated' => $this->is_validated,
            'validated_at' => $this->validated_at?->toISOString(),
            'validation_ip' => $this->validation_ip,
        ];
    }
}