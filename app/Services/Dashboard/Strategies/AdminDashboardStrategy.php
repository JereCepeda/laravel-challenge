<?php

namespace App\Services\Dashboard\Strategies;

use App\Models\User;
use App\Repositories\TicketRepository;
use App\Repositories\RedemptionRepository;
use App\Services\Dashboard\Contracts\RoleDashboardInterface;

class AdminDashboardStrategy implements RoleDashboardInterface
{
    public function __construct(
        private TicketRepository $ticketRepo,
        private RedemptionRepository $redemptionRepo
    ) {}
    
    public function getInitialView(): string
    {
        return 'dashboard.admin.statistics';
    }
    
    public function getAllowedSections(array $permissions): array
    {
        $sections = [];
        
        if (in_array('view_statistics', $permissions)) {
            $sections[] = 'statistics';
        }
        
        if (in_array('view_reports', $permissions)) {
            $sections[] = 'reports';
        }
        
        if (in_array('view_used_tickets', $permissions)) {
            $sections[] = 'used-tickets';
        }
        
        if (in_array('view_redemptions_history', $permissions)) {
            $sections[] = 'redemptions-history';
        }
        
        return $sections;
    }
    
    public function getDataForSection(string $section, User $user): array
    {
        return match($section) {
            'statistics' => $this->getStatisticsData(),
            'used-tickets' => $this->getUsedTicketsData(),
            'redemptions-history' => $this->getRedemptionsHistoryData(),
            'reports' => $this->getReportsData(),
            default => []
        };
    }
    
    private function getStatisticsData(): array
    {
        $totalTickets = $this->ticketRepo->getTotalCount();
        $validatedTickets = $this->ticketRepo->getValidatedCount();
        
        return [
            'total_tickets' => $totalTickets,
            'validated_tickets' => $validatedTickets,
            'validation_rate' => $totalTickets > 0 ? round(($validatedTickets / $totalTickets) * 100, 2) : 0,
            'total_redemptions' => $this->redemptionRepo->getTotalCount()
        ];
    }
    
    private function getUsedTicketsData(): array
    {
        return [
            'events' => $this->ticketRepo->getUniqueEvents(),
            'filters' => $this->ticketRepo->getFilterOptions()
        ];
    }
    
    private function getRedemptionsHistoryData(): array
    {
        return [
            'events' => $this->redemptionRepo->getAllRedemptions(),
            'filters' => $this->redemptionRepo->getFilterOptions()
        ];
    }
    
    private function getReportsData(): array
    {
        return [
            'reports' => []
        ];
    }
}