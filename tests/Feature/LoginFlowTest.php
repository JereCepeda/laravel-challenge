<?php

use App\Models\Role;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserValidationService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

beforeEach(function () {
    /**
     * @var Role $this->adminRole
     * @var Role $this->checkerRole
     */
    $this->adminRole = Role::create([
        'name' => 'Administrador',
        'slug' => 'admin',
        'description' => 'Admin role',
        'permissions' => ['view_statistics', 'view_reports'],
        'is_active' => true
    ]);

    $this->checkerRole = Role::create([
        'name' => 'Checker',
        'slug' => 'checker',
        'description' => 'Checker role',
        'permissions' => ['validate_tickets'],
        'is_active' => true
    ]);
});

test('login page is accessible', function () {
    /**
     * @var Tests\TestCase $this
     */
    $response = $this->get('/login');
    
    $response->assertStatus(Response::HTTP_OK)
            ->assertViewIs('auth.login');
});

test('root redirects to login', function () {
    /**
     * @var Tests\TestCase $this
     */
    $response = $this->get('/');
    
    $response->assertRedirect('/login');
});

test('dashboard page is accessible', function () {
    /**
     * @var Tests\TestCase $this
     */
    $response = $this->get('/dashboard');
    
    $response->assertStatus(Response::HTTP_OK)
             ->assertViewIs('dashboard-loader');
});

test('api login returns token for admin user', function () {
    /**
     * @var Tests\TestCase $this
     * @var Role $this->adminRole
     */
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $this->adminRole->id
    ]);
    $user->load('role');

    // Mock UserValidationService
    $this->mock(UserValidationService::class, function ($mock) use ($user) {
        $mock->shouldReceive('validateUserCredentials')
            ->once()
            ->with('admin@example.com', 'password123')
            ->andReturn([
                'valid' => true,
                'user' => $user,
                'error_type' => null,
                'message' => null
            ]);
    });

    // Mock AuthService
    $this->mock(AuthService::class, function ($mock) use ($user) {
        $mock->shouldReceive('authenticate')
            ->once()
            ->with('admin@example.com', 'password123')
            ->andReturn($user);
            
        $mock->shouldReceive('generateToken')
            ->once()
            ->andReturn('mocked-token-12345');
    });

    $response = $this->postJson('/api/login', [
        'email' => 'admin@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role' => [
                        'id',
                        'name',
                        'slug',
                        'permissions'
                    ]
                ],
                'token'
            ])
            ->assertJson([
                'message' => 'Login successful',
                'token' => 'mocked-token-12345'
            ]);
});

test('api login returns token for checker user', function () {
    /**
     * @var Tests\TestCase $this
     * @var Role $this->checkerRole
     */ 
    $user = User::factory()->create([
        'email' => 'checker@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $this->checkerRole->id
    ]);
    $user->load('role');

    // Mock UserValidationService
    $this->mock(UserValidationService::class, function ($mock) use ($user) {
        $mock->shouldReceive('validateUserCredentials')
            ->once()
            ->with('checker@example.com', 'password123')
            ->andReturn([
                'valid' => true,
                'user' => $user,
                'error_type' => null,
                'message' => null
            ]);
    });

    // Mock AuthService
    $this->mock(AuthService::class, function ($mock) use ($user) {
        $mock->shouldReceive('authenticate')
            ->once()
            ->with('checker@example.com', 'password123')
            ->andReturn($user);
            
        $mock->shouldReceive('generateToken')
            ->once()
            ->andReturn('mocked-token-67890');
    });

    $response = $this->postJson('/api/login', [
        'email' => 'checker@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['message', 'user', 'token'])
            ->assertJson([
                'message' => 'Login successful',
                'token' => 'mocked-token-67890'
            ]);
});

test('api login fails with invalid credentials', function () {
    /**
     * @var Tests\TestCase $this
     * @var Role $this->adminRole
     */
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $this->adminRole->id
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'user@example.com',
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonStructure(['message', 'errors']);
});

test('api login fails with missing email', function () {
    /**
     * @var Tests\TestCase $this
     */
    $response = $this->postJson('/api/login', [
        'password' => 'password123'
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
});

test('api login fails with missing password', function () {
    /**
     * @var Tests\TestCase $this
     */
    $response = $this->postJson('/api/login', [
        'email' => 'user@example.com'
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password']);
});

test('api login fails with invalid email format', function () {
    /** 
     * @var Tests\TestCase $this
     */
    $response = $this->postJson('/api/login', [
        'email' => 'invalid-email',
        'password' => 'password123'
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
});

test('api login fails with short password', function () {
    /** 
     * @var Tests\TestCase $this
     */
    $response = $this->postJson('/api/login', [
        'email' => 'user@example.com',
        'password' => '123'
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password']);
});

test('token from login can be used to access protected routes', function () {
    /**
     * @var Tests\TestCase $this
     * @var Role $this->adminRole
     * @var User $user
     */
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $this->adminRole->id
    ]);
    $user->load('role');

    // Usar Passport para actuar como usuario autenticado
    Passport::actingAs($user);

    // Mock AuthService para el método formatUserResponse
    $this->mock(AuthService::class, function ($mock) use ($user) {
        $mock->shouldReceive('formatUserResponse')
            ->andReturn([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->toArray()
            ]);
    });

    // Acceder a ruta protegida
    $response = $this->getJson('/api/user');

    $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['user']);
});

test('logout invalidates token', function () {
    /**
     * @var Tests\TestCase $this
     * @var Role $this->adminRole
     * @var User $user
     */
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password123'),
        'role_id' => $this->adminRole->id
    ]);

    // Mock AuthService para revokeUserToken
    $this->mock(AuthService::class, function ($mock) use ($user) {
        $mock->shouldReceive('revokeUserToken')
            ->once()
            ->with(\Mockery::on(function ($arg) use ($user) {
                return $arg->id === $user->id;
            }))
            ->andReturn(true);
    });

    // Usar Passport actingAs para simular autenticación
    Passport::actingAs($user);
    
    $logoutResponse = $this->postJson('/api/logout');

    $logoutResponse->assertStatus(Response::HTTP_OK)
                ->assertJson(['message' => 'Logged out successfully']);
});
test('logout fails if not authenticated', function () {
    /**
     * @var Tests\TestCase $this
     */
    $response = $this->postJson('/api/logout');

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(['message' => 'Unauthenticated.']);
});
