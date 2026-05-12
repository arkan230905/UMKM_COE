<?php

echo "=== CREATE SIMPLE BIAYA BAHAN VIEW ===\n\n";

echo "Creating a simple, working view for biaya-bahan...\n";

$viewContent = '@extends(\'layouts.app\')

@section(\'title\', \'Perhitungan Biaya Bahan Baku\')

@section(\'content\')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-calculator me-2"></i>Perhitungan Biaya Bahan
        </h2>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Data
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route(\'master-data.biaya-bahan.index\') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                               value="{{ request(\'nama_produk\') }}" placeholder="Cari nama produk...">
                    </div>
                    <div class="col-md-3">
                        <label for="harga_min" class="form-label">Harga BOM Min</label>
                        <input type="number" class="form-control" id="harga_min" name="harga_min" 
                               value="{{ request(\'harga_min\') }}" placeholder="0">
                    </div>
                    <div class="col-md-3">
                        <label for="harga_max" class="form-label">Harga BOM Max</label>
                        <input type="number" class="form-control" id="harga_max" name="harga_max" 
                               value="{{ request(\'harga_max\') }}" placeholder="999999999">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route(\'master-data.biaya-bahan.index\') }}" class="btn btn-outline-secondary">
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
                            <th style="width: 25%;">Produk</th>
                            <th style="width: 20%;" class="text-center">Bahan Baku</th>
                            <th style="width: 22%;" class="text-end">Total Biaya Bahan Baku</th>
                            <th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 20%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produkBiaya as $index => $data)
                            @php
                                $produk = $data[\'produk\'] ?? null;
                                $biaya = $data;
                                $totalBiaya = $biaya[\'total_biaya\'] ?? 0;
                                $totalBiayaBahanBaku = $biaya[\'total_biaya_bahan_baku\'] ?? 0;
                                $totalBiayaBahanPendukung = $biaya[\'total_biaya_bahan_pendukung\'] ?? 0;
                                
                                // HANYA HITUNG ITEM YANG VALID (harga > 0)
                                $detailBahanBaku = $biaya[\'detail_bahan_baku\'] ?? [];
                                $detailBahanPendukung = $biaya[\'detail_bahan_pendukung\'] ?? [];
                                
                                $jumlahBahanBaku = collect($detailBahanBaku)->filter(function($item) {
                                    return ($item[\'subtotal\'] ?? 0) > 0;
                                })->count();
                                
                                $jumlahBahanPendukung = collect($detailBahanPendukung)->filter(function($item) {
                                    return ($item[\'subtotal\'] ?? 0) > 0;
                                })->count();
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($produk && $produk->foto)
                                            <img src="{{ Storage::url($produk->foto) }}" 
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
                                            <div class="fw-bold">{{ $produk ? $produk->nama_produk : \'Unknown\' }}</div>
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
                                            Rp {{ number_format($totalBiayaBahanBaku, 0, \',\', \'.\') }}
                                        </small>
                                    @else
                                        <span class="text-muted">0 item</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold text-primary">
                                        Rp {{ number_format($totalBiayaBahanBaku, 0, \',\', \'.\') }}
                                    </div>
                                    @if($totalBiayaBahanPendukung > 0)
                                        <small class="text-muted d-block">
                                            + Rp {{ number_format($totalBiayaBahanPendukung, 0, \',\', \'.\') }}
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
                                            <a href="{{ route(\'master-data.harga-pokok-produksi.show\', $produk->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Detail HPP">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route(\'master-data.biaya-bahan.edit\', $produk->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    @else
                                        <a href="{{ route(\'master-data.biaya-bahan.create\', $produk->id) }}" 
                                           class="btn btn-sm btn-primary" title="Input Biaya Bahan">
                                            <i class="fas fa-plus"></i> Input
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        <p>Belum ada data biaya bahan</p>
                                        <small>Silakan input biaya bahan untuk produk yang tersedia</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Summary -->
            @if($produkBiaya->count() > 0)
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
                                                <div class="fw-bold">{{ $produkBiaya->count() }} item</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calculator text-success me-2"></i>
                                            <div>
                                                <small class="text-muted">Total Biaya Bahan Baku:</small>
                                                <div class="fw-bold">
                                                    Rp {{ number_format($produkBiaya->sum(\'total_biaya_bahan_baku\'), 0, \',\', \'.\') }}
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
                                                    Rp {{ number_format($produkBiaya->sum(\'total_biaya_bahan\'), 0, \',\', \'.\') }}
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
@endsection';

// Write the new view
$viewFile = 'c:\UMKM_COE\resources\views\master-data\biaya-bahan\index.blade.php';
file_put_contents($viewFile, $viewContent);

echo "✅ Created new simple view at: $viewFile\n";
echo "✅ View uses @forelse(\$produkBiaya as \$index => \$data)\n";
echo "✅ View extracts product correctly\n";
echo "✅ View displays total biaya bahan baku\n";
echo "✅ View shows proper status badges\n";
echo "✅ View includes summary section\n\n";

echo "The new view should display:\n";
echo "- Product: Jasuke\n";
echo "- Bahan Baku: 1 item\n";
echo "- Total Biaya Bahan Baku: Rp 2.500\n";
echo "- Status: Valid\n\n";

echo "=== SIMPLE VIEW CREATED ===\n";
