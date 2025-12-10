<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Admin\MetricsService;
use App\Services\Admin\AdminReportService;
use App\Http\Resources\Admin\UsedTicketResource;
use App\Http\Requests\Admin\GetUsedTicketsRequest;
use App\Http\Requests\Admin\GetRedemptionHistoryRequest;
use App\Http\Resources\Admin\UsedTicketDataTableResource;
use App\Http\Requests\Admin\GetUsedTicketsDataTableRequest;
use App\Http\Requests\Admin\GetRedemptionHistoryDataTableRequest;

class AdminApiController extends Controller
{
    public function __construct(
        private AdminReportService $adminReportService,
        private MetricsService $metricsService
    ) {}
    
    /**
     * Estadísticas del dashboard
     */
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
    
    public function getUsedTickets(Request $request, ?string $event_name = null)
    {
        if ($request->has('draw')) {
            return $this->getUsedTicketsDataTable(
                app(GetUsedTicketsDataTableRequest::class)
            );
        }
        
        return $this->getUsedTicketsApi(
            app(GetUsedTicketsRequest::class),
            $event_name
        );
    }

    /**
     * Tickets usados para API REST estándar
     */
    private function getUsedTicketsApi(GetUsedTicketsRequest $request, ?string $event_name = null)
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 15;
        
        $eventNameToSearch = $event_name ?? $validated['event_name'] ?? '';
        
        $tickets = $this->adminReportService->getUsedTicketsForEvent(
            eventName: $eventNameToSearch,
            filters: $validated,
            perPage: $perPage
        );
        
        if ($tickets->isEmpty()) {
            return response()->json([
                'message' => 'No used tickets found',
                'event_searched' => $eventNameToSearch ?: null,
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                    'has_more_pages' => false
                ]
            ], 200);
        }
        
        return response()->json([
            'message' => 'Used tickets retrieved successfully',
            'event_searched' => $eventNameToSearch ?: null,
            'data' => UsedTicketResource::collection($tickets->items()),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'has_more_pages' => $tickets->hasMorePages()
            ]
        ]);
    }
    
    /**
     * Tickets usados para DataTables Server-Side Processing
     */
    private function getUsedTicketsDataTable(GetUsedTicketsDataTableRequest $request)
    {
        $validated = $request->validated();
        $draw = $validated['draw'];
        $start = $validated['start'];
        $length = $validated['length'];
        $currentPage = ($start / $length) + 1;
        
        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });
        $filters = [
            'event_name' => $validated['event_name'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
        ];
        info('DataTables filtros recibidos: ' . json_encode($filters));
        $tickets = $this->adminReportService->getUsedTicketsForEvent(
            eventName: $filters['event_name'] ?? '',
            filters: array_filter($filters),
            perPage: $length
        );
        
        $transformedData = UsedTicketDataTableResource::collection($tickets->items());
        
        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $tickets->total(),
            'recordsFiltered' => $tickets->total(),
            'data' => $transformedData
        ]);
    }
    
    /**
     * Historial de canjes/redenciones
     */
    public function getRedemptionsHistory(Request $request)
    {
        if($request->has('draw')) {
            return $this->getRedemptionsHistoryDataTable(
                app(GetRedemptionHistoryDataTableRequest::class)
            );

        }
        return $this->getRedemptionsHistoryApi(
            app(GetRedemptionHistoryRequest::class)
        );
    }
    public function getRedemptionsHistoryApi(GetRedemptionHistoryRequest $request)
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 15;
        
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
                    'total' => 0,
                    'has_more_pages' => false
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
                'total' => $redemptions->total(),
                'has_more_pages' => $redemptions->hasMorePages()
            ]
        ]);
    }
    
    public function getRedemptionsHistoryDataTable(GetRedemptionHistoryDataTableRequest $request)
    {
        $validated = $request->validated();
        $draw = $validated['draw'];
        $start = $validated['start'];
        $length = $validated['length'];
        $currentPage = ($start / $length) + 1;
        
        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });
        
        $filters = [
            'event_name' => $validated['event_name'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
        ];
        
        $redemptions = $this->adminReportService->getRedemptionHistory(
            filters: array_filter($filters),
            perPage: $length
        );
        
        $transformedData = \App\Http\Resources\Admin\RedemptionHistoryDataTableResource::collection($redemptions->items());
        
        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $redemptions->total(),
            'recordsFiltered' => $redemptions->total(),
            'data' => $transformedData
        ]);
    }


    public function getMetrics(Request $request)
    {
        return response()->json([
            'message' => 'Metrics endpoint - implement as needed'
        ]);
    }
    public function getReports(Request $request)
    {
        return response()->json([
            'message' => 'Reports endpoint - implement as needed'
        ]);
    }

}