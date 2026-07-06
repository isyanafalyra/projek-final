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
        <div class="min-vh-100 d-flex flex-col-custom flex-column">
            <!-- Navigation -->
            @include('layouts.navigation')

            <!-- Page Heading (Optional) -->
            @isset($header)
                <header class="py-4 border-bottom border-secondary" style="background-color: rgba(30, 41, 59, 0.4);">
                    <div class="container">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-grow-1 py-5">
                <div class="container">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- Bootstrap 5 JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        
        @stack('scripts')
    </body>
</html>
