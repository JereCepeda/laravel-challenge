<?php

namespace App\Providers;

use App\Events\TicketValidated;
use App\Listeners\ProcessTicketValidationEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TicketValidated::class => [
            ProcessTicketValidationEvent::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}