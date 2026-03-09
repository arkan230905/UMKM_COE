@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-calculator me-2"></i>Detail Perhitungan Biaya Bahan
            <small class="text-muted fw-normal">- {{ $produk->nama_produk }}</small>
        </h2>
        <div class="btn-group">
            <a href="{{ route('master-data.biaya-bahan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('master-data.biaya-bahan.edit', $produk->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Product Info -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-box me-2"></i>Informasi Produk
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Nama Produk:</strong></td>
                            <td>{{ $produk->nama_produk }}</td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi:</strong></td>
                            <td>{{ $produk->deskripsi ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Stok:</strong></td>
                            <td>
                                <span class="badge {{ $produk->stok <= 0 ? 'bg-danger' : 'bg-success' }}">
                                    {{ number_format($produk->stok, 2, ',', '.') }} {{ $produk->satuan ? $produk->satuan->nama : 'unit' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Total Biaya Bahan:</strong></td>
                            <td>
                                <span class="badge bg-info">
                                    Rp {{ number_format($totalBiayaBahan, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-4">
                    @if($produk->foto)
                        <div class="text-center">
                            <img src="{{ Storage::url($produk->foto) }}" 
                                 alt="{{ $produk->nama_produk }}" 
                                 class="img-fluid rounded shadow"
                                 style="max-height: 150px;">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Materials Used in Product -->
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Bahan yang Digunakan dalam Produk
            </h6>
        </div>
        <div class="card-body">
            @if($allDetails && count($allDetails) > 0)
                <!-- Bahan Baku Section -->
                @if($detailBahanBaku && count($detailBahanBaku) > 0)
                    <div class="mb-4">
                        <h6 class="text-info mb-3">
                            <i class="fas fa-cube me-2"></i>Bahan Baku ({{ count($detailBahanBaku) }} item)
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Nama Bahan</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Satuan</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detailBahanBaku as $index => $bahan)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold {{ ($bahan['status'] ?? '') == 'dihapus' ? 'text-decoration-line-through text-danger' : '' }}">
                                                        {{ $bahan['nama_bahan'] }}
                                                    </div>
                                                    @if(($bahan['status'] ?? '') == 'dihapus')
                                                        <div class="text-danger small">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                            <strong>Dihapus</strong>
                                                        </div>
                                                    @endif
                                                    <small class="text-muted">{{ $bahan['satuan'] }}</small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                {{ number_format($bahan['qty'], 2, ',', '.') }} {{ $bahan['satuan'] }}
                                            </td>
                                            <td class="text-center">
                                                {{ $bahan['satuan'] }}
                                            </td>
                                            <td class="text-end">
                                                @if(($bahan['status'] ?? '') == 'dihapus')
                                                    <del>Rp {{ number_format($bahan['harga_satuan'], 0, ',', '.') }}</del>
                                                    <br>
                                                    <small class="text-danger">
                                                        Terakhir: Rp {{ number_format($bahan['harga_terakhir'], 0, ',', '.') }}
                                                    </small>
                                                @else
                                                    Rp {{ number_format($bahan['harga_satuan'], 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if(($bahan['status'] ?? '') == 'dihapus')
                                                    <del>Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}</del>
                                                @else
                                                    <strong>Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}</strong>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total Bahan Baku:</th>
                                        <th class="text-end">
                                            <strong>Rp {{ number_format($totalBiayaBahanBaku, 0, ',', '.') }}</strong>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif
                
                <!-- Bahan Pendukung Section -->
                @if($detailBahanPendukung && count($detailBahanPendukung) > 0)
                    <div class="mb-4">
                        <h6 class="text-warning mb-3">
                            <i class="fas fa-flask me-2"></i>Bahan Pendukung ({{ count($detailBahanPendukung) }} item)
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Nama Bahan</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Satuan</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detailBahanPendukung as $index => $bahan)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold {{ ($bahan['status'] ?? '') == 'dihapus' ? 'text-decoration-line-through text-danger' : '' }}">
                                                        {{ $bahan['nama_bahan'] }}
                                                    </div>
                                                    @if(($bahan['status'] ?? '') == 'dihapus')
                                                        <div class="text-danger small">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                            <strong>Dihapus</strong>
                                                        </div>
                                                    @endif
                                                    <small class="text-muted">{{ $bahan['satuan'] }}</small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                {{ number_format($bahan['qty'], 2, ',', '.') }} {{ $bahan['satuan'] }}
                                            </td>
                                            <td class="text-center">
                                                {{ $bahan['satuan'] }}
                                            </td>
                                            <td class="text-end">
                                                @if(($bahan['status'] ?? '') == 'dihapus')
                                                    <del>Rp {{ number_format($bahan['harga_satuan'], 0, ',', '.') }}</del>
                                                    <br>
                                                    <small class="text-danger">
                                                        Terakhir: Rp {{ number_format($bahan['harga_terakhir'], 0, ',', '.') }}
                                                    </small>
                                                @else
                                                    Rp {{ number_format($bahan['harga_satuan'], 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if(($bahan['status'] ?? '') == 'dihapus')
                                                    <del>Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}</del>
                                                @else
                                                    <strong>Rp {{ number_format($bahan['subtotal'], 0, ',', '.') }}</strong>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total Bahan Pendukung:</th>
                                        <th class="text-end">
                                            <strong>Rp {{ number_format($totalBiayaBahanPendukung, 0, ',', '.') }}</strong>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif
                
                <!-- Summary -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-light">
                            <h6 class="alert-heading">Ringkasan Biaya Bahan untuk Produk</h6>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>Total Bahan Baku:</strong><br>
                                        <span class="text-info fs-5">Rp {{ number_format($totalBiayaBahanBaku, 0, ',', '.') }}</span>
                                        <br><small class="text-muted">{{ $detailBahanBaku ? count($detailBahanBaku) : 0 }} item</small>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>Total Bahan Pendukung:</strong><br>
                                        <span class="text-warning fs-5">Rp {{ number_format($totalBiayaBahanPendukung, 0, ',', '.') }}</span>
                                        <br><small class="text-muted">{{ $detailBahanPendukung ? count($detailBahanPendukung) : 0 }} item</small>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>Total Biaya Bahan:</strong><br>
                                        <span class="text-success fs-5">Rp {{ number_format($totalBiayaBahan, 0, ',', '.') }}</span>
                                        <br><small class="text-muted">{{ $allDetails ? count($allDetails) : 0 }} item total</small>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Perhitungan biaya bahan untuk produk ini
                                    @if($totalBiayaBahanBaku > 0 && $totalBiayaBahanPendukung == 0)
                                        (hanya bahan baku)
                                    @elseif($totalBiayaBahanBaku == 0 && $totalBiayaBahanPendukung > 0)
                                        (hanya bahan pendukung)
                                    @endif
                                </small>
                            </div>
                            @if(collect($detailBahanBaku)->contains('status', 'dihapus') || collect($detailBahanPendukung)->contains(fn($b) => ($b['status'] ?? '') == 'dihapus'))
                                <div class="alert alert-warning mt-3">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Informasi Bahan yang Dihapus</h6>
                                    <p class="mb-0">
                                        <strong>Bahan yang dihapus tidak mempengaruhi perhitungan biaya bahan:</strong>
                                    </p>
                                    <ul class="mb-2">
                                        <li><strong>Harga satuan dan subtotal diset ke 0</strong> - agar tidak merusak perhitungan biaya bahan yang masih aktif</li>
                                        <li><strong>Data terakhir tersimpan</strong> - harga dan nama bahan terakhir dicatat untuk referensi</li>
                                        <li><strong>Status "Dihapus" ditampilkan</strong> - memberikan keterangan visual yang jelas</li>
                                    </ul>
                                    <p class="mb-0">
                                        <small class="text-muted">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            <strong>Catatan:</strong> Perubahan pada harga bahan atau penambahan bahan baru akan otomatis mengupdate perhitungan biaya bahan dan harga pokok produksi.
                                        </small>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5 class="text-warning">Belum Ada Bahan</h5>
                    <p class="text-muted">Produk ini belum memiliki perhitungan biaya bahan</p>
                    <a href="{{ route('master-data.biaya-bahan.edit', $produk->id) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Biaya Bahan
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .table th {
        border-top: none;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .alert {
        border: none;
        border-radius: 0.5rem;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .fs-5 {
        font-size: 1.25rem;
    }
</style>
@endpush
@endsection
