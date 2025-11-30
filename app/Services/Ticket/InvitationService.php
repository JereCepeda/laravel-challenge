<?php

namespace App\Services\Ticket;

use App\Models\Ticket;
use App\Models\InvitationRedemption;
use Illuminate\Support\Str;
use App\Jobs\SendTicketNotificationJob;
use App\Events\InvitationRedeemed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\InvitationException;
use Symfony\Component\HttpFoundation\Response;

class InvitationService
{
    public function __construct(
        private ExternalApiService $externalApiService
    ) {}

    public function redeemInvitation(string $hash): array
    {
        $this->checkAlreadyRedeemed($hash);

        try {
            return DB::transaction(function () use ($hash) {
                $this->checkAlreadyRedeemed($hash);
                
                $invitationData = $this->externalApiService->getInvitationData($hash);
                
                $redemption = $this->createRedemptionRecord($invitationData);
                
                $tickets = $this->generateTickets($invitationData);
                
                $redemption->update(['tickets_generated' => count($tickets)]);
                
                Log::info("Invitation redeemed successfully", [
                    'hash' => $hash,
                    'tickets_generated' => count($tickets)
                ]);
                
                return [
                    'invitation_data' => $invitationData,
                    'tickets' => $tickets,
                    'redemption' => $redemption
                ];
            }, 3); 
            
        } catch (\Exception $e) {
            Log::error("Failed to redeem invitation", [
                'hash' => $hash,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createRedemptionRecord(array $invitationData): InvitationRedemption
    {
        return InvitationRedemption::create([
            'invitation_id' => $invitationData['invitation_id'],
            'event_name' => $invitationData['event_name'],
            'event_date' => $invitationData['event_date'],
            'sector' => $invitationData['sector'],
            'guest_count' => $invitationData['guest_count'],
            'tickets_generated' => 0,
            'redeemed_at' => now(),
            'redeemed_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => [
                'request_id' => Str::uuid(),
                'source' => 'api',
                'version' => '1.0'
            ]
        ]);
    }

    private function checkAlreadyRedeemed(string $hash): void
    {
        if (InvitationRedemption::where('invitation_id', $hash)->exists()) {
            throw new InvitationException('Invitation already redeemed', Response::HTTP_CONFLICT);
        }
    }

    private function generateTickets(array $invitationData): array
    {
        $tickets = [];
        
        for ($i = 1; $i <= $invitationData['guest_count']; $i++) {
            $ticket = Ticket::create([
                'ticket_code' => $this->generateUniqueTicketCode(),
                'invitation_id' => $invitationData['invitation_id'],
                'event_name' => $invitationData['event_name'],
                'event_date' => $invitationData['event_date'],
                'sector' => $invitationData['sector'],
                'redeemed_ip' => request()->ip()
            ]);
            $tickets[] = $ticket;
        }
        
        return $tickets;
    }

    private function generateUniqueTicketCode(): string
    {
        do {
            $code = 'TCK-' . strtoupper(Str::random(8));
        } while (Ticket::where('ticket_code', $code)->exists());
        
        return $code;
    }
}