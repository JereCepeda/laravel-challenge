<?php

namespace Database\Seeders;

use App\Models\InvitationRedemption;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class InvitationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Eventos variados con diferentes escenarios
        $invitations = [
            // Eventos pasados
            [
                'invitation_id' => 'INV-' . strtoupper(Str::random(8)),
                'event_name' => 'Rock Festival 2024',
                'event_date' => now()->subDays(30),
                'sector' => 'VIP',
                'guest_count' => 2,
            ],
            [
                'invitation_id' => 'INV-' . strtoupper(Str::random(8)),
                'event_name' => 'Jazz Night',
                'event_date' => now()->subDays(15),
                'sector' => 'General',
                'guest_count' => 1,
            ],
            
            // Eventos próximos (para validar)
            [
                'invitation_id' => 'INV-' . strtoupper(Str::random(8)),
                'event_name' => 'Electronic Music Party',
                'event_date' => now()->addDays(3),
                'sector' => 'VIP',
                'guest_count' => 3,
            ],
            [
                'invitation_id' => 'INV-' . strtoupper(Str::random(8)),
                'event_name' => 'Electronic Music Party',
                'event_date' => now()->addDays(8),
                'sector' => 'General',
                'guest_count' => 2,
            ],
            [
                'invitation_id' => 'INV-' . strtoupper(Str::random(8)),
                'event_name' => 'Comedy Show',
                'event_date' => now()->addDays(5),
                'sector' => 'Premium',
                'guest_count' => 1,
            ],
            
            // Eventos futuros - Primera función de Classical Concert
            [
                'invitation_id' => 'INV-' . strtoupper(Str::random(8)),
                'event_name' => 'Classical Concert',
                'event_date' => now()->addDays(20),
                'sector' => 'Platea',
                'guest_count' => 4,
            ],
            // Segunda función de Classical Concert (2 días después)
            [
                'invitation_id' => 'INV-' . strtoupper(Str::random(8)),
                'event_name' => 'Classical Concert',
                'event_date' => now()->addDays(22),
                'sector' => 'Palco',
                'guest_count' => 2,
            ],
            // Primera función de Metal Festival
            [
                'invitation_id' => 'INV-' . strtoupper(Str::random(8)),
                'event_name' => 'Metal Festival',
                'event_date' => now()->addDays(45),
                'sector' => 'VIP',
                'guest_count' => 1,
            ],
            // Segunda función de Metal Festival (3 días después)
            [
                'invitation_id' => 'INV-' . strtoupper(Str::random(8)),
                'event_name' => 'Metal Festival',
                'event_date' => now()->addDays(48),
                'sector' => 'General',
                'guest_count' => 3,
            ],
            [
                'invitation_id' => 'INV-' . strtoupper(Str::random(8)),
                'event_name' => 'Pop Concert 2025',
                'event_date' => now()->addDays(60),
                'sector' => 'VIP',
                'guest_count' => 2,
            ],
        ];
        
        foreach ($invitations as $invitationData) {
            InvitationRedemption::create([
                'invitation_id' => $invitationData['invitation_id'],
                'event_name' => $invitationData['event_name'],
                'event_date' => $invitationData['event_date'],
                'sector' => $invitationData['sector'],
                'guest_count' => $invitationData['guest_count'],
                'tickets_generated' => 0,
                'redeemed_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'redeemed_ip' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'metadata' => [
                    'request_id' => Str::uuid(),
                    'source' => fake()->randomElement(['web', 'mobile', 'api']),
                    'version' => '1.0'
                ]
            ]);
        }
    }
}
