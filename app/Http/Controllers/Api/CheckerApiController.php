<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckerApiController extends Controller
{
    /**
     * Lista de eventos activos para validación
     */
    public function getActiveEvents(Request $request)
    {
        $events = \App\Models\Ticket::select('event_name', 'event_date', 'sector')
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
        
        return response()->json(['data' => $events]);
    }
}