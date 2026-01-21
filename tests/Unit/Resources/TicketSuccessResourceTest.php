<?php

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Resources\Ticket\TicketResource;
use App\Http\Resources\Ticket\TicketSuccessResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ticket success resource has correct structure', function () {
    $ticket = new Ticket([
        'ticket_code' => 'TCK-STRUCT1',
        'event_name' => 'Test Event',
        'sector' => 'A1',
        'is_validated' => false,
    ]);
    
    $data = (object) [
        'access_granted' => true,
        'message' => 'Test message',
        'ticket' => $ticket,
    ];

    $resource = new TicketSuccessResource($data);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toBeArray()
        ->toHaveKeys(['access_granted', 'message', 'ticket_info'])
        ->and(count($transformed))->toBe(3); // Solo 3 campos
});

test('ticket success resource nests ticket resource correctly', function () {
    $ticket = new Ticket([
        'ticket_code' => 'TCK-NESTED01',
        'event_name' => 'Nested Event Test',
        'sector' => 'A1',
        'is_validated' => false,
    ]);

    $data = (object) [
        'access_granted' => true,
        'message' => 'Success',
        'ticket' => $ticket,
    ];

    $resource = new TicketSuccessResource($data);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed['ticket_info'])
        ->toBeInstanceOf(TicketResource::class);
    
    // Verificar que al resolver el TicketResource tiene la estructura correcta
    $ticketResourceData = $transformed['ticket_info']->toArray($request);
    
    expect($ticketResourceData)
        ->toHaveKey('code')
        ->toHaveKey('event')
        ->toHaveKey('sector')
        ->and($ticketResourceData['code'])->toBe('TCK-NESTED01')
        ->and($ticketResourceData['event'])->toBe('Nested Event Test');
});

test('ticket success resource handles access denied scenario', function () {
    $ticket = new Ticket([
        'ticket_code' => 'TCK-DENIED01',
        'event_name' => 'Past Event',
        'sector' => 'B1',
        'is_validated' => false,
    ]);

    $data = (object) [
        'access_granted' => false,
        'message' => 'Access denied - Event has passed',
        'ticket' => $ticket,
    ];

    $resource = new TicketSuccessResource($data);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toHaveKey('access_granted')
        ->and($transformed['access_granted'])->toBeFalse()
        ->and($transformed['message'])->toContain('denied');
});
