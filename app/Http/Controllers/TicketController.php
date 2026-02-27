<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Exceptions\TicketException;
use App\Exceptions\InvitationException;
use App\Exceptions\ExternalApiException;
use App\Services\Ticket\InvitationService;
use App\Http\Requests\ValidateTicketRequest;
use App\Http\Requests\RedeemInvitationRequest;
use App\Services\Ticket\TicketValidationService;
use App\Http\Resources\Ticket\TicketSuccessResource;
use App\Http\Resources\Ticket\InvitationSuccessResource;

class TicketController extends Controller
{
    public function __construct(
        private InvitationService $invitationService,
        private TicketValidationService $ticketValidationService
    ) {}

    public function redeemInvitation(RedeemInvitationRequest $request): JsonResponse
    {

        $hash = $request->validated()['hash'];
        try {
            $result = $this->invitationService->redeemInvitation($hash);
            info('Invitation redeemed successfully', ['result' => $result]);
            return InvitationSuccessResource::make((object)$result)->response()->setStatusCode(201);
        } catch (InvitationException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'already_redeemed' => str_contains($e->getMessage(), 'already')
            ], $e->getCode());

        } catch (ExternalApiException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'fallback_message' => 'The invitation service is temporarily unavailable. Please try again later.'
            ], $e->getCode());

        } catch (\Exception $e) {
            \Log::error('Unexpected error redeeming invitation', [
                'hash' => $hash,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => 'Please try again later',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function validateTicket(ValidateTicketRequest $request): JsonResponse
    {
        $ticketCode = $request->validated()['ticket_code'];

        try {
            $result = $this->ticketValidationService->validateTicket($ticketCode);
            return TicketSuccessResource::make((object)$result)->response()->setStatusCode(200);
        } catch (TicketException $e) {
            return response()->json([
                'access_granted' => false,
                'message' => 'Access denied: ' . $e->getMessage(),
                'error_code' => $this->getErrorCode($e->getCode())
            ], $e->getCode());

        } catch (\Exception $e) {
            return response()->json([
                'access_granted' => false,
                'message' => 'Validation service temporarily unavailable'
            ], 500);
        }
    }

    private function getErrorCode(int $httpCode): string
    {
        return match($httpCode) {
            404 => 'TICKET_NOT_FOUND',
            409 => 'TICKET_ALREADY_VALIDATED', 
            400 => 'EVENT_PASSED',
            default => 'VALIDATION_ERROR'
        };
    }
}
