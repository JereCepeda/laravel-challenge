<?php
<li class="px-6 py-2 mt-4">
    <span class="text-xs font-semibold text-gray-400 uppercase">Administration</span>
</li>

<li class="nav-item mb-2">
    <a href="{{ route('admin.reports') }}" 
       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
        <i class="fas fa-chart-line me-2"></i>
        Reports & Analytics
    </a>
</li>

<li class="nav-item mb-2">
    <a href="{{ route('admin.statistics') }}" 
       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.statistics') ? 'active' : '' }}">
        <i class="fas fa-chart-bar me-2"></i>
        Statistics
    </a>
</li>

<li class="nav-item mb-2">
    <a href="{{ route('admin.redemptions') }}" 
       class="nav-link d-flex align-items-center {{ request()->routeIs('admin.redemptions') ? 'active' : '' }}">
        <i class="fas fa-history me-2"></i>
        Redemption History
    </a>
</li>