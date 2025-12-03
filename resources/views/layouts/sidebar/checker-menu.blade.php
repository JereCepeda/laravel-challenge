
<li class="px-6 py-2 mt-4">
    <span class="text-xs font-semibold text-gray-400 uppercase">Ticket Validation</span>
</li>

<li>
    <a href="{{ route('checker.validate') }}" 
       class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('checker.validate') ? 'bg-gray-100 border-r-4 border-blue-500' : '' }}">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Validate Tickets
    </a>
</li>

<li>
    <a href="{{ route('checker.status') }}" 
       class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('checker.status') ? 'bg-gray-100 border-r-4 border-blue-500' : '' }}">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Check Status
    </a>
</li>