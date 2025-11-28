<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Event Organizer with full permissions',
                'permissions' => [
                    'manage_events',
                    'manage_users',
                    'validate_tickets',
                    'scan_qr_codes',
                    'view_ticket_details',
                    'generate_reports',
                    'manage_settings'    
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Content Checker',
                'slug' => 'checker',
                'description' => 'Content checker with limited permissions',
                'permissions' => [ 
                    'validate_tickets',
                    'scan_qr_codes',
                    'view_ticket_details'
                ],
                'is_active' => true,
            ],
        ];
        foreach ($roles as $role) {
            Role::updateOrCreate(
                    ['slug' => $role['slug']],
                    $role
                );
        }
    }
}
