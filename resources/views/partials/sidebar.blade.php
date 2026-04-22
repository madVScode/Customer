{{-- ============================================================ --}}
{{-- resources/views/partials/sidebar.blade.php                  --}}
{{-- Sidebar navigasi utama                                      --}}
{{-- ✅ Companies disembunyikan untuk owner_business             --}}
{{-- ============================================================ --}}
<div class="sidebar">

    {{-- Header --}}
    <div class="sidebar-header">
        <div class="sidebar-title">Customer Management</div>
    </div>

    {{-- Menu Utama --}}
    <div class="sidebar-menu">
        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="sidebar-icon">📊</span>
            <span>Dashboard</span>
        </a>

        {{-- ✅ Companies — hanya admin_system sahaja --}}
        @if(auth()->user()->role === 'admin_system')
        <a href="{{ route('dashboard.companies') }}"
           class="sidebar-link {{ request()->routeIs('dashboard.companies') ? 'active' : '' }}">
            <span class="sidebar-icon">🏢</span>
            <span>Companies</span>
        </a>
        @endif

        <a href="{{ route('dashboard.add-customer') }}"
           class="sidebar-link {{ request()->routeIs('dashboard.add-customer') ? 'active' : '' }}">
            <span class="sidebar-icon">👥</span>
            <span>Customer</span>
        </a>

        <a href="{{ route('dashboard.add-trip') }}"
           class="sidebar-link {{ request()->routeIs('dashboard.add-trip') ? 'active' : '' }}">
            <span class="sidebar-icon">✈️</span>
            <span>Add Trip</span>
        </a>

        <a href="{{ route('dashboard.view-trips') }}"
           class="sidebar-link {{ request()->routeIs('dashboard.view-trips') ? 'active' : '' }}">
            <span class="sidebar-icon">🗺️</span>
            <span>View Trips</span>
        </a>

        <a href="{{ route('dashboard.bookings.create') }}"
           class="sidebar-link {{ request()->routeIs('dashboard.bookings.create') ? 'active' : '' }}">
            <span class="sidebar-icon">📝</span>
            <span>Create Booking</span>
        </a>

        <a href="{{ route('dashboard.bookings') }}"
           class="sidebar-link {{ request()->routeIs('dashboard.bookings') ? 'active' : '' }}">
            <span class="sidebar-icon">📄</span>
            <span>View Bookings</span>
        </a>

        <a href="{{ route('dashboard.receipts.create') }}"
           class="sidebar-link {{ request()->routeIs('dashboard.receipts.create') ? 'active' : '' }}">
            <span class="sidebar-icon">🧾</span>
            <span>Create Receipt</span>
        </a>

        <a href="{{ route('dashboard.receipts') }}"
           class="sidebar-link {{ request()->routeIs('dashboard.receipts') ? 'active' : '' }}">
            <span class="sidebar-icon">📋</span>
            <span>View Receipts</span>
        </a>

        <a href="{{ route('dashboard.settings') }}"
           class="sidebar-link {{ request()->routeIs('dashboard.settings') ? 'active' : '' }}">
            <span class="sidebar-icon">⚙️</span>
            <span>Settings</span>
        </a>
    </div>

    {{-- Tourist Portal --}}
    <div style="padding:.5rem 0;">
        <a href="{{ route('tourist.index') }}" class="sidebar-tourist-btn" target="_blank" title="Buka Portal Pelancong">
            <span class="sidebar-icon">🌏</span>
            <span>Portal Pelancong</span>
            <span style="margin-left:auto;font-size:.72rem;opacity:.7;">↗</span>
        </a>
    </div>

    {{-- Logout --}}
    <div style="border-top:1px solid var(--border-color);margin-top:auto;padding-top:1rem;">
        <form id="logoutForm" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-link"
                    style="width:100%;text-align:left;background:none;border:none;cursor:pointer;">
                <span class="sidebar-icon">🚪</span>
                <span>Logout</span>
            </button>
        </form>
    </div>

</div>