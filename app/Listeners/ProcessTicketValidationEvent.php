<?php

namespace App\Listeners;

use App\Events\TicketValidated;
use App\Jobs\LogTicketValidationAudit;
use App\Jobs\SendTicketValidationNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcessTicketValidationEvent implements ShouldQueue
{
    public string $queue = 'validation-events';
    public int $tries = 3;

    public function handle(TicketValidated $event): void
    {
        try {
            Log::info('Processing ticket validation event', [
                'ticket_id' => $event->ticket->id,
                'validator_id' => $event->validator?->id
            ]);

            LogTicketValidationAudit::dispatch($event)
                ->onQueue('audit')
                ->delay(now()->addSeconds(1));

            SendTicketValidationNotifications::dispatch($event)
                ->onQueue('notifications')
                ->delay(now()->addSeconds(10));

            Log::info('Validation event jobs dispatched successfully', [
                'ticket_id' => $event->ticket->id,
                'jobs_dispatched' => ['audit', 'notifications']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process ticket validation event', [
                'ticket_id' => $event->ticket->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function failed(TicketValidated $event, \Throwable $exception): void
    {
        Log::critical('TICKET_VALIDATION_EVENT_LISTENER_FAILED', [
            'ticket_id' => $event->ticket->id,
            'validator_id' => $event->validator?->id,
            'exception' => $exception->getMessage(),
            'failed_at' => now()->toISOString()
        ]);
    }
}