<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class MockExternalApiController extends Controller
{
    /**
     * Mock de la API externa para pruebas locales
     * GET /mock/invitations/{hash}
     */
    public function getInvitation(string $hash): JsonResponse
    {
        // Hashes validos del ejercicio
        $validHashes = [
            'a8f22d', 'a8f22e', 'a8f22f', 'b9g33e', 'b9g33f', 'b9g33g', 
            'c0h44f', 'c0h44g', 'c0h44h', 'd1i55h', 'd1i55g', 'd1i55i', 
            'e2j66h', 'e2j66i', 'f3k77i', 'f3k77j', 'f3k77k', 'g4l88j', 
            'g4l88k', 'h5m99k', 'h5m99l', 'h5m99m', 'i6n00l', 'i6n00m', 
            'j7o11m', 'j7o11n', 'k8p22n', 'k8p22o', 'k8p22p', 'l9q33o', 'l9q33p'
        ];

        // Verificar que el hash es valido
        if (!in_array($hash, $validHashes)) {
            return response()->json([
                'error' => 'Invitation not found'
            ], 404);
        }

        // Verificar autorizacion
        $authHeader = request()->header('Authorization');
        if ($authHeader !== 'Bearer secret123') {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        // Retornar datos simulados
        return response()->json([
            'invitation_id' => $hash,
            'event_name' => 'Festival de Musica Rosario',
            'event_date' => '2026-12-10 21:00:00',
            'guest_count' => rand(1, 4),
            'sector' => ['VIP', 'General', 'Premium', 'Platea'][rand(0, 3)]
        ]);
    }
}
