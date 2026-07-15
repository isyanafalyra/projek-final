<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Global Chain Risk') }}</title>

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome Icons -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <!-- Custom Premium Styling -->
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        
        @stack('styles')
    </head>
    <body>
        <!-- Mobile Sidebar Backdrop -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <div class="app-layout">
            <!-- Sidebar Navigation -->
            @include('layouts.sidebar')

            <!-- Main Content Area -->
            <div class="main-container d-flex flex-column flex-grow-1 min-vh-100">
                <!-- Top Navbar for Mobile Toggle -->
                <nav class="navbar-mobile-toggle d-flex d-lg-none align-items-center justify-content-between">
                    <button class="btn btn-outline-secondary border-0 px-2" id="sidebarToggle">
                        <i class="fa-solid fa-bars text-white fs-4"></i>
                    </button>
                    <span class="text-white fw-bold fs-5">
                        <i class="fa-solid fa-globe-asia text-info me-1"></i>Global<span class="text-info">Chain</span>
                    </span>
                    <div style="width: 40px;"></div> <!-- Spacer for centering -->
                </nav>

                <!-- Page Heading (Optional) -->
                @isset($header)
                    <header class="py-4 border-bottom border-secondary" style="background-color: rgba(30, 41, 59, 0.4);">
                        <div class="container-fluid px-3 px-md-4 px-lg-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-grow-1 py-4 py-md-5">
                    <div class="container-fluid px-3 px-md-4 px-lg-5">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <!-- Bootstrap 5 JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Global Sidebar Toggle Script -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const sidebar = document.getElementById('sidebar');
                const toggleBtn = document.getElementById('sidebarToggle');
                const closeBtn = document.getElementById('sidebarClose');
                const backdrop = document.getElementById('sidebarBackdrop');

                // Sidebar Toggle Mobile Functions
                function openSidebar() {
                    sidebar.classList.add('show');
                    backdrop.classList.add('show');
                }

                function closeSidebar() {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                }

                if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
                if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
                if (backdrop) backdrop.addEventListener('click', closeSidebar);
            });
        </script>
        
        @stack('scripts')
    </body>
</html>
