<?php

namespace App\Services\Dashboard\Strategies;

use App\Models\User;
use App\Repositories\RedemptionRepository;
use App\Repositories\TicketRepository;
use App\Services\Dashboard\Contracts\RoleDashboardInterface;
use App\Services\Stats\CheckerStatsService;

class CheckerDashboardStrategy implements RoleDashboardInterface
{
    public function __construct(
        private TicketRepository $ticketRepo,
        private RedemptionRepository $redemptionRepo,
        private CheckerStatsService $statsService
    ) {}
    
    public function getInitialView(): string
    {
        return 'dashboard.checker.scan-menu';
    }
    
    public function getAllowedSections(array $permissions): array
    {
        $sections = [];

        if (in_array('validate_tickets', $permissions)) {
            $sections[] = array_merge($sections, ['scan-menu','validate-ticket','my-stats']);
        }
        
        if (in_array('redeem_invitations', $permissions)) {
            $sections[] = 'redeem_invitation';
        }
        
        return $sections;
    }
    
    public function getDataForSection(string $section, User $user): array
    {
        return match($section) {
            'scan-menu' => $this->getScanMenuData($user),
            'validate-ticket' => $this->getValidateTicketData($user),
            'redeem_invitation' => $this->getRedeemInvitationData($user),
            'my-stats' => $this->getStatisticsData($user->id),
            default => []
        };
    }
    
    private function getScanMenuData(User $user): array
    {
        return [
            'active_events' => $this->ticketRepo->getActiveEvents(),
            'today_stats'=>$this->statsService->getTodayStats($user->id),
        ];
    }

    private function getValidateTicketData(User $user): array
    {
        return [
            'recent_validations' => $this->ticketRepo->getRecentValidations($user->id)
        ];
    }

    private function getRedeemInvitationData(User $user): array
    {
        return [
            'recent_redemptions' => $this->redemptionRepo->getRecentByUser($user->id)
        ];
    }
    private function getStatisticsData(int $checkerId): array
    {
        return [
            'daily_stats' => $this->statsService->getDailyStats($checkerId),
            'weekly_stats' => $this->statsService->getWeeklyStats($checkerId),
            'monthly_stats' => $this->statsService->getMonthlyStats($checkerId),
            'top_events' => $this->statsService->getTopEvents($checkerId)
        ];
    }
}