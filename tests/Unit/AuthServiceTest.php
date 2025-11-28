<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_password_works_correctly()
    {
        $authService = app(AuthService::class);
        $plainPassword = 'password123';
        $hashedPassword = Hash::make($plainPassword);

        $result = $authService->verifyPassword($plainPassword, $hashedPassword);
        $this->assertTrue($result);
        
        $result = $authService->verifyPassword('wrongpassword', $hashedPassword);
        $this->assertFalse($result);
    }

    public function test_is_user_active_returns_correct_boolean()
    {
        $authService = app(AuthService::class);
        
        $activeRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin role',
            'permissions' => ['manage_users'],
            'is_active' => true
        ]);
        
        $inactiveRole = Role::create([
            'name' => 'Inactive',
            'slug' => 'inactive',
            'description' => 'Inactive role',
            'permissions' => ['view_users'],
            'is_active' => false
        ]);
        
        $activeUser = User::factory()->create(['role_id' => $activeRole->id]);
        $activeUser->load('role');
        
        $inactiveUser = User::factory()->create(['role_id' => $inactiveRole->id]);
        $inactiveUser->load('role');

        $this->assertTrue($authService->isUserActive($activeUser));
        $this->assertFalse($authService->isUserActive($inactiveUser));
    }

    public function test_format_user_response_returns_correct_structure()
    {
        $authService = app(AuthService::class);
        
        $activeRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin role',
            'permissions' => ['manage_users'],
            'is_active' => true
        ]);
        
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role_id' => $activeRole->id
        ]);
        $user->load('role');

        $response = $authService->formatUserResponse($user);

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('email', $response);
        $this->assertArrayHasKey('role', $response);
        $this->assertEquals('John Doe', $response['name']);
        $this->assertEquals('john@example.com', $response['email']);
        $this->assertArrayHasKey('name', $response['role']);
        $this->assertArrayHasKey('slug', $response['role']);
        $this->assertArrayHasKey('permissions', $response['role']);
    }

    public function test_authenticate_returns_null_with_invalid_email()
    {
        $authService = app(AuthService::class);
        $result = $authService->authenticate('invalid@example.com', 'password123');
        $this->assertNull($result);
    }

    public function test_authenticate_returns_null_with_invalid_password()
    {
        $authService = app(AuthService::class);
        
        $activeRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin role',
            'permissions' => ['manage_users'],
            'is_active' => true
        ]);
        
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $activeRole->id
        ]);

        $result = $authService->authenticate('test@example.com', 'wrongpassword');
        $this->assertNull($result);
    }

    public function test_authenticate_returns_null_for_inactive_user()
    {
        $authService = app(AuthService::class);
        
        $inactiveRole = Role::create([
            'name' => 'Inactive',
            'slug' => 'inactive',
            'description' => 'Inactive role',
            'permissions' => ['view_users'],
            'is_active' => false
        ]);
        
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $inactiveRole->id
        ]);

        $result = $authService->authenticate('test@example.com', 'password123');
        $this->assertNull($result);
    }

    public function test_authenticate_returns_user_with_valid_credentials()
    {
        $authService = app(AuthService::class);
        
        $activeRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin role',
            'permissions' => ['manage_users'],
            'is_active' => true
        ]);
        
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $activeRole->id
        ]);

        $result = $authService->authenticate('test@example.com', 'password123');

        $this->assertNotNull($result);
        $this->assertEquals('test@example.com', $result->email);
        $this->assertNotNull($result->role);
    }
}
