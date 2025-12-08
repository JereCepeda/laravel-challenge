<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Admin\MetricsService;
use App\Services\Admin\AdminReportService;
use App\Http\Requests\Admin\GetUsedTicketsRequest;
use App\Http\Requests\Admin\GetRedemptionHistoryRequest;

class AdminApiController extends Controller
{
    public function __construct(
        private AdminReportService $adminReportService,
        private MetricsService $metricsService
    ) {}
    
    public function getStatistics(Request $request)
    {
        $mainKpis = $this->metricsService->getMainKpis();
        $additionalMetrics = $this->metricsService->getAdditionalMetrics();
        
        return response()->json([
            'main_kpis' => $mainKpis,
            'additional_metrics' => $additionalMetrics,
            'last_updated' => now()->toISOString()
        ]);
    }
    

    public function getUsedTickets(GetUsedTicketsRequest $request)
    {
        $validated = $request->validated();
        info('Get Used Tickets Request', $validated);
        if ($request->has('draw')) {
            return $this->getUsedTicketsDataTable($request);
        }
        
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
     * Tickets usados para DataTables Server-Side Processing
     */
    private function getUsedTicketsDataTable(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start', 0);
        $length = $request->input('length', 15);
        $currentPage = ($start / $length) + 1;
        
        // Establecer la página actual para el paginador de Laravel
        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });
        
        $filters = [
            'event_name' => $request->input('event_name'),
            'sector' => $request->input('sector'),
            'event_date' => $request->input('event_date'),
        ];
        
        // Usar el AdminReportService para obtener tickets
        $tickets = $this->adminReportService->getUsedTicketsForEvent(
            eventName: $filters['event_name'] ?? '',
            filters: array_filter($filters),
            perPage: $length
        );
        
        // Transformar datos para DataTables
        $transformedData = collect($tickets->items())->map(function ($ticket) {
            return [
                'qr_code' => $ticket->ticket_code,
                'event_name' => $ticket->event_name,
                'event_date' => $ticket->event_date,
                'sector' => $ticket->sector,
                'user' => [
                    'name' => $ticket->validator?->name ?? 'N/A',
                    'email' => $ticket->validator?->email ?? ''
                ],
                'validated_at' => $ticket->validated_at?->toISOString()
            ];
        })->values()->all();
        
        Log::info('Used Tickets DataTable', ['filters' => $filters, 'draw' => $draw, 'start' => $start, 'length' => $length]);
        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $tickets->total(),
            'recordsFiltered' => $tickets->total(),
            'data' => $transformedData
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