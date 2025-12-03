<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Dashboard</span>
                <div class="navbar-nav ms-auto">
                    <span class="navbar-text me-3">
                        Bienvenido, {{ $user->name ?? 'Usuario' }}
                    </span>
                    <span class="badge bg-primary me-3">
                        {{ $user->role->name ?? 'Sin rol' }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                    </form>
                </div>
            </div>
        </nav>

        <div class="row">
            <div class="col-md-3">
                <div class="list-group mt-3">
                    <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action active">
                        Dashboard
                    </a>
                    
                    @if(in_array('view_statistics', $permissions))
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar"></i> Estadísticas
                        </a>
                    @endif
                    
                    @if(in_array('view_redemptions_history', $permissions))
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-history"></i> Historial de Canjes
                        </a>
                    @endif
                    
                    @if(in_array('view_reports', $permissions))
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt"></i> Reportes
                        </a>
                    @endif
                </div>
            </div>

            <div class="col-md-9">
                <div class="container-fluid p-3">
                    <div class="row">
                        <div class="col-12">
                            <h2>Dashboard Principal</h2>
                            <hr>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Información de Usuario</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nombre:</strong> {{ $user->name }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                    <p><strong>Rol:</strong> {{ $user->role->name }}</p>
                                    <p><strong>Descripción del Rol:</strong> {{ $user->role->description }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Permisos Disponibles</h5>
                                </div>
                                <div class="card-body">
                                    @if(count($permissions) > 0)
                                        <div class="row">
                                            @foreach($permissions as $permission)
                                                <div class="col-md-6 mb-2">
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> 
                                                        {{ ucfirst(str_replace('_', ' ', $permission)) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">No tienes permisos asignados.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(in_array('view_statistics', $permissions))
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Estadísticas Rápidas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <div class="card bg-primary text-white">
                                                    <div class="card-body">
                                                        <h4>--</h4>
                                                        <p>Tickets Validados</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card bg-success text-white">
                                                    <div class="card-body">
                                                        <h4>--</h4>
                                                        <p>Invitaciones Canjeadas</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card bg-warning text-white">
                                                    <div class="card-body">
                                                        <h4>--</h4>
                                                        <p>Usuarios Activos</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card bg-info text-white">
                                                    <div class="card-body">
                                                        <h4>--</h4>
                                                        <p>Eventos</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>