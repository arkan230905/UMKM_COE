@extends('layouts.app')

@section('title', 'Hitung HPP - ' . $produk->nama_produk)

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 text-dark">
                <i class="fas fa-calculator me-2"></i>Hitung Harga Pokok Produksi
            </h2>
            <p class="text-muted mb-0">{{ $produk->nama_produk }}</p>
        </div>
        <div>
            <a href="{{ route('hpp.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Info Produk -->
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background-color: #6c757d; color: white;">
            <h6 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>Informasi Produk
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Nama Produk:</strong> {{ $produk->nama_produk }}</p>
                    <p><strong>Stok Saat Ini:</strong> {{ $produk->stok }} Unit</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Harga Jual:</strong> Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</p>
                    <p><strong>Satuan:</strong> {{ $produk->satuan ?? 'Unit' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Total Biaya Bahan:</strong> Rp {{ number_format($biayaBahanBaku->sum('subtotal'), 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Perhitungan HPP -->
    <form action="{{ route('hpp.store', $produk->id) }}" method="POST">
        @csrf
        <div class="row">
            <!-- Biaya Bahan Baku (Read-only) -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #28a745; color: white;">
                        <h6 class="mb-0">
                            <i class="fas fa-box me-2"></i>Biaya Bahan Baku (BBB)
                            <span class="float-end">Total: Rp {{ number_format($biayaBahanBaku->sum('subtotal'), 0, ',', '.') }}</span>
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
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Perhatian!</strong> Belum ada data biaya bahan baku untuk produk ini. 
                                <a href="{{ route('master-data.biaya-bahan.create', $produk->id) }}" class="alert-link">Tambah biaya bahan baku terlebih dahulu</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- BTKL Selection -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #007bff; color: white;">
                        <h6 class="mb-0">
                            <i class="fas fa-users me-2"></i>Pilih Proses BTKL
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($btkls->count() > 0)
                            <div class="row">
                                @foreach($btkls as $btkl)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="btkl_selected[]" value="{{ $btkl->id }}" id="btkl_{{ $btkl->id }}">
                                                    <label class="form-check-label" for="btkl_{{ $btkl->id }}">
                                                        <strong>{{ $btkl->nama_btkl }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            Jabatan: {{ $btkl->jabatan->nama }} | 
                                                            Kapasitas: {{ $btkl->kapasitas_per_jam }} {{ $btkl->satuan }}/jam | 
                                                            Biaya: Rp {{ number_format($btkl->biaya_per_produk, 0, ',', '.') }}/unit
                                                        </small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Belum ada data BTKL. <a href="{{ route('master-data.btkl.create') }}" class="alert-link">Tambah BTKL terlebih dahulu</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- BOP Selection -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #ffc107; color: black;">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>BOP (Biaya Overhead Pabrik)
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($bops->count() > 0)
                            <div class="row">
                                @foreach($bops as $bop)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="bop_selected[]" value="{{ $bop->id }}" id="bop_{{ $bop->id }}">
                                                    <label class="form-check-label w-100" for="bop_{{ $bop->id }}">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <strong class="text-primary">{{ $bop->nama_bop_proses }}</strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    @php
                                                                        $komponenCount = 0;
                                                                        if ($bop->komponen_bahan_pendukung && is_array($bop->komponen_bahan_pendukung)) {
                                                                            $komponenCount += count($bop->komponen_bahan_pendukung);
                                                                        }
                                                                        if ($bop->komponen_lainnya && is_array($bop->komponen_lainnya)) {
                                                                            $komponenCount += count($bop->komponen_lainnya);
                                                                        }
                                                                    @endphp
                                                                    {{ $komponenCount }} komponen
                                                                </small>
                                                            </div>
                                                            <div class="text-end">
                                                                <strong class="text-warning">Rp {{ number_format($bop->total_bop_per_produk, 0, ',', '.') }}</strong>
                                                                <br>
                                                                <small class="text-muted">/ produk</small>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Tampilkan komponen -->
                                                        @if($komponenCount > 0)
                                                            <div class="mt-2 pt-2 border-top">
                                                                <small class="text-muted d-block mb-1"><strong>Komponen:</strong></small>
                                                                
                                                                <!-- Bahan Pendukung -->
                                                                @if($bop->komponen_bahan_pendukung && is_array($bop->komponen_bahan_pendukung))
                                                                    @foreach($bop->komponen_bahan_pendukung as $komponen)
                                                                        <small class="d-block">
                                                                            <i class="fas fa-box text-success me-1"></i>
                                                                            {{ $komponen['nama'] ?? 'N/A' }} 
                                                                            <span class="text-muted">(Rp {{ number_format($komponen['total'] ?? 0, 0, ',', '.') }})</span>
                                                                        </small>
                                                                    @endforeach
                                                                @endif
                                                                
                                                                <!-- Lainnya -->
                                                                @if($bop->komponen_lainnya && is_array($bop->komponen_lainnya))
                                                                    @foreach($bop->komponen_lainnya as $komponen)
                                                                        <small class="d-block">
                                                                            <i class="fas fa-tools text-primary me-1"></i>
                                                                            {{ $komponen['nama_komponen'] ?? 'N/A' }} 
                                                                            <span class="text-muted">(Rp {{ number_format($komponen['nilai_per_produk'] ?? 0, 0, ',', '.') }})</span>
                                                                        </small>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="mt-2 pt-2 border-top">
                                                                <small class="text-muted fst-italic">Tidak ada komponen</small>
                                                            </div>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Belum ada data BOP Proses. <a href="{{ route('master-data.bop.index') }}" class="alert-link">Tambah BOP Proses terlebih dahulu</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form Input -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #6f42c1; color: white;">
                        <h6 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Detail Perhitungan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="jumlah_produk" class="form-label">Jumlah Produk</label>
                                    <input type="number" class="form-control" id="jumlah_produk" name="jumlah_produk" 
                                           value="1" min="1" required>
                                    <small class="form-text text-muted">Jumlah produk yang akan dihitung HPP-nya</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="1" 
                                              placeholder="Opsional: tambahkan keterangan perhitungan HPP">{{ 'Perhitungan HPP ' . now()->format('d/m/Y') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-calculator me-2"></i>Hitung HPP
                        </button>
                        <a href="{{ route('hpp.index') }}" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-select all BTKL if none selected (optional)
    $('input[name="btkl_selected[]"]').on('change', function() {
        // Add any interactive features here
    });

    // Auto-select all BOP if none selected (optional)
    $('input[name="bop_selected[]"]').on('change', function() {
        // Add any interactive features here
    });
});
</script>
@endpush
@endsection
