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
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <aside class="sidebar" id="sidebar">
        @include('layouts.partials.sidebar')
    </aside>
    
    <div class="main-wrapper">
        <header class="topbar">
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="bi bi-list"></i>
            </button>
            <h1 id="pageTitle">Dashboard</h1>
            <div class="topbar-actions">
                <button class="btn-icon" title="Notificaciones">
                    <i class="bi bi-bell"></i>
                </button>
                <button class="btn-icon" title="Configuración">
                    <i class="bi bi-gear"></i>
                </button>
            </div>
        </header>
        
        <main class="content-area">
            @include('layouts.partials.alerts')
            
            <div id="spa-content">
                @include($initialView)
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    @if($initialView === 'dashboard.checker.scan-menu')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/checker.css') }}?v={{ time() }}">
    <script src="{{ asset('js/checker/validation-handler.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/checker/qr-scanner-store.js') }}?v={{ time() }}"></script>
    @endif
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        window.App = {
            user: @json($user),
            role: '{{ $role }}',
            permissions: @json($permissions),
            csrfToken: '{{ csrf_token() }}',
            apiUrl: '{{ url('/api/dashboard') }}',
            baseUrl: '{{ url('/') }}'
        };
    </script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>