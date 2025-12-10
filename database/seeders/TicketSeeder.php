<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\Models\InvitationRedemption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $invitations = InvitationRedemption::all();
        
        if ($invitations->isEmpty()) {
            $this->command->warn('No hay invitaciones. Ejecuta InvitationSeeder primero.');
            return;
        }
        
        $validators = User::whereHas('role', function($query) {
            $query->whereIn('slug', ['checker', 'admin']);
        })->get();
        
        $totalTickets = 0;
        $validatedTickets = 0;
        
        foreach ($invitations as $invitation) {

            for ($i = 1; $i <= $invitation->guest_count; $i++) {
                $ticket = Ticket::create([
                    'ticket_code' => $this->generateTicketCode(),
                    'invitation_id' => $invitation->invitation_id,
                    'event_name' => $invitation->event_name,
                    'event_date' => $invitation->event_date,
                    'sector' => $invitation->sector,
                    'is_validated' => false,
                    'redeemed_ip' => $invitation->redeemed_ip
                ]);
                
                $totalTickets++;
                

                if (!$validators->isEmpty() && 
                    $invitation->event_date > now() && 
                    fake()->boolean(70)) {
                    
                    $validator = $validators->random();
                    $ticket->update([
                        'is_validated' => true,
                        'validated_at' => now(),
                        'validated_by' => $validator->id,
                        'validation_ip' => fake()->ipv4()
                    ]);
                    $validatedTickets++;
                }
            }
            $invitation->update(['tickets_generated' => $invitation->guest_count]);
        }
    }
    
    private function generateTicketCode(): string
    {
        do {
            $code = 'TCK-' . strtoupper(Str::random(8));
        } while (Ticket::where('ticket_code', $code)->exists());
        
        return $code;
    }
}
