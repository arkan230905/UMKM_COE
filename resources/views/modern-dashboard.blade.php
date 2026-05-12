@extends('layouts.app')

@section('title', 'Modern Dashboard')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --info-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        min-height: 100vh;
    }

    .dashboard-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Modern Header */
    .dashboard-header {
        background: var(--primary-gradient);
        border-radius: 25px;
        padding: 3rem 2rem;
        margin-bottom: 3rem;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: float 8s ease-in-out infinite;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite reverse;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-30px) rotate(180deg); }
    }

    .dashboard-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .dashboard-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        font-weight: 300;
    }

    /* Modern Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.2);
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

    .stat-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }

    .stat-card.primary { --gradient: var(--primary-gradient); }
    .stat-card.secondary { --gradient: var(--secondary-gradient); }
    .stat-card.success { --gradient: var(--success-gradient); }
    .stat-card.warning { --gradient: var(--warning-gradient); }
    .stat-card.info { --gradient: var(--info-gradient); }
    .stat-card.dark { --gradient: var(--dark-gradient); }

    .stat-card::before {
        background: var(--gradient);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gradient);
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-label {
        font-size: 1rem;
        color: #7f8c8d;
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .stat-change {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .stat-change.positive {
        color: #27ae60;
    }

    .stat-change.negative {
        color: #e74c3c;
    }

    /* Modern Chart Cards */
    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .chart-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .chart-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .chart-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f8f9fa;
    }

    .chart-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .chart-subtitle {
        font-size: 0.875rem;
        color: #7f8c8d;
    }

    .chart-container {
        height: 300px;
        position: relative;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 15px;
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Modern Activity Feed */
    .activity-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .activity-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .activity-header {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f8f9fa;
    }

    .activity-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: var(--primary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.25rem;
    }

    .activity-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .activity-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: #f8f9fa;
        border-radius: 10px;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--success-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1rem;
    }

    .activity-content {
        flex: 1;
    }

    .activity-text {
        font-size: 0.875rem;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .activity-time {
        font-size: 0.75rem;
        color: #7f8c8d;
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .quick-action {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .quick-action::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        transition: left 0.5s;
    }

    .quick-action:hover::before {
        left: 100%;
    }

    .quick-action:hover {
        transform: translateY(-8px) scale(1.05);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        color: inherit;
        text-decoration: none;
    }

    .quick-action-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        background: var(--primary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .quick-action-title {
        font-size: 1rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .quick-action-desc {
        font-size: 0.875rem;
        color: #7f8c8d;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }
        
        .dashboard-header {
            padding: 2rem 1.5rem;
            text-align: center;
        }
        
        .dashboard-title {
            font-size: 2rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .chart-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Loading Animation */
    .loading-shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-gradient);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-gradient);
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Modern Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="dashboard-title">Dashboard</h1>
                <p class="dashboard-subtitle">Selamat datang kembali! Berikut ringkasan aktivitas hari ini.</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="text-white">
                    <div class="h5 mb-1">{{ date('d F Y') }}</div>
                    <div class="opacity-75">{{ date('l, H:i') }} WIB</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-header">
                <div>
                    <div class="stat-value">3</div>
                    <div class="stat-label">Total Produk</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>
                +12% dari bulan lalu
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-header">
                <div>
                    <div class="stat-value">5</div>
                    <div class="stat-label">Bahan Baku</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>
                +8% dari bulan lalu
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div>
                    <div class="stat-value">2</div>
                    <div class="stat-label">Karyawan</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>
                Stabil
            </div>
        </div>

        <div class="stat-card secondary">
            <div class="stat-header">
                <div>
                    <div class="stat-value">4</div>
                    <div class="stat-label">Transaksi Pembelian Hari Ini</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>
                +25% dari kemarin
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-header">
                <div>
                    <div class="stat-value">3</div>
                    <div class="stat-label">Transaksi Penjualan Hari Ini</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-down me-1"></i>
                -5% dari kemarin
            </div>
        </div>

        <div class="stat-card dark">
            <div class="stat-header">
                <div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Transaksi Sebulan</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-calendar"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>
                Baru dimulai
            </div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-header">
                <div>
                    <div class="stat-value">Rp 3.584.745</div>
                    <div class="stat-label">Pembelian Hari Ini</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>
                +15% dari kemarin
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-header">
                <div>
                    <div class="stat-value">Rp 9.311.957</div>
                    <div class="stat-label">Total Pembelian</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>
                +22% bulan ini
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div>
                    <div class="stat-value">Rp 338.550</div>
                    <div class="stat-label">Penjualan Hari Ini</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
            </div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-down me-1"></i>
                -8% dari kemarin
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-header">
                <div>
                    <div class="stat-value">Rp 7.276.050</div>
                    <div class="stat-label">Total Penjualan</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>
                +18% bulan ini
            </div>
        </div>
    </div>

    <!-- Modern Charts -->
    <div class="chart-grid">
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Grafik Pembelian per Bulan</div>
                    <div class="chart-subtitle">Trend pembelian dalam 6 bulan terakhir</div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="purchaseChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Grafik Penjualan per Bulan</div>
                    <div class="chart-subtitle">Trend penjualan dalam 6 bulan terakhir</div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="salesChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="#" class="quick-action">
            <div class="quick-action-icon">
                <i class="fas fa-plus"></i>
            </div>
            <div class="quick-action-title">Tambah Produk</div>
            <div class="quick-action-desc">Buat produk baru</div>
        </a>

        <a href="#" class="quick-action">
            <div class="quick-action-icon" style="background: var(--success-gradient);">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="quick-action-title">Pembelian Baru</div>
            <div class="quick-action-desc">Buat transaksi pembelian</div>
        </a>

        <a href="#" class="quick-action">
            <div class="quick-action-icon" style="background: var(--warning-gradient);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="quick-action-title">Penjualan Baru</div>
            <div class="quick-action-desc">Buat transaksi penjualan</div>
        </a>

        <a href="#" class="quick-action">
            <div class="quick-action-icon" style="background: var(--info-gradient);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="quick-action-title">Laporan</div>
            <div class="quick-action-desc">Lihat laporan lengkap</div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="activity-card">
                <div class="activity-header">
                    <div class="activity-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="activity-title">Aktivitas Terbaru</div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-avatar">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Pembelian bahan baku tepung 50kg berhasil ditambahkan</div>
                        <div class="activity-time">2 jam yang lalu</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-avatar" style="background: var(--warning-gradient);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Penjualan produk roti tawar sebanyak 10 unit</div>
                        <div class="activity-time">4 jam yang lalu</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-avatar" style="background: var(--info-gradient);">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Karyawan baru Ahmad ditambahkan ke sistem</div>
                        <div class="activity-time">1 hari yang lalu</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-avatar" style="background: var(--secondary-gradient);">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">Produk baru "Roti Coklat" berhasil ditambahkan</div>
                        <div class="activity-time">2 hari yang lalu</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Purchase Chart
    const purchaseCtx = document.getElementById('purchaseChart').getContext('2d');
    new Chart(purchaseCtx, {
        type: 'line',
        data: {
            labels: ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Pembelian (Juta Rp)',
                data: [7, 8, 9, 8.5, 9.2, 9.3],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Penjualan (Juta Rp)',
                data: [6, 6.5, 7, 6.8, 7.1, 7.3],
                borderColor: '#43e97b',
                backgroundColor: 'rgba(67, 233, 123, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#43e97b',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Add loading animation to cards
    const cards = document.querySelectorAll('.stat-card, .chart-card, .activity-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush

@endsection