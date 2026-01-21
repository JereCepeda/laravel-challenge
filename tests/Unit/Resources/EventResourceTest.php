<?php

use Illuminate\Http\Request;
use App\Http\Resources\Ticket\EventResource;

test('event resource transforms data correctly', function () {
    $eventData = (object) [
        'name' => 'Summer Festival 2026',
        'date' => '2026-07-15 18:00:00',
        'sector' => 'General',
    ];

    $resource = new EventResource($eventData);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('date')
        ->toHaveKey('sector')
        ->and($transformed['name'])->toBe('Summer Festival 2026')
        ->and($transformed['date'])->toBe('2026-07-15 18:00:00')
        ->and($transformed['sector'])->toBe('General');
});

test('event resource has correct structure', function () {
    $eventData = (object) [
        'name' => 'Test Event',
        'date' => now()->toDateTimeString(),
        'sector' => 'A1',
    ];

    $resource = new EventResource($eventData);
    $request = Request::create('/test');
    
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toBeArray()
        ->toHaveKeys(['name', 'date', 'sector'])
        ->and(count($transformed))->toBe(3); // Solo 3 campos
});

test('event resource handles different sectors', function () {
    $sectors = ['VIP', 'General', 'A1', 'B2', 'Preferencial'];

    foreach ($sectors as $sector) {
        $eventData = (object) [
            'name' => 'Event',
            'date' => now()->toDateTimeString(),
            'sector' => $sector,
        ];

        $resource = new EventResource($eventData);
        $request = Request::create('/test');
        $transformed = $resource->toArray($request);

        expect($transformed['sector'])->toBe($sector);
    }
});
