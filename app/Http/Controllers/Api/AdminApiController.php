<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Services\Admin\MetricsService;
use App\Services\Admin\AdminReportService;
use App\Http\Resources\Admin\UsedTicketCollection;
use App\Http\Resources\Admin\RedemptionHistoryResource;
use App\Http\Resources\Admin\RedemptionHistoryCollection;
use App\Http\Resources\Admin\UsedTicketDataTableResource;
use App\Http\Requests\Admin\GetUsedTicketsRequest;
use App\Http\Requests\Admin\GetRedemptionHistoryRequest;
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
        
        $response = (new UsedTicketCollection($tickets))->toResponse($request);
        
        // Agregar event_searched al JSON si existe
        if ($eventNameToSearch) {
            $data = $response->getData(true);
            $data['event_searched'] = $eventNameToSearch;
            $response->setData($data);
        }
        
        return $response->setStatusCode(200);
    }
    
    private function getUsedTicketsDataTable(GetUsedTicketsDataTableRequest $request)
    {
        $validated = $request->validated();
        $draw = $validated['draw'];
        $start = $validated['start'];
        $length = $validated['length'];
        $currentPage = ($start / $length) + 1;
        
        Paginator::currentPageResolver(fn() => $currentPage);
        
        $filters = array_filter([
            'event_name' => $validated['event_name'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
        ]);
        
        $tickets = $this->adminReportService->getUsedTicketsForEvent(
            eventName: $filters['event_name'] ?? '',
            filters: $filters,
            perPage: $length
        );
        
        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $tickets->total(),
            'recordsFiltered' => $tickets->total(),
            'data' => UsedTicketDataTableResource::collection($tickets->items())
        ]);
    }
    
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
        
        return (new RedemptionHistoryCollection($redemptions))
            ->toResponse($request)
            ->setStatusCode(200);
    }
    
    public function getRedemptionsHistoryDataTable(GetRedemptionHistoryDataTableRequest $request)
    {
        $validated = $request->validated();
        $draw = $validated['draw'];
        $start = $validated['start'];
        $length = $validated['length'];
        $currentPage = ($start / $length) + 1;
        
        Paginator::currentPageResolver(fn() => $currentPage);
        
        $filters = array_filter([
            'event_name' => $validated['event_name'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
        ]);
        
        $redemptions = $this->adminReportService->getRedemptionHistory(
            filters: $filters,
            perPage: $length
        );
        
        // ✅ DataTable usa Resource directo (formato especial)
        $transformedData = array_map(
            fn($item) => RedemptionHistoryResource::forDataTable($item),
            $redemptions->items()
        );

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