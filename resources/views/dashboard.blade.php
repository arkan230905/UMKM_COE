@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">Dashboard</h2>
            <p class="text-muted">Selamat datang, {{ Auth::user()->name }}! {{ now()->format('l, d F Y H:i') }} WIB</p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: var(--primary-gold);">
                                <i class="fas fa-wallet text-white fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Kas & Bank</h6>
                            <h4 class="mb-0">Rp {{ number_format($totalKasBank, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: #28A745;">
                                <i class="fas fa-chart-line text-white fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pendapatan Bulan Ini</h6>
                            <h4 class="mb-0">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: #FFC107;">
                                <i class="fas fa-receipt text-white fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Piutang</h6>
                            <h4 class="mb-0">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: #DC3545;">
                                <i class="fas fa-credit-card text-white fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Utang</h6>
                            <h4 class="mb-0">Rp {{ number_format($totalUtang, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Data Section -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-database me-2"></i>Master Data</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                                <h6 class="mb-1">Pegawai</h6>
                                <h4 class="mb-0">{{ $totalPegawai }}</h4>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-box text-white"></i>
                                </div>
                                <h6 class="mb-1">Produk</h6>
                                <h4 class="mb-0">{{ $totalProduk }}</h4>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-truck text-white"></i>
                                </div>
                                <h6 class="mb-1">Vendor</h6>
                                <h4 class="mb-0">{{ $totalVendor }}</h4>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-cubes text-white"></i>
                                </div>
                                <h6 class="mb-1">Bahan Baku</h6>
                                <h4 class="mb-0">{{ $totalBahanBaku }}</h4>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-list-alt text-white"></i>
                                </div>
                                <h6 class="mb-1">COA</h6>
                                <h4 class="mb-0">{{ $totalCOA }}</h4>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-shopping-cart text-white"></i>
                                </div>
                                <h6 class="mb-1">Pembelian</h6>
                                <h4 class="mb-0">{{ $totalPembelian }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>Lihat Semua
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-cash-register me-2"></i>Penjualan
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-shopping-cart me-2"></i>Pembelian
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-industry me-2"></i>Produksi
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('akuntansi.laba-rugi') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-chart-line me-2"></i>Laporan Laba Rugi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ TAMBAHAN: Kas & Bank Details dan Sales Chart -->
    <div class="row g-4 mb-5">
        <!-- Kas & Bank Details -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Detail Kas & Bank</h5>
                </div>
                <div class="card-body">
                    @if($kasBankDetails->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($kasBankDetails as $detail)
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">{{ $detail['nama_akun'] }}</h6>
                                        <small class="text-muted">{{ $detail['kode_akun'] }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ $detail['saldo'] >= 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                                            Rp {{ number_format($detail['saldo'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">Total Kas & Bank</h6>
                                <h5 class="mb-0 fw-bold {{ $totalKasBank >= 0 ? 'text-primary' : 'text-danger' }}">
                                    Rp {{ number_format($totalKasBank, 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('laporan.kas-bank') }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-eye me-2"></i>Lihat Laporan Lengkap
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-wallet text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-3">Belum ada data Kas & Bank</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Grafik Penjualan (12 Bulan Terakhir)</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pembayaran Beban Terakhir</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-money-bill-wave text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="text-muted mt-3">Belum ada transaksi</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-handshake me-2"></i>Pelunasan Utang Terakhir</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-credit-card text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="text-muted mt-3">Belum ada transaksi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ✅ TAMBAHAN: Chart.js untuk Sales Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ✅ Sales Chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($salesChartData['labels']) !!},
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: {!! json_encode($salesChartData['data']) !!},
                    borderColor: '#28A745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#28A745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#28A745',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value);
                            },
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
});
</script>
@endsection