<?php

namespace App\Jobs;

use App\Events\TicketValidated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogTicketValidationAudit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        private TicketValidated $event
    ) {
        $this->delay(now()->addSeconds(2));
    }

    public function handle(): void
    {
        try {
            $ticket = $this->event->ticket;
            $validator = $this->event->validator;

            Log::channel('audit')->info('TICKET_VALIDATION_COMPLETE_AUDIT', [
                'validation_event' => [
                    'ticket_id' => $ticket->id,
                    'ticket_code' => $ticket->ticket_code,
                    'event_name' => $ticket->event_name,
                    'event_date' => $ticket->event_date,
                    'sector' => $ticket->sector,
                ],
                'validator_info' => [
                    'user_id' => $validator?->id,
                    'user_name' => $validator?->name,
                    'user_role' => $validator?->role?->slug ?? 'unknown',
                ],
                'validation_context' => [
                    'validated_at' => $ticket->validated_at,
                    'validation_ip' => $this->event->validationIp,
                    'user_agent' => $this->event->metadata['user_agent'] ?? 'Unknown',
                    'method' => $this->event->metadata['method'] ?? 'unknown',
                ],
                'audit_metadata' => [
                    'processed_at' => now()->toISOString(),
                    'environment' => app()->environment(),
                    'job_attempts' => $this->attempts(),
                ]
            ]);

            Log::channel('metrics')->info('VALIDATION_BUSINESS_METRIC', [
                'event_name' => $ticket->event_name,
                'sector' => $ticket->sector,
                'validation_hour' => $ticket->validated_at->format('H'),
                'validation_weekday' => $ticket->validated_at->format('N'),
                'validator_role' => $validator?->role?->slug ?? 'unknown',
                'days_before_event' => $ticket->event_date->diffInDays($ticket->validated_at),
            ]);

        } catch (\Exception $e) {
            Log::error('AUDIT_JOB_PROCESSING_FAILED', [
                'ticket_id' => $this->event->ticket->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('AUDIT_JOB_FAILED_PERMANENTLY', [
            'ticket_id' => $this->event->ticket->id,
            'validator_id' => $this->event->validator?->id,
            'exception_message' => $exception->getMessage(),
            'total_attempts' => $this->attempts(),
            'failed_at' => now()->toISOString(),
        ]);
    }
}