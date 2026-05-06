@extends('layouts.app')

@section('title', 'Perhitungan Biaya Bahan Baku')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-calculator me-2"></i>Perhitungan Biaya Bahan Baku
        </h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Data
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.biaya-bahan.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                               value="{{ request('nama_produk') }}" placeholder="Cari nama produk...">
                    </div>
                    <div class="col-md-3">
                        <label for="harga_min" class="form-label">Harga BOM Min</label>
                        <input type="number" class="form-control" id="harga_min" name="harga_min" 
                               value="{{ request('harga_min') }}" placeholder="0">
                    </div>
                    <div class="col-md-3">
                        <label for="harga_max" class="form-label">Harga BOM Max</label>
                        <input type="number" class="form-control" id="harga_max" name="harga_max" 
                               value="{{ request('harga_max') }}" placeholder="999999999">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('master-data.biaya-bahan.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-warning">
                        <tr>
                            <th style="width: 3%;" class="text-center">No</th>
<<<<<<< HEAD
                            <th style="width: 30%;">Produk</th>
                            <th style="width: 20%;" class="text-center">Bahan Baku</th>
                            <th style="width: 22%;" class="text-end">Total Biaya</th>
=======
                            <th style="width: 25%;">Produk</th>
                            <th style="width: 20%;" class="text-center">Bahan Baku</th>
                            <th style="width: 22%;" class="text-end">Total Biaya Bahan Baku</th>
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                            <th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 20%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produkBiaya as $index => $data)
                            @php
<<<<<<< HEAD
                                $biaya = $produkBiaya[$produk->id] ?? [];
                                $totalBiaya = $biaya['total_biaya_bahan_baku'] ?? 0; // Hanya BBB
=======
                                $produk = $data['produk'] ?? null;
                                $biaya = $data;
                                $totalBiaya = $biaya['total_biaya'] ?? 0;
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                                $totalBiayaBahanBaku = $biaya['total_biaya_bahan_baku'] ?? 0;
                                
                                // HANYA HITUNG ITEM BAHAN BAKU YANG VALID (harga > 0)
                                $detailBahanBaku = $biaya['detail_bahan_baku'] ?? [];
                                
                                $jumlahBahanBaku = collect($detailBahanBaku)->filter(function($item) {
                                    return ($item['subtotal'] ?? 0) > 0;
                                })->count();
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($produk && $produk->foto)
                                            <img src="{{ storage_url($produk->foto) }}" 
                                                 alt="{{ $produk->nama_produk }}" 
                                                 class="rounded me-2"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-box text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $produk ? $produk->nama_produk : 'Unknown' }}</div>
                                            @if($produk && $produk->barcode)
                                                <small class="text-muted">{{ $produk->barcode }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($jumlahBahanBaku > 0)
                                        <div class="mb-1">
                                            <span class="text-warning fw-semibold">{{ $jumlahBahanBaku }} item</span>
                                        </div>
                                        <small class="text-muted d-block">
                                            Rp {{ number_format($totalBiayaBahanBaku, 0, ',', '.') }}
                                        </small>
                                    @else
<<<<<<< HEAD
                                        <span class="text-muted">-</span>
=======
                                        <span class="text-muted">0 item</span>
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold text-primary">
                                        Rp {{ number_format($totalBiayaBahanBaku, 0, ',', '.') }}
                                    </div>
                                    @if($totalBiayaBahanPendukung > 0)
                                        <small class="text-muted d-block">
                                            + Rp {{ number_format($totalBiayaBahanPendukung, 0, ',', '.') }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($jumlahBahanBaku > 0 || $jumlahBahanPendukung > 0)
                                        <span class="badge bg-success">Valid</span>
                                    @else
                                        <span class="badge bg-secondary">Kosong</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($jumlahBahanBaku > 0 || $jumlahBahanPendukung > 0)
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('master-data.biaya-bahan.detail', $produk->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Detail Biaya Bahan">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('master-data.biaya-bahan.edit', $produk->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    @else
                                        <a href="{{ route('master-data.biaya-bahan.create', $produk->id) }}" 
                                           class="btn btn-sm btn-primary" title="Input Biaya Bahan">
                                            <i class="fas fa-plus"></i> Input
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
<<<<<<< HEAD
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-calculator fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Belum ada data perhitungan biaya bahan</p>
=======
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        <p>Belum ada data biaya bahan</p>
                                        <small>Silakan input biaya bahan untuk produk yang tersedia</small>
                                    </div>
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
<<<<<<< HEAD
                    @if($produks->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Total Keseluruhan:</th>
                            <th class="text-center">
                                <div class="badge bg-info">
                                    {{ collect($produkBiaya)->sum(fn($item) => count($item['detail_bahan_baku'] ?? [])) }} item
                                </div>
                                <div class="small text-muted mt-1">
                                    Rp {{ number_format(collect($produkBiaya)->sum('total_biaya_bahan_baku'), 0, ',', '.') }}
                                </div>
                            </th>
                            <th class="text-end">
                                <div class="fw-bold text-success fs-5">
                                    Rp {{ number_format(collect($produkBiaya)->sum('total_biaya_bahan_baku'), 0, ',', '.') }}
                                </div>
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                    @endif
=======
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
                </table>
            </div>
            
            <!-- Summary -->
            @if(count($produkBiaya) > 0)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-box text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted">Total Keseluruhan:</small>
                                                <div class="fw-bold">{{ count($produkBiaya) }} item</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calculator text-success me-2"></i>
                                            <div>
                                                <small class="text-muted">Total Biaya Bahan Baku:</small>
                                                <div class="fw-bold">
                                                    Rp {{ number_format(array_sum(array_column($produkBiaya, 'total_biaya_bahan_baku')), 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-chart-line text-info me-2"></i>
                                            <div>
                                                <small class="text-muted">Total Biaya Keseluruhan:</small>
                                                <div class="fw-bold">
                                                    Rp {{ number_format(array_sum(array_column($produkBiaya, 'total_biaya_bahan')), 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection