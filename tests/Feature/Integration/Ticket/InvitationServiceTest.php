<?php

namespace Tests\Integration\Ticket;

use Tests\TestCase;
use App\Models\Ticket;
use App\Models\InvitationRedemption;
use App\Services\Ticket\InvitationService;
use App\Services\Ticket\ExternalApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_redeem_invitation_returns_service_structure()
    {
        // ✅ Configurar mock ANTES de resolver el servicio
        $this->mock(ExternalApiService::class, function ($mock) {
            $mock->shouldReceive('getInvitationData')
                ->once()
                ->with('a8f22d')
                ->andReturn([
                    'invitation_id' => 'a8f22d',
                    'event_name' => 'Concert',
                    'event_date' => '2024-12-31 20:00:00',
                    'sector' => 'VIP',
                    'guest_count' => 2
                ]);
        });

        // ✅ Resolver el servicio DESPUÉS del mock
        $invitationService = app(InvitationService::class);
        $result = $invitationService->redeemInvitation('a8f22d');

        $this->assertArrayHasKey('invitation_data', $result);
        $this->assertArrayHasKey('tickets', $result);
        $this->assertArrayHasKey('redemption', $result);

        $this->assertEquals([
            'invitation_id' => 'a8f22d',
            'event_name' => 'Concert',
            'event_date' => '2024-12-31 20:00:00',
            'sector' => 'VIP',
            'guest_count' => 2
        ], $result['invitation_data']);

        $this->assertCount(2, $result['tickets']);
        foreach ($result['tickets'] as $ticket) {
            $this->assertInstanceOf(Ticket::class, $ticket);
            $this->assertMatchesRegularExpression('/^TCK-[A-Z0-9]{8}$/', $ticket->ticket_code);
            $this->assertEquals('a8f22d', $ticket->invitation_id);
            $this->assertEquals('Concert', $ticket->event_name);
        }

        $this->assertInstanceOf(InvitationRedemption::class, $result['redemption']);
        $this->assertEquals('a8f22d', $result['redemption']->invitation_id);
        $this->assertEquals(2, $result['redemption']->tickets_generated);

        $this->assertDatabaseHas('invitation_redemptions', [
            'invitation_id' => 'a8f22d',
            'event_name' => 'Concert',
            'tickets_generated' => 2
        ]);
        
        $this->assertDatabaseCount('tickets', 2);
    }

    public function test_prevents_double_redemption()
    {
        $this->mock(ExternalApiService::class, function ($mock) {
            $mock->shouldReceive('getInvitationData')
                ->once()
                ->with('a8f22d')
                ->andReturn([
                    'invitation_id' => 'a8f22d',
                    'event_name' => 'Concert',
                    'event_date' => '2024-12-31 20:00:00',
                    'sector' => 'VIP',
                    'guest_count' => 2
                ]);
        });

        $invitationService = app(InvitationService::class);
        $invitationService->redeemInvitation('a8f22d');

        $this->expectException(\App\Exceptions\InvitationException::class);
        $this->expectExceptionMessage('Invitation already redeemed');
        
        // ✅ Segunda llamada usa la verificación en BD, no necesita mock
        $invitationService->redeemInvitation('a8f22d');
    }

    public function test_handles_external_api_failure()
    {
        $this->mock(ExternalApiService::class, function ($mock) {
            $mock->shouldReceive('getInvitationData')
                ->once()
                ->with('invalidhash')
                ->andThrow(new \App\Exceptions\ExternalApiException('External API error', 503));
        });

        $invitationService = app(InvitationService::class);

        $this->expectException(\App\Exceptions\ExternalApiException::class);
        $this->expectExceptionMessage('External API error');

        $invitationService->redeemInvitation('invalidhash');
    }

    public function test_validates_guest_count_from_external_api()
    {
        $this->mock(ExternalApiService::class, function ($mock) {
            $mock->shouldReceive('getInvitationData')
                ->once()
                ->with('b9g33e')
                ->andReturn([
                    'invitation_id' => 'b9g33e',
                    'event_name' => 'Large Event',
                    'event_date' => '2024-12-31 20:00:00',
                    'sector' => 'General',
                    'guest_count' => 5
                ]);
        });

        $invitationService = app(InvitationService::class);
        $result = $invitationService->redeemInvitation('b9g33e');

        $this->assertCount(5, $result['tickets']);
        $this->assertEquals(5, $result['redemption']->tickets_generated);
        $this->assertDatabaseCount('tickets', 5);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}