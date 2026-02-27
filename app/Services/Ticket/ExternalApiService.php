<?php

namespace App\Services\Ticket;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ExternalApiException;

class ExternalApiService
{
    private const EXTERNAL_API_URL = 'https://mds-events-main-nfwvz9.laravel.cloud/api/invitations/';
    private const API_TOKEN = 'secret123';
    private const TIMEOUT = 10; 
    private const CACHE_TTL = 300; 

    /**
     * Obtener la URL base de la API (externa o mock local)
     */
    private function getApiUrl(): string
    {
        // Si USE_MOCK_API=true en .env, usar mock local
        if (config('app.use_mock_api', false)) {
            return config('app.url') . '/api/mock/invitations/';
        }
        
        return self::EXTERNAL_API_URL;
    }

    public function getInvitationData(string $hash): array
    {
        $cacheKey = "invitation_data_{$hash}";
        
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            Log::info("Invitation data retrieved from cache", ['hash' => $hash]);
            return $cachedData;
        }

        $apiUrl = $this->getApiUrl();
        
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . self::API_TOKEN])
                ->timeout(self::TIMEOUT)
                ->retry(3, 1000)
                ->withOptions(['verify' => false]) // SOLO PARA DESARROLLO: deshabilitar verificacion SSL
                ->get($apiUrl . $hash);
            if (!$response->successful()) {
                throw new ExternalApiException(
                    "External API returned status: {$response->status()}",
                    $response->status()
                );
            }
            $data = $response->json();
            
            $this->validateResponseData($data);
            
            Cache::put($cacheKey, $data, self::CACHE_TTL);
            
            Log::info("Invitation data retrieved from external API", ['hash' => $hash]);
            
            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Connection timeout to external API", [
                'hash' => $hash,
                'error' => $e->getMessage()
            ]);
            throw new ExternalApiException("External service is temporarily unavailable", 503);
            
        } catch (\Exception $e) {
            Log::error("External API error", [
                'hash' => $hash,
                'error' => $e->getMessage()
            ]);
            throw new ExternalApiException("Failed to fetch invitation data", 502);
        }
    }

    private function validateResponseData(array $data): void
    {
        $required = ['invitation_id', 'event_name', 'event_date', 'guest_count', 'sector'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new ExternalApiException("Invalid response format: missing {$field}");
            }
        }

        if (!is_numeric($data['guest_count']) || $data['guest_count'] <= 0) {
            throw new ExternalApiException("Invalid guest_count value");
        }
    }
}