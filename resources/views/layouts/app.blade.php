<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIMCOST - @yield('title', 'Dashboard')</title>
    <!-- Favicon menggunakan logo asli - PRIORITAS UKURAN BESAR -->
    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('images/logo.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/logo.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/logo.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('images/logo.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}?v={{ time() }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}?v={{ time() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo.png') }}?v={{ time() }}">
    
    <!-- CSS untuk optimasi favicon logo asli -->
    <link href="{{ asset('css/favicon-fix.css') }}" rel="stylesheet">
    
    <!-- Meta untuk Windows dan mobile -->
    <meta name="msapplication-TileImage" content="{{ asset('images/logo.png') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css?v={{ time() }}" rel="stylesheet">
    
    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v={{ time() }}" rel="stylesheet">
    
    {{-- Custom Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Modern Dashboard CSS - HIGHEST PRIORITY --}}
    <link href="{{ asset('css/modern-dashboard.css') }}?v={{ time() }}" rel="stylesheet">
    
    {{-- CRITICAL: Inline CSS Fallback --}}
    <style>
        /* Critical CSS - Always loaded */
        :root {
            --body-bg: #F4F6F9;
            --sidebar-bg: #8A6B48;
            --card-bg: #FFFFFF;
            --border: #E8ECF0;
            --text-primary: #1A1A2E;
            --text-secondary: #6B7280;
            --text-muted: #9CA3AF;
            --brown: #5C3D2E;
            --brown-light: #8B6347;
            --green: #22C55E;
            --green-bg: #DCFCE7;
            --yellow: #F59E0B;
            --yellow-bg: #FEF3C7;
            --blue: #3B82F6;
            --blue-bg: #DBEAFE;
            --red: #EF4444;
            --red-bg: #FEE2E2;
            --purple: #8B5CF6;
            --purple-bg: #EDE9FE;
        }
        
        body {
            font-family: 'Poppins', sans-serif !important;
            background: var(--body-bg) !important;
            color: var(--text-primary) !important;
            font-size: 0.8125rem !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .layout {
            display: flex !important;
            min-height: 100vh !important;
        }
        
        .sidebar {
            width: 220px !important;
            min-height: 100vh !important;
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            bottom: 0 !important;
            z-index: 1000 !important;
            overflow-y: auto !important;
            background: #8A6B48 !important;
            box-shadow: 3px 0 16px rgba(0,0,0,0.10) !important;
        }
        
        .content {
            margin-left: 220px !important;
            flex: 1 !important;
            min-height: 100vh !important;
            background: var(--body-bg) !important;
            padding-top: 90px !important;
        }
        
        .topbar {
            background: var(--body-bg) !important;
            border-bottom: 1px solid var(--border) !important;
            padding: 16px 24px !important;
            position: fixed !important;
            top: 0 !important;
            left: 220px !important;
            right: 0 !important;
            z-index: 999 !important;
        }
        
        .page-wrapper {
            padding: 20px 24px !important;
        }
        
        .kpi-card {
            background: var(--card-bg) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            padding: 16px 18px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
        }
        
        .dash-card {
            background: var(--card-bg) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
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
        @yield('content')
    </main>

</div>

@if(session('success') || session('error') || session('warning'))
<div id="notif-flash" style="position:fixed;top:20px;right:20px;z-index:99999;min-width:300px;max-width:450px;padding:14px 18px;border-radius:8px;color:white;font-size:14px;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,0.25);display:flex;align-items:center;gap:10px;background:{{ session('success') ? '#28a745' : (session('error') ? '#dc3545' : '#e6a817') }}">
    <span style="font-size:18px;flex-shrink:0">{{ session('success') ? '✔' : (session('error') ? '✖' : '⚠') }}</span>
    <span style="flex:1">{{ session('success') ?? session('error') ?? session('warning') }}</span>
    <button onclick="document.getElementById('notif-flash').remove()" style="margin-left:auto;background:none;border:none;color:white;font-size:22px;cursor:pointer;line-height:1">&times;</button>
</div>
<script>setTimeout(function(){var e=document.getElementById('notif-flash');if(e)e.remove();},3500);</script>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- Auto Reset System untuk Multi-Perusahaan --}}
{{-- <script src="{{ asset('js/auto-reset.js') }}"></script> --}}

{{-- Favicon Optimizer --}}
<script src="{{ asset('js/favicon-optimizer.js') }}"></script>

@stack('scripts')

<script>
// Auto-hide flash messages setelah 3 detik
setTimeout(function() {
    ['flash-success','flash-error'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            var alert = bootstrap.Alert.getOrCreateInstance(el);
            alert.close();
        }
    });
}, 3000);
</script>
</body>
</html>
