@extends('layouts.app')

@section('title', 'Detail Biaya Bahan Baku')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-eye me-2"></i>Detail Biaya Bahan Baku
        </h2>
        <a href="{{ route('master-data.biaya-bahan.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
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
                    <h5 class="mb-1">{{ $produk->nama_produk }}</h5>
                    @if($produk->barcode)
                        <p class="text-muted mb-0">Kode: {{ $produk->barcode }}</p>
                    @endif
                    @if($produk->deskripsi)
                        <p class="text-muted">{{ $produk->deskripsi }}</p>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    <div class="mb-2">
                        <small class="text-muted">Total Biaya</small>
                        <div class="h4 text-primary fw-bold">
                            Rp {{ number_format($totalSubtotal, 0, ',', '.') }}
                        </div>
                    </div>
                    <div>
                        <small class="text-muted">Total Item</small>
                        <div class="h5 text-secondary fw-bold">
                            {{ $bbbData->count() }} item
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bahan Baku Details -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Detail Bahan Baku
            </h6>
        </div>
        <div class="card-body">
            @if($bbbData->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 20%;">Nama Bahan Baku</th>
                                <th style="width: 20%;">COA</th>
                                <th style="width: 12%;" class="text-center">Jumlah</th>
                                <th style="width: 12%;" class="text-center">Harga Satuan</th>
                                <th style="width: 12%;" class="text-end">Subtotal</th>
                                <th style="width: 8%;" class="text-center">Tanggal</th>
                                <th style="width: 11%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bbbData as $index => $bbb)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $bbb->nama_bahan }}</div>
                                        @if($bbb->keterangan)
                                            <small class="text-muted">{{ $bbb->keterangan }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($bbb->coa_id)
                                            <span class="badge bg-info">
                                                {!! $bbb->coa_info !!}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                Default (1141)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($bbb->qty, 2, ',', '.') }} {{ $bbb->satuan_nama ?? $bbb->satuan }}
                                    </td>
                                    <td class="text-center">
                                        Rp {{ number_format($bbb->harga_satuan, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-bold text-primary">
                                        Rp {{ number_format($bbb->subtotal, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($bbb->created_at)->format('d/m/Y') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('master-data.biaya-bahan.edit', $produk->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete({{ $bbb->id }})" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="5" class="text-end fw-bold">TOTAL:</th>
                                <th class="text-end fw-bold text-primary h5">
                                    Rp {{ number_format($totalSubtotal, 0, ',', '.') }}
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">Belum ada data biaya bahan</h5>
                    <p class="text-muted">Produk ini belum memiliki data biaya bahan yang tersimpan.</p>
                    <a href="{{ route('master-data.biaya-bahan.create', $produk->id) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Biaya Bahan
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data biaya bahan ini?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        // Implement delete functionality
        alert('Fitur hapus akan segera tersedia');
    }
}
</script>
@endsection