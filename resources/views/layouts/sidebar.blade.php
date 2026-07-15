<div class="sidebar d-flex flex-column" id="sidebar">
    <!-- Brand / Logo -->
    <div class="sidebar-brand d-flex align-items-center justify-content-between p-3 border-bottom border-secondary">
        <a class="d-flex align-items-center text-decoration-none" href="{{ route('dashboard') }}">
            <i class="fa-solid fa-globe-asia text-info me-2 fs-4"></i>
            <span class="text-white fw-bold fs-5 brand-text">Global<span class="text-info">Chain</span></span>
        </a>
        <button class="btn btn-sm btn-dark d-lg-none" id="sidebarClose">
            <i class="fa-solid fa-xmark text-white"></i>
        </button>
    </div>
    
    <!-- Sidebar Menu Items -->
    <div class="sidebar-menu flex-grow-1 overflow-y-auto py-3 px-2">
        <!-- Overview Link -->
        <div class="menu-item mb-1">
            <a href="{{ route('dashboard') }}?tab=overview" class="nav-link-sidebar" id="link-overview" data-tab-target="#overview-content">
                <i class="fa-solid fa-house me-2"></i>
                <span class="menu-label">Dashboard</span>
            </a>
        </div>

        <div class="menu-group-label text-secondary small fw-bold px-3 py-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Analytics</div>
        
        <div class="menu-item mb-1">
            <a href="{{ route('dashboard') }}?tab=global-country" class="nav-link-sidebar" id="link-global-country" data-tab-target="#country-profile-content">
                <i class="fa-solid fa-earth-americas me-2"></i>
                <span class="menu-label">Global Country</span>
            </a>
        </div>
        <div class="menu-item mb-1">
            <a href="{{ route('dashboard') }}?tab=risk-scoring" class="nav-link-sidebar" id="link-risk-scoring" data-tab-target="#risk-scoring-content">
                <i class="fa-solid fa-shield-halved me-2"></i>
                <span class="menu-label">Risk Scoring</span>
            </a>
        </div>
        <div class="menu-item mb-1">
            <a href="{{ route('dashboard') }}?tab=weather-monitor" class="nav-link-sidebar" id="link-weather-monitor" data-tab-target="#weather-monitor-content">
                <i class="fa-solid fa-cloud-bolt me-2"></i>
                <span class="menu-label">Weather Monitor</span>
            </a>
        </div>
        <div class="menu-item mb-1">
            <a href="{{ route('dashboard') }}?tab=currency-impact" class="nav-link-sidebar" id="link-currency-impact" data-tab-target="#currency-impact-content">
                <i class="fa-solid fa-money-bill-trend-up me-2"></i>
                <span class="menu-label">Currency Impact</span>
            </a>
        </div>

        <div class="menu-group-label text-secondary small fw-bold px-3 py-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Intelligence</div>
        
        <div class="menu-item mb-1">
            <a href="{{ route('dashboard') }}?tab=news-feed" class="nav-link-sidebar" id="link-news-feed" data-tab-target="#news-feed-content">
                <i class="fa-solid fa-newspaper me-2"></i>
                <span class="menu-label">News Feed</span>
            </a>
        </div>
        <div class="menu-item mb-1">
            <a href="{{ route('dashboard') }}?tab=ports-logistics" class="nav-link-sidebar" id="link-ports-logistics" data-tab-target="#ports-logistics-content">
                <i class="fa-solid fa-ship me-2"></i>
                <span class="menu-label">Ports & Logistics</span>
            </a>
        </div>
        <div class="menu-item mb-1">
            <a href="{{ route('dashboard') }}?tab=data-visualization" class="nav-link-sidebar" id="link-data-visualization" data-tab-target="#data-visualization-content">
                <i class="fa-solid fa-chart-line me-2"></i>
                <span class="menu-label">Data Visualization</span>
            </a>
        </div>
        <div class="menu-item mb-1">
            <a href="{{ route('dashboard') }}?tab=compare-countries" class="nav-link-sidebar" id="link-compare-countries" data-tab-target="#compare-countries-content">
                <i class="fa-solid fa-scale-balanced me-2"></i>
                <span class="menu-label">Compare Countries</span>
            </a>
        </div>

        <div class="menu-group-label text-secondary small fw-bold px-3 py-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">User</div>
        
        <div class="menu-item mb-1">
            <a href="{{ route('dashboard') }}?tab=watchlist" class="nav-link-sidebar" id="link-watchlist" data-tab-target="#watchlist-content">
                <i class="fa-solid fa-star me-2"></i>
                <span class="menu-label">My Watchlist</span>
            </a>
        </div>

        <div class="menu-group-label text-secondary small fw-bold px-3 py-2 text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Admin</div>
        
        @if (Auth::user()->is_admin)
            <div class="menu-item mb-1">
                <a href="{{ route('admin.dashboard') }}" class="nav-link-sidebar {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" id="link-admin-panel">
                    <i class="fa-solid fa-screwdriver-wrench me-2"></i>
                    <span class="menu-label">Control Panel</span>
                </a>
            </div>
        @endif

        <div class="menu-item mb-1">
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                @csrf
            </form>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link-sidebar text-danger" id="link-logout">
                <i class="fa-solid fa-right-from-bracket me-2 text-danger"></i>
                <span class="menu-label text-danger">Logout</span>
            </a>
        </div>
    </div>

    <!-- Small Footer showing logged in user name -->
    <div class="sidebar-footer p-3 border-top border-secondary text-center text-truncate d-block">
        <span class="text-secondary small">Masuk sebagai:</span>
        <span class="text-white d-block fw-bold text-truncate" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</span>
    </div>
</div>
