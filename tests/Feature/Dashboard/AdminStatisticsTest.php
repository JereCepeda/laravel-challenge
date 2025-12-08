<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

beforeEach(function () {
    /**
     * @var Role $this->adminRole
     * @var Role $this->checkerRole
     * @var User $this->adminUser
     * @var User $this->checkerUser
     */
    // Crear roles
    $this->adminRole = Role::factory()->create([
        'slug' => 'admin',
        'permissions' => ['view_statistics', 'view_reports', 'view_redemptions_history']
    ]);

    $this->checkerRole = Role::factory()->create([
        'slug' => 'checker',
        'permissions' => ['validate_tickets']
    ]);

    // Crear usuarios
    $this->adminUser = User::factory()->create([
        'email' => 'admin@test.com',
        'role_id' => $this->adminRole->id
    ]);

    $this->checkerUser = User::factory()->create([
        'email' => 'checker@test.com',
        'role_id' => $this->checkerRole->id
    ]);
});

test('admin can access statistics section with correct HTML structure', function () {
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
    
    // Verificar estructura principal
    expect($content)->toContain('Estadísticas del Sistema')
        ->toContain('id="refreshMetrics"')
        ->toContain('id="totalValidated"')
        ->toContain('id="totalInvitations"')
        ->toContain('id="activeEvents"')
        ->toContain('id="conversionRate"');
});

test('statistics section loads metrics from correct API endpoint', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar que el script hace fetch a la URL correcta
    expect($content)->toContain("fetch('http://localhost/api/admin/statistics'")
        ->toContain("method: 'GET'")
        ->toContain("'Authorization': `Bearer \${token}`");
});

test('statistics section handles 401 unauthorized correctly', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar que maneja el status 401 y redirige a login
    expect($content)->toContain('if (response.status === 401)')
        ->toContain("localStorage.removeItem('auth_token')")
        ->toContain("window.location.href = '/login'");
});

test('statistics section includes all KPI cards', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar las 4 tarjetas principales
    expect($content)->toContain('stat-card bg-primary')
        ->toContain('Tickets Validados')
        ->toContain('stat-card bg-success')
        ->toContain('Invitaciones Canjeadas')
        ->toContain('stat-card bg-info')
        ->toContain('Eventos Activos')
        ->toContain('stat-card bg-warning')
        ->toContain('Tasa de Conversión');
});

test('statistics section includes additional metrics', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar métricas adicionales
    expect($content)->toContain('id="pendingTickets"')
        ->toContain('Tickets Pendientes')
        ->toContain('id="eventsWithValidations"')
        ->toContain('Eventos con Validaciones')
        ->toContain('id="avgTickets"')
        ->toContain('Promedio Tickets/Invitación')
        ->toContain('id="popularSector"')
        ->toContain('Sector Más Popular');
});

test('statistics section includes refresh button functionality', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */ 

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar botón de actualización
    expect($content)->toContain('id="refreshMetrics"')
        ->toContain('addEventListener(\'click\'')
        ->toContain('loadMetrics()');
});

test('statistics section auto-loads metrics on initialization', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar que loadMetrics() se ejecuta inmediatamente
    expect($content)->toContain('(function() {')
        ->toContain('loadMetrics();');
});

test('statistics section updates UI with fetched data', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar función updateMetricsUI
    expect($content)->toContain('function updateMetricsUI(data)')
        ->toContain('main_kpis')
        ->toContain('additional_metrics')
        ->toContain('last_updated')
        ->toContain('toLocaleString(');
});

test('statistics section handles errors gracefully', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar manejo de errores
    expect($content)->toContain('catch (error)')
        ->toContain('console.error')
        ->toContain('Error al cargar datos');
});

test('checker cannot access statistics section', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->checkerUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('unauthenticated user cannot access statistics section', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_FOUND); // Redirect
});

test('statistics section uses Bootstrap icons correctly', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar iconos de Bootstrap
    expect($content)->toContain('bi-graph-up-arrow')
        ->toContain('bi-arrow-clockwise')
        ->toContain('bi-check-circle-fill')
        ->toContain('bi-ticket-perforated')
        ->toContain('bi-calendar-event')
        ->toContain('bi-percent');
});

test('statistics section includes proper CORS and security headers', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar headers de seguridad en la llamada fetch
    expect($content)->toContain("'Accept': 'application/json'")
        ->toContain("'Content-Type': 'application/json'")
        ->toContain("'X-Requested-With': 'XMLHttpRequest'");
});

test('statistics section formats dates correctly', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar formato de fecha en español
    expect($content)->toContain("toLocaleString('es-ES')");
});

test('statistics section displays loading state', function () {

    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
        Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/statistics', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar estado de carga
    expect($content)->toContain('id="lastUpdated"')
        ->toContain('Cargando...');
});
