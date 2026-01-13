@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3><i class="bi bi-calculator me-2"></i>HPP - {{ $produk->nama_produk }}</h3>
            <small class="text-muted">Kode: {{ $produk->kode_produk }}</small>
        </div>
        <div>
            <a href="{{ route('master-data.bom-job-costing.print', $produk->id) }}" class="btn btn-outline-secondary" target="_blank"><i class="bi bi-printer me-1"></i> Cetak</a>
            <a href="{{ route('master-data.bom-job-costing.edit', $produk->id) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> Edit</a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#hapusModal">
                <i class="bi bi-trash me-1"></i> Hapus Biaya Bahan
            </button>
            <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <!-- Info Produk -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-dark text-white"><h5 class="mb-0"><i class="bi bi-box me-2"></i>Informasi Produk</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Produk:</strong><br>{{ $produk->nama_produk }}</div>
                <div class="col-md-2"><strong>Kode:</strong><br><code>{{ $produk->kode_produk }}</code></div>
                <div class="col-md-2"><strong>Jumlah per Batch:</strong><br><span class="badge bg-success fs-6">{{ number_format($bom->jumlah_produk) }} pcs</span></div>
                <div class="col-md-2"><strong>HPP/Unit:</strong><br><span class="badge bg-primary fs-6">Rp {{ number_format($bom->hpp_per_unit, 0, ',', '.') }}</span></div>
                <div class="col-md-3"><strong>Harga Jual:</strong><br>Rp {{ number_format($produk->harga_jual ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- 1. BBB -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>1. Biaya Bahan Baku (BBB)</h5></div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light"><tr><th>#</th><th>Bahan Baku</th><th class="text-center">Jumlah</th><th class="text-center">Satuan</th><th class="text-end">Harga/Satuan</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @forelse($bom->detailBBB as $i => $d)
                    <tr><td>{{ $i+1 }}</td><td>{{ $d->bahanBaku->nama_bahan ?? '-' }}</td><td class="text-center">{{ number_format($d->jumlah, 2) }}</td><td class="text-center">{{ $d->satuan }}</td><td class="text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td><td class="text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td></tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">-</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr class="table-warning"><td colspan="5" class="text-end fw-bold">Total BBB</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bbb, 0, ',', '.') }}</td></tr></tfoot>
            </table>
        </div>
    </div>

    <!-- 2. Bahan Penolong -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="bi bi-droplet me-2"></i>2. Bahan Penolong/Pendukung</h5></div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light"><tr><th>#</th><th>Bahan Penolong</th><th class="text-center">Jumlah</th><th class="text-center">Satuan</th><th class="text-end">Harga/Satuan</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @forelse($bom->detailBahanPendukung as $i => $d)
                    <tr><td>{{ $i+1 }}</td><td>{{ $d->bahanPendukung->nama_bahan ?? '-' }}</td><td class="text-center">{{ number_format($d->jumlah, 2) }}</td><td class="text-center">{{ $d->satuan }}</td><td class="text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td><td class="text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td></tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">-</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr class="table-info"><td colspan="5" class="text-end fw-bold">Total Bahan Penolong</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bahan_pendukung, 0, ',', '.') }}</td></tr></tfoot>
            </table>
        </div>
    </div>

    <!-- Ringkasan Biaya Bahan -->
    <div class="card shadow-sm mb-3 border-dark">
        <div class="card-header bg-dark text-white"><h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Ringkasan Biaya Bahan</h5></div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr><td width="60%">Total Biaya Bahan Baku (BBB)</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bbb, 0, ',', '.') }}</td></tr>
                <tr><td>Total Bahan Penolong</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bahan_pendukung, 0, ',', '.') }}</td></tr>
                <tr class="table-info"><td class="fw-bold">Harga BOM</td><td class="text-end fw-bold">Rp {{ number_format($bom->total_bbb + $bom->total_bahan_pendukung, 0, ',', '.') }}</td></tr>
                <tr class="table-primary"><td class="fw-bold fs-5">TOTAL BIAYA BAHAN PER PCS</td><td class="text-end fw-bold fs-5">Rp {{ number_format(($bom->total_bbb + $bom->total_bahan_pendukung) / max($bom->jumlah_produk, 1), 0, ',', '.') }}</td></tr>
            </table>
        </div>
    </div>
    
    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="hapusModal" tabindex="-1" aria-labelledby="hapusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="hapusModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Hapus Biaya Bahan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold">Apakah Anda yakin ingin menghapus biaya bahan untuk produk ini?</p>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Informasi Produk:</strong><br>
                        Nama: <strong>{{ $produk->nama_produk }}</strong><br>
                        Kode: <strong>{{ $produk->kode_produk }}</strong><br>
                        Total Biaya Bahan Saat Ini: <strong>Rp {{ number_format($produk->biaya_bahan, 0, ',', '.') }}</strong>
                    </div>
                    
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perhatian!</strong><br>
                        • Semua data BOM Job Costing akan dihapus permanen<br>
                        • Biaya bahan dan harga BOM akan direset ke 0<br>
                        • Tindakan ini tidak dapat dibatalkan
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <form action="{{ route('master-data.bom-job-costing.destroy', $produk->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>Ya, Hapus Biaya Bahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
