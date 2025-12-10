<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clock-history"></i> Historial de Canjes</h2>
        <button class="btn btn-outline-primary" id="refreshHistory">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </button>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Últimos Canjes</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Evento</label>
                    <select class="form-select" id="filterEventName">
                        <option value="">Todos los eventos</option>
                        @foreach($events as $event)
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
            <div class="table-responsive">
                <table id="redemptionsHistoryTable" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Invitación</th>
                            <th>Cantidad de Invitados</th>
                            <th>Tickets Generados</th>
                            <th>Evento</th>
                            <th>Fecha de Canje</th>
                            <th>Sector</th>
                            <th>Canjeado por </th>
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
    $(function() {
        let table;
        function initDataTable() {
            if(table) {
                table.destroy();
            }
            const token =localStorage.getItem('auth_token');
            table = $('#redemptionsHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                
                ajax: {
                    url: `{{url('api/admin/redemptions/history')}}`,
                    type: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: function(d) {
                        d.event_name = $('#filterEventName').val();
                        d.sector = $('#filterSector').val();
                        d.event_date = $('#filterEventDate').val();
                    }
                },
                columns: [
                    { 
                        data: 'invitation_code',
                        render: function(data) {
                            return `<code>${data}</code>`;
                        }
                    },
                    { 
                        data: 'guest_count',
                        render: function(data) {
                            return `<span class="badge bg-secondary">${data}</span>`;
                        }
                    },
                    { 
                        data: 'tickets_generated',
                        render: function(data) {
                            return `<span class="badge bg-success">${data}</span>`;
                        }
                    },
                    { 
                        data: 'event_name',
                        render: function(data) {
                            return `<strong>${data}</strong>`;
                        }
                    },
                    {
                        data: 'redeemed_at',
                        render: function(data) {
                            const date = new Date(data);
                            return date.toLocaleString('es-ES', { 
                                year: 'numeric', 
                                month: '2-digit', 
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            });
                        }
                    },
                    { 
                        data: 'sector',
                        render: function(data) {
                            return `<span class="badge bg-info">${data}</span>`;
                        }
                    },
                    { 
                        data: 'redeemed_by',
                        render: function(data) {
                            return `<strong>${data}</strong>`;
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
        
        $('#refreshHistory').off('click').on('click', function() {
            if (table) {
                table.ajax.reload();
            }
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
    });

</script>


