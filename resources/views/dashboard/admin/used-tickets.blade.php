<div class="container-fluid">
    <!-- Header de la sección -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-ticket-perforated"></i> Tickets Usados</h2>
        <button class="btn btn-primary" id="btnRefresh">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </button>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Evento</label>
                    <select class="form-select" id="filterEventName">
                        <option value="">Todos los eventos</option>
                        @foreach($filters as $event)
                            <option value="{{ $event->event_name }}">
                                {{ $event->event_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sector</label>
                    <input type="text" class="form-control" id="filterSector" placeholder="Ej: VIP, Platea...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="filterEventDate">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-success w-100" id="btnApplyFilters">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla con DataTables -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="ticketsTable" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Código QR</th>
                            <th>Evento</th>
                            <th>Fecha</th>
                            <th>Sector</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables cargará los datos aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let table;
    

    function initDataTable() {
        if ($.fn.DataTable.isDataTable('#ticketsTable')) {
            $('#ticketsTable').DataTable().destroy();
        }
        
        const token = localStorage.getItem('auth_token');
        
        table = $('#ticketsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ url("/api/admin/tickets/used") }}',
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function(d) {

                    d.event_name = $('#filterEventName').val();
                    d.sector = $('#filterSector').val();
                    d.event_date = $('#filterEventDate').val();
                },
                error: function(xhr, error, thrown) {
                    if (xhr.status === 401) {
                        localStorage.removeItem('auth_token');
                        window.location.href = '/login';
                    }
                    console.error('Error al cargar datos:', error);
                }
            },
            columns: [
                { 
                    data: 'ticket_code',
                    render: function(data) {
                        return `<code>${data}</code>`;
                    }
                },
                { data: 'event_name' ,
                    render: function(data) {
                        return `<strong>${data}</strong>`;
                    }
                },
                { 
                    data: 'event_date',
                    render: function(data) {
                        const [year, month, day] = data.split('-');
                        const date = new Date(year, month - 1, day);
                        return date.toLocaleDateString('es-ES', { 
                            year: 'numeric', 
                            month: '2-digit', 
                            day: '2-digit' 
                        });
                    }
                },
                { 
                    data: 'sector',
                    render: function(data) {
                        return `<span class="badge bg-info">${data}</span>`;
                    }
                }
            ],
            language: {
                "decimal": ",",
                "emptyTable": "No hay datos disponibles en la tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "infoPostFix": "",
                "thousands": ".",
                "lengthMenu": "Mostrar _MENU_ registros",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar la columna ascendente",
                    "sortDescending": ": activar para ordenar la columna descendente"
                }
            },
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
            order: [[1, 'desc']],
            responsive: true
        });
    }
    

    initDataTable();
    

    $('#btnApplyFilters').off('click').on('click', function() {
        table.ajax.reload();
    });
    
    $('#btnRefresh').off('click').on('click', function() {
        const btn = $(this);
        btn.html('<i class="bi bi-arrow-clockwise"></i> Actualizando...');
        btn.prop('disabled', true);
        
        table.ajax.reload(function() {
            btn.html('<i class="bi bi-arrow-clockwise"></i> Actualizar');
            btn.prop('disabled', false);
        });
    });
    

    $('#filterEventName, #filterSector, #filterEventDate').off('keypress').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btnApplyFilters').click();
        }
    });
})();
</script>