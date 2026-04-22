@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">
    <!-- Modern Header -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card header-card" style="background: linear-gradient(135deg, #F5F0E8 0%, #FFFFFF 100%); border: 1px solid #E5DDD0; box-shadow: 0 4px 12px rgba(139, 115, 92, 0.15);">
                <div class="card-body py-3 px-3">
                    <div class="text-center" style="padding-top: 8px;">
                        <h1 style="color: #5A4A3A; font-weight: 700; margin-bottom: 10px; font-size: 1.5rem; letter-spacing: -0.3px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif; line-height: 1.3;">Dashboard</h1>
                        <p style="color: #7A6A5A; margin: 0 0 4px 0; font-size: 0.875rem; line-height: 1.5; font-weight: 400;">
                            Selamat datang, <span style="font-weight: 600; color: #5A4A3A;">{{ Auth::user()->name }}</span>
                        </p>
                        <p id="realtime-clock" style="color: #9A8A7A; margin: 0; font-size: 0.8125rem; line-height: 1.4;">
                            {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY • HH:mm:ss') }} WIB
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern KPI Cards -->
    <div class="row g-2 mb-3">
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card" style="background: linear-gradient(135deg, #FFFFFF 0%, #F5F0E8 100%); border: 1px solid #E5DDD0; box-shadow: 0 3px 10px rgba(139, 115, 92, 0.12); transition: all 0.3s ease; position: relative; overflow: hidden; min-height: 110px;">
                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: radial-gradient(circle, rgba(139, 115, 92, 0.08) 0%, transparent 70%); border-radius: 50%;"></div>
                <div class="card-body p-3" style="position: relative; z-index: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                    <div class="kpi-icon" style="width: 48px; height: 48px; background: #6B5847; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 4px 10px rgba(107, 88, 71, 0.4);">
                        <i class="fas fa-wallet" style="color: white; font-size: 20px;"></i>
                    </div>
                    <div style="color: #7A6A5A; font-size: 0.75rem; margin-bottom: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Kas & Bank</div>
                    <div style="color: #5A4A3A; font-weight: 700; font-size: 1.125rem; letter-spacing: -0.5px;">Rp {{ number_format($totalKasBank, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card" style="background: linear-gradient(135deg, #FFFFFF 0%, #F5F0E8 100%); border: 1px solid #E5DDD0; box-shadow: 0 3px 10px rgba(139, 115, 92, 0.12); transition: all 0.3s ease; position: relative; overflow: hidden; min-height: 110px;">
                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: radial-gradient(circle, rgba(139, 115, 92, 0.08) 0%, transparent 70%); border-radius: 50%;"></div>
                <div class="card-body p-3" style="position: relative; z-index: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                    <div class="kpi-icon" style="width: 48px; height: 48px; background: #6B5847; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 4px 10px rgba(107, 88, 71, 0.4);">
                        <i class="fas fa-chart-line" style="color: white; font-size: 20px;"></i>
                    </div>
                    <div style="color: #7A6A5A; font-size: 0.75rem; margin-bottom: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Pendapatan Bulan Ini</div>
                    <div style="color: #5A4A3A; font-weight: 700; font-size: 1.125rem; letter-spacing: -0.5px;">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card" style="background: linear-gradient(135deg, #FFFFFF 0%, #F5F0E8 100%); border: 1px solid #E5DDD0; box-shadow: 0 3px 10px rgba(139, 115, 92, 0.12); transition: all 0.3s ease; position: relative; overflow: hidden; min-height: 110px;">
                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: radial-gradient(circle, rgba(139, 115, 92, 0.08) 0%, transparent 70%); border-radius: 50%;"></div>
                <div class="card-body p-3" style="position: relative; z-index: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                    <div class="kpi-icon" style="width: 48px; height: 48px; background: #6B5847; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 4px 10px rgba(107, 88, 71, 0.4);">
                        <i class="fas fa-receipt" style="color: white; font-size: 20px;"></i>
                    </div>
                    <div style="color: #7A6A5A; font-size: 0.75rem; margin-bottom: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Piutang</div>
                    <div style="color: #5A4A3A; font-weight: 700; font-size: 1.125rem; letter-spacing: -0.5px;">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card kpi-card" style="background: linear-gradient(135deg, #FFFFFF 0%, #F5F0E8 100%); border: 1px solid #E5DDD0; box-shadow: 0 3px 10px rgba(139, 115, 92, 0.12); transition: all 0.3s ease; position: relative; overflow: hidden; min-height: 110px;">
                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: radial-gradient(circle, rgba(139, 115, 92, 0.08) 0%, transparent 70%); border-radius: 50%;"></div>
                <div class="card-body p-3" style="position: relative; z-index: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                    <div class="kpi-icon" style="width: 48px; height: 48px; background: #6B5847; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 4px 10px rgba(107, 88, 71, 0.4);">
                        <i class="fas fa-credit-card" style="color: white; font-size: 20px;"></i>
                    </div>
                    <div style="color: #7A6A5A; font-size: 0.75rem; margin-bottom: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Utang</div>
                    <div style="color: #5A4A3A; font-weight: 700; font-size: 1.125rem; letter-spacing: -0.5px;">Rp {{ number_format($totalUtang, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Data Section -->
    <div class="row g-2 mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" style="font-size: 0.875rem;"><i class="fas fa-database me-2"></i>Master Data</h5>
                </div>
                <div class="card-body" style="padding: 0.75rem;">
                    <div class="row g-2">
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('master-data.pegawai.index') }}" class="text-decoration-none">
                                <div class="text-center p-2 rounded master-data-card" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1); cursor: pointer; transition: all 0.3s ease;">
                                    <div class="rounded-circle mx-auto mb-1 p-2" style="background-color: var(--primary-gold); width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-users text-white" style="font-size: 0.75rem;"></i>
                                    </div>
                                    <h6 class="mb-1 text-dark" style="font-size: 0.75rem;">Pegawai</h6>
                                    <h4 class="mb-0" style="color: var(--primary-gold); font-size: 1.125rem;">{{ $totalPegawai }}</h4>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('master-data.produk.index') }}" class="text-decoration-none">
                                <div class="text-center p-2 rounded master-data-card" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1); cursor: pointer; transition: all 0.3s ease;">
                                    <div class="rounded-circle mx-auto mb-1 p-2" style="background-color: var(--primary-gold); width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-box text-white" style="font-size: 0.75rem;"></i>
                                    </div>
                                    <h6 class="mb-1 text-dark" style="font-size: 0.75rem;">Produk</h6>
                                    <h4 class="mb-0" style="color: var(--primary-gold); font-size: 1.125rem;">{{ $totalProduk }}</h4>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('master-data.vendor.index') }}" class="text-decoration-none">
                                <div class="text-center p-2 rounded master-data-card" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1); cursor: pointer; transition: all 0.3s ease;">
                                    <div class="rounded-circle mx-auto mb-1 p-2" style="background-color: var(--primary-gold); width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-truck text-white" style="font-size: 0.75rem;"></i>
                                    </div>
                                    <h6 class="mb-1 text-dark" style="font-size: 0.75rem;">Vendor</h6>
                                    <h4 class="mb-0" style="color: var(--primary-gold); font-size: 1.125rem;">{{ $totalVendor }}</h4>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('master-data.bahan-baku.index') }}" class="text-decoration-none">
                                <div class="text-center p-2 rounded master-data-card" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1); cursor: pointer; transition: all 0.3s ease;">
                                    <div class="rounded-circle mx-auto mb-1 p-2" style="background-color: var(--primary-gold); width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-cubes text-white" style="font-size: 0.75rem;"></i>
                                    </div>
                                    <h6 class="mb-1 text-dark" style="font-size: 0.75rem;">Bahan Baku</h6>
                                    <h4 class="mb-0" style="color: var(--primary-gold); font-size: 1.125rem;">{{ $totalBahanBaku }}</h4>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('master-data.coa.index') }}" class="text-decoration-none">
                                <div class="text-center p-2 rounded master-data-card" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1); cursor: pointer; transition: all 0.3s ease;">
                                    <div class="rounded-circle mx-auto mb-1 p-2" style="background-color: var(--primary-gold); width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-list-alt text-white" style="font-size: 0.75rem;"></i>
                                    </div>
                                    <h6 class="mb-1 text-dark" style="font-size: 0.75rem;">COA</h6>
                                    <h4 class="mb-0" style="color: var(--primary-gold); font-size: 1.125rem;">{{ $totalCOA }}</h4>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="{{ route('transaksi.pembelian.index') }}" class="text-decoration-none">
                                <div class="text-center p-2 rounded master-data-card" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1); cursor: pointer; transition: all 0.3s ease;">
                                    <div class="rounded-circle mx-auto mb-1 p-2" style="background-color: var(--primary-gold); width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-shopping-cart text-white" style="font-size: 0.75rem;"></i>
                                    </div>
                                    <h6 class="mb-1 text-dark" style="font-size: 0.75rem;">Pembelian</h6>
                                    <h4 class="mb-0" style="color: var(--primary-gold); font-size: 1.125rem;">{{ $totalPembelian }}</h4>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-2 mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" style="font-size: 0.875rem;"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body" style="padding: 0.75rem;">
                    <div class="row g-2">
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-outline-primary w-100" style="font-size: 0.75rem;">
                                <i class="fas fa-cash-register me-1"></i>Penjualan
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-outline-primary w-100" style="font-size: 0.75rem;">
                                <i class="fas fa-shopping-cart me-1"></i>Pembelian
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-outline-primary w-100" style="font-size: 0.75rem;">
                                <i class="fas fa-industry me-1"></i>Produksi
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('akuntansi.laba-rugi') }}" class="btn btn-outline-primary w-100" style="font-size: 0.75rem;">
                                <i class="fas fa-chart-line me-1"></i>Laporan Laba Rugi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ TAMBAHAN: Kas & Bank Details dan Sales Chart -->
    <div class="row g-2 mb-3">
        <!-- Kas & Bank Details -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0" style="font-size: 0.875rem;"><i class="fas fa-wallet me-2"></i>Detail Kas & Bank</h5>
                </div>
                <div class="card-body" style="padding: 0.75rem;">
                    @if($kasBankDetails->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($kasBankDetails as $detail)
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center" style="padding: 0.5rem 0;">
                                    <div>
                                        <h6 class="mb-0" style="font-size: 0.8125rem;">{{ $detail['nama_akun'] }}</h6>
                                        <small class="text-muted" style="font-size: 0.6875rem;">{{ $detail['kode_akun'] }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ $detail['saldo_akhir'] >= 0 ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.75rem;">
                                            Rp {{ number_format($detail['saldo_akhir'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8125rem;">Total Kas & Bank</h6>
                                <h5 class="mb-0 fw-bold {{ $totalKasBank >= 0 ? 'text-primary' : 'text-danger' }}" style="font-size: 0.9375rem;">
                                    Rp {{ number_format($totalKasBank, 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('laporan.kas-bank') }}" class="btn btn-outline-primary btn-sm w-100" style="font-size: 0.75rem;">
                                <i class="fas fa-eye me-1"></i>Lihat Laporan Lengkap
                            </a>
                        </div>
                    @else
                        <div class="text-center py-2">
                            <i class="fas fa-wallet text-muted" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2 mb-0" style="font-size: 0.8125rem;">Belum ada data Kas & Bank</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0" style="font-size: 0.875rem;"><i class="fas fa-chart-line me-2"></i>Grafik Penjualan (12 Bulan Terakhir)</h5>
                </div>
                <div class="card-body" style="padding: 0.75rem;">
                    <canvas id="salesChart" style="max-height: 220px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row g-2">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" style="font-size: 0.875rem;"><i class="fas fa-clock me-2"></i>Pembayaran Beban</h5>
                </div>
                <div class="card-body" style="padding: 0.75rem;">
                    @if(!empty($recentExpensePayments) && count($recentExpensePayments) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentExpensePayments as $payment)
                                <a href="{{ route('transaksi.pembayaran-beban.show', $payment->id) }}" class="text-decoration-none">
                                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center master-data-card" style="cursor: pointer; transition: all 0.3s ease; padding: 0.5rem 0;">
                                        <div>
                                            <h6 class="mb-0 text-dark" style="font-size: 0.8125rem;">{{ $payment->bebanOperasional->nama_beban ?? $payment->coaBeban->nama_akun ?? 'Beban' }}</h6>
                                            <small class="text-muted" style="font-size: 0.6875rem;">{{ \Carbon\Carbon::parse($payment->tanggal)->format('d M Y') }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-danger" style="font-size: 0.75rem;">
                                                Rp {{ number_format($payment->jumlah, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('transaksi.pembayaran-beban.index') }}" class="btn btn-outline-primary btn-sm w-100" style="font-size: 0.75rem;">
                                <i class="fas fa-eye me-1"></i>Lihat Semua
                            </a>
                        </div>
                    @else
                        <div class="text-center py-2">
                            <i class="fas fa-money-bill-wave text-muted" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2 mb-0" style="font-size: 0.8125rem;">Belum ada transaksi</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" style="font-size: 0.875rem;"><i class="fas fa-handshake me-2"></i>Pelunasan Utang</h5>
                </div>
                <div class="card-body" style="padding: 0.75rem;">
                    @if($recentApSettlements->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentApSettlements as $settlement)
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center" style="padding: 0.5rem 0;">
                                    <div>
                                        <h6 class="mb-0" style="font-size: 0.8125rem;">{{ $settlement->nama_vendor ?? 'Vendor' }}</h6>
                                        <small class="text-muted" style="font-size: 0.6875rem;">{{ \Carbon\Carbon::parse($settlement->tanggal)->format('d M Y') }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success" style="font-size: 0.75rem;">
                                            Rp {{ number_format($settlement->jumlah, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('transaksi.pelunasan-utang.index') }}" class="btn btn-outline-primary btn-sm w-100" style="font-size: 0.75rem;">
                                <i class="fas fa-eye me-1"></i>Lihat Semua
                            </a>
                        </div>
                    @else
                        <div class="text-center py-2">
                            <i class="fas fa-credit-card text-muted" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2 mb-0" style="font-size: 0.8125rem;">Belum ada transaksi</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.hover-lift {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-lift:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
}

.hover-lift .rounded-circle {
    transition: all 0.3s ease;
}

.hover-lift:hover .rounded-circle {
    transform: scale(1.1) rotate(5deg);
    background: rgba(255,255,255,0.3) !important;
}

.hover-lift h4 {
    transition: all 0.3s ease;
}

.hover-lift:hover h4 {
    transform: scale(1.05);
}

/* Animation untuk header */
@keyframes slideInFromTop {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card:nth-child(1) .hover-lift {
    animation: slideInFromTop 0.6s ease-out;
}

.card:nth-child(2) .hover-lift {
    animation: slideInFromTop 0.7s ease-out;
}

.card:nth-child(3) .hover-lift {
    animation: slideInFromTop 0.8s ease-out;
}

.card:nth-child(4) .hover-lift {
    animation: slideInFromTop 0.9s ease-out;
}

/* KPI Card Hover Effects */
.kpi-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(139, 115, 92, 0.25) !important;
}

.kpi-card:hover .kpi-icon {
    transform: scale(1.1) rotate(-5deg);
}

.kpi-icon {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Header Animation */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.header-card {
    animation: fadeInDown 0.6s ease-out;
}

/* KPI Cards Staggered Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.kpi-card:nth-child(1) {
    animation: fadeInUp 0.5s ease-out 0.1s both;
}

.kpi-card:nth-child(2) {
    animation: fadeInUp 0.5s ease-out 0.2s both;
}

.kpi-card:nth-child(3) {
    animation: fadeInUp 0.5s ease-out 0.3s both;
}

.kpi-card:nth-child(4) {
    animation: fadeInUp 0.5s ease-out 0.4s both;
}
</style>

<!-- ✅ TAMBAHAN: Chart.js untuk Sales Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ✅ Realtime Clock
function updateClock() {
    const now = new Date();
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    const dayName = days[now.getDay()];
    const day = now.getDate();
    const month = months[now.getMonth()];
    const year = now.getFullYear();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    
    const timeString = `${dayName}, ${day} ${month} ${year} • ${hours}:${minutes}:${seconds} WIB`;
    
    const clockElement = document.getElementById('realtime-clock');
    if (clockElement) {
        clockElement.textContent = timeString;
    }
}

// Update clock every second
setInterval(updateClock, 1000);
// Update immediately on load
updateClock();

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