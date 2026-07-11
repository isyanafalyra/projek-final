<nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-3">
    <div class="container">
        <!-- Brand / Logo -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <i class="fa-solid fa-globe-asia text-info me-2"></i>Global<span class="text-info">Chain</span>
        </a>

        <!-- Hamburger Button for Mobile -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links & Actions -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-chart-line me-1"></i> Dashboard
                    </a>
                </li>
                @if (Auth::user()->is_admin)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="fa-solid fa-user-shield me-1"></i> Admin Panel
                        </a>
                    </li>
                @endif
            </ul>

            <!-- Settings Dropdown -->
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-regular fa-user-circle fs-5 me-2"></i> {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fa-regular fa-user me-2"></i> {{ __('Profile') }}
                            </a>
                        </li>
                        <li><hr class="dropdown-divider border-secondary"></li>
                        <li>
                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger d-flex align-items-center">
                                    <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> {{ __('Log Out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
