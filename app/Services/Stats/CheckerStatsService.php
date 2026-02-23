<?php

namespace App\Services\Stats;

use App\Models\Ticket;

class CheckerStatsService
{
    /**
     * Estadísticas del día actual
     */
    public function getTodayStats(int $checkerId): array
    {
        return [
            'validated_today' => $this->getValidatedToday($checkerId),
            'validation_rate' => $this->calculateValidationRate($checkerId)
        ];
    }
    
    /**
     * Estadísticas diarias
     */
    public function getDailyStats(int $checkerId): array
    {
        $today = today();
        
        return [
            'total_today' => $this->getValidatedToday($checkerId),
            'by_hour' => $this->getValidationsByHour($checkerId, $today),
            'by_event' => $this->getValidationsByEvent($checkerId, $today)
        ];
    }
    
    /**
     * Estadísticas semanales
     */
    public function getWeeklyStats(int $checkerId): array
    {
        return [
            'total_week' => $this->getValidatedThisWeek($checkerId),
            'daily_average' => $this->getDailyAverage($checkerId)
        ];
    }
    
    /**
     * Estadísticas mensuales
     */
    public function getMonthlyStats(int $checkerId): array
    {
        return [
            'total_month' => $this->getValidatedThisMonth($checkerId),
            'weekly_breakdown' => $this->getWeeklyBreakdown($checkerId)
        ];
    }
    
    /**
     * Top eventos validados
     */
    public function getTopEvents(int $checkerId, int $limit = 5)
    {
        return Ticket::where('validated_by', $checkerId)
            ->select('event_name', 'event_date')
            ->selectRaw('COUNT(*) as total_validated')
            ->groupBy('event_name', 'event_date')
            ->orderByDesc('total_validated')
            ->limit($limit)
            ->get();
    }
    
    // ========== MÉTODOS PRIVADOS ==========
    
    private function getValidatedToday(int $checkerId): int
    {
        return Ticket::where('validated_by', $checkerId)
            ->whereDate('validated_at', today()) 
            ->count();
    }
    
    private function calculateValidationRate(int $checkerId): float
    {
        $totalValidated = $this->getValidatedToday($checkerId);
        $hoursSinceStart = now()->diffInHours(today()->setTime(8, 0));
        
        return $hoursSinceStart > 0 ? round($totalValidated / $hoursSinceStart, 2) : 0;
    }
    
    private function getValidationsByHour(int $checkerId, $date)
    {
        return Ticket::where('validated_by', $checkerId)
            ->whereDate('validated_at', $date) 
            ->selectRaw('HOUR(validated_at) as hour, COUNT(*) as count') 
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }
    
    private function getValidationsByEvent(int $checkerId, $date)
    {
        return Ticket::where('validated_by', $checkerId)
            ->whereDate('validated_at', $date) 
            ->select('event_name')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('event_name')
            ->orderByDesc('count')
            ->get();
    }
    
    private function getValidatedThisWeek(int $checkerId): int
    {
        return Ticket::where('validated_by', $checkerId)
            ->whereBetween('validated_at', [now()->startOfWeek(), now()->endOfWeek()]) 
            ->count();
    }
    
    private function getDailyAverage(int $checkerId): float
    {
        $data = Ticket::where('validated_by', $checkerId)
            ->whereBetween('validated_at', [now()->startOfWeek(), now()->endOfWeek()]) 
            ->selectRaw('DATE(validated_at) as date, COUNT(*) as count') 
            ->groupBy('date')
            ->get();
            
        return $data->avg('count') ?? 0;
    }
    
    private function getValidatedThisMonth(int $checkerId): int
    {
        return Ticket::where('validated_by', $checkerId)
            ->whereMonth('validated_at', now()->month) 
            ->whereYear('validated_at', now()->year) 
            ->count();
    }
    
    private function getWeeklyBreakdown(int $checkerId)
    {
        return Ticket::where('validated_by', $checkerId)
            ->whereMonth('validated_at', now()->month) 
            ->whereYear('validated_at', now()->year) 
            ->selectRaw('WEEK(validated_at) as week, COUNT(*) as count') 
            ->groupBy('week')
            ->orderBy('week')
            ->get();
    }
}
