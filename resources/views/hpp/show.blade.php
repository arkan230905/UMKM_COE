@extends('layouts.app')

@section('title', 'Detail Harga Pokok Produksi - ' . $produk->nama_produk)

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 text-dark">
                <i class="fas fa-calculator me-2"></i>Detail Harga Pokok Produksi
            </h2>
            <p class="text-muted mb-0">{{ $produk->nama_produk }}</p>
        </div>
        <div>
            <a href="{{ route('hpp.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ route('hpp.recalculate', $produk->id) }}" class="btn btn-warning">
                <i class="fas fa-sync-alt me-2"></i>Hitung Ulang
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">Rp {{ number_format($totalHPP, 0, ',', '.') }}</h3>
                    <p class="mb-0">Total HPP</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">Rp {{ number_format($totalHPP / max(1, $produk->bomJobCosting->jumlah_produk ?? 1), 0, ',', '.') }}</h3>
                    <p class="mb-0">HPP per Unit</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</h3>
                    <p class="mb-0">Harga Jual</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format((($produk->harga_jual - ($totalHPP / max(1, $produk->bomJobCosting->jumlah_produk ?? 1))) / $produk->harga_jual) * 100, 1) }}%</h3>
                    <p class="mb-0">Margin</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Komponen Biaya -->
    <div class="row">
        <!-- Biaya Bahan Baku -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #28a745; color: white;">
                    <h6 class="mb-0">
                        <i class="fas fa-box me-2"></i>Biaya Bahan Baku (BBB)
                        <span class="float-end">Total: Rp {{ number_format($totalBBB, 0, ',', '.') }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($biayaBahanBaku->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama Bahan</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($biayaBahanBaku as $item)
                                        <tr>
                                            <td>{{ $item->bahanBaku->nama_bahan }}</td>
                                            <td class="text-end">{{ number_format($item->jumlah, 2) }} {{ $item->satuan }}</td>
                                            <td class="text-end">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Belum ada data biaya bahan baku</p>
                            <a href="{{ route('master-data.biaya-bahan.create', $produk->id) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-1"></i>Tambah Biaya Bahan
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Biaya Tenaga Kerja Langsung -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #007bff; color: white;">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>Biaya Tenaga Kerja Langsung (BTKL)
                        <span class="float-end">Total: Rp {{ number_format($totalBTKL, 0, ',', '.') }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($btklComponents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Proses</th>
                                        <th>Jabatan</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Tarif</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($btklComponents as $item)
                                        <tr>
                                            <td>{{ $item->btkl->nama_btkl }}</td>
                                            <td>{{ $item->btkl->jabatan->nama }}</td>
                                            <td class="text-end">{{ number_format($item->jumlah, 2) }}</td>
                                            <td class="text-end">Rp {{ number_format($item->tarif, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($item->jumlah * $item->tarif, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Belum ada data BTKL</p>
                            <a href="{{ route('master-data.btkl.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Tambah BTKL
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Biaya Overhead Produksi -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #ffc107; color: black;">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Biaya Overhead Produksi (BOP)
                        <span class="float-end">Total: Rp {{ number_format($totalBOP, 0, ',', '.') }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($bopComponents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Akun BOP</th>
                                        <th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bopComponents as $item)
                                        <tr>
                                            <td>{{ $item->bop->nama_akun }}</td>
                                            <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Belum ada data BOP</p>
                            <a href="{{ route('master-data.bop.create') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-plus me-1"></i>Tambah BOP
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary HPP -->
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #6f42c1; color: white;">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Ringkasan Perhitungan HPP
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Total Biaya Bahan Baku (BBB)</strong></td>
                                    <td class="text-end">Rp {{ number_format($totalBBB, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Biaya Tenaga Kerja Langsung (BTKL)</strong></td>
                                    <td class="text-end">Rp {{ number_format($totalBTKL, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Biaya Overhead Produksi (BOP)</strong></td>
                                    <td class="text-end">Rp {{ number_format($totalBOP, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>TOTAL HARGA POKOK PRODUKSI</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format($totalHPP, 0, ',', '.') }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Jumlah Produk</strong></td>
                                    <td class="text-end">{{ $produk->bomJobCosting->jumlah_produk ?? 1 }} Unit</td>
                                </tr>
                                <tr>
                                    <td><strong>HPP per Unit</strong></td>
                                    <td class="text-end">Rp {{ number_format($totalHPP / max(1, $produk->bomJobCosting->jumlah_produk ?? 1), 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Harga Jual per Unit</strong></td>
                                    <td class="text-end">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>MARGIN KEUNTUNGAN</strong></td>
                                    <td class="text-end"><strong>{{ number_format((($produk->harga_jual - ($totalHPP / max(1, $produk->bomJobCosting->jumlah_produk ?? 1))) / $produk->harga_jual) * 100, 1) }}%</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
