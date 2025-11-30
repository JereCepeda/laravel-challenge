<?php

namespace App\Jobs;

use App\Events\TicketValidated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTicketValidationNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 30;

    public function __construct(private TicketValidated $event) {
        $this->onQueue('notifications');
        $this->delay(now()->addSeconds(5));
    }

    public function handle(): void
    {
        try {
            $ticket = $this->event->ticket;
            $validator = $this->event->validator;

            $this->sendAdminEmailNotification($ticket, $validator);
            $this->sendValidatorSummaryNotification($ticket, $validator);

            Log::info('All validation notifications processed successfully', [
                'ticket_id' => $ticket->id,
                'notification_channels' => 2,
                'sent_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::warning('Notification job processing failed', [
                'ticket_id' => $this->event->ticket->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }

    private function sendAdminEmailNotification($ticket, $validator): void
    {
        Log::channel('notifications')->info('ADMIN_EMAIL_NOTIFICATION', [
            'type' => 'admin_email',
            'to' => 'admin@event-system.com',
            'subject' => "Ticket Validated: {$ticket->ticket_code}",
            'template' => 'ticket_validated_admin',
            'data' => [
                'ticket_code' => $ticket->ticket_code,
                'event_name' => $ticket->event_name,
                'sector' => $ticket->sector,
                'event_date' => $ticket->event_date->format('Y-m-d H:i'),
                'validated_by' => $validator?->name ?? 'Unknown',
                'validated_at' => $ticket->validated_at->format('Y-m-d H:i:s'),
                'guest_info' => $ticket->invitationRedemption?->guest_name ?? 'N/A',
            ],
            'sent_at' => now()->toISOString()
        ]);
    }

    private function sendValidatorSummaryNotification($ticket, $validator): void
    {
        if (!$validator) return;

        Log::channel('notifications')->info('VALIDATOR_SUMMARY_NOTIFICATION', [
            'type' => 'validator_summary',
            'to' => $validator->email,
            'method' => 'in_app_notification',
            'content' => [
                'title' => 'Validation Completed',
                'message' => "You successfully validated ticket {$ticket->ticket_code} for {$ticket->event_name}",
                'action_performed' => 'ticket_validation',
                'timestamp' => $ticket->validated_at->toISOString(),
            ],
            'sent_at' => now()->toISOString()
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('NOTIFICATION_JOB_FAILED_PERMANENTLY', [
            'ticket_id' => $this->event->ticket->id,
            'exception' => $exception->getMessage(),
            'total_attempts' => $this->attempts(),
        ]);
    }
}