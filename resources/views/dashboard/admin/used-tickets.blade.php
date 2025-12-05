<div class="container-fluid">
    <!-- Header de la sección -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-ticket-perforated"></i> Tickets Usados</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" 
                data-bs-target="#filterModal">
            <i class="bi bi-funnel"></i> Filtros
        </button>
    </div>
    
    <!-- Filtros visibles -->
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Evento</label>
                    <input type="text" class="form-control" name="event_name" placeholder="Nombre del evento">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" class="form-control" name="date_from">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" class="form-control" name="date_to">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div id="resultsContainer">
                <!-- Aquí se cargan los resultados vía AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('filterForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // Llamar a tu API
    const response = await fetch('/api/admin/used-tickets', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': window.App.csrfToken
        }
    });
    
    const data = await response.json();
    renderResults(data);
});
</script>