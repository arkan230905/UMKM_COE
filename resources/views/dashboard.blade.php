@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

{{-- ============================================================
     TOPBAR
     ============================================================ --}}
<div class="topbar">
    <div class="topbar-left">
        <h1>Dashboard</h1>
        <p>Selamat datang kembali, <strong>{{ Auth::user()->name }}</strong> 👋</p>
        <p style="font-size:0.72rem;color:var(--text-muted);margin:0;display:flex;align-items:center;gap:5px;">
            <i class="fas fa-calendar-alt" style="color:var(--brown-light);"></i>
            <span id="realtime-clock">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }} &bull; {{ now()->format('H:i:s') }} WIB</span>
        </p>
    </div>
    <div class="topbar-right">
        {{-- Quick action buttons --}}
        <div class="quick-actions-bar d-none d-lg-flex">
            <a href="{{ route('transaksi.penjualan.index') }}" class="quick-action-btn">
                <div class="quick-action-icon bg-green-soft text-green"><i class="fas fa-shopping-bag"></i></div>
                <div class="quick-action-text"><strong>Penjualan</strong><small>Buat transaksi</small></div>
            </a>
            <a href="{{ route('transaksi.pembelian.index') }}" class="quick-action-btn">
                <div class="quick-action-icon bg-yellow-soft text-yellow"><i class="fas fa-shopping-cart"></i></div>
                <div class="quick-action-text"><strong>Pembelian</strong><small>Buat transaksi</small></div>
            </a>
            <a href="{{ route('transaksi.produksi.index') }}" class="quick-action-btn">
                <div class="quick-action-icon bg-blue-soft text-blue"><i class="fas fa-industry"></i></div>
                <div class="quick-action-text"><strong>Produksi</strong><small>Buat produksi</small></div>
            </a>
            <a href="{{ route('akuntansi.laba-rugi') }}" class="quick-action-btn">
                <div class="quick-action-icon bg-purple-soft" style="color:#8B5CF6"><i class="fas fa-chart-bar"></i></div>
                <div class="quick-action-text"><strong>Laporan</strong><small>Lihat laporan</small></div>
            </a>
        </div>
        {{-- Notif + logo --}}
        @php
            $notifCount = \App\Models\Penjualan::whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)->count()
                + \App\Models\Pembelian::whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)->count();
        @endphp
        <a href="#" class="topbar-btn ms-2">
            <i class="fas fa-bell"></i>
            @if($notifCount > 0)
            <span class="notif-badge">{{ $notifCount > 99 ? '99+' : $notifCount }}</span>
            @endif
        </a>
        <div style="margin-left:8px;">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height:38px;width:auto;object-fit:contain;display:block;">
        </div>
    </div>
</div>

<div class="page-wrapper">

{{-- ============================================================
     KPI CARDS ROW
     ============================================================ --}}
<div class="row g-3 mb-3">
    {{-- Total Kas & Bank --}}
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="kpi-label">Total Kas &amp; Bank</div>
                    <div class="kpi-value">Rp {{ number_format($totalKasBank, 0, ',', '.') }}</div>
                    <div class="kpi-sub">Total saldo tersedia</div>
                </div>
                <div class="kpi-icon-wrap bg-brown-soft" style="color:var(--brown);">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="kpi-sparkline">
                <canvas id="sparkKas" height="40"></canvas>
            </div>
        </div>
    </div>
    {{-- Pendapatan Bulan Ini --}}
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="kpi-label">Pendapatan Bulan Ini</div>
                    <div class="kpi-value">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</div>
                    <div class="kpi-sub">Total pendapatan</div>
                </div>
                <div class="kpi-icon-wrap bg-green-soft text-green">
                    <i class="fas fa-arrow-trend-up"></i>
                </div>
            </div>
            <div class="kpi-sparkline">
                <canvas id="sparkPendapatan" height="40"></canvas>
            </div>
        </div>
    </div>
    {{-- Total Piutang --}}
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="kpi-label">Total Piutang</div>
                    <div class="kpi-value">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
                    <div class="kpi-sub">Belum dibayar pelanggan</div>
                </div>
                <div class="kpi-icon-wrap bg-blue-soft text-blue">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
            <div class="kpi-sparkline">
                <canvas id="sparkPiutang" height="40"></canvas>
            </div>
        </div>
    </div>
    {{-- Total Utang --}}
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="kpi-label">Total Utang</div>
                    <div class="kpi-value">Rp {{ number_format($totalUtang, 0, ',', '.') }}</div>
                    <div class="kpi-sub">Belum dibayar ke supplier</div>
                </div>
                <div class="kpi-icon-wrap bg-red-soft text-red">
                    <i class="fas fa-credit-card"></i>
                </div>
            </div>
            <div class="kpi-sparkline">
                <canvas id="sparkUtang" height="40"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     ROW 2: GRAFIK PENJUALAN + RINGKASAN MASTER DATA
     ============================================================ --}}
