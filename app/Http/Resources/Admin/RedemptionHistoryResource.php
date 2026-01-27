<?php
// app/Http/Resources/Admin/RedemptionHistoryResource.php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RedemptionHistoryResource extends JsonResource
{
    private bool $forDataTable = false;

    public static function forDataTable($resource): self
    {
        $instance = new static($resource);
        $instance->forDataTable = true;
        return $instance;
    }

    public function toArray(Request $request): array
    {
        $data = [
            'guest_count' => $this->guest_count,
            'tickets_generated' => $this->tickets_generated,
            'event_name' => $this->event_name,
            'redeemed_at' => $this->redeemed_at?->format('Y-m-d H:i:s'),
            'sector' => $this->sector,
        ];

        if ($this->forDataTable) {
            return array_merge(['invitation_code' => $this->invitation_id], $data, ['redeemed_by' => 'Admin']);
        }

        return array_merge([
            'id' => $this->id,
            'invitation_id' => $this->invitation_id]
            , $data, [
            'event_date' => $this->event_date?->format('Y-m-d H:i'),
            'redeemed_ip' => $this->when($request->user()?->hasRole('admin'),
                            $this->redeemed_ip)]);
    }
}