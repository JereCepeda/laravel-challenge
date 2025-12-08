<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Loading inicial -->
    <div class="loading-screen" id="loadingScreen">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3">Cargando dashboard...</p>
    </div>

    <!-- Contenedor del dashboard -->
    <div id="dashboard-app"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        window.App = {
            csrfToken: '{{ csrf_token() }}',
            baseUrl: '{{ url('/') }}'
        };

        // Verificar token INMEDIATAMENTE antes de cualquier otra cosa
        const token = localStorage.getItem('auth_token');
        
        if (!token) {
            console.log('No hay token, redirigiendo al login...');
            window.location.replace('{{ url('/login') }}');
            // No ejecutar nada más
        } else {
            // Solo si hay token, cargar el dashboard
            (async function() {
                try {
                    console.log('Cargando dashboard desde API...');
                    
                    // Cargar dashboard desde API
                    const response = await fetch('{{ url('/api/dashboard') }}', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.status === 401) {
                        console.error('Token inválido, limpiando y redirigiendo...');
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('user');
                        window.location.replace('{{ url('/login') }}');
                        return;
                    }

                    if (!response.ok) {
                        throw new Error(`Error ${response.status}: ${response.statusText}`);
                    }

                    const html = await response.text();
                    console.log('Dashboard cargado correctamente');
                    
                    // Insertar dashboard
                    document.getElementById('dashboard-app').innerHTML = html;
                    document.getElementById('loadingScreen').style.display = 'none';

                    // Cargar JS del dashboard e inicializar SPA
                    const script = document.createElement('script');
                    script.src = '{{ asset('js/dashboard.js') }}';
                    script.onload = () => {
                        console.log('Dashboard JS cargado');
                        // Inicializar SPA manualmente después de cargar dinámicamente
                        if (typeof DashboardSPA !== 'undefined') {
                            window.dashboardSPA = new DashboardSPA();
                        }
                    };
                    document.body.appendChild(script);

                } catch (error) {
                    console.error('Error al cargar dashboard:', error);
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    document.getElementById('loadingScreen').innerHTML = `
                        <div class="alert alert-danger m-4">
                            <h4>Error al cargar el dashboard</h4>
                            <p>${error.message}</p>
                            <a href="{{ url('/login') }}" class="btn btn-primary mt-3">Volver al login</a>
                        </div>
                    `;
                }
            })();
        }
    </script>

    <style>
        .loading-screen {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f8f9fa;
        }
    </style>
</body>
</html>