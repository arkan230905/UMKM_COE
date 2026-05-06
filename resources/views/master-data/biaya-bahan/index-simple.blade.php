@extends('layouts.app')

@section('title', 'Data Biaya Bahan Baku')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-cubes me-2"></i>Data Biaya Bahan Baku
        </h2>
        <div class="text-muted">
            <small>Data produk diambil dari halaman produk sesuai user</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Form -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('master-data.biaya-bahan.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk" 
                               class="form-control" 
                               value="{{ request('nama_produk') }}"
                               placeholder="Cari nama produk...">
                    </div>
                    <div class="col-md-4">
                        <label for="harga_bom_min" class="form-label">Harga BOM Min</label>
                        <input type="number" name="harga_bom_min" id="harga_bom_min" 
                               class="form-control" 
                               value="{{ request('harga_bom_min') }}"
                               placeholder="Minimal harga">
                    </div>
                    <div class="col-md-4">
                        <label for="harga_bom_max" class="form-label">Harga BOM Max</label>
                        <input type="number" name="harga_bom_max" id="harga_bom_max" 
                               class="form-control" 
                               value="{{ request('harga_bom_max') }}"
                               placeholder="Maksimal harga">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Cari
                        </button>
                        <a href="{{ route('master-data.biaya-bahan.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Produk dan Biaya Bahan
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Produk</th>
                            <th>Kode Produk</th>
                            <th>Total Biaya Bahan Baku</th>
                            <th>Jumlah Item</th>
                            <th>HPP per Unit</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $index => $produk)
                            <tr>
                                <td class="text-center">{{ $produks->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $produk->nama_produk }}</strong>
                                    @if($produk->deskripsi)
                                        <br><small class="text-muted">{{ Str::limit($produk->deskripsi, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $produk->kode_produk }}</code>
                                </td>
                                <td class="text-end">
                                    @php
                                        $biayaData = $produkBiaya[$produk->id] ?? null;
                                        $totalBiaya = $biayaData['total_biaya'] ?? 0;
                                    @endphp
                                    <strong class="text-success">
                                        Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td class="text-center">
                                    @php
                                        $jumlahItem = count($biayaData['detail_bahan_baku'] ?? []);
                                    @endphp
                                    <span class="badge bg-primary">{{ $jumlahItem }} item</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-primary">
                                        Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td class="text-center">
                                    @php
                                        // 🔒 CHECK CONDITIONS FOR RESET BUTTON
                                        $canReset = false;
                                        if ($biayaData && count($biayaData['detail_bahan_baku']) > 0) {
                                            // Check if product has no BTKL, BOP, HPP data and not in production
                                            $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)
                                                ->where('user_id', auth()->id())
                                                ->first();
                                            
                                            if ($bomJobCosting) {
                                                $hasBTKL = $bomJobCosting->total_btkl > 0;
                                                $hasBOP = $bomJobCosting->total_bop > 0;
                                                $hasHPP = $bomJobCosting->total_hpp > $bomJobCosting->total_bbb;
                                                
                                                // Check if product is in production (you may need to adjust this logic based on your production tracking)
                                                $isInProduction = false; // Adjust this based on your production tracking logic
                                                
                                                $canReset = !$hasBTKL && !$hasBOP && !$hasHPP && !$isInProduction;
                                            }
                                        }
                                    @endphp
                                    
                                    @if($biayaData && count($biayaData['detail_bahan_baku']) > 0)
                                        @if($canReset)
                                            <a href="{{ route('master-data.biaya-bahan.create', $produk->id) }}" 
                                               class="btn-modern btn-modern-warning" 
                                               title="Reset Biaya Bahan">
                                                <i class="fas fa-redo"></i>
                                                <span>Reset</span>
                                            </a>
                                        @else
                                            <div class="action-buttons">
                                                <a href="{{ route('master-data.biaya-bahan.show', $produk->id) }}" 
                                                   class="btn-modern btn-modern-info" 
                                                   title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('master-data.biaya-bahan.edit', $produk->id) }}" 
                                                   class="btn-modern btn-modern-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <a href="{{ route('master-data.biaya-bahan.create', $produk->id) }}" 
                                           class="btn-modern btn-modern-success" 
                                           title="Tambah Biaya">
                                            <i class="fas fa-plus"></i>
                                            <span>Tambah biaya</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">Belum ada data biaya bahan</p>
                                    <p class="text-muted small mt-2">Tambah produk terlebih dahulu di halaman produk</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($produks->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            Menampilkan {{ $produks->firstItem() }} - {{ $produks->lastItem() }} 
                            dari {{ $produks->total() }} data
                        </small>
                    </div>
                    <div>
                        {{ $produks->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Modern Button Styles */
.btn-modern {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    border-radius: 6px;
    border: none;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.btn-modern:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
    text-decoration: none;
}

.btn-modern:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.btn-modern i {
    font-size: 11px;
}

.btn-modern span {
    font-weight: 600;
}

/* Success Button */
.btn-modern-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-modern-success:hover {
    background: linear-gradient(135deg, #059669, #047857);
}

/* Warning Button */
.btn-modern-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.btn-modern-warning:hover {
    background: linear-gradient(135deg, #d97706, #b45309);
}

/* Info Button */
.btn-modern-info {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
}

.btn-modern-info:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

/* Action Buttons Container */
.action-buttons {
    display: flex;
    gap: 4px;
    align-items: center;
}

.action-buttons .btn-modern {
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 50%;
}

.action-buttons .btn-modern i {
    font-size: 12px;
    margin: 0;
}

/* Ripple Effect */
.btn-modern::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.3s, height 0.3s;
}

.btn-modern:hover::before {
    width: 100%;
    height: 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-modern {
        padding: 5px 10px;
        font-size: 11px;
    }
    
    .btn-modern i {
        font-size: 10px;
    }
    
    .action-buttons .btn-modern {
        width: 28px;
        height: 28px;
    }
    
    .action-buttons .btn-modern i {
        font-size: 10px;
    }
}
</style>
@endpush
