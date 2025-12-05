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
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Event Organizer with full permissions',
                'permissions' => [
                        'view_statistics',
                        'view_redemptions_history',
                        'view_reports'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Content Checker',
                'slug' => 'checker',
                'description' => 'Content checker with limited permissions',
                'permissions' => [ 
                    'validate_tickets',
                    'check_ticket_status'
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
