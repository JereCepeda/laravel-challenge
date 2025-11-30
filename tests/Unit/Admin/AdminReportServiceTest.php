<?php

namespace Tests\Unit\Admin;

use Tests\TestCase;
use App\Models\Ticket;
use App\Models\InvitationRedemption;
use App\Models\User;
use App\Services\Admin\AdminReportService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminReportService $service;
    private Collection $tickets;
    private Collection $redemptions;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->redemptions = InvitationRedemption::factory()->count(2)->create();
        $this->tickets = new Collection();
        $role = \App\Models\Role::factory()->create(['slug' => 'admin']);
        $this->user = User::factory()->create(['role_id' => $role->id]);
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
        $this->service = new AdminReportService();
    }

    public function test_get_used_tickets_for_event_filters_correctly()
    {
        $result = $this->service->getUsedTicketsForEvent(
            eventName: $this->redemptions[0]->event_name,
            filters: ['event_name' => $this->redemptions[0]->event_name],
            perPage: 10
        );
        $this->assertEquals(3, $result->total());
        $this->assertStringContainsString($this->redemptions[0]->event_name, $result->items()[0]->event_name);
    }

    public function test_get_redemption_history_applies_filters()
    {
        $result = $this->service->getRedemptionHistory(
            filters: ['sector' => $this->redemptions[1]->sector],
            perPage: 10
        );

        $this->assertEquals(1, $result->total());
        $this->assertEquals($this->redemptions[1]->sector, $result->items()[0]->sector);
    }
}
