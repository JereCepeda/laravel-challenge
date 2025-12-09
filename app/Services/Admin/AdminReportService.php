<?php

namespace App\Services\Admin;

use App\Models\Ticket;
use App\Models\InvitationRedemption;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminReportService
{
    /**
     * Requirement 1: List used tickets for a specific event, with pagination
     */
    public function getUsedTicketsForEvent(string $eventName,array $filters = [],int $perPage = 15): LengthAwarePaginator 
    {        
        $query = Ticket::with(['invitationRedemption', 'validator'])
                ->where('is_validated', true)
                ->where('event_name', 'like', '%' . $eventName . '%')
                ->orderBy('validated_at', 'desc');

        if (!empty($filters['sector'])) {
            $query->where('sector', $filters['sector']);
        }

        if (!empty($filters['event_date'])) {
            $query->whereDate('event_date', $filters['event_date']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('validated_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('validated_at', '<=', $filters['to_date']);
        }
        return $query->paginate($perPage);
    }

    public function getRedemptionHistory(array $filters = [],int $perPage = 15): LengthAwarePaginator {
        $query = InvitationRedemption::with('tickets')->orderBy('redeemed_at', 'desc');

        if (!empty($filters['event_name'])) {
            $query->where('event_name', 'like', '%' . $filters['event_name'] . '%');
        }

        if (!empty($filters['sector'])) {
            $query->where('sector', $filters['sector']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('redeemed_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('redeemed_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['event_date'])) {
            $query->whereDate('event_date', $filters['event_date']);
        }

        if (!empty($filters['tickets_generated'])) {
            $query->where('tickets_generated', $filters['tickets_generated']);
        }

        return $query->paginate($perPage);
    }
}