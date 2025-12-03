@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #1e1e2f; min-height: 100vh;">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h1 class="text-white mb-0">Dashboard</h1>
            <small class="text-white-50">Selamat datang, {{ Auth::user()->name }}! {{ now()->format('d M Y H:i') }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('transaksi.pembayaran-beban.create') }}" class="btn btn-primary">
                <i class="bi bi-cash-coin me-1"></i> Bayar Beban
            </a>
            <a href="{{ route('transaksi.ap-settlement.index') }}" class="btn btn-success">
                <i class="bi bi-currency-exchange me-1"></i> Lunasi Utang
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Kas & Bank</h6>
                            <h3 class="mb-0">Rp {{ number_format($totalKasBank, 0, ',', '.') }}</h3>
                        </div>
                        <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Pendapatan Bulan Ini</h6>
                            <h3 class="mb-0">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</h3>
                        </div>
                        <i class="bi bi-graph-up-arrow fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Piutang</h6>
                            <h3 class="mb-0">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>
                        </div>
                        <i class="bi bi-receipt fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Utang</h6>
                            <h3 class="mb-0">Rp {{ number_format($totalUtang, 0, ',', '.') }}</h3>
                        </div>
                        <i class="bi bi-credit-card fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card" style="background-color: #2c2c3e; border: none;">
                <div class="card-body">
                    <h5 class="text-white mb-3">Quick Actions</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-outline-light">
                            <i class="bi bi-cart-plus me-1"></i> Transaksi Baru
                        </a>
                        <a href="{{ route('transaksi.pembelian.create') }}" class="btn btn-outline-light">
                            <i class="bi bi-cart4 me-1"></i> Pembelian Baru
                        </a>
                        <a href="{{ route('transaksi.produksi.create') }}" class="btn btn-outline-light">
                            <i class="bi bi-gear me-1"></i> Produksi Baru
                        </a>
                        <a href="{{ route('transaksi.pembayaran-beban.create') }}" class="btn btn-outline-light">
                            <i class="bi bi-cash-coin me-1"></i> Bayar Beban
                        </a>
                        <a href="{{ route('transaksi.ap-settlement.index') }}" class="btn btn-outline-light">
                            <i class="bi bi-currency-exchange me-1"></i> Lunasi Utang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Cards: Master Data -->
    <div class="card mb-4" style="background-color: #2c2c3e; border: none;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white mb-0">Master Data</h5>
                <a href="#" class="text-primary text-decoration-none small">Lihat Semua</a>
            </div>
            <div class="row g-3">
                @php
                    $masterData = [
                        ['title'=>'Pegawai','count'=>$totalPegawai,'icon'=>'bi-people-fill','route'=>'master-data.pegawai.index','color'=>'primary'],
                        ['title'=>'Bahan Baku','count'=>$totalBahanBaku,'icon'=>'bi-droplet-fill','route'=>'master-data.bahan-baku.index','color'=>'info'],
                        ['title'=>'Produk','count'=>$totalProduk,'icon'=>'bi-box-seam-fill','route'=>'master-data.produk.index','color'=>'success'],
                        ['title'=>'Vendor','count'=>$totalVendor,'icon'=>'bi-shop','route'=>'master-data.vendor.index','color'=>'warning'],
                        ['title'=>'BOM','count'=>$totalBOM,'icon'=>'bi-gear-fill','route'=>'master-data.bom.index','color'=>'danger'],
                        ['title'=>'Presensi','count'=>$totalPresensi,'icon'=>'bi-calendar-check-fill','route'=>'master-data.presensi.index','color'=>'secondary'],
                    ];
                @endphp

                @foreach($masterData as $data)
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route($data['route']) }}" class="text-decoration-none">
                        <div class="card shadow hover-card text-white text-center" style="background-color: #2c2c3e; border-radius: 15px; min-height: 140px; transition: transform 0.2s;">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <div class="rounded-circle mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, 
                                    @if($data['color'] == 'primary') #667eea 0%, #764ba2 100%
                                    @elseif($data['color'] == 'info') #11cdef 0%, #1171ef 100%
                                    @elseif($data['color'] == 'success') #2dce89 0%, #2dcecc 100%
                                    @elseif($data['color'] == 'warning') #fb6340 0%, #fbb140 100%
                                    @elseif($data['color'] == 'danger') #f5365c 0%, #f56036 100%
                                    @else #6c757d 0%, #495057 100%
                                    @endif
                                );">
                                    <i class="bi {{ $data['icon'] }} fs-2 text-white"></i>
                                </div>
                                <h6 class="card-title text-white-50 mb-1 small">{{ $data['title'] }}</h6>
                                <h4 class="card-text fw-bold text-white mb-0">{{ $data['count'] }}</h4>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="row">
        <!-- Pembayaran Beban Terbaru -->
        <div class="col-md-6 mb-4">
            <div class="card h-100" style="background-color: #2c2c3e; border: none;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-white mb-0">Pembayaran Beban Terakhir</h5>
                        <a href="{{ route('transaksi.pembayaran-beban.index') }}" class="btn btn-sm btn-outline-light">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-borderless">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentExpensePayments as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $item->deskripsi ?? 'Pembayaran Beban' }}</td>
                                    <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pelunasan Utang Terbaru -->
        <div class="col-md-6 mb-4">
            <div class="card h-100" style="background-color: #2c2c3e; border: none;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-white mb-0">Pelunasan Utang Terakhir</h5>
                        <a href="{{ route('transaksi.ap-settlement.index') }}" class="btn btn-sm btn-outline-light">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-borderless">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Vendor</th>
                                    <th class="text-end">Dibayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentApSettlements as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $item->pembelian->vendor->nama_vendor ?? 'Vendor' }}</td>
                                    <td class="text-end">Rp {{ number_format($item->dibayar_bersih, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Ringkasan Transaksi -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card" style="background-color: #2c2c3e; border: none;">
                <div class="card-body">
                    <h5 class="text-white mb-3">Ringkasan Transaksi</h5>
                    <div class="row g-3">
                        @php
                            $transaksiData = [
                                [
                                    'title' => 'Penjualan',
                                    'count' => $totalPenjualan,
                                    'icon' => 'bi-cart-check',
                                    'route' => 'transaksi.penjualan.index',
                                    'color' => 'success',
                                    'trend' => $trendPenjualan ?? 0
                                ],
                                [
                                    'title' => 'Pembelian',
                                    'count' => $totalPembelian,
                                    'icon' => 'bi-cart4',
                                    'route' => 'transaksi.pembelian.index',
                                    'color' => 'primary',
                                    'trend' => $trendPembelian ?? 0
                                ],
                                [
                                    'title' => 'Produksi',
                                    'count' => $totalProduksi ?? 0,
                                    'icon' => 'bi-gear-wide-connected',
                                    'route' => 'transaksi.produksi.index',
                                    'color' => 'info',
                                    'trend' => $trendProduksi ?? 0
                                ],
                                [
                                    'title' => 'Retur',
                                    'count' => $totalRetur ?? 0,
                                    'icon' => 'bi-arrow-return-left',
                                    'route' => 'transaksi.retur.index',
                                    'color' => 'warning',
                                    'trend' => $trendRetur ?? 0
                                ]
                            ];
                        @endphp
                        
                        @foreach($transaksiData as $data)
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route($data['route']) }}" class="text-decoration-none">
                                <div class="card h-100" style="background-color: #2c2c3e; border-left: 4px solid var(--bs-{{ $data['color'] }});">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1 text-white-50">{{ $data['title'] }}</h6>
                                                <h4 class="mb-0 text-white">{{ number_format($data['count']) }}</h4>
                                            </div>
                                            <div class="bg-{{ $data['color'] }} bg-opacity-10 p-2 rounded">
                                                <i class="bi {{ $data['icon'] }} fs-4 text-{{ $data['color'] }}"></i>
                                            </div>
                                        </div>
                                        @if(isset($data['trend']))
                                        <div class="mt-2">
                                            <span class="badge bg-{{ $data['trend'] >= 0 ? 'success' : 'danger' }} bg-opacity-25 text-{{ $data['trend'] >= 0 ? 'success' : 'danger' }} me-1">
                                                <i class="bi {{ $data['trend'] >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }} me-1"></i>
                                                {{ abs($data['trend']) }}%
                                            </span>
                                            <small class="text-white-50">vs bulan lalu</small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary: #5e72e4;
    --secondary: #6c757d;
    --success: #2dce89;
    --info: #11cdef;
    --warning: #fb6340;
    --danger: #f5365c;
    --light: #f8f9fa;
    --dark: #32325d;
}

body {
    background-color: #1e1e2f;
    color: #fff;
}

.card {
    border: none;
    transition: all 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
}

.hover-card:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.bg-primary { background-color: var(--primary) !important; }
.bg-success { background-color: var(--success) !important; }
.bg-warning { background-color: var(--warning) !important; }
.bg-danger { background-color: var(--danger) !important; }
.bg-info { background-color: var(--info) !important; }

.text-primary { color: var(--primary) !important; }
.text-success { color: var(--success) !important; }
.text-warning { color: var(--warning) !important; }
.text-danger { color: var(--danger) !important; }
.text-info { color: var(--info) !important; }

.table {
    --bs-table-bg: transparent;
    --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
    --bs-table-hover-bg: rgba(255, 255, 255, 0.05);
    margin-bottom: 0;
}

.table > :not(caption) > * > * {
    padding: 1rem 1.5rem;
    border-bottom-width: 1px;
    box-shadow: inset 0 0 0 9999px var(--bs-table-accent-bg);
}

.table > :not(:first-child) {
    border-top: 0;
}

.table-hover > tbody > tr:hover > * {
    --bs-table-accent-bg: var(--bs-table-hover-bg);
    color: var(--bs-table-hover-color);
}

.btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 0.5rem 1.25rem;
}

.btn-outline-light {
    border-color: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
}

.btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
    font-size: 0.75em;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #2c2c3e;
}

::-webkit-scrollbar-thumb {
    background: #5e72e4;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #4a5bd9;
}

/* Ensure Bootstrap Icons are loaded */
.bi::before {
    display: inline-block;
    font-family: "bootstrap-icons" !important;
    font-style: normal;
    font-weight: normal !important;
    font-variant: normal;
    text-transform: none;
    line-height: 1;
    vertical-align: -.125em;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
</style>

@push('scripts')
<script>
    // Update time every minute
    function updateTime() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        document.getElementById('current-time').textContent = now.toLocaleDateString('id-ID', options);
    }
    
    // Initial call
    updateTime();
    
    // Update every minute
    setInterval(updateTime, 60000);
</script>
@endpush
@endsection
