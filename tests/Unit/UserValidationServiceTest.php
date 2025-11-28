<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Services\UserValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $validationService;
    protected $activeRole;
    protected $inactiveRole;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validationService = app(UserValidationService::class);
        
        $this->activeRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin role',
            'permissions' => ['manage_users', 'view_reports'],
            'is_active' => true
        ]);

        $this->inactiveRole = Role::create([
            'name' => 'Inactive',
            'slug' => 'inactive',
            'description' => 'Inactive role',
            'permissions' => ['view_users'],
            'is_active' => false
        ]);
    }

    public function test_validate_user_credentials_returns_valid_for_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->activeRole->id
        ]);

        $result = $this->validationService->validateUserCredentials('test@example.com', 'password123');

        $this->assertTrue($result['valid']);
        $this->assertNull($result['error_type']);
        $this->assertEquals('valid credentials', $result['message']);
        $this->assertNotNull($result['user']);
        $this->assertEquals('test@example.com', $result['user']->email);
    }

    public function test_validate_user_credentials_returns_invalid_for_non_existent_user()
    {
        $result = $this->validationService->validateUserCredentials('nonexistent@example.com', 'password123');

        $this->assertFalse($result['valid']);
        $this->assertEquals('invalid_credentials', $result['error_type']);
        $this->assertEquals('invalid credentials', $result['message']);
        $this->assertNull($result['user']);
    }

    public function test_validate_user_credentials_returns_invalid_for_wrong_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->activeRole->id
        ]);

        $result = $this->validationService->validateUserCredentials('test@example.com', 'wrongpassword');

        $this->assertFalse($result['valid']);
        $this->assertEquals('invalid_credentials', $result['error_type']);
        $this->assertEquals('invalid credentials', $result['message']);
        $this->assertNull($result['user']);
    }

    public function test_validate_user_credentials_returns_invalid_for_inactive_account()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->inactiveRole->id
        ]);

        $result = $this->validationService->validateUserCredentials('test@example.com', 'password123');

        $this->assertFalse($result['valid']);
        $this->assertEquals('inactive_user', $result['error_type']);
        $this->assertEquals('user is inactive', $result['message']);
        $this->assertNull($result['user']);
    }

    public function test_is_user_active_returns_correct_boolean()
    {
        $activeUser = User::factory()->create(['role_id' => $this->activeRole->id]);
        $activeUser->load('role');
        
        $inactiveUser = User::factory()->create(['role_id' => $this->inactiveRole->id]);
        $inactiveUser->load('role');

        $this->assertTrue($this->validationService->isUserActive($activeUser));
        $this->assertFalse($this->validationService->isUserActive($inactiveUser));
    }

    public function test_verify_password_works_correctly()
    {
        $plainPassword = 'password123';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $this->assertTrue($this->validationService->verifyPassword($plainPassword, $hashedPassword));
        $this->assertFalse($this->validationService->verifyPassword('wrongpassword', $hashedPassword));
    }

    public function test_is_valid_email_returns_correct_boolean()
    {
        $this->assertTrue($this->validationService->isValidEmail('test@example.com'));
        $this->assertTrue($this->validationService->isValidEmail('user.name+tag@domain.co.uk'));
        $this->assertFalse($this->validationService->isValidEmail('invalid-email'));
        $this->assertFalse($this->validationService->isValidEmail('test@'));
        $this->assertFalse($this->validationService->isValidEmail('@example.com'));
    }
}
