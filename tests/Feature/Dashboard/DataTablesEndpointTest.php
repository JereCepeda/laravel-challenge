<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** 
     * @var Role $this->adminRole
     * @var Role $this->checkerRole
     * @var User $this->adminUser
     * @var User $this->checkerUser
     */

    $this->adminRole = Role::factory()->create([
        'slug' => 'admin',
        'permissions' => ['view_statistics', 'view_reports', 'view_redemptions_history']
    ]);

    $this->checkerRole = Role::factory()->create([
        'slug' => 'checker',
        'permissions' => ['validate_tickets']
    ]);

    // Crear usuarios
    $this->adminUser = User::factory()->create([
        'email' => 'admin@test.com',
        'role_id' => $this->adminRole->id
    ]);

    $this->checkerUser = User::factory()->create([
        'email' => 'checker@test.com',
        'role_id' => $this->checkerRole->id
    ]);

    // Crear tickets de prueba con estructura correcta
    $this->tickets = Ticket::factory()->count(5)->create([
        'event_name' => 'Concierto Rock 2025',
        'event_date' => '2025-12-20',
        'sector' => 'VIP',
        'is_validated' => true,
        'validated_at' => now()
    ]);
});

test('admin can fetch used tickets via DataTables endpoint', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK);
});

test('DataTables endpoint accepts draw parameter', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=1', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data'
            ]);
});

test('DataTables endpoint returns correct structure', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=15', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    $data = $response->json();
    
    expect($data)->toHaveKeys(['draw', 'recordsTotal', 'recordsFiltered', 'data'])
        ->and($data['draw'])->toBe(1)
        ->and($data['recordsTotal'])->toBeGreaterThanOrEqual(0)
        ->and($data['recordsFiltered'])->toBeGreaterThanOrEqual(0)
        ->and($data['data'])->toBeArray();
});

test('DataTables endpoint returns tickets with correct fields', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=15', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    $data = $response->json();
    
    if (count($data['data']) > 0) {
        $ticket = $data['data'][0];
        expect($ticket)->toHaveKeys([
            'qr_code',
            'event_name',
            'event_date',
            'sector',
            'validated_at'
        ]);
    }
});

test('DataTables endpoint accepts all required parameters', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $params = [
        'draw' => 1,
        'start' => 0,
        'length' => 15,
        'columns' => [
            ['data' => 'qr_code', 'searchable' => true, 'orderable' => true],
            ['data' => 'event_name', 'searchable' => true, 'orderable' => true],
            ['data' => 'event_date', 'searchable' => true, 'orderable' => true],
            ['data' => 'sector', 'searchable' => true, 'orderable' => true],
            ['data' => 'user', 'searchable' => true, 'orderable' => true],
            ['data' => 'validated_at', 'searchable' => true, 'orderable' => true],
        ],
        'order' => [
            ['column' => 5, 'dir' => 'desc']
        ],
        'search' => [
            'value' => '',
            'regex' => false
        ]
    ];

    $response = $this->getJson('/api/admin/tickets/used?' . http_build_query($params), [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK);
});

test('DataTables endpoint accepts custom filters', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $params = [
        'draw' => 1,
        'start' => 0,
        'length' => 15,
        'event_name' => 'Concierto Rock 2025',
        'sector' => 'VIP',
        'event_date' => '2025-12-20'
    ];

    $response = $this->getJson('/api/admin/tickets/used?' . http_build_query($params), [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data'
            ]);
});

test('DataTables endpoint handles pagination correctly', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    // Primera página
    $response1 = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=2', [
        'Accept' => 'application/json'
    ]);

    $response1->assertStatus(Response::HTTP_OK);
    $data1 = $response1->json();
    
    expect($data1['data'])->toHaveCount(min(2, $data1['recordsTotal']));

    // Segunda página
    if ($data1['recordsTotal'] > 2) {
        
        $response2 = $this->getJson('/api/admin/tickets/used?draw=2&start=2&length=2', [
            'Accept' => 'application/json'
        ]);

        $response2->assertStatus(Response::HTTP_OK);
        $data2 = $response2->json();
        
        expect($data2['draw'])->toBe(2);
    }
});

test('DataTables endpoint filters by event name', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Ticket::factory()->create([
        'event_name' => 'Festival Jazz 2025',
        'event_date' => '2025-12-25',
        'sector' => 'Platea',
        'is_validated' => true,
        'validated_at' => now()
    ]);

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=15&event_name=Festival Jazz 2025', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    $data = $response->json();
    
    if (count($data['data']) > 0) {
        foreach ($data['data'] as $ticket) {
            expect($ticket['event_name'])->toBe('Festival Jazz 2025');
        }
    }
});

test('DataTables endpoint filters by sector', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    
    Ticket::factory()->create([
        'event_name' => 'Concierto Rock 2025',
        'event_date' => '2025-12-20',
        'sector' => 'Platea',
        'is_validated' => true,
        'validated_at' => now()
    ]);

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=15&sector=Platea', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    $data = $response->json();
    
    if (count($data['data']) > 0) {
        foreach ($data['data'] as $ticket) {
            expect($ticket['sector'])->toBe('Platea');
        }
    }
});

test('DataTables endpoint returns empty data when no tickets match filters', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=15&event_name=NonExistentEvent', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    $data = $response->json();
    
    expect($data['data'])->toBeArray()
        ->and($data['recordsFiltered'])->toBe(0);
});

test('DataTables endpoint increments draw parameter correctly', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=5&start=0&length=15', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['draw' => 5]);
});

test('DataTables endpoint respects length parameter', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=3', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    $data = $response->json();
    
    expect(count($data['data']))->toBeLessThanOrEqual(3);
});

test('checker cannot access DataTables endpoint', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->checkerUser
     */
    Passport::actingAs($this->checkerUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=15', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('unauthenticated user cannot access DataTables endpoint', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    $response = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=15', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED); // 401 para JSON requests
});

test('DataTables endpoint validates integer parameters', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=invalid&start=0&length=15', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('DataTables endpoint validates length bounds', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    // Length mayor a 100 debe fallar
    $response = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=150', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('DataTables endpoint returns data in correct format for frontend rendering', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/admin/tickets/used?draw=1&start=0&length=15', [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    $data = $response->json();
    
    // Verificar que cada ticket tiene los campos necesarios para el renderizado
    if (count($data['data']) > 0) {
        $ticket = $data['data'][0];
        
        // QR Code debe ser string
        expect($ticket['qr_code'])->toBeString();
        
        // Event name debe ser string
        expect($ticket['event_name'])->toBeString();
        
        // Event date debe ser válido
        expect($ticket['event_date'])->toBeString();
        
        // Sector debe ser string
        expect($ticket['sector'])->toBeString();
        
        // Validated_at puede ser null o string
        $validatedAt = $ticket['validated_at'];
        expect($validatedAt === null || is_string($validatedAt))->toBeTrue();
    }
});

test('DataTables endpoint handles cache buster parameter', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    
    Passport::actingAs($this->adminUser);

    $timestamp = time() * 1000; // Simular timestamp JavaScript
    
    $response = $this->getJson("/api/admin/tickets/used?draw=1&start=0&length=15&_={$timestamp}", [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_OK);
});
