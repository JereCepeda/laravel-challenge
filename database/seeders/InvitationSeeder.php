<?php

namespace Database\Seeders;

use App\Models\InvitationRedemption;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class InvitationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * NOTA: Este seeder NO crea invitaciones en la API externa.
     * Solo simula invitaciones YA CANJEADAS para testing del historial.
     * 
     * Para probar el canje de invitaciones, usa los hashes validos:
     * a8f22d, a8f22e, a8f22f, b9g33e, b9g33f, b9g33g, c0h44f, c0h44g, c0h44h, d1i55h,
     * d1i55g, d1i55i, e2j66h, e2j66i, f3k77i, f3k77j, f3k77k, g4l88j, g4l88k, h5m99k,
     * h5m99l, h5m99m, i6n00l, i6n00m, j7o11m, j7o11n, k8p22n, k8p22o, k8p22p, l9q33o, l9q33p
     */
    public function run(): void
    {
        // Hashes validos de la API externa (solo algunos para simular ya canjeados)
        $validHashes = ['a8f22d', 'a8f22e', 'b9g33e', 'c0h44f', 'd1i55h'];
        
        $invitations = [
            [
                'invitation_id' => $validHashes[0],
                'event_name' => 'Rock Festival 2024',
                'event_date' => now()->subDays(30),
                'sector' => 'VIP',
                'guest_count' => 2,
            ],
            [
                'invitation_id' => $validHashes[1],
                'event_name' => 'Jazz Night',
                'event_date' => now()->subDays(15),
                'sector' => 'General',
                'guest_count' => 1,
            ],
            [
                'invitation_id' => $validHashes[2],
                'event_name' => 'Electronic Music Party',
                'event_date' => now()->addDays(3),
                'sector' => 'VIP',
                'guest_count' => 3,
            ],
            [
                'invitation_id' => $validHashes[3],
                'event_name' => 'Comedy Show',
                'event_date' => now()->addDays(5),
                'sector' => 'Premium',
                'guest_count' => 1,
            ],
            [
                'invitation_id' => $validHashes[4],
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
