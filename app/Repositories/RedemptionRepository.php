<?php

namespace App\Repositories;

use App\Models\InvitationRedemption;
use Illuminate\Support\Collection;

class RedemptionRepository
{
    /**
     * Obtener total de canjes
     */
    public function getTotalCount(): int
    {
        return InvitationRedemption::count();
    }
    
    /**
     * Obtener todos los canjes
     */
    public function getAllRedemptions(): Collection
    {
        return InvitationRedemption::select(
                'event_name', 
                'sector', 
                'event_date', 
                'tickets_generated', 
                'invitation_id',
                'guest_count',
                'user_agent',
                'redeemed_at'
            )
            ->orderBy('event_name')
            ->get();
    }
    
    /**
     * Obtener opciones de filtros
     */
    public function getFilterOptions(): Collection
    {
        return InvitationRedemption::select('event_name', 'event_date', 'sector')
            ->distinct()
            ->groupBy('event_name', 'event_date', 'sector')
            ->orderBy('event_name')
            ->get();
    }
    
    /**
     * Obtener canjes recientes de un usuario
     */
    public function getRecentByUser(int $userId, int $limit = 10): Collection
    {
        return InvitationRedemption::where('user_id', $userId)
            ->orderBy('redeemed_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
