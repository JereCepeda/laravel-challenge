<?php

use App\Models\Role;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Services\UserValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport; 

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

test('login view is accessible', function () {
    /** 
     * @var \Tests\TestCase $this
     * @var \Illuminate\Foundation\Application $this->app
    */
    $response = $this->get('/login');
    $response->assertStatus(Response::HTTP_OK);
});

test('successful login returns correct response structure', function () {
    /** 
     * @var \Tests\TestCase $this
     * @var \Illuminate\Foundation\Application $this->app
    */

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $this->activeRole->id
    ]);
    $user->load('role');

    $this->mock(UserValidationService::class, function ($mock) use ($user) {
        $mock->shouldReceive('validateUserCredentials')
            ->with('test@example.com', 'password123')
            ->andReturn([
                'valid' => true,
                'error_type' => null,
                'message' => 'Valid credentials',
                'user' => $user
            ]);
    });
    $this->mock(AuthService::class, function ($mock) use ($user) {
        // Este era el método faltante
        $mock->shouldReceive('authenticate')
            ->with('test@example.com', 'password123')
            ->andReturn($user);
            
        $mock->shouldReceive('generateToken')
            ->with($user, 'API Token')  // Nota: incluir el segundo parámetro
            ->andReturn('mocked-access-token-12345');
    });

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'message',
            'user', 
            'token' 
        ])
        ->assertJson([
            'message' => 'Login successful',
            'token' => 'mocked-access-token-12345'
        ]);
});

test('login fails with invalid credentials', function () {
    /** 
     * @var \Tests\TestCase $this
     * @var \Illuminate\Foundation\Application $this->app
    */

    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $this->activeRole->id
    ]);

    $this->mock(UserValidationService::class, function ($mock) {
        $mock->shouldReceive('validateUserCredentials')
            ->with('test@example.com', 'wrongpassword')
            ->andReturn([
                'valid' => false,
                'error_type' => 'invalid_credentials',
                'message' => 'Invalid credentials',
                'user' => null
            ]);
    });

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJson([
            'message' => 'Invalid credentials',
            'errors' => [
                'authentication' => ['Invalid credentials']
            ]
        ]);
});

test('login fails with inactive user role', function () {
    /** 
     * @var \Tests\TestCase $this
     * @var \Illuminate\Foundation\Application $this->app
    */
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $this->inactiveRole->id
    ]);
    $user->load('role');

    $this->mock(UserValidationService::class, function ($mock) use ($user) {
        $mock->shouldReceive('validateUserCredentials')
            ->with('test@example.com', 'password123')
            ->andReturn([
                'valid' => false,
                'error_type' => 'inactive_account',
                'message' => 'Account is inactive',
                'user' => $user
            ]);
    });

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN)
        ->assertJson([
            'message' => 'User is inactive',
            'errors' => [
                'authentication' => ['Account is inactive']
            ]
        ]);
});

test('login fails with validation errors for missing email', function () {
    /** 
     * @var \Tests\TestCase $this
     * @var \Illuminate\Foundation\Application $this->app
    */
    $response = $this->postJson('/api/login', [
        'password' => 'password123',
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email']);
});

test('login fails with validation errors for invalid email format', function () {
    /** 
     * @var \Tests\TestCase $this
     * @var \Illuminate\Foundation\Application $this->app
    */
    $response = $this->postJson('/api/login', [
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email']);
});

test('login fails with validation errors for short password', function () {
    /** 
     * @var \Tests\TestCase $this
     * @var \Illuminate\Foundation\Application $this->app
    */
    User::factory()->create([
        'email' => 'test@example.com',
        'role_id' => $this->activeRole->id
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => '123',
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['password']);
});

test('login fails with validation errors for missing fields', function () {
    /** 
     * @var \Tests\TestCase $this
     * @var \Illuminate\Foundation\Application $this->app
    */

    $response = $this->postJson('/api/login', []);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('authenticated user can access profile', function () {
    /** 
     * @var \Tests\TestCase $this
     * @var \Illuminate\Foundation\Application $this->app
    */
    $user = User::factory()->create([
        'role_id' => $this->activeRole->id
    ]);
    $user->load('role');

    Passport::actingAs($user);

    $this->mock(AuthService::class, function ($mock) use ($user) {
        $mock->shouldReceive('formatUserResponse')
            ->andReturn([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => [
                    'name' => $user->role->name,
                    'slug' => $user->role->slug,
                    'permissions' => $user->role->permissions
                ]
            ]);
    });

    $response = $this->getJson('/api/user');

    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'role'
            ]
        ]);
});

test('unauthenticated user cannot access protected routes', function () {
    /** 
     * @var \Tests\TestCase $this
    */ 
    $response = $this->getJson('/api/user');
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});