<div class="row g-3 mb-3">
    {{-- Grafik Penjualan --}}
    <div class="col-lg-7">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h6><i class="fas fa-chart-line me-2" style="color:var(--brown-light);"></i>Grafik Penjualan (30 Hari Terakhir)</h6>
                <div class="chart-filter">
                    <button class="chart-filter-btn active" id="filter30">30 Hari Terakhir</button>
                    <button class="chart-filter-btn" id="filter12">12 Bulan</button>
                </div>
            </div>
            <div class="dash-card-body">
                <canvas id="salesChart" height="180"></canvas>
            </div>
        </div>
    </div>

    {{-- Ringkasan Master Data --}}
    <div class="col-lg-5">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h6><i class="fas fa-database me-2" style="color:var(--brown-light);"></i>Ringkasan Master Data</h6>
                <a href="{{ route('master-data.coa.index') }}" class="card-link">Lihat Semua</a>
            </div>
            <div class="dash-card-body">
                <div class="master-grid">
                    <a href="{{ route('master-data.coa.index') }}" class="master-item">
                        <div class="master-item-icon"><i class="fas fa-book"></i></div>
                        <div class="master-item-label">COA</div>
                        <div class="master-item-count">{{ $totalCOA }}</div>
                    </a>
                    <a href="{{ route('master-data.aset.index') }}" class="master-item">
                        <div class="master-item-icon"><i class="fas fa-laptop"></i></div>
                        <div class="master-item-label">Aset</div>
                        <div class="master-item-count">{{ $totalAset }}</div>
                    </a>
                    <a href="{{ route('master-data.satuan.dashboard') }}" class="master-item">
                        <div class="master-item-icon"><i class="fas fa-balance-scale"></i></div>
                        <div class="master-item-label">Satuan</div>
                        <div class="master-item-count">{{ $totalSatuan }}</div>
                    </a>
                    <a href="{{ route('master-data.produk.index') }}" class="master-item">
                        <div class="master-item-icon"><i class="fas fa-box"></i></div>
                        <div class="master-item-label">Produk</div>
                        <div class="master-item-count">{{ $totalProduk }}</div>
                    </a>
                    <a href="{{ route('master-data.vendor.index') }}" class="master-item">
                        <div class="master-item-icon"><i class="fas fa-truck"></i></div>
                        <div class="master-item-label">Vendor</div>
                        <div class="master-item-count">{{ $totalVendor }}</div>
                    </a>
                    <a href="{{ route('master-data.pegawai.index') }}" class="master-item">
                        <div class="master-item-icon"><i class="fas fa-users"></i></div>
                        <div class="master-item-label">Pegawai</div>
                        <div class="master-item-count">{{ $totalPegawai }}</div>
                    </a>
                    <a href="{{ route('master-data.pelanggan.index') }}" class="master-item">
                        <div class="master-item-icon"><i class="fas fa-user-friends"></i></div>
                        <div class="master-item-label">Pelanggan</div>
                        <div class="master-item-count">{{ $totalPelanggan }}</div>
                    </a>
                    <a href="{{ route('master-data.bahan-baku.index') }}" class="master-item">
                        <div class="master-item-icon"><i class="fas fa-cubes"></i></div>
                        <div class="master-item-label">Bahan Baku</div>
                        <div class="master-item-count">{{ $totalBahanBaku }}</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     ROW 3: TRANSAKSI HARI INI + ARUS KAS
     ============================================================ --}}
<div class="row g-3 mb-3">
    {{-- Transaksi Bulan Ini --}}
    <div class="col-lg-7">
        <div class="dash-card">
            <div class="dash-card-header">
                <h6><i class="fas fa-receipt me-2" style="color:var(--brown-light);"></i>Transaksi Bulan Ini</h6>
                <span style="font-size:0.7rem;color:var(--text-muted);">{{ now()->locale('id')->isoFormat('MMMM YYYY') }}</span>
            </div>
            <div class="dash-card-body">
                @php
                    $bln = now()->month;
                    $thn = now()->year;
                    $penjualanBulanIni = \App\Models\Penjualan::whereMonth('tanggal',$bln)->whereYear('tanggal',$thn)->count();
                    $pembelianBulanIni = \App\Models\Pembelian::whereMonth('tanggal',$bln)->whereYear('tanggal',$thn)->count();
                    $produksiBulanIni  = 0;
                    try {
                        if (\Schema::hasTable('produksis'))
                            $produksiBulanIni = \App\Models\Produksi::whereMonth('tanggal',$bln)->whereYear('tanggal',$thn)->count();
                    } catch(\Exception $e) {}
                    $returBulanIni = \App\Models\Retur::whereMonth('tanggal',$bln)->whereYear('tanggal',$thn)->count();
                    $maxBulan = max($penjualanBulanIni, $pembelianBulanIni, $produksiBulanIni, $returBulanIni, 1);
                @endphp
                <div class="today-grid">
                    <a href="{{ route('transaksi.penjualan.index') }}" class="today-item">
                        <div class="today-item-top">
                            <div class="today-item-icon bg-green-soft text-green"><i class="fas fa-shopping-bag"></i></div>
                            <span class="today-item-name">Penjualan</span>
                        </div>
                        <div class="today-item-count text-green">{{ $penjualanBulanIni }}</div>
                        <div class="today-item-sub">Transaksi bulan ini</div>
                        <div class="today-item-bar">
                            <div class="today-item-bar-fill" style="width:{{ round($penjualanBulanIni/$maxBulan*100) }}%;background:var(--green);"></div>
                        </div>
                    </a>
                    <a href="{{ route('transaksi.pembelian.index') }}" class="today-item">
                        <div class="today-item-top">
                            <div class="today-item-icon bg-yellow-soft text-yellow"><i class="fas fa-shopping-cart"></i></div>
                            <span class="today-item-name">Pembelian</span>
                        </div>
                        <div class="today-item-count text-yellow">{{ $pembelianBulanIni }}</div>
                        <div class="today-item-sub">Transaksi bulan ini</div>
                        <div class="today-item-bar">
                            <div class="today-item-bar-fill" style="width:{{ round($pembelianBulanIni/$maxBulan*100) }}%;background:var(--yellow);"></div>
                        </div>
                    </a>
                    <a href="{{ route('transaksi.produksi.index') }}" class="today-item">
                        <div class="today-item-top">
                            <div class="today-item-icon bg-blue-soft text-blue"><i class="fas fa-industry"></i></div>
                            <span class="today-item-name">Produksi</span>
                        </div>
                        <div class="today-item-count text-blue">{{ $produksiBulanIni }}</div>
                        <div class="today-item-sub">Transaksi bulan ini</div>
                        <div class="today-item-bar">
                            <div class="today-item-bar-fill" style="width:{{ round($produksiBulanIni/$maxBulan*100) }}%;background:var(--blue);"></div>
                        </div>
                    </a>
                    <a href="{{ route('transaksi.retur.index') }}" class="today-item">
                        <div class="today-item-top">
                            <div class="today-item-icon bg-red-soft text-red"><i class="fas fa-undo"></i></div>
                            <span class="today-item-name">Retur</span>
                        </div>
                        <div class="today-item-count text-red">{{ $returBulanIni }}</div>
                        <div class="today-item-sub">Transaksi bulan ini</div>
                        <div class="today-item-bar">
                            <div class="today-item-bar-fill" style="width:{{ round($returBulanIni/$maxBulan*100) }}%;background:var(--red);"></div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Arus Kas Bulan Ini --}}
    <div class="col-lg-5">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h6><i class="fas fa-circle-dollar-to-slot me-2" style="color:var(--brown-light);"></i>Arus Kas (Bulan Ini)</h6>
            </div>
            <div class="dash-card-body">
                @php
                    $pemasukan   = $pendapatanBulanIni;
                    $pengeluaran = $totalUtang > 0 ? min($totalUtang, $pemasukan * 0.6) : 0;
                    // Hitung dari pembelian bulan ini sebagai pengeluaran
                    $pengeluaranBulan = \App\Models\Pembelian::whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year)->sum('total_harga');
                    $pengeluaran = (float)$pengeluaranBulan;
                    $totalArus   = $pemasukan + $pengeluaran;
                    $pctMasuk    = $totalArus > 0 ? round($pemasukan / $totalArus * 100) : 50;
                    $pctKeluar   = 100 - $pctMasuk;
                    $totalDisplay = $pemasukan - $pengeluaran;
                @endphp
                <div class="d-flex align-items-center gap-4">
                    <div style="position:relative;width:130px;height:130px;flex-shrink:0;">
                        <canvas id="arusKasChart" width="130" height="130"></canvas>
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                            <div style="font-size:0.65rem;color:var(--text-muted);line-height:1.2;">Rp {{ number_format(abs($totalDisplay)/1000000,1) }}jt</div>
                            <div style="font-size:0.6rem;color:var(--text-muted);">Total</div>
                        </div>
                    </div>
                    <div class="arus-kas-legend flex-1">
                        <div class="arus-kas-item">
                            <div class="arus-kas-label">
                                <span class="arus-kas-dot" style="background:var(--green);"></span>
                                Pemasukan
                            </div>
                            <div>
                                <span class="arus-kas-value">Rp {{ number_format($pemasukan,0,',','.') }}</span>
                                <span class="arus-kas-pct">{{ $pctMasuk }}%</span>
                            </div>
                        </div>
                        <div class="arus-kas-item">
                            <div class="arus-kas-label">
                                <span class="arus-kas-dot" style="background:var(--red);"></span>
                                Pengeluaran
                            </div>
                            <div>
                                <span class="arus-kas-value">Rp {{ number_format($pengeluaran,0,',','.') }}</span>
                                <span class="arus-kas-pct">{{ $pctKeluar }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     ROW 4: TRANSAKSI TERBARU + PENGINGAT
     ============================================================ --}}
