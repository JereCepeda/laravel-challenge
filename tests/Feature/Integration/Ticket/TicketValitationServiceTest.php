<?php

namespace Tests\Integration\Ticket;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Ticket;
use App\Services\Ticket\TicketValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;

class TicketValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private TicketValidationService $ticketValidationService;
    private User $validatorUser;
    private Role $activeRole;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->activeRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Admin role',
            'permissions' => ['manage_events', 'manage_users'],
            'is_active' => true
        ]);

        $this->validatorUser = User::factory()->create([
            'role_id' => $this->activeRole->id,
            'email' => 'validator@example.com',
            'password' => Hash::make('password123')
        ]);

        Passport::actingAs($this->validatorUser, [], 'api');

        $this->ticketValidationService = new TicketValidationService();
    }

    public function test_validate_ticket_success()
    {
        $ticket = Ticket::create([
            'ticket_code' => 'TCK-VALID1',
            'invitation_id' => 'INV-12345',
            'event_name' => 'Music Festival',
            'event_date' => now()->addDays(5),
            'sector' => 'A1',
            'is_validated' => false,
            'redeemed_ip' => '127.0.0.1',
        ]);

        $result = $this->ticketValidationService->validateTicket('TCK-VALID1');

        $this->assertTrue($result['access_granted']);
        $this->assertEquals('Access granted - Welcome to Music Festival', $result['message']);
        $this->assertEquals('TCK-VALID1', $result['ticket']['ticket_code']);

        $ticket->refresh();
        $this->assertTrue($ticket->is_validated);
        $this->assertNotNull($ticket->validated_at);
        $this->assertEquals($this->validatorUser->id, $ticket->validated_by);
    }

    public function test_validate_ticket_not_found()
    {
        $this->expectException(\App\Exceptions\TicketException::class);
        $this->expectExceptionMessage('Ticket not found');
        $this->expectExceptionCode(404);

        $this->ticketValidationService->validateTicket('TCK-NOTFOUND');
    }

    public function test_validate_ticket_already_validated()
    {
        Ticket::create([
            'ticket_code' => 'TCK-USED1',
            'invitation_id' => 'INV-12346',
            'event_name' => 'Concert',
            'event_date' => now()->addDays(3),
            'sector' => 'VIP',
            'is_validated' => true, 
            'validated_at' => now()->subHour(),
            'validated_by' => $this->validatorUser->id,
            'redeemed_ip' => '127.0.0.1',
        ]);

        $this->expectException(\App\Exceptions\TicketException::class);
        $this->expectExceptionMessage('Ticket already validated');
        $this->expectExceptionCode(409);

        $this->ticketValidationService->validateTicket('TCK-USED1');
    }

    public function test_validate_ticket_event_passed()
    {
        Ticket::create([
            'ticket_code' => 'TCK-EXPIRED',
            'invitation_id' => 'INV-12347',
            'event_name' => 'Past Event',
            'event_date' => now()->subDays(1), 
            'sector' => 'General',
            'is_validated' => false,
            'redeemed_ip' => '127.0.0.1',
        ]);

        $this->expectException(\App\Exceptions\TicketException::class);
        $this->expectExceptionMessage('Event has already passed');
        $this->expectExceptionCode(400);

        $this->ticketValidationService->validateTicket('TCK-EXPIRED');
    }

    public function test_get_ticket_info_returns_ticket()
    {
        $ticket = Ticket::create([
            'ticket_code' => 'TCK-INFO1',
            'invitation_id' => 'INV-12348',
            'event_name' => 'Test Event',
            'event_date' => now()->addDays(2),
            'sector' => 'B1',
            'is_validated' => false,
            'redeemed_ip' => '127.0.0.1',
        ]);

        $result = $this->ticketValidationService->getTicketInfo('TCK-INFO1');

        $this->assertInstanceOf(Ticket::class, $result);
        $this->assertEquals('TCK-INFO1', $result->ticket_code);
        $this->assertEquals('Test Event', $result->event_name);
    }

    public function test_get_ticket_info_returns_null()
    {
        $result = $this->ticketValidationService->getTicketInfo('TCK-NOTEXIST');
        $this->assertNull($result);
    }

    public function test_is_ticket_validated_returns_correct_boolean()
    {
        Ticket::create([
            'ticket_code' => 'TCK-VALIDATED',
            'invitation_id' => 'INV-12349',
            'event_name' => 'Event',
            'event_date' => now()->addDays(1),
            'sector' => 'A1',
            'is_validated' => true,
            'redeemed_ip' => '127.0.0.1',
        ]);

        Ticket::create([
            'ticket_code' => 'TCK-PENDING',
            'invitation_id' => 'INV-12350',
            'event_name' => 'Event',
            'event_date' => now()->addDays(1),
            'sector' => 'A2',
            'is_validated' => false,
            'redeemed_ip' => '127.0.0.1',
        ]);

        $this->assertTrue($this->ticketValidationService->isTicketValidated('TCK-VALIDATED'));
        $this->assertFalse($this->ticketValidationService->isTicketValidated('TCK-PENDING'));
        $this->assertFalse($this->ticketValidationService->isTicketValidated('TCK-NOTEXIST'));
    }

    public function test_ticket_event_passed_returns_correct_boolean()
    {
        Ticket::create([
            'ticket_code' => 'TCK-PAST',
            'event_date' => now()->subDays(1),
            'invitation_id' => 'INV-PAST',
            'event_name' => 'Past Event',
            'sector' => 'A1',
            'redeemed_ip' => '127.0.0.1',
        ]);

        Ticket::create([
            'ticket_code' => 'TCK-FUTURE',
            'event_date' => now()->addDays(1),
            'invitation_id' => 'INV-FUTURE',
            'event_name' => 'Future Event',
            'sector' => 'A1',
            'redeemed_ip' => '127.0.0.1',
        ]);

        
        $this->assertTrue($this->ticketValidationService->ticketEventPassed('TCK-PAST'));
        $this->assertFalse($this->ticketValidationService->ticketEventPassed('TCK-FUTURE'));
        $this->assertFalse($this->ticketValidationService->ticketEventPassed('TCK-NOTEXIST'));
    }

    public function test_mark_ticket_as_validated()
    {
        $ticket = Ticket::create([
            'ticket_code' => 'TCK-TOMARK',
            'invitation_id' => 'INV-MARK',
            'event_name' => 'Event',
            'event_date' => now()->addDays(1),
            'sector' => 'A1',
            'is_validated' => false,
            'redeemed_ip' => '127.0.0.1',
        ]);

        $this->ticketValidationService->markTicketAsValidated('TCK-TOMARK');

        $ticket->refresh();
        $this->assertTrue($ticket->is_validated);
        $this->assertNotNull($ticket->validated_at);
        $this->assertEquals($this->validatorUser->id, $ticket->validated_by);
    }
}