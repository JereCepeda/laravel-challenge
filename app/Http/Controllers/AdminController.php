<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminReportService;
use App\Http\Requests\Admin\GetUsedTicketsRequest;
use App\Http\Requests\Admin\GetRedemptionHistoryRequest;

class AdminController extends Controller
{
    public function __construct(private AdminReportService $adminReportService) {}

    public function getUsedTickets(GetUsedTicketsRequest $request, string $event_name)
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 15;

        $usedTickets = $this->adminReportService->getUsedTicketsForEvent(
            eventName: $event_name,
            filters: $validated,
            perPage: $perPage
        );

        if ($usedTickets->isEmpty()) {
            return response()->json([
                'message' => 'No used tickets found for this event',
                'event_searched' => $event_name,
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
            'event_searched' => $event_name,
            'data' => $usedTickets->items(),
            'meta' => [
                'current_page' => $usedTickets->currentPage(),
                'last_page' => $usedTickets->lastPage(),
                'per_page' => $usedTickets->perPage(),
                'total' => $usedTickets->total(),
                'has_more_pages' => $usedTickets->hasMorePages()
            ]
        ], 200);
    }

    public function getRedemptionHistory(GetRedemptionHistoryRequest $request)
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
        ], 200);
    }
}

