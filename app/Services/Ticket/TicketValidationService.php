<?php

namespace App\Services\Ticket;

use App\Models\Ticket;
use App\Events\TicketValidated;
use App\Exceptions\TicketException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketValidationService
{
    public function validateTicket(string $ticketCode): array
    {
        try {
            return DB::transaction(function () use ($ticketCode) {
                $ticket = Ticket::where('ticket_code', $ticketCode)
                    ->lockForUpdate()
                    ->first();

                if (!$ticket) {
                    throw new TicketException('Ticket not found', 404);
                }

                if ($ticket->is_validated) {
                    throw new TicketException('Ticket already validated', 409);
                }

                if ($ticket->event_date < now()) {
                    throw new TicketException('Event has already passed', 400);
                }

                $ticket->update([
                    'is_validated' => true,
                    'validated_at' => now(),
                    'validated_by' => auth('api')->id(), 
                    'validation_ip' => request()->ip()  
                ]);

                $this->logValidation($ticket, auth('api')->user());

                // event(new TicketValidated($ticket));

                return [
                    'ticket' => $ticket,
                    'access_granted' => true,
                    'message' => 'Access granted - Welcome to ' . $ticket->event_name
                ];
            });

        } catch (TicketException $e) {
            $this->logValidationFailure($ticketCode, $e->getMessage());
            throw $e;
        }
    }

    private function logValidation(Ticket $ticket, $user): void
    {
        Log::info("Ticket validated successfully", [
            'ticket_code' => $ticket->ticket_code,
            'event_name' => $ticket->event_name,
            'event_date' => $ticket->event_date,
            'sector' => $ticket->sector,
            'validated_by' => $user?->name ?? 'Unknown',
            'validator_role' => $user?->role?->name ?? 'Unknown',
            'validation_time' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    private function logValidationFailure(string $ticketCode, string $reason): void
    {
        Log::warning("Ticket validation failed", [
            'ticket_code' => $ticketCode,
            'reason' => $reason,
            'attempted_by' => auth('api')->user()?->name ?? 'Unknown',
            'ip_address' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);
    }

    public function getTicketInfo(string $ticketCode): ?Ticket
    {
        return Ticket::where('ticket_code', $ticketCode)->first();
    }

    public function isTicketValidated(string $ticketCode): bool
    {
        $ticket = $this->getTicketInfo($ticketCode);
        return $ticket ? $ticket->is_validated : false;
    }

    public function ticketEventPassed(string $ticketCode): bool
    {
        $ticket = $this->getTicketInfo($ticketCode);
        return $ticket ? ($ticket->event_date < now()) : false;
    }

    public function markTicketAsValidated(string $ticketCode): void
    {
        $ticket = $this->getTicketInfo($ticketCode);
        if ($ticket && !$ticket->is_validated) {
            $ticket->update([
                'is_validated' => true,
                'validated_at' => now(),
                'validated_by' => auth('api')->id()
            ]);
        }
    }
}