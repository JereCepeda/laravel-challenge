<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

beforeEach(function () {
    /**
     * @var Tests\TestCase $this
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

test('admin can access used tickets section with correct HTML structure', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_OK);

    $content = $response->getContent();
    
    // Verificar estructura principal
    expect($content)->toContain('Tickets Usados')
        ->toContain('id="btnRefresh"')
        ->toContain('id="ticketsTable"')
        ->toContain('id="filterEventName"')
        ->toContain('id="filterSector"')
        ->toContain('id="filterEventDate"');
});

test('used tickets section includes DataTables configuration', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar configuración de DataTables
    expect($content)->toContain('DataTable(')
        ->toContain('processing: true')
        ->toContain('serverSide: true')
        ->toContain('ajax:');
});

test('used tickets section uses correct API endpoint', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar URL del endpoint
    expect($content)->toContain("url: 'http://localhost/api/admin/tickets/used'")
        ->toContain("type: 'GET'");
});

test('used tickets section includes authorization headers', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar headers
    expect($content)->toContain("'Authorization': `Bearer \${token}`")
        ->toContain("'X-Requested-With': 'XMLHttpRequest'");
});

test('used tickets section handles 401 unauthorized correctly', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar manejo de error 401
    expect($content)->toContain('if (xhr.status === 401)')
        ->toContain("localStorage.removeItem('auth_token')")
        ->toContain("window.location.href = '/login'");
});

test('used tickets section includes filter functionality', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar filtros
    expect($content)->toContain('data: function(d)')
        ->toContain("d.event_name = $('#filterEventName').val()")
        ->toContain("d.sector = $('#filterSector').val()")
        ->toContain("d.event_date = $('#filterEventDate').val()");
});

test('used tickets section includes apply filters button', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar botón de aplicar filtros
    expect($content)->toContain('id="btnApplyFilters"')
        ->toContain("$('#btnApplyFilters').on('click'")
        ->toContain('table.ajax.reload()');
});

test('used tickets section includes refresh button functionality', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar botón de refrescar
    expect($content)->toContain('id="btnRefresh"')
        ->toContain("$('#btnRefresh').on('click'")
        ->toContain('Actualizando...');
});

test('used tickets section includes all required table columns', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar columnas de la tabla
    expect($content)->toContain('Código QR')
        ->toContain('Evento')
        ->toContain('Fecha')
        ->toContain('Sector');
});

test('used tickets section includes column renderers', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar renders personalizados
    expect($content)->toContain("data: 'ticket_code'")
        ->toContain("data: 'event_name'")
        ->toContain("data: 'event_date'")
        ->toContain("data: 'sector'")
        ->toContain('render: function(data)');
});

test('used tickets section includes Spanish language configuration', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar configuración de idioma
    expect($content)->toContain('language:')
        ->toContain('No hay datos disponibles en la tabla')
        ->toContain('Mostrando _START_ a _END_ de _TOTAL_ registros')
        ->toContain('Buscar:')
        ->toContain('Procesando...');
});

test('used tickets section includes pagination configuration', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);


    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar paginación
    expect($content)->toContain('pageLength: 15')
        ->toContain('lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]]');
});

test('used tickets section includes default sorting', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();

    // Verificar orden por defecto (columna 1 = event_name, descendente)
    expect($content)->toContain("order: [[1, 'desc']]");
});test('used tickets section is responsive', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar responsive
    expect($content)->toContain('responsive: true');
});

test('used tickets section includes event list dropdown', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar dropdown de eventos
    expect($content)->toContain('id="filterEventName"')
        ->toContain('Todos los eventos');
});

test('used tickets section allows Enter key for filter search', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar búsqueda con Enter
    expect($content)->toContain("on('keypress', function(e)")
        ->toContain('if (e.which === 13)')
        ->toContain("$('#btnApplyFilters').click()");
});

test('used tickets section renders QR codes as code elements', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar render de código QR
    expect($content)->toContain('return `<code>${data}</code>`');
});

test('used tickets section renders sector as badge', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar render de sector
    expect($content)->toContain('return `<span class="badge bg-info">${data}</span>`');
});

test('checker cannot access used tickets section', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->checkerUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('unauthenticated user cannot access used tickets section', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $response->assertStatus(Response::HTTP_FOUND); // Redirect
});

test('used tickets section initializes DataTable on load', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar inicialización
    expect($content)->toContain('function initDataTable()')
        ->toContain('initDataTable();');
});

test('used tickets section destroys existing DataTable before reinit', function () {
    /**
     * @var Tests\TestCase $this
     * @var User $this->adminUser
     */
    
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/dashboard/used-tickets', [
        'Accept' => 'text/html'
    ]);

    $content = $response->getContent();
    
    // Verificar destrucción antes de reinicializar
    expect($content)->toContain('if ($.fn.DataTable.isDataTable')
        ->toContain('.DataTable().destroy()');
});
