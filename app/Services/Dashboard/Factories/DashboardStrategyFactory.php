<?php

namespace App\Services\Dashboard\Strategies;
use App\Repositories\RedemptionRepository;
use App\Repositories\TicketRepository;
use App\Services\Dashboard\Contracts\RoleDashboardInterface;
use App\Services\Dashboard\Strategies\AdminDashboardStrategy;
use App\Services\Dashboard\Strategies\CheckerDashboardStrategy;
use App\Services\Stats\CheckerStatsService;


class DashboardStrategyFactory 
{
    public function __construct(
        private TicketRepository $ticketRepo,
        private RedemptionRepository $redemptionRepo,
        private CheckerStatsService $statsService
    ) {}
    
    public function make(string $role): RoleDashboardInterface
    {
        return match($role) {
            'admin' => new AdminDashboardStrategy($this->ticketRepo, $this->redemptionRepo),
            'checker' => new CheckerDashboardStrategy($this->ticketRepo, $this->redemptionRepo, $this->statsService),
            default => throw new \InvalidArgumentException("Rol no soportado: $role")
        };
    }
}