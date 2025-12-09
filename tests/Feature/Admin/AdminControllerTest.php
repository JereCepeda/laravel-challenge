<?php

namespace Tests\Feature\Admin;

use App\Models\InvitationRedemption;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Ticket;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $checkerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::factory()->create([
            'slug' => 'admin',
            'is_active' => true,
            'permissions' => json_encode(['view_statistics', 'view_reports', 'view_redemptions_history'])
        ]);
            
        $checkerRole = Role::factory()->create([
            'slug' => 'checker',
            'is_active' => true,
            'permissions' => json_encode(['validate_tickets', 'check_ticket_status'])
        ]);            
            
        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);
        $this->checkerUser = User::factory()->create(['role_id' => $checkerRole->id]);
    }

    public function test_admin_can_access_redemption_history()
    {
        $redemption = InvitationRedemption::factory()->create([
            'event_name' => 'Test Concert',
            'sector' => 'VIP',
            'guest_count' => 2,
            'tickets_generated' => 2,
            'redeemed_at' => now()
        ]);

        Passport::actingAs($this->adminUser, [], 'api');
        
        $response = $this->getJson('/api/admin/redemptions/history');
                
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data',
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'has_more_pages'
                    ]
                ])
                ->assertJsonPath('meta.total', 1);
    }

    public function test_admin_can_access_used_tickets_for_specific_event()
    {
        $redemption = InvitationRedemption::factory()->create([
            'event_name' => 'Rock Festival 2024',
            'invitation_id' => 'TEST123'
        ]);

        $ticket = Ticket::factory()->create([
            'invitation_id' => 'TEST123',
            'event_name' => 'Rock Festival 2024',
            'is_validated' => true,
            'validated_at' => now(),
            'validated_by' => $this->checkerUser->id
        ]);

        Passport::actingAs($this->adminUser, [], 'api');
        
        $response = $this->getJson('/api/admin/tickets/used/'.$redemption->event_name);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'event_searched',
                    'data',
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'has_more_pages'
                    ]
                ])
                ->assertJsonPath('event_searched', 'Rock Festival 2024')
                ->assertJsonPath('meta.total', 1);
    }

    public function test_checker_cannot_access_admin_routes()
    {
        Passport::actingAs($this->checkerUser, [], 'api');

        $response = $this->getJson('/api/admin/redemptions/history');
        $response->assertStatus(403);

        $response = $this->getJson('/api/admin/tickets/used/Test%20Event');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_routes()
    {
        $response = $this->getJson('/api/admin/redemptions/history');
        $response->assertStatus(401);

        $response = $this->getJson('/api/admin/tickets/used/Test%20Event');
        $response->assertStatus(401);
    }
}