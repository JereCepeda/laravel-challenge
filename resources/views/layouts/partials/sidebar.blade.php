<div class=" d-flex flex-column flex-shrink-0 p-3 text-white bg-dark">
    <div class="sidebar-header">
            <i class="bi bi-people"></i>
            <h5>{{ $user->name }}</h5>
            <span class="badge">{{ $user->role->name }}</span>
        </div>

        <!-- Navegación dinámica según rol-->
        {{--<div class="sidebar-nav">
            @if($user->role->slug === 'admin')
                @include('layouts.sidebar.admin-menu')
            @elseif($user->role->slug === 'checker')
                @include('layouts.sidebar.checker-menu')
            @elseif($user->role->slug === 'seller')
                @include('layouts.sidebar.seller-menu')
            @else
                @include('layouts.sidebar.user-menu')
            @endif
        </div>  --}}

        <div class="sidebar-brand">
            <h4><i class="bi bi-ticket-perforated-fill me-2"></i>Ticket Manager</h4>
        </div>

        <div class="user-profile">
            <div class="user-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="user-info">
                <h6>{{ $user->name }}</h6>
                <small>{{ $user->email }}</small>
                <div>
                    <span class="user-badge">{{ ucfirst($role) }}</span>
                </div>
            </div>
        </div>
        <div class="sidebar-menu flex-grow-1 ">
            <nav class="sidebar-nav">
                @if($role === 'admin')
                    <div class="nav-section-title">Administración</div>
                        <ul class="nav nav-pills flex-column mb-auto scrollarea list-group list-group-flush border-bottom pb-3">
                        @if(in_array('view_statistics', $permissions))
                            <li class="nav-item">
                                <a href="#" class="nav-link spa-link active" data-section="statistics" data-title="Estadísticas">
                                    <i class="bi bi-graph-up-arrow"></i>
                                    <span>Estadísticas</span>
                                </a>
                            </li>
                        @endif
                        
                        @if(in_array('view_reports', $permissions))
                            <li class="nav-item">
                                <a href="#" class="nav-link spa-link" data-section="reports" data-title="Reportes">
                                    <i class="bi bi-file-earmark-bar-graph"></i>
                                    <span>Reportes</span>
                                </a>
                            </li>
                        @endif
                        
                        @if(in_array('view_statistics', $permissions))
                            <li class="nav-item">
                                <a href="#" class="nav-link spa-link" data-section="used-tickets" data-title="Tickets Usados">
                                    <i class="bi bi-ticket-detailed"></i>
                                    <span>Tickets Usados</span>
                                </a>
                            </li>
                        @endif
                        
                        @if(in_array('view_redemptions_history', $permissions))
                            <li class="nav-item">
                                <a href="#" class="nav-link spa-link" data-section="redemptions-history" data-title="Historial de Canjes">
                                <i class="bi bi-clock-history"></i>
                                <span>Historial de Canjes</span>
                            </a>
                            </li>
                        @endif
                    </ul>
                @endif
                
                @if($role === 'checker')
                    <div class="nav-section-title">Validación</div>
                    
                    @if(in_array('validate_tickets', $permissions))
                        <a href="#" class="nav-link spa-link active" data-section="scan-menu" data-title="Escanear QR">
                            <i class="bi bi-qr-code-scan"></i>
                            <span>Escanear QR</span>
                        </a>
                    @endif
                @endif
            </nav>
        </div>
        <ur>
        <div class="sidebar-footer mt-auto p-3 bg-dark">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-logout">
                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</div>