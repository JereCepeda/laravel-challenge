<?php

namespace Tests\Feature\Ticket;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use App\Services\Ticket\TicketValidationService;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::factory()->create([
            'slug' => 'checker',
            'permissions' => ['validate_tickets'],
            'is_active' => true
        ]);

        $this->user = User::factory()->create([
            'role_id' => $this->role->id
        ]);
        
        // ✅ Especificar scope 'api'
        Passport::actingAs($this->user, [], 'api');
    }

    public function test_validate_ticket_endpoint_success()
    {
        $ticket = Ticket::factory()->create([
            'ticket_code' => 'TCK-VALID123',
            'event_name' => 'Test Event',
            'event_date' => now()->addDays(1),
            'sector' => 'A1',
            'is_validated' => false,
        ]);

        $response = $this->postJson('/api/tickets/validate', [
            'ticket_code' => $ticket->ticket_code
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'access_granted' => true,
                    'message' => "Access granted - Welcome to {$ticket->event_name}",
                    'ticket_info' => [
                        'code' => $ticket->ticket_code,
                        'event' => $ticket->event_name,
                        'sector' => $ticket->sector,
                    ]
                ]);
    }

    public function test_validate_ticket_endpoint_invalid_ticket()
    {
        $response = $this->postJson('/api/tickets/validate', [
            'ticket_code' => 'TCK-NOTFOUND'
        ]);

        $response->assertStatus(404)
                ->assertJsonStructure([
                'access_granted',
                'message',
                'error_code'
            ])
            ->assertJson([
                'access_granted' => false,
                'message' => 'Access denied: Ticket not found',
                'error_code' => 'TICKET_NOT_FOUND'
            ]);
    }

    public function test_validate_ticket_endpoint_already_validated()
    {
        $ticket = Ticket::factory()->create([
            'ticket_code' => 'TCK-USED0123',
            'is_validated' => true,
            'validated_at' => now()->subHour(),
            'validated_by' => $this->user->id
        ]);

        $response = $this->postJson('/api/tickets/validate', [
            'ticket_code' => $ticket->ticket_code
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'access_granted' => false,
                'message' => 'Access denied: Ticket already validated',
                'error_code' => 'TICKET_ALREADY_VALIDATED'
            ]);
    }

    public function test_validate_ticket_endpoint_event_passed()
    {
        $ticket = Ticket::factory()->create([
            'ticket_code' => 'TCK-EXPIRED1',
            'event_date' => now()->subDays(1), 
            'is_validated' => false
        ]);

        $response = $this->postJson('/api/tickets/validate', [
            'ticket_code' => $ticket->ticket_code
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'access_granted' => false,
                'message' => 'Access denied: Event has already passed',
                'error_code' => 'EVENT_PASSED'
            ]);
    }

    public function test_validate_ticket_endpoint_validation_errors()
    {
        $response = $this->postJson('/api/tickets/validate', [
            'ticket_code' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ticket_code'])
            ->assertJsonFragment([
                'ticket_code' => ['Ticket code is required.']
            ]);
    }

    public function test_validate_ticket_endpoint_invalid_format()
    {
        $response = $this->postJson('/api/tickets/validate', [
            'ticket_code' => 'INVALIDFORMAT'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ticket_code'])
            ->assertJsonFragment([
                'ticket_code' => ['Invalid ticket code format. Expected format: TCK-XXXXXXXX']
            ]);
    }

    public function test_validate_ticket_endpoint_requires_authentication()
    {
        $this->app['auth']->forgetGuards();
        $this->withoutToken();


        $response = $this->postJson('/api/tickets/validate', [
            'ticket_code' => 'TCK-TEST0123'
        ]);

        $response->assertStatus(401);
    }

    public function test_validate_ticket_endpoint_handles_service_exception()
    {
        $this->mock(TicketValidationService::class, function ($mock) {
            $mock->shouldReceive('validateTicket')
                ->with('TCK-TEST0123')
                ->andThrow(new \Exception('Database error'));
        });

        $response = $this->postJson('/api/tickets/validate', [
            'ticket_code' => 'TCK-TEST0123'
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'access_granted' => false,
                'message' => 'Validation service temporarily unavailable'
            ]);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}