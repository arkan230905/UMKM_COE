@extends('layouts.pegawai-gudang')

@section('title', 'Dashboard Pegawai Gudang')

@push('styles')
<style>
    /* Ultra Modern Pegawai Gudang Dashboard */
    :root {
        --primary-gradient: linear-gradient(135deg, #BBAB8C 0%, #8B7355 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --info-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
    }

    .dashboard-header {
        background: var(--primary-gradient);
        border-radius: 25px;
        color: white;
        padding: 3rem 2rem;
        margin-bottom: 3rem;
        box-shadow: 0 20px 50px rgba(102, 126, 234, 0.4);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: float 8s ease-in-out infinite;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 250px;
        height: 250px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite reverse;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-30px) rotate(180deg); }
    }
    
    .stat-card {
        background: rgba(255,255,255,0.1) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        border-radius: 20px !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        overflow: hidden;
        position: relative;
        color: white !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
    }
    
    .stat-card:hover {
        transform: translateY(-15px) scale(1.05) !important;
        box-shadow: 0 25px 60px rgba(0,0,0,0.3) !important;
        background: rgba(255,255,255,0.15) !important;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }
    
    .stat-card .card-body {
        padding: 2rem;
        position: relative;
        z-index: 2;
    }
    
    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .stat-number.text-success {
        color: #198754 !important;
    }
    
    .stat-number.text-info {
        color: #0dcaf0 !important;
    }
    
    .stat-number.text-primary {
        color: #0d6efd !important;
    }
    
    .stat-number.text-warning {
        color: #ffc107 !important;
    }
    
    .stat-icon {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 15px 40px rgba(0,0,0,0.4);
    }
    
    .icon-primary { background: var(--primary-gradient); }
    .icon-warning { background: var(--secondary-gradient); }
    .icon-success { background: var(--success-gradient); }
    .icon-info { background: var(--warning-gradient); }
    
    .notification-card {
        background: white !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 107, 107, 0.3) !important;
        border-radius: 20px !important;
        box-shadow: 0 15px 40px rgba(255,107,107,0.3) !important;
        animation: pulse-glow 3s infinite;
        position: relative;
        overflow: hidden;
        color: #333 !important;
    }

    .notification-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--danger-gradient);
        animation: pulse-width 2s infinite;
    }
    
    @keyframes pulse-glow {
        0%, 100% { 
            box-shadow: 0 15px 40px rgba(255,107,107,0.3);
            border-color: rgba(255, 107, 107, 0.3);
        }
        50% { 
            box-shadow: 0 20px 50px rgba(255,107,107,0.5);
            border-color: rgba(255, 107, 107, 0.5);
        }
    }

    @keyframes pulse-width {
        0%, 100% { transform: scaleX(1); }
        50% { transform: scaleX(0.8); }
    }
    
    .quick-action-btn {
        background: rgba(255,255,255,0.1) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        border-radius: 20px !important;
        padding: 1.5rem 1rem !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        text-decoration: none !important;
        color: white !important;
        text-align: center !important;
        position: relative !important;
        overflow: hidden !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
        min-height: 150px !important;
        height: 150px !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .quick-action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .quick-action-btn:hover::before {
        left: 100%;
    }
    
    .quick-action-btn:hover {
        transform: translateY(-10px) scale(1.05) !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3) !important;
        color: white !important;
        text-decoration: none !important;
    }
    
    .quick-action-btn.btn-primary:hover { 
        background: var(--primary-gradient) !important;
        border-color: rgba(102, 126, 234, 0.5) !important;
    }
    .quick-action-btn.btn-warning:hover { 
        background: var(--secondary-gradient) !important;
        border-color: rgba(240, 147, 251, 0.5) !important;
    }
    .quick-action-btn.btn-success:hover { 
        background: var(--success-gradient) !important;
        border-color: rgba(79, 172, 254, 0.5) !important;
    }
    .quick-action-btn.btn-info:hover { 
        background: var(--warning-gradient) !important;
        border-color: rgba(67, 233, 123, 0.5) !important;
    }
    
    .modern-card {
        background: rgba(255,255,255,0.1) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        border-radius: 20px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        color: white !important;
    }
    
    .modern-card:hover {
        transform: translateY(-8px) !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3) !important;
        background: rgba(255,255,255,0.15) !important;
    }
    
    .modern-card .card-header {
        background: rgba(255,255,255,0.05) !important;
        border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        border-radius: 20px 20px 0 0 !important;
        padding: 1.5rem 2rem !important;
        color: white !important;
    }
    
    .alert-modern {
        background: #fff3cd !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 193, 7, 0.4) !important;
        border-left: 4px solid #ffc107 !important;
        border-radius: 15px !important;
        color: #856404 !important;
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3) !important;
    }

    .alert-modern strong {
        color: #856404 !important;
    }

    .alert-modern small {
        color: #856404 !important;
    }
    
    .table-modern {
        border-radius: 15px;
        overflow: hidden;
        background: transparent !important;
    }
    
    .table-modern thead th {
        background: var(--primary-gradient) !important;
        color: white !important;
        border: none !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.5px !important;
        padding: 1.2rem 1rem !important;
    }
    
    .table-modern tbody tr {
        border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        transition: all 0.3s ease !important;
    }
    
    .table-modern tbody tr:hover {
        background: #f8f9fa !important;
        transform: scale(1.02) !important;
    }

    .table-modern tbody td {
        border: none !important;
        color: #333 !important;
        padding: 1.2rem 1rem !important;
    }
    
    .welcome-text {
        font-size: 1.1rem;
        opacity: 0.9;
    }
    
    .company-info {
        background: rgba(255,255,255,0.2);
        padding: 1rem;
        border-radius: 10px;
        backdrop-filter: blur(10px);
    }
    
    .avatar-sm {
        width: 32px;
        height: 32px;
    }
    
    .avatar-lg {
        width: 80px;
        height: 80px;
    }
    
    .info-list .d-flex {
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .info-list .d-flex:last-child {
        border-bottom: none;
    }

    /* Force dark text for all important elements */
    .modern-card h5,
    .modern-card h6,
    .stat-card h6,
    .notification-card h5,
    .notification-card h6 {
        color: #333 !important;
    }

    .badge.bg-light {
        background: rgba(255,255,255,0.9) !important;
        color: #333 !important;
    }

    .btn-outline-primary {
        border-color: rgba(102, 126, 234, 0.5) !important;
        color: #667eea !important;
        background: rgba(102, 126, 234, 0.1) !important;
    }

    .btn-outline-primary:hover {
        background: var(--primary-gradient) !important;
        border-color: transparent !important;
        color: white !important;
    }
    
    @media (max-width: 768px) {
        .dashboard-header {
            text-align: center;
            padding: 1.5rem;
        }
        
        .dashboard-header .row {
            flex-direction: column;
        }
        
        .company-info {
            margin-top: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
        }
        
        .quick-action-btn {
            padding: 1rem 0.5rem;
        }
        
        .quick-action-btn i {
            font-size: 1.5rem !important;
        }
    }
    
    @media (max-width: 576px) {
        .stat-card .card-body {
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 1.8rem;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }
    }

    /* Force white text for profile information */
    .info-list .text-white,
    .info-list .fw-bold {
        color: white !important;
    }
    
    /* Equal height cards */
    .modern-card.h-100 {
        display: flex;
        flex-direction: column;
    }

    .modern-card.h-100 .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Quick actions layout adjustment */
    .quick-actions-grid {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .quick-actions-buttons {
        flex: 1;
        display: flex;
        align-items: center;
    }

    .quick-actions-buttons .row {
        height: 100%;
        align-items: stretch;
    }

    .quick-actions-buttons .col-lg-3,
    .quick-actions-buttons .col-md-6 {
        display: flex;
        align-items: stretch;
    }
    
    /* Custom grid for 5 columns */
    .quick-actions-buttons .col-lg-2-4 {
        flex: 0 0 auto;
        width: 20%;
    }
    
    @media (max-width: 992px) {
        .quick-actions-buttons .col-lg-2-4 {
            width: 50%;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Modern Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-warehouse me-3"></i>
                    Dashboard Pegawai Gudang
                </h1>
                <p class="welcome-text mb-0">
                    Selamat datang kembali, <strong>{{ $pegawai['nama'] }}</strong> 
                    <span class="badge bg-light text-dark ms-2">{{ $pegawai['jabatan'] }}</span>
                </p>
                <small class="opacity-75">{{ date('l, d F Y') }} • {{ date('H:i') }} WIB</small>
            </div>
            <div class="col-md-4 text-end">
                <div class="company-info">
                    <h6 class="mb-1">{{ $perusahaan['nama'] }}</h6>
                    <small>Kode: {{ $perusahaan['kode'] }}</small>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Modern Quick Actions & Profile - Moved to Top -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card modern-card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-rocket fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Menu Cepat</h5>
                            <small class="text-muted">Akses fitur utama dengan sekali klik</small>
                        </div>
                    </div>
                </div>
                <div class="card-body quick-actions-grid">
                    <div class="quick-actions-buttons">
                        <div class="row g-3 w-100">
                            <div class="col-lg-2-4 col-md-6">
                                <a href="{{ route('pegawai-gudang.bahan-baku.index') }}" class="quick-action-btn btn-primary h-100 d-flex flex-column justify-content-center">
                                    <i class="fas fa-boxes fa-2x mb-3"></i>
                                    <h6 class="mb-1">Bahan Baku</h6>
                                    <small class="text-muted">Kelola stok bahan</small>
                                </a>
                            </div>
                            <div class="col-lg-2-4 col-md-6">
                                <a href="{{ route('pegawai-gudang.bahan-pendukung.index') }}" class="quick-action-btn btn-warning h-100 d-flex flex-column justify-content-center">
                                    <i class="fas fa-tools fa-2x mb-3"></i>
                                    <h6 class="mb-1">Bahan Pendukung</h6>
                                    <small class="text-muted">Kelola bahan tambahan</small>
                                </a>
                            </div>
                            <div class="col-lg-2-4 col-md-6">
                                <a href="{{ route('pegawai-gudang.vendor.index') }}" class="quick-action-btn btn-success h-100 d-flex flex-column justify-content-center">
                                    <i class="fas fa-truck fa-2x mb-3"></i>
                                    <h6 class="mb-1">Vendor</h6>
                                    <small class="text-muted">Kelola supplier</small>
                                </a>
                            </div>
                            <div class="col-lg-2-4 col-md-6">
                                <a href="{{ route('pegawai-gudang.pembelian.index') }}" class="quick-action-btn btn-info h-100 d-flex flex-column justify-content-center">
                                    <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                                    <h6 class="mb-1">Pembelian</h6>
                                    <small class="text-muted">Transaksi pembelian</small>
                                </a>
                            </div>
                            <div class="col-lg-2-4 col-md-6">
                                <a href="{{ route('pegawai-gudang.laporan-stok.index') }}" class="quick-action-btn btn-secondary h-100 d-flex flex-column justify-content-center">
                                    <i class="fas fa-chart-line fa-2x mb-3"></i>
                                    <h6 class="mb-1">Laporan Stok</h6>
                                    <small class="text-muted">Analisis pergerakan stok</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card modern-card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-user-circle fa-lg text-success"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Profil Pegawai</h5>
                            <small class="text-muted">Informasi akun Anda</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-lg mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(45deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user fa-2x text-white"></i>
                        </div>
                        <h6 class="mb-1 text-white fw-bold">{{ $pegawai['nama'] }}</h6>
                        <span class="badge bg-primary rounded-pill">{{ $pegawai['jabatan'] }}</span>
                    </div>
                    
                    <div class="info-list">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-id-badge text-muted me-3"></i>
                            <div>
                                <small class="text-white">Kode Pegawai</small>
                                <div class="fw-bold text-white">{{ $pegawai['kode'] }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-envelope text-muted me-3"></i>
                            <div>
                                <small class="text-white">Email</small>
                                <div class="fw-bold text-white">{{ $pegawai['email'] }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-muted me-3"></i>
                            <div>
                                <small class="text-white">Login Terakhir</small>
                                <div class="fw-bold text-white">{{ date('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>

        .modern-card.h-100 .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Quick actions layout adjustment */
        .quick-actions-grid {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
            <div class="card notification-card">
                <div class="card-header" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border-radius: 15px 15px 0 0;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">🚨 Peringatan Stok Minimum!</h5>
                            <p class="mb-0 opacity-90">Beberapa bahan memerlukan pembelian segera</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($stok_minimum['bahan_baku']->count() > 0)
                        <div class="mb-4">
                            <h6 class="text-danger mb-3">
                                <i class="fas fa-boxes me-2"></i>Bahan Baku ({{ $stok_minimum['bahan_baku']->count() }} item)
                            </h6>
                            <div class="row">
                                @foreach($stok_minimum['bahan_baku'] as $bahan)
                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <div class="alert alert-modern">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="text-dark">{{ $bahan->nama_bahan }}</strong>
                                                    <br><small class="text-muted">
                                                        Stok: <span class="text-danger fw-bold">{{ $bahan->stok }}</span> / 
                                                        Min: <span class="text-warning fw-bold">{{ $bahan->stok_minimum }}</span> {{ $bahan->satuanRelation->kode ?? '' }}
                                                    </small>
                                                </div>
                                                <div class="text-warning">
                                                    <i class="fas fa-exclamation-circle fa-lg"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if($stok_minimum['bahan_pendukung']->count() > 0)
                        <div class="mb-4">
                            <h6 class="text-danger mb-3">
                                <i class="fas fa-tools me-2"></i>Bahan Pendukung ({{ $stok_minimum['bahan_pendukung']->count() }} item)
                            </h6>
                            <div class="row">
                                @foreach($stok_minimum['bahan_pendukung'] as $bahan)
                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <div class="alert alert-modern">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="text-dark">{{ $bahan->nama_bahan }}</strong>
                                                    <br><small class="text-muted">
                                                        Stok: <span class="text-danger fw-bold">{{ $bahan->stok }}</span> / 
                                                        Min: <span class="text-warning fw-bold">{{ $bahan->stok_minimum }}</span> {{ $bahan->satuanRelation->kode ?? '' }}
                                                    </small>
                                                </div>
                                                <div class="text-warning">
                                                    <i class="fas fa-exclamation-circle fa-lg"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <div class="text-center">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Stock Cards -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number text-success">{{ number_format($stats['total_stok_bahan_baku'], 2, ',', '.') }}</div>
                            <h6 class="text-muted mb-0">Total Stok Bahan Baku</h6>
                            <small class="text-success">
                                <i class="fas fa-cubes"></i> Real-time
                            </small>
                        </div>
                        <div class="stat-icon icon-primary">
                            <i class="fas fa-warehouse"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number text-info">{{ number_format($stats['total_stok_bahan_pendukung'], 2, ',', '.') }}</div>
                            <h6 class="text-muted mb-0">Total Stok Bahan Pendukung</h6>
                            <small class="text-info">
                                <i class="fas fa-tools"></i> Real-time
                            </small>
                        </div>
                        <div class="stat-icon icon-warning">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number text-success">{{ $stats['total_bahan_baku'] }}</div>
                            <h6 class="text-muted mb-0">Jenis Bahan Baku</h6>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> Tersedia
                            </small>
                        </div>
                        <div class="stat-icon icon-primary">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number text-info">{{ $stats['total_bahan_pendukung'] }}</div>
                            <h6 class="text-muted mb-0">Jenis Bahan Pendukung</h6>
                            <small class="text-info">
                                <i class="fas fa-tools"></i> Siap Pakai
                            </small>
                        </div>
                        <div class="stat-icon icon-warning">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number text-primary">{{ $stats['total_vendor'] }}</div>
                            <h6 class="text-muted mb-0">Vendor Aktif</h6>
                            <small class="text-primary">
                                <i class="fas fa-handshake"></i> Kerjasama
                            </small>
                        </div>
                        <div class="stat-icon icon-success">
                            <i class="fas fa-truck"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number text-warning">{{ $stats['total_pembelian_bulan_ini'] }}</div>
                            <h6 class="text-muted mb-0">Pembelian</h6>
                            <small class="text-warning">
                                <i class="fas fa-calendar"></i> Bulan Ini
                            </small>
                        </div>
                        <div class="stat-icon icon-info">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Recent Purchases -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-history fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Pembelian Terakhir</h5>
                            <small class="text-muted">Riwayat transaksi pembelian terbaru</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($recent_purchases->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-modern table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-calendar me-2"></i>Tanggal</th>
                                        <th><i class="fas fa-truck me-2"></i>Vendor</th>
                                        <th><i class="fas fa-money-bill me-2"></i>Total</th>
                                        <th><i class="fas fa-cog me-2"></i>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_purchases as $pembelian)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $pembelian->tanggal->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-building text-white"></i>
                                                    </div>
                                                    <span class="text-dark">{{ $pembelian->vendor->nama_vendor }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    Rp {{ number_format($pembelian->total, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('pegawai-gudang.pembelian.show', $pembelian->id) }}" 
                                                   class="btn btn-sm btn-outline-primary rounded-pill">
                                                    <i class="fas fa-eye me-1"></i>Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h6 class="text-dark">Belum ada data pembelian</h6>
                            <p class="text-muted">Mulai lakukan pembelian untuk melihat riwayat di sini</p>
                            <a href="{{ route('pegawai-gudang.pembelian.create') }}" class="btn btn-primary rounded-pill">
                                <i class="fas fa-plus me-2"></i>Buat Pembelian Baru
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


</div>

@push('scripts')
<script>
    // Add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
        // Animate stat cards on load
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Add hover effects to quick action buttons
        const quickBtns = document.querySelectorAll('.quick-action-btn');
        quickBtns.forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    });
</script>
@endpush

@endsection