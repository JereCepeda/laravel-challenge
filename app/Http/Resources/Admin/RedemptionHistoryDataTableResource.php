<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RedemptionHistoryDataTableResource extends JsonResource
{
    /**
     * Transform the resource into an array for DataTables.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'invitation_code' => $this->invitation_id,
            'guest_count' => $this->guest_count,
            'tickets_generated' => $this->tickets_generated,
            'event_name' => $this->event_name,
            'redeemed_at' => $this->redeemed_at?->format('Y-m-d H:i:s'),
            'sector' => $this->sector,
            'redeemed_by' => 'Admin' 
        ];
    }
}
