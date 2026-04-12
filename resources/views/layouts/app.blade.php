<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    {{-- Custom Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Modern Dashboard CSS - HIGHEST PRIORITY --}}
    <link href="{{ asset('css/modern-dashboard.css') }}?v={{ time() }}" rel="stylesheet">
    
    {{-- Auto Reset System CSS --}}
    <style>
        .position-fixed {
            position: fixed !important;
            z-index: 9999;
        }
        .alert-success {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }
        .btn-close {
            background: none;
            border: none;
            color: white;
            opacity: 0.8;
        }
        .btn-close:hover {
            opacity: 1;
        }
    </style>

    {{-- Custom Alert Styles --}}
    <style>
        .alert {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-left: 5px solid #155724;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: white;
            border-left: 5px solid #721c24;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #ffc107, #f39c12);
            color: #212529;
            border-left: 5px solid #856404;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #17a2b8, #3498db);
            color: white;
            border-left: 5px solid #0c5460;
        }
        
        .alert .btn-close {
            filter: brightness(0) invert(1);
        }
        
        .alert-warning .btn-close {
            filter: brightness(0);
        }
    </style>

    @yield('head')
    @stack('styles')
</head>
<body>

<div class="layout">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        @include('layouts.sidebar')
    </aside>

    {{-- KONTEN --}}
    <main class="content">
        {{-- Flash Messages --}}
        @once
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @php
                    session()->forget('success');
                @endphp
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @php
                    session()->forget('error');
                @endphp
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @php
                    session()->forget('warning');
                @endphp
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @php
                    session()->forget('info');
                @endphp
            @endif
        @endonce

        @yield('content')
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- Auto Reset System untuk Multi-Perusahaan --}}
<script src="{{ asset('js/auto-reset.js') }}"></script>
@stack('scripts')
</body>
</html>
