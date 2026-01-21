<?php

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Resources\Ticket\TicketResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ticket resource transforms data correctly', function () {
    $ticket = new Ticket([
        'ticket_code' => 'TCK-TEST1234',
        'event_name' => 'Rock Concert',
        'sector' => 'VIP',
        'is_validated' => false,
        'validated_at' => null,
    ]);

    $resource = new TicketResource($ticket);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toHaveKey('code')
        ->toHaveKey('event')
        ->toHaveKey('sector')
        ->and($transformed['code'])->toBe('TCK-TEST1234')
        ->and($transformed['event'])->toBe('Rock Concert')
        ->and($transformed['sector'])->toBe('VIP');
});

test('ticket resource excludes validated_at when ticket is not validated', function () {
    $ticket = new Ticket([
        'ticket_code' => 'TCK-NOT123',
        'event_name' => 'Test Event',
        'sector' => 'A1',
        'is_validated' => false,
        'validated_at' => null,
    ]);

    $resource = new TicketResource($ticket);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toHaveKeys(['code', 'event', 'sector'])
        ->and($transformed['code'])->toBe('TCK-NOT123');
});

test('ticket resource has correct structure', function () {
    $ticket = new Ticket([
        'ticket_code' => 'TCK-STRUCT1',
        'event_name' => 'Structure Event',
        'sector' => 'B1',
        'is_validated' => false,
    ]);
    $resource = new TicketResource($ticket);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toBeArray()
        ->toHaveKeys(['code', 'event', 'sector']);
});
