@extends('layouts.pegawai-pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">
            <i class="bi bi-speedometer2"></i> Dashboard Pegawai Pembelian
        </h2>
        <p class="text-muted">Selamat datang, {{ Auth::user()->name }}! Kelola pembelian bahan baku dengan mudah.</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card blue">
            <i class="bi bi-box-seam stat-icon"></i>
            <div class="stat-label">Total Bahan Baku</div>
            <div class="stat-value">{{ $totalBahanBaku }}</div>
            <small>Item tersedia</small>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card green">
            <i class="bi bi-building stat-icon"></i>
            <div class="stat-label">Total Vendor</div>
            <div class="stat-value">{{ $totalVendor }}</div>
            <small>Vendor terdaftar</small>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card orange">
            <i class="bi bi-cart-check stat-icon"></i>
            <div class="stat-label">Pembelian Bulan Ini</div>
            <div class="stat-value">{{ $totalPembelianBulanIni }}</div>
            <small>Transaksi</small>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card red">
            <i class="bi bi-cash-stack stat-icon"></i>
            <div class="stat-label">Nilai Pembelian</div>
            <div class="stat-value">Rp {{ number_format($totalNilaiPembelianBulanIni, 0, ',', '.') }}</div>
            <small>Bulan ini</small>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning-charge"></i> Aksi Cepat
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('pegawai-pembelian.pembelian.create') }}" class="btn btn-primary w-100">
                            <i class="bi bi-cart-plus"></i> Buat Pembelian Baru
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('pegawai-pembelian.bahan-baku.create') }}" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle"></i> Tambah Bahan Baku
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('pegawai-pembelian.vendor.create') }}" class="btn btn-info w-100">
                            <i class="bi bi-building"></i> Tambah Vendor
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('pegawai-pembelian.retur.create') }}" class="btn btn-warning w-100">
                            <i class="bi bi-arrow-return-left"></i> Buat Retur
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Pembelian Terbaru -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Pembelian Terbaru</span>
                <a href="{{ route('pegawai-pembelian.pembelian.index') }}" class="btn btn-sm btn-light">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                @if($pembelianTerbaru->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Vendor</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pembelianTerbaru as $pembelian)
                            <tr>
                                <td>
                                    <a href="{{ route('pegawai-pembelian.pembelian.show', $pembelian->id) }}" class="text-decoration-none">
                                        {{ $pembelian->nomor_pembelian ?? 'PB-' . str_pad($pembelian->id, 5, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td>{{ $pembelian->vendor->nama_vendor ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($pembelian->tanggal)->format('d/m/Y') }}</td>
                                <td class="fw-bold">Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-2">Belum ada pembelian</p>
                    <a href="{{ route('pegawai-pembelian.pembelian.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Buat Pembelian
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bahan Baku Stok Rendah -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle"></i> Stok Bahan Baku Rendah</span>
                <a href="{{ route('pegawai-pembelian.bahan-baku.index') }}" class="btn btn-sm btn-light">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                @if($bahanBakuStokRendah->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Bahan</th>
                                <th>Stok</th>
                                <th>Satuan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bahanBakuStokRendah as $bahan)
                            <tr>
                                <td>{{ $bahan->nama_bahan }}</td>
                                <td class="fw-bold text-danger">{{ $bahan->stok }}</td>
                                <td>{{ $bahan->satuan->nama_satuan ?? '-' }}</td>
                                <td>
                                    @if($bahan->stok < 5)
                                    <span class="badge bg-danger">Kritis</span>
                                    @else
                                    <span class="badge bg-warning">Rendah</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-check-circle" style="font-size: 3rem; color: #2ecc71;"></i>
                    <p class="text-muted mt-2">Semua stok aman!</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Vendor Aktif -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-star"></i> Vendor Aktif Bulan Ini</span>
                <a href="{{ route('pegawai-pembelian.vendor.index') }}" class="btn btn-sm btn-light">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                @if($vendorAktif->count() > 0)
                <div class="row g-3">
                    @foreach($vendorAktif as $vendor)
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-body">
                                <h6 class="card-title">{{ $vendor->nama_vendor }}</h6>
                                <p class="card-text small text-muted mb-2">
                                    <i class="bi bi-telephone"></i> {{ $vendor->no_telp ?? '-' }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">{{ $vendor->pembelians_count }} Transaksi</span>
                                    <a href="{{ route('pegawai-pembelian.vendor.show', $vendor->id) }}" class="btn btn-sm btn-outline-primary">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-building" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-2">Belum ada vendor aktif bulan ini</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
