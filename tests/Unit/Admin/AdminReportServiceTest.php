<?php

// namespace Tests\Unit\Admin;

// use Tests\TestCase;
// use App\Models\Ticket;
// use App\Models\InvitationRedemption;
// use App\Services\Admin\AdminReportService;
// use Illuminate\Foundation\Testing\RefreshDatabase;

// class AdminReportServiceTest extends TestCase
// {
//     use RefreshDatabase;

//     private AdminReportService $service;
//     private Ticket $ticket;
//     private InvitationRedemption $invitationRedemption;

//     protected function setUp(): void
//     {
//         parent::setUp();
//         $this->service = new AdminReportService();
//         $this->invitationRedemption = InvitationRedemption::factory()->count(3)->create();
//         foreach($this->invitationRedemption as $invitation) {
//             Ticket::factory()->create([
//                 'invitation_id' => $invitation->invitation_id,
//                 'is_validated' => true,
//                 'validated_at' => now(),
//             ]);
//         }
//     }

//     public function test_get_used_tickets_for_event_filters_correctly()
//     {
//         $result = $this->service->getUsedTicketsForEvent(
//             eventName: $this->invitationRedemption[0]->event_name,
//             filters: ['event_name' => $this->invitationRedemption[0]->event_name],
//             perPage: 10
//         );
//         $this->assertEquals(1, $result->total());
//         $this->assertStringContainsString('Rock Concert', $result->items()[0]->event_name);
//     }

//     public function test_get_redemption_history_applies_filters()
//     {
//         // Arrange
//         InvitationRedemption::factory()->create([
//             'event_name' => 'Rock Concert',
//             'sector' => 'VIP',
//             'redeemed_at' => now()
//         ]);

//         InvitationRedemption::factory()->create([
//             'event_name' => 'Jazz Night',
//             'sector' => 'General',
//             'redeemed_at' => now()
//         ]);

//         // Act
//         $result = $this->service->getRedemptionHistory(['event_name' => 'Rock', 'sector' => 'VIP']);

//         // Assert
//         $this->assertEquals(1, $result->total());
//         $this->assertEquals('VIP', $result->items()[0]->sector);
//     }
// }
