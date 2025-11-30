<?php

// namespace Tests\Feature\Admin;

// use App\Models\InvitationRedemption;
// use Tests\TestCase;
// use App\Models\Role;
// use App\Models\User;
// use App\Models\Ticket;
// use Laravel\Passport\Passport;
// use Illuminate\Foundation\Testing\RefreshDatabase;

// class AdminControllerTest extends TestCase
// {
//     use RefreshDatabase;

//     private User $adminUser;
//     private Role $role;
//     private User $checkerUser;
//     private Role $checkerRole;
//     private InvitationRedemption $redemption;
//     private Ticket $ticket;
//     protected function setUp(): void
//     {
//         parent::setUp();

//         $this->role = Role::factory()->create(
//             ['slug' => 'admin','is_active' => true,'permissions' => json_encode(['view_statistics','view_reports','view_redemptions_history'])]);
            
//         $this->checkerRole = Role::factory()->create(['slug' => 'checker','is_active' => true,'permissions' => json_encode(['validate_tickets','check_ticket_status'])]);            
            
//         $this->adminUser = User::factory()->create(['role_id' => $this->role->id]);
            
//         $this->checkerUser = User::factory()->create(['role_id' => $this->checkerRole->id,]);
        
//         $this->redemption = InvitationRedemption::factory()->create();
//         $this->ticket = Ticket::factory()->create(['invitation_id' => $this->redemption->invitation_id]);
//     }

//     public function test_admin_routes_redemption_history()
//     {
        
//         Passport::actingAs($this->adminUser, [], 'api');
//         $response = $this->getJson('/api/admin/redemption/history');
        
//         $response->assertStatus(200);
//         $response->assertJsonStructure([
//             'message',
//             'data',
//             'meta' => [
//                 'current_page',
//                 'last_page',
//                 'per_page',
//                 'total',
//                 'has_more_pages'
//             ]
//         ]);
//     }
//     public function test_admin_routes_used_tickets()
//     {
//         $event_name = $this->redemption->event_name;
//         Passport::actingAs($this->adminUser, [], 'api');
//         $response = $this->getJson('/api/admin/tickets/used/'.urlencode($event_name));
//         $response->assertStatus(200);
//         $response->assertJsonStructure([
//             'message',
//             'data',
//             'meta' => [
//                 'current_page',
//                 'last_page',
//                 'per_page',
//                 'total',
//                 'has_more_pages'
//             ]
//         ]);
//     }
//     public function test_checker_cannot_access_admin_routes()
//     {
//         Passport::actingAs($this->checkerUser, [], 'api');
//         $response = $this->getJson('/api/admin/redemption/history');
//         $response->assertStatus(403);

//         $event_name = $this->redemption->event_name;

//         $response = $this->getJson('/api/admin/tickets/used/'.urlencode($event_name));
//         $response->assertStatus(403);
//     }
// }