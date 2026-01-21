<?php

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Resources\Ticket\EventResource;
use App\Http\Resources\Ticket\InvitationSuccessResource;

test('invitation success resource transforms redemption response correctly', function () {
    $tickets = [
        ['ticket_code' => 'TCK-TICKET01', 'event_name' => 'Concert', 'sector' => 'VIP'],
        ['ticket_code' => 'TCK-TICKET02', 'event_name' => 'Concert', 'sector' => 'VIP'],
    ];

    $eventData = (object) [
        'name' => 'Rock Concert 2026',
        'date' => '2026-12-31 20:00:00',
        'sector' => 'VIP',
    ];

    $data = (object) [
        'message' => 'Invitation redeemed successfully',
        'event' => $eventData,
        'tickets' => $tickets,
    ];

    $resource = new InvitationSuccessResource($data);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toBeArray()
        ->toHaveKey('message')
        ->toHaveKey('event')
        ->toHaveKey('tickets')
        ->and($transformed['message'])->toBe('Invitation redeemed successfully')
        ->and($transformed['event'])->toBeInstanceOf(EventResource::class)
        ->and($transformed['tickets'])->toBeArray()
        ->and($transformed['tickets'])->toHaveCount(2);
});

test('invitation success resource has correct structure', function () {
    $eventData = (object) [
        'name' => 'Test Event',
        'date' => now()->toDateTimeString(),
        'sector' => 'General',
    ];

    $data = (object) [
        'message' => 'Success',
        'event' => $eventData,
        'tickets' => [],
    ];

    $resource = new InvitationSuccessResource($data);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toBeArray()
        ->toHaveKeys(['message', 'event', 'tickets'])
        ->and(count($transformed))->toBe(3); // Solo 3 campos principales
});

test('invitation success resource nests event resource correctly', function () {
    $eventData = (object) [
        'name' => 'Jazz Festival',
        'date' => '2026-08-20 19:00:00',
        'sector' => 'Preferencial',
    ];

    $data = (object) [
        'message' => 'Redeemed',
        'event' => $eventData,
        'tickets' => [],
    ];

    $resource = new InvitationSuccessResource($data);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed['event'])->toBeInstanceOf(EventResource::class);
    
    // Verificar que al resolver el EventResource tiene la estructura correcta
    $eventResourceData = $transformed['event']->toArray($request);
    
    expect($eventResourceData)
        ->toHaveKey('name')
        ->toHaveKey('date')
        ->toHaveKey('sector')
        ->and($eventResourceData['name'])->toBe('Jazz Festival')
        ->and($eventResourceData['date'])->toBe('2026-08-20 19:00:00')
        ->and($eventResourceData['sector'])->toBe('Preferencial');
});

test('invitation success resource handles multiple tickets', function () {
    $tickets = collect(range(1, 5))->map(function ($i) {
        return [
            'ticket_code' => "TCK-MULTI{$i}",
            'event_name' => 'Multi Event',
            'sector' => 'General',
        ];
    })->toArray();

    $eventData = (object) [
        'name' => 'Multi Event',
        'date' => now()->addDays(10)->toDateTimeString(),
        'sector' => 'General',
    ];

    $data = (object) [
        'message' => 'Invitation redeemed successfully',
        'event' => $eventData,
        'tickets' => $tickets,
    ];

    $resource = new InvitationSuccessResource($data);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed['tickets'])
        ->toBeArray()
        ->toHaveCount(5)
        ->and($transformed['tickets'][0]['ticket_code'])->toBe('TCK-MULTI1')
        ->and($transformed['tickets'][4]['ticket_code'])->toBe('TCK-MULTI5');
});

test('invitation success resource handles empty tickets array', function () {
    $eventData = (object) [
        'name' => 'Empty Tickets Event',
        'date' => now()->toDateTimeString(),
        'sector' => 'A1',
    ];

    $data = (object) [
        'message' => 'No tickets generated',
        'event' => $eventData,
        'tickets' => [],
    ];

    $resource = new InvitationSuccessResource($data);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed['tickets'])
        ->toBeArray()
        ->toBeEmpty();
});

test('invitation success resource message is customizable', function () {
    $messages = [
        'Invitation redeemed successfully',
        'Welcome! Your tickets are ready',
        'Redemption completed',
    ];

    foreach ($messages as $message) {
        $eventData = (object) [
            'name' => 'Event',
            'date' => now()->toDateTimeString(),
            'sector' => 'A1',
        ];

        $data = (object) [
            'message' => $message,
            'event' => $eventData,
            'tickets' => [],
        ];

        $resource = new InvitationSuccessResource($data);
        $request = Request::create('/test');
        $transformed = $resource->toArray($request);

        expect($transformed['message'])->toBe($message);
    }
});
