<?php

namespace App\Http\Controllers\Api;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\TicketRepository;
use App\Services\Stats\CheckerStatsService;

class CheckerApiController extends Controller
{
    public function __construct(
        private TicketRepository $ticketRepo,
        private CheckerStatsService $statsService
    ) {}
    
    /**
     * Lista de eventos activos para validación
     */
    public function getActiveEvents(Request $request)
    {
        $events = $this->ticketRepo->getActiveEvents();
        return response()->json(['data' => $events]);
    }
    
    /**
     * Estadísticas del día para el checker autenticado
     */
    public function getTodayStats(Request $request)
    {
        $checker = $request->user();
        $stats = $this->statsService->getTodayStats($checker->id);
        
        return response()->json($stats);
    }
    
    /**
     * Historial de validaciones del checker
     */
    public function getValidationHistory(Request $request)
    {
        $checker = $request->user();
        
        $query = Ticket::where('validated_by', $checker->id)
            ->where('is_validated', true);
        
        // Filtros opcionales
        if ($request->has('event_name')) {
            $query->where('event_name', $request->event_name);
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('validated_at', '>=', $request->date_from); 
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('validated_at', '<=', $request->date_to); 
        }
        
        $tickets = $query->orderBy('validated_at', 'desc') 
            ->paginate(15);
        
        return response()->json($tickets);
    }
}