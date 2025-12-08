<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

beforeEach(function () {
    /**
     * @var Role $this->adminRole
     * @var Role $this->checkerRole
     * @var User $this->adminUser
     * @var User $this->checkerUser
     */
    $this->adminRole = Role::create([
        'name' => 'Administrador',
        'slug' => 'admin',
        'description' => 'Admin role',
        'permissions' => ['view_statistics', 'view_reports', 'view_redemptions_history'],
        'is_active' => true
    ]);

    $this->checkerRole = Role::create([
        'name' => 'Checker',
        'slug' => 'checker',
        'description' => 'Checker role',
        'permissions' => ['validate_tickets'],
        'is_active' => true
    ]);

    $this->adminUser = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => Hash::make('password123'),
        'role_id' => $this->adminRole->id
    ]);

    $this->checkerUser = User::factory()->create([
        'name' => 'Checker User',
        'email' => 'checker@test.com',
        'password' => Hash::make('password123'),
        'role_id' => $this->checkerRole->id
    ]);
});

test('admin can access dashboard index', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    // Verificar que contiene elementos del dashboard
    $content = $response->getContent();
    expect($content)->toContain('sidebar');
    expect($content)->toContain('spa-content');
    expect($content)->toContain($this->adminUser->name);
});

test('checker can access dashboard index', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->checkerUser
     */
    Passport::actingAs($this->checkerUser);

    $response = $this->getJson('/api/dashboard', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    $content = $response->getContent();
    expect($content)->toContain('sidebar');
    expect($content)->toContain($this->checkerUser->name);
});

test('unauthenticated user cannot access dashboard', function () {
    /**
     * @var Tests\TestCase $this
     */
    $response = $this->getJson('/api/dashboard', [
        'Accept' => 'text/html'
    ]);

    // Laravel redirige a login (302) cuando no hay token
    $response->assertStatus(Response::HTTP_FOUND);
});

test('admin can access statistics section', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    $content = $response->getContent();
    expect($content)->toContain('Estadísticas');
});

test('admin can access reports section', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/reports', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_OK);
    
    $content = $response->getContent();
    expect($content)->toContain('Reportes');
});

test('admin can access used tickets section', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_OK);
});

test('admin can access redemptions history section', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/redemptions-history', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_OK);
});

test('checker can access scan menu section', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->checkerUser
     */
    Passport::actingAs($this->checkerUser);

    $response = $this->getJson('/api/dashboard/scan-menu', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_OK);
});

test('checker cannot access admin statistics section', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->checkerUser
     */
    Passport::actingAs($this->checkerUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('admin dashboard contains sidebar with correct links', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar que contiene los links del admin
    expect($content)->toContain('data-section="statistics"');
    expect($content)->toContain('data-section="reports"');
    expect($content)->toContain('data-section="used-tickets"');
    expect($content)->toContain('data-section="redemptions-history"');
    
    // Verificar que contiene las clases SPA
    expect($content)->toContain('spa-link');
});

test('checker dashboard contains sidebar with correct links', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->checkerUser
     */
    Passport::actingAs($this->checkerUser);

    $response = $this->getJson('/api/dashboard', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar que contiene los links del checker
    expect($content)->toContain('data-section="scan-menu"');
    expect($content)->toContain('Escanear QR');
});

test('dashboard contains mobile menu toggle button', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    expect($content)->toContain('id="mobileMenuToggle"');
    expect($content)->toContain('id="sidebarOverlay"');
});

test('dashboard contains logout button', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    expect($content)->toContain('data-action="logout"');
});

test('dashboard loads window.App configuration', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar que window.App está configurado
    expect($content)->toContain('window.App');
    expect($content)->toContain('csrfToken');
    expect($content)->toContain('baseUrl');
    expect($content)->toContain('user');
});

test('dashboard includes dashboard.js script', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    expect($content)->toContain('dashboard.js');
});
