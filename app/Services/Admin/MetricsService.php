<?php

namespace App\Services\Admin;

use App\Models\Ticket;
use App\Models\InvitationRedemption;
use Illuminate\Support\Facades\DB;

class MetricsService
{
    /**
     * Obtiene las métricas principales (KPIs) del dashboard
     */
    public function getMainKpis(): array
    {
        return [
            'total_tickets_validated' => $this->getTotalTicketsValidated(),
            'total_invitations_redeemed' => $this->getTotalInvitationsRedeemed(),
            'active_events' => $this->getActiveEvents(),
            'conversion_rate' => $this->getConversionRate()
        ];
    }
    
    /**
     * KPI 1: Total de Tickets Validados
     */
    private function getTotalTicketsValidated(): int
    {
        return Ticket::where('is_validated', true)->count();
    }
    
    /**
     * KPI 2: Total de Invitaciones Canjeadas
     */
    private function getTotalInvitationsRedeemed(): int
    {
        return InvitationRedemption::count();
    }
    
    /**
     * KPI 3: Eventos Activos (eventos únicos en el sistema)
     */
    private function getActiveEvents(): int
    {
        return Ticket::distinct('event_name')->count('event_name');
    }
    
    /**
     * KPI 4: Tasa de Conversión (% de invitaciones que resultaron en tickets validados)
     */
    private function getConversionRate(): float
    {
        $totalInvitations = InvitationRedemption::count();
        
        if ($totalInvitations === 0) {
            return 0.0;
        }
        
        $invitationsWithValidatedTickets = InvitationRedemption::whereHas('tickets', function($query) {
            $query->where('is_validated', true);
        })->count();
        
        return round(($invitationsWithValidatedTickets / $totalInvitations) * 100, 2);
    }
    
    /**
     * Métricas adicionales para contexto
     */
    public function getAdditionalMetrics(): array
    {
        return [
            'pending_tickets' => Ticket::where('is_validated', false)->count(),
            'events_with_validations' => $this->getEventsWithValidations(),
            'avg_tickets_per_invitation' => $this->getAvgTicketsPerInvitation(),
            'most_popular_sector' => $this->getMostPopularSector()
        ];
    }
    
    private function getEventsWithValidations(): int
    {
        return Ticket::where('is_validated', true)
            ->distinct('event_name')
            ->count('event_name');
    }
    
    private function getAvgTicketsPerInvitation(): float
    {
        $totalInvitations = InvitationRedemption::count();
        
        if ($totalInvitations === 0) {
            return 0.0;
        }
        
        $totalTickets = Ticket::count();
        return round($totalTickets / $totalInvitations, 2);
    }
    
    private function getMostPopularSector(): ?string
    {
        return Ticket::select('sector')
            ->groupBy('sector')
            ->orderByRaw('COUNT(*) DESC')
            ->value('sector');
    }
}