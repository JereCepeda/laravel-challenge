{{-- filepath: f:\xampp\htdocs\laravel-challenge\resources\views\layouts\sidebar.blade.php --}}
<div class="p-3">
    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
            <span class="text-white fw-bold">{{ substr($user->name, 0, 1) }}</span>
        </div>
        <div>
            <h6 class="mb-0">{{ $user->name }}</h6>
            <small class="text-muted">{{ $user->role->name }}</small>
        </div>
    </div>

    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item mb-2">
            <a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </a>
        </li>
        @if($user->hasRole('admin'))
            <li class="nav-item mb-1">
                <small class="text-muted text-uppercase fw-bold px-3">Administration</small>
            </li>
            @include('layouts.sidebar.admin-menu')
        @endif

        @if($user->hasRole('checker'))
            <li class="nav-item mb-1">
                <small class="text-muted text-uppercase fw-bold px-3">Ticket Validation</small>
            </li>
            @include('layouts.sidebar.checker-menu')
        @endif
    </ul>

    <!-- Logout -->
    <div class="border-top pt-3 mt-3">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                <i class="fas fa-sign-out-alt me-2"></i>
                Logout
            </button>
        </form>
    </div>
</div>