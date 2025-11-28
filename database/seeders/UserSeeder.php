<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Test Checker',
                'email' => 'testchecker@example.com',
                'password' => bcrypt('password123'),
                'role_id' => \App\Models\Role::where('slug', 'checker')->first()->id
            ],
            [
                'name' => 'Test2 Checker',
                'email' => 'test2checker@example.com',
                'password' => bcrypt('password123'),
                'role_id' => \App\Models\Role::where('slug', 'checker')->first()->id
            ],
            [
                'name' => 'Test Admin',
                'email' => 'testadmin@example.com',
                'password' => bcrypt('password123'),
                'role_id' => \App\Models\Role::where('slug', 'admin')->first()->id
            ]
        ];
        foreach ($users as $user) {
            \App\Models\User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
