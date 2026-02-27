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
    <!-- Alpine.js se carga manualmente después de los componentes -->
    
    <script>
        window.App = {
            csrfToken: '{{ csrf_token() }}',
            baseUrl: '{{ url('/') }}'
        };

        const token = localStorage.getItem('auth_token');
        
        if (!token) {
            console.log('No hay token, redirigiendo al login...');
            window.location.replace('{{ url('/login') }}');
        } else {
            (async function() {
                try {
                    console.log('Cargando dashboard desde API...');
                    
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
                    
                    // 1. Crear elemento temporal para extraer scripts
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    
                    // 2. Extraer scripts ANTES de procesar
                    const scripts = Array.from(tempDiv.querySelectorAll('script'));
                    const externalScripts = [];
                    const inlineScripts = [];
                    
                    scripts.forEach(script => {
                        if (script.src) {
                            externalScripts.push(script.src);
                        } else if (script.textContent.trim()) {
                            inlineScripts.push(script.textContent);
                        }
                        script.remove();
                    });
                    
                    // 3. Reemplazar x-data temporalmente
                    let processedHtml = tempDiv.innerHTML.replace(/x-data=/g, 'data-x-data-deferred=');
                    document.getElementById('dashboard-app').innerHTML = processedHtml;
                    document.getElementById('loadingScreen').style.display = 'none';

                    // 4. Función para cargar scripts externos secuencialmente
                    const loadScript = (src) => {
                        return new Promise((resolve) => {
                            const s = document.createElement('script');
                            s.src = src;
                            s.onload = resolve;
                            s.onerror = resolve;
                            document.body.appendChild(s);
                        });
                    };
                    
                    // 5. Cargar scripts externos en orden (excepto dashboard.js y Alpine)
                    for (const src of externalScripts) {
                        if (!src.includes('dashboard.js') && !src.includes('alpinejs')) {
                            console.log('Cargando:', src);
                            await loadScript(src);
                        }
                    }
                    
                    // 6. Ejecutar scripts inline (excepto los de window.App que ya están)
                    for (const code of inlineScripts) {
                        if (!code.includes('window.App')) {
                            try {
                                const fn = new Function(code);
                                fn();
                            } catch(e) {
                                console.error('Error script inline:', e);
                            }
                        }
                    }
                    
                    // 7. Cargar dashboard.js
                    const script = document.createElement('script');
                    script.src = '{{ asset('js/dashboard.js') }}';
                    script.onload = async () => {
                        console.log('Dashboard JS cargado');
                        
                        // 8. Restaurar x-data ANTES de cargar Alpine
                        document.querySelectorAll('[data-x-data-deferred]').forEach(el => {
                            const value = el.getAttribute('data-x-data-deferred');
                            el.removeAttribute('data-x-data-deferred');
                            el.setAttribute('x-data', value);
                        });
                        
                        // 9. Cargar Alpine.js DESPUÉS de restaurar x-data y tener todos los componentes
                        console.log('Cargando Alpine.js...');
                        await loadScript('https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js');
                        console.log('Alpine.js cargado e inicializado');
                        
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