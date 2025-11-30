<?php

namespace Tests\Feature\Integration\Admin;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Ticket;
use Laravel\Passport\Passport;
use App\Models\InvitationRedemption;
use App\Services\Admin\AdminReportService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminReportService $service;
    private User $user;
    private Collection $redemptions;
    private Collection $tickets;

    protected function setUp(): void
    {
        parent::setUp();
        
        $role = Role::factory()->create(['slug' => 'admin']);
        $this->user = User::factory()->create(['role_id' => $role->id]);
        $this->redemptions = InvitationRedemption::factory()->count(2)->create();
        $this->tickets = new Collection();

        foreach ($this->redemptions as $redemption) {
            $tickets = Ticket::factory()->count(3)->create([
                'invitation_id' => $redemption->invitation_id,
                'event_name' => $redemption->event_name,
                'is_validated' => true,
                'validated_at' => now(),
                'validated_by' => $this->user->id
            ]);
            $this->tickets = $this->tickets->merge($tickets);
        }

        Passport::actingAs($this->user, [], 'api');
        $this->service = new AdminReportService();
    }

    private function createEventWithValidatedTickets(string $eventName, int $ticketCount = 3, array $ticketOverrides = []): array 
    {
        $redemption = InvitationRedemption::factory()->create([
            'event_name' => $eventName,
            'invitation_id' => 'INV_' . uniqid(),
            'sector' => $ticketOverrides['sector'] ?? 'General'
        ]);

        $defaultTicketData = [
            'invitation_id' => $redemption->invitation_id,
            'event_name' => $eventName,
            'sector' => $redemption->sector,
            'is_validated' => true,
            'validated_at' => now(),
            'validated_by' => $this->user->id
        ];

        $tickets = Ticket::factory()->count($ticketCount)->create(
            array_merge($defaultTicketData, $ticketOverrides)
        );

        return [
            'redemption' => $redemption,
            'tickets' => $tickets,
            'event_name' => $eventName,
            'total_tickets' => $ticketCount
        ];
    }

    private function createTicketsWithSectors(string $eventName, array $sectors): array
    {
        $allTickets = collect();
        $redemptions = collect();

        foreach ($sectors as $sector => $count) {
            $eventData = $this->createEventWithValidatedTickets($eventName, $count, [
                'sector' => $sector
            ]);
            
            $allTickets = $allTickets->merge($eventData['tickets']);
            $redemptions->push($eventData['redemption']);
        }

        return [
            'tickets' => $allTickets,
            'redemptions' => $redemptions,
            'total_count' => $allTickets->count()
        ];
    }

    public function test_get_used_tickets_for_event_filters_correctly()
    {
        $event_name = $this->tickets->first()->event_name;
        $result = $this->service->getUsedTicketsForEvent($event_name);
        
        $expectedCount = $this->tickets->where('event_name', $event_name)->count();
        $this->assertEquals($expectedCount, $result->total());
        $this->assertStringContainsString($event_name, $result->items()[0]->event_name);
    }

    public function test_get_used_tickets_for_event_applies_sector_filter()
    {
        $ticketsData = $this->createTicketsWithSectors('Music Festival', [
            'VIP' => 2,
            'General' => 3
        ]);

        $result = $this->service->getUsedTicketsForEvent('Music Festival', ['sector' => 'VIP']);

        $this->assertEquals(2, $result->total());
        foreach ($result->items() as $ticket) {
            $this->assertEquals('VIP', $ticket->sector);
        }
    }

    public function test_get_used_tickets_excludes_non_validated_tickets()
    {
        $redemption = InvitationRedemption::factory()->create([
            'event_name' => 'Test Event',
            'invitation_id' => 'TEST004'
        ]);

        Ticket::factory()->create([
            'invitation_id' => $redemption->invitation_id,
            'event_name' => $redemption->event_name,
            'is_validated' => true,
            'validated_at' => now(),
            'validated_by' => $this->user->id
        ]);

        Ticket::factory()->create([
            'invitation_id' => $redemption->invitation_id,
            'event_name' => $redemption->event_name,
            'is_validated' => false,
            'validated_at' => null
        ]);

        $result = $this->service->getUsedTicketsForEvent($redemption->event_name);

        $this->assertEquals(1, $result->total());
        $this->assertTrue($result->items()[0]->is_validated);
    }

    public function test_get_redemption_history_applies_filters()
    {
        InvitationRedemption::factory()->create([
            'event_name' => 'Rock Concert',
            'sector' => 'VIP',
            'redeemed_at' => now()
        ]);

        InvitationRedemption::factory()->create([
            'event_name' => 'Jazz Night',
            'sector' => 'General',
            'redeemed_at' => now()
        ]);

        $result = $this->service->getRedemptionHistory(['event_name' => 'Rock', 'sector' => 'VIP']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('VIP', $result->items()[0]->sector);
    }

    public function test_get_redemption_history_applies_date_filters()
    {
        InvitationRedemption::factory()->create([
            'event_name' => 'Past Event',
            'redeemed_at' => now()->subDays(5)
        ]);

        InvitationRedemption::factory()->create([
            'event_name' => 'Recent Event',
            'redeemed_at' => now()->subDays(1)
        ]);

        $result = $this->service->getRedemptionHistory([
            'from_date' => now()->subDays(2)->format('Y-m-d'),
            'to_date' => now()->format('Y-m-d')
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Recent Event', $result->items()[0]->event_name);
    }

    public function test_get_used_tickets_handles_empty_filters()
    {
        $redemption = InvitationRedemption::factory()->create([
            'event_name' => 'Test Event',
            'invitation_id' => 'TEST005'
        ]);

        Ticket::factory()->create([
            'invitation_id' => $redemption->invitation_id,
            'event_name' => $redemption->event_name,
            'is_validated' => true,
            'validated_at' => now(),
            'validated_by' => $this->user->id
        ]);

        $result = $this->service->getUsedTicketsForEvent($redemption->event_name, [
            'sector' => '',
            'from_date' => null,
            'to_date' => ''
        ]);

        $this->assertEquals(1, $result->total());
    }

    public function test_pagination_works_correctly()
    {
        $eventData = $this->createEventWithValidatedTickets('Big Event', 25);

        $result = $this->service->getUsedTicketsForEvent('Big Event', [], 10);

        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(3, $result->lastPage());
        $this->assertCount(10, $result->items());
    }

    public function test_multiple_sectors_filtering_works()
    {
        $this->createTicketsWithSectors('Festival 2024', [
            'VIP' => 5,
            'General' => 10,
            'Premium' => 3
        ]);

        $vipResult = $this->service->getUsedTicketsForEvent('Festival 2024', ['sector' => 'VIP']);
        $generalResult = $this->service->getUsedTicketsForEvent('Festival 2024', ['sector' => 'General']);
        $allResult = $this->service->getUsedTicketsForEvent('Festival 2024');

        $this->assertEquals(5, $vipResult->total());
        $this->assertEquals(10, $generalResult->total());
        $this->assertEquals(18, $allResult->total()); 
    }
}