<div class="row g-3 mb-3">
    {{-- Transaksi Terbaru --}}
    <div class="col-lg-7">
        <div class="dash-card">
            <div class="dash-card-header">
                <h6><i class="fas fa-clock-rotate-left me-2" style="color:var(--brown-light);"></i>Transaksi Terbaru — {{ now()->locale('id')->isoFormat('MMMM YYYY') }}</h6>
                <a href="{{ route('transaksi.penjualan.index') }}" class="card-link">Lihat Semua</a>
            </div>
            <div class="dash-card-body p-0">
                @php
                    // Gabungkan penjualan, pembelian, produksi bulan ini
                    $bln = now()->month; $thn = now()->year;
                    $recentPenjualan = \App\Models\Penjualan::whereMonth('tanggal',$bln)->whereYear('tanggal',$thn)->latest('tanggal')->take(3)->get()->map(function($r){
                        return [
                            'kode'   => $r->nomor_faktur ?? 'PJ-'.$r->id,
                            'jenis'  => 'Penjualan',
                            'nama'   => $r->nama_pelanggan ?? 'Penjualan Umum',
                            'total'  => $r->total,
                            'waktu'  => $r->tanggal,
                            'status' => $r->status ?? 'selesai',
                            'icon'   => 'fas fa-shopping-bag',
                            'color'  => 'green',
                        ];
                    });
                    $recentPembelian = \App\Models\Pembelian::whereMonth('tanggal',$bln)->whereYear('tanggal',$thn)->latest('tanggal')->take(3)->get()->map(function($r){
                        return [
                            'kode'   => $r->nomor_po ?? 'PO-'.$r->id,
                            'jenis'  => 'Pembelian',
                            'nama'   => optional($r->vendor)->nama_vendor ?? 'Pembelian Bahan Baku',
                            'total'  => $r->total_harga,
                            'waktu'  => $r->tanggal,
                            'status' => $r->status ?? 'selesai',
                            'icon'   => 'fas fa-shopping-cart',
                            'color'  => 'yellow',
                        ];
                    });
                    $recentProduksi = collect();
                    try {
                        if (\Schema::hasTable('produksis')) {
                            $recentProduksi = \App\Models\Produksi::whereMonth('tanggal',$bln)->whereYear('tanggal',$thn)->latest('tanggal')->take(2)->get()->map(function($r){
                                return [
                                    'kode'   => $r->kode_produksi ?? 'PROD-'.$r->id,
                                    'jenis'  => 'Produksi',
                                    'nama'   => optional($r->produk)->nama_produk ?? 'Produksi',
                                    'total'  => $r->total_biaya ?? 0,
                                    'waktu'  => $r->tanggal,
                                    'status' => $r->status ?? 'selesai',
                                    'icon'   => 'fas fa-industry',
                                    'color'  => 'blue',
                                ];
                            });
                        }
                    } catch(\Exception $e) {}

                    $allRecent = $recentPenjualan->concat($recentPembelian)->concat($recentProduksi)
                        ->sortByDesc('waktu')->take(6)->values();
                @endphp
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th style="width:32px;">NO</th>
                            <th>KODE</th>
                            <th>JENIS</th>
                            <th>NAMA</th>
                            <th>TOTAL</th>
                            <th>WAKTU</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allRecent as $i => $tx)
                        <tr>
                            <td style="color:var(--text-muted);">{{ $i+1 }}</td>
                            <td style="font-family:monospace;font-size:0.72rem;color:var(--text-secondary);">{{ $tx['kode'] }}</td>
                            <td>
                                <span class="tx-type-badge tx-type-{{ strtolower($tx['jenis']) }}">
                                    <i class="{{ $tx['icon'] }}"></i> {{ $tx['jenis'] }}
                                </span>
                            </td>
                            <td>{{ Str::limit($tx['nama'], 22) }}</td>
                            <td style="font-weight:600;">Rp {{ number_format($tx['total'],0,',','.') }}</td>
                            <td style="color:var(--text-muted);">
                                {{ \Carbon\Carbon::parse($tx['waktu'])->format('H:i') }} WIB
                            </td>
                            <td>
                                @php
                                    $st = strtolower($tx['status']);
                                    $stClass = $st === 'lunas' || $st === 'selesai' || $st === 'completed' ? 'selesai'
                                             : ($st === 'pending' || $st === 'belum_lunas' ? 'pending' : 'proses');
                                    $stLabel = $st === 'lunas' || $st === 'selesai' || $st === 'completed' ? 'Selesai'
                                             : ($st === 'pending' || $st === 'belum_lunas' ? 'Pending' : 'Proses');
                                @endphp
                                <span class="status-badge status-{{ $stClass }}">{{ $stLabel }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:24px;color:var(--text-muted);">
                                <i class="fas fa-inbox me-2"></i>Belum ada transaksi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pengingat --}}
    <div class="col-lg-5">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h6><i class="fas fa-bell me-2" style="color:var(--brown-light);"></i>Pengingat</h6>
                <a href="#" class="card-link">Lihat Semua</a>
            </div>
            <div class="dash-card-body">
                @php
                    // Piutang: penjualan kredit
                    $piutangJatuhTempo = \App\Models\Penjualan::where('payment_method','credit')->count();
                    $totalPiutangJT    = \App\Models\Penjualan::where('payment_method','credit')->sum('total');

                    // Pembelian belum lunas
                    $pembelianBelumDiterima = \App\Models\Pembelian::where(function($q){
                        $q->where('status','pending')->orWhere('status','belum_lunas');
                    })->count();
                    $totalPembelianBD = \App\Models\Pembelian::where(function($q){
                        $q->where('status','pending')->orWhere('status','belum_lunas');
                    })->sum('total_harga');

                    // Stok bahan baku mendekati / di bawah minimum
                    $bahanMenipis = collect();
                    try {
                        $bahanMenipis = \App\Models\BahanBaku::whereColumn('stok', '<=', 'stok_minimum')
                            ->where('stok_minimum', '>', 0)
                            ->select('id','nama_bahan','stok','stok_minimum','satuan_id')
                            ->with('satuan:id,nama_satuan')
                            ->orderByRaw('(stok_minimum - stok) DESC')
                            ->get();
                    } catch(\Exception $e) {}
                    $stokMenipis = $bahanMenipis->count();
                @endphp

                @if($piutangJatuhTempo > 0)
                <a href="{{ route('transaksi.penjualan.index') }}" class="reminder-item">
                    <div class="reminder-icon bg-yellow-soft text-yellow">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="reminder-text">
                        <strong>{{ $piutangJatuhTempo }} transaksi penjualan kredit</strong>
                        <small>Total: Rp {{ number_format($totalPiutangJT,0,',','.') }}</small>
                    </div>
                    <i class="fas fa-chevron-right reminder-arrow"></i>
                </a>
                @endif

                @if($pembelianBelumDiterima > 0)
                <a href="{{ route('transaksi.pembelian.index') }}" class="reminder-item">
                    <div class="reminder-icon bg-blue-soft text-blue">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="reminder-text">
                        <strong>{{ $pembelianBelumDiterima }} pembelian belum lunas</strong>
                        <small>Total: Rp {{ number_format($totalPembelianBD,0,',','.') }}</small>
                    </div>
                    <i class="fas fa-chevron-right reminder-arrow"></i>
                </a>
                @endif

                @if($stokMenipis > 0)
                {{-- Header stok menipis --}}
                <div class="reminder-item" style="cursor:default;">
                    <div class="reminder-icon bg-red-soft text-red">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="reminder-text">
                        <strong>{{ $stokMenipis }} bahan baku perlu dibeli</strong>
                        <small>Stok sudah mencapai atau di bawah batas minimum</small>
                    </div>
                    <a href="{{ route('master-data.bahan-baku.index') }}" class="reminder-arrow" title="Lihat semua">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>

                {{-- Detail per bahan baku --}}
                <div style="margin: 4px 0 8px 0; border: 1px solid var(--border); border-radius: 10px; overflow: hidden;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.75rem;">
                        <thead>
                            <tr style="background:var(--body-bg);">
                                <th style="padding:7px 12px; text-align:left; font-size:0.65rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; border-bottom:1px solid var(--border);">Bahan Baku</th>
                                <th style="padding:7px 12px; text-align:center; font-size:0.65rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; border-bottom:1px solid var(--border);">Stok Saat Ini</th>
                                <th style="padding:7px 12px; text-align:center; font-size:0.65rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; border-bottom:1px solid var(--border);">Minimum</th>
                                <th style="padding:7px 12px; text-align:center; font-size:0.65rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; border-bottom:1px solid var(--border);">Harus Beli</th>
                                <th style="padding:7px 12px; text-align:center; font-size:0.65rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; border-bottom:1px solid var(--border);">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bahanMenipis as $bahan)
                            @php
                                $satuan = optional($bahan->satuan)->nama_satuan ?? '';
                                $selisih = $bahan->stok_minimum - $bahan->stok;
                                $harusBeli = max($selisih, 0);
                                $isHabis = $bahan->stok <= 0;
                            @endphp
                            <tr style="{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
                                <td style="padding:8px 12px; font-weight:500; color:var(--text-primary);">
                                    {{ $bahan->nama_bahan }}
                                </td>
                                <td style="padding:8px 12px; text-align:center;">
                                    <span style="font-weight:700; color:{{ $isHabis ? 'var(--red)' : 'var(--yellow)' }};">
                                        {{ number_format($bahan->stok, 0, ',', '.') }}
                                    </span>
                                    <span style="color:var(--text-muted); font-size:0.68rem;"> {{ $satuan }}</span>
                                </td>
                                <td style="padding:8px 12px; text-align:center; color:var(--text-secondary);">
                                    {{ number_format($bahan->stok_minimum, 0, ',', '.') }}
                                    <span style="color:var(--text-muted); font-size:0.68rem;"> {{ $satuan }}</span>
                                </td>
                                <td style="padding:8px 12px; text-align:center;">
                                    <span style="font-weight:700; color:var(--red);">
                                        +{{ number_format($harusBeli, 0, ',', '.') }}
                                    </span>
                                    <span style="color:var(--text-muted); font-size:0.68rem;"> {{ $satuan }}</span>
                                </td>
                                <td style="padding:8px 12px; text-align:center;">
                                    @if($isHabis)
                                        <span class="status-badge" style="background:var(--red-bg);color:#B91C1C;">Habis</span>
                                    @else
                                        <span class="status-badge" style="background:var(--yellow-bg);color:#92400E;">Menipis</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if($piutangJatuhTempo == 0 && $pembelianBelumDiterima == 0 && $stokMenipis == 0)
                <div style="text-align:center;padding:32px 0;color:var(--text-muted);">
                    <i class="fas fa-check-circle" style="font-size:2rem;color:var(--green);margin-bottom:8px;display:block;"></i>
                    <div style="font-size:0.8rem;">Semua berjalan lancar!</div>
                    <div style="font-size:0.72rem;margin-top:4px;">Tidak ada pengingat saat ini</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

</div>{{-- end page-wrapper --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ===== DATA FROM SERVER =====
const salesLabels12 = @json($salesChartData['labels'] ?? []);
const salesData12   = @json($salesChartData['data'] ?? []);

// Last 30 days labels (daily)
const labels30 = [];
const data30   = [];
@php
    $daily = [];
    for ($d = 29; $d >= 0; $d--) {
        $date = now()->subDays($d);
        $total = \App\Models\Penjualan::whereDate('tanggal', $date->toDateString())->sum('total');
        $daily[] = ['label' => $date->format('j M'), 'value' => (float)$total];
    }
@endphp
@foreach($daily as $day)
labels30.push("{{ $day['label'] }}");
data30.push({{ $day['value'] }});
@endforeach

// ===== REALTIME CLOCK (update setiap detik) =====
function updateClock() {
    const now = new Date();
    const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const str = days[now.getDay()] + ', '
              + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear()
              + ' \u2022 '
              + String(now.getHours()).padStart(2,'0') + ':'
              + String(now.getMinutes()).padStart(2,'0') + ':'
              + String(now.getSeconds()).padStart(2,'0')
              + ' WIB';
    const el = document.getElementById('realtime-clock');
    if (el) el.textContent = str;
}
updateClock();
setInterval(updateClock, 1000);

// ===== SPARKLINE HELPER =====
function makeSparkline(id, color, data) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map((_,i) => i),
            datasets: [{
                data: data,
                borderColor: color,
                borderWidth: 2,
                fill: true,
                backgroundColor: color + '18',
                tension: 0.4,
                pointRadius: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } },
            animation: { duration: 800 }
        }
    });
}

// Sparklines — use last 7 months of sales data as proxy
const spark7 = salesData12.slice(-7);
makeSparkline('sparkKas',        '#5C3D2E', spark7.map(v => v * 1.2));
makeSparkline('sparkPendapatan', '#22C55E', spark7);
makeSparkline('sparkPiutang',    '#3B82F6', spark7.map(v => v * 0.15));
makeSparkline('sparkUtang',      '#EF4444', spark7.map(v => v * 0.45));

// ===== MAIN SALES CHART =====
const salesCtx = document.getElementById('salesChart');
let salesChart;

function buildSalesChart(labels, data, label) {
    if (salesChart) salesChart.destroy();
    salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                borderColor: '#8B6347',
                borderWidth: 2.5,
                backgroundColor: 'rgba(139,99,71,0.08)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#8B6347',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, color: '#9CA3AF', maxTicksLimit: 8 }
                },
                y: {
                    grid: { color: '#F3F4F6' },
                    ticks: {
                        font: { size: 10 }, color: '#9CA3AF',
                        callback: v => v >= 1000000 ? (v/1000000).toFixed(0)+'jt' : v >= 1000 ? (v/1000).toFixed(0)+'rb' : v
                    }
                }
            }
        }
    });
}

buildSalesChart(labels30, data30, 'Penjualan Harian');

document.getElementById('filter30').addEventListener('click', function() {
    this.classList.add('active');
    document.getElementById('filter12').classList.remove('active');
    buildSalesChart(labels30, data30, 'Penjualan Harian');
});
document.getElementById('filter12').addEventListener('click', function() {
    this.classList.add('active');
    document.getElementById('filter30').classList.remove('active');
    buildSalesChart(salesLabels12, salesData12, 'Penjualan Bulanan');
});

// ===== ARUS KAS DONUT =====
const arusCtx = document.getElementById('arusKasChart');
if (arusCtx) {
    const pemasukan   = {{ $pemasukan ?? 0 }};
    const pengeluaran = {{ $pengeluaran ?? 0 }};
    new Chart(arusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pemasukan', 'Pengeluaran'],
            datasets: [{
                data: [pemasukan || 1, pengeluaran || 1],
                backgroundColor: ['#22C55E', '#EF4444'],
                borderWidth: 0,
                hoverOffset: 4,
            }]
        },
        options: {
            responsive: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + ctx.parsed.toLocaleString('id-ID')
                    }
                }
            }
        }
    });
}
</script>
@endpush
