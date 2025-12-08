<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-graph-up-arrow"></i> Estadísticas del Sistema</h2>
        <button class="btn btn-outline-primary" id="refreshMetrics">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </button>
    </div>
    
    <!-- KPIs Principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-primary text-white">
                <div class="stat-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-value" id="totalValidated">-</div>
                <div class="stat-label">Tickets Validados</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card bg-success text-white">
                <div class="stat-icon">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div class="stat-value" id="totalInvitations">-</div>
                <div class="stat-label">Invitaciones Canjeadas</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card bg-info text-white">
                <div class="stat-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stat-value" id="activeEvents">-</div>
                <div class="stat-label">Eventos Activos</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card bg-warning text-white">
                <div class="stat-icon">
                    <i class="bi bi-percent"></i>
                </div>
                <div class="stat-value" id="conversionRate">-</div>
                <div class="stat-label">Tasa de Conversión</div>
            </div>
        </div>
    </div>
    
    <!-- Métricas Adicionales -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-info-circle"></i> Métricas Adicionales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="metric-item">
                                <span class="metric-value" id="pendingTickets">-</span>
                                <span class="metric-label">Tickets Pendientes</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-item">
                                <span class="metric-value" id="eventsWithValidations">-</span>
                                <span class="metric-label">Eventos con Validaciones</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-item">
                                <span class="metric-value" id="avgTickets">-</span>
                                <span class="metric-label">Promedio Tickets/Invitación</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-item">
                                <span class="metric-value" id="popularSector">-</span>
                                <span class="metric-label">Sector Más Popular</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-clock"></i> Última Actualización</h5>
                </div>
                <div class="card-body">
                    <p id="lastUpdated" class="text-muted">Cargando...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // Ejecutar inmediatamente al cargar la vista
    loadMetrics();
    
    // Configurar el botón de refresh
    const refreshBtn = document.getElementById('refreshMetrics');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Actualizando...';
            loadMetrics();
        });
    }

    async function loadMetrics() {
        try {
            const token = localStorage.getItem('auth_token');
            
            const response = await fetch('{{ url("/api/admin/statistics") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.status === 401) {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
                return;
            }
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            updateMetricsUI(data);
            
        } catch (error) {
            console.error('Error al cargar métricas:', error);
            document.getElementById('lastUpdated').textContent = 'Error al cargar datos';
        }
    }

    function updateMetricsUI(data) {
        const { main_kpis, additional_metrics, last_updated } = data;
        
        // KPIs Principales
        document.getElementById('totalValidated').textContent = main_kpis.total_tickets_validated.toLocaleString();
        document.getElementById('totalInvitations').textContent = main_kpis.total_invitations_redeemed.toLocaleString();
        document.getElementById('activeEvents').textContent = main_kpis.active_events.toLocaleString();
        document.getElementById('conversionRate').textContent = main_kpis.conversion_rate + '%';
        
        // Métricas Adicionales
        document.getElementById('pendingTickets').textContent = additional_metrics.pending_tickets.toLocaleString();
        document.getElementById('eventsWithValidations').textContent = additional_metrics.events_with_validations.toLocaleString();
        document.getElementById('avgTickets').textContent = additional_metrics.avg_tickets_per_invitation;
        document.getElementById('popularSector').textContent = additional_metrics.most_popular_sector || 'N/A';
        
        // Última actualización
        const lastUpdatedDate = new Date(last_updated);
        document.getElementById('lastUpdated').textContent = lastUpdatedDate.toLocaleString('es-ES');
        
        // Restaurar botón de refresh
        const refreshBtn = document.getElementById('refreshMetrics');
        if (refreshBtn) {
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Actualizar';
        }
    }
})();
</script>