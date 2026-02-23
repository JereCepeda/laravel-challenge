<?php

namespace App\Repositories;

use App\Models\Ticket;
use Illuminate\Support\Collection;

class TicketRepository
{

    public function getTotalCount(): int
    {
        return Ticket::count();
    }
    
    public function getValidatedCount(): int
    {
        return Ticket::where('is_validated', true)->count();
    }
    
    public function getUniqueEvents(): Collection
    {
        return Ticket::select('event_name', 'event_date', 'sector')
            ->groupBy('event_name', 'event_date', 'sector')
            ->orderBy('event_date', 'desc')
            ->get();
    }
    public function getFilterOptions(): Collection
    {
        return Ticket::select('event_name', 'event_date')
            ->distinct()
            ->groupBy('event_name', 'event_date')
            ->orderBy('event_name')
            ->get();
    }
    public function getActiveEvents(): Collection
    {
        return Ticket::select('event_name', 'event_date', 'sector')
            ->where('is_validated', false)
            ->whereDate('event_date', '>=', now())
            ->distinct()
            ->orderBy('event_date')
            ->get()
            ->groupBy('event_name')
            ->map(function ($tickets, $eventName) {
                return [
                    'event_name' => $eventName,
                    'event_date' => $tickets->first()->event_date,
                    'sectors' => $tickets->pluck('sector')->unique()->values(),
                    'pending_tickets' => $tickets->count()
                ];
            })
            ->values();
    }
    public function getRecentValidations(int $checkerId, int $limit = 5): Collection
    {
        return Ticket::where('validated_by', $checkerId)
            ->where('is_validated', true)
            ->orderBy('validation_date', 'desc')
            ->limit($limit)
            ->get();
    }
}
