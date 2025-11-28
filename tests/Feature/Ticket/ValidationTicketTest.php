<?php

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);

beforeEach(function () {
    /** 
     * @var \Tests\TestCase $this
     * @var \Illuminate\Foundation\Application $this->app
    */
    $this->activeRole = Role::create([
        'name' => 'Administrator',
        'slug' => 'admin',
        'description' => 'Admin role',
        'permissions' => ['manage_events', 'manage_users'],
        'is_active' => true
    ]);

    $this->inactiveRole = Role::create([
        'name' => 'Inactive Role',
        'slug' => 'inactive',
        'description' => 'Inactive role',
        'permissions' => ['view_events'],
        'is_active' => false
    ]);
});

