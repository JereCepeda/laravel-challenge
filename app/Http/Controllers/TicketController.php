<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Exceptions\TicketException;
use App\Exceptions\InvitationException;
use App\Exceptions\ExternalApiException;
use App\Http\Requests\RedeemInvitationRequest;
use App\Services\Ticket\InvitationService;
use App\Http\Requests\ValidateTicketRequest;
use App\Services\Ticket\TicketValidationService;

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

            return response()->json([
                'message' => 'Invitation redeemed successfully',
                'event' => [
                    'name' => $result['invitation_data']['event_name'],
                    'date' => $result['invitation_data']['event_date'],
                    'sector' => $result['invitation_data']['sector']
                ],
                'tickets' => $result['tickets']
            ], 201);

        } catch (InvitationException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode());

        } catch (ExternalApiException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'fallback_message' => 'The invitation service is temporarily unavailable. Please try again later.'
            ], $e->getCode());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => 'Please try again later'
            ], 500);
        }
    }

    public function validateTicket(ValidateTicketRequest $request): JsonResponse
    {
        $ticketCode = $request->validated()['ticket_code'];

        try {
            $result = $this->ticketValidationService->validateTicket($ticketCode);

            return response()->json([
                'access_granted' => $result['access_granted'],
                'message' => $result['message'],
                'ticket_info' => [
                    'code' => $result['ticket']['ticket_code'],
                    'event' => $result['ticket']['event_name'],
                    'sector' => $result['ticket']['sector'],
                    'validated_at' => $result['ticket']['validated_at']
                ]
            ]);

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
