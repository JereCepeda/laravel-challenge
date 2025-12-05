<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\AdminReportService;
use App\Http\Requests\Admin\GetUsedTicketsRequest;
use App\Http\Requests\Admin\GetRedemptionHistoryRequest;

class AdminApiController extends Controller
{
    public function __construct(
        private AdminReportService $adminReportService
    ) {}
    
    /**
     * Estadísticas generales del dashboard
     */
    public function getStatistics(Request $request)
    {
        $stats = [
            'total_tickets_validated' => \App\Models\Ticket::where('is_validated', true)->count(),
            'total_invitations_redeemed' => \App\Models\InvitationRedemption::count(),
            'active_events' => \App\Models\Ticket::distinct('event_name')->count('event_name'),
            'recent_validations' => \App\Models\Ticket::where('is_validated', true)
                ->latest('validated_at')
                ->take(5)
                ->get(['ticket_code', 'event_name', 'validated_at'])
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Tickets usados con filtros y paginación - usando GetUsedTicketsRequest
     */
    public function getUsedTickets(GetUsedTicketsRequest $request)
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 15;
        
        // Usar el AdminReportService para obtener tickets
        $tickets = $this->adminReportService->getUsedTicketsForEvent(
            eventName: $validated['event_name'] ?? '',
            filters: $validated,
            perPage: $perPage
        );
        
        if ($tickets->isEmpty()) {
            return response()->json([
                'message' => 'No used tickets found',
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0
                ]
            ], 200);
        }
        
        return response()->json([
            'message' => 'Used tickets retrieved successfully',
            'data' => $tickets->items(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total()
            ]
        ]);
    }
    
    /**
     * Historial de canjes - usando GetRedemptionHistoryRequest
     */
    public function getRedemptionsHistory(GetRedemptionHistoryRequest $request)
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 15;
        
        // Usar el AdminReportService para obtener redemptions
        $redemptions = $this->adminReportService->getRedemptionHistory(
            filters: $validated,
            perPage: $perPage
        );
        
        if ($redemptions->isEmpty()) {
            return response()->json([
                'message' => 'No redemption history found',
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0
                ]
            ], 200);
        }
        
        return response()->json([
            'message' => 'Redemption history retrieved successfully',
            'data' => $redemptions->items(),
            'meta' => [
                'current_page' => $redemptions->currentPage(),
                'last_page' => $redemptions->lastPage(),
                'per_page' => $redemptions->perPage(),
                'total' => $redemptions->total()
            ]
        ]);
    }
    
    /**
     * Reportes del sistema
     */
    public function getReports(Request $request)
    {
        // Implementar lógica de reportes según necesites
        return response()->json([
            'message' => 'Reports endpoint - implement as needed'
        ]);
    }
}