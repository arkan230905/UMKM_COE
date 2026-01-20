@extends('layouts.app')

@push('styles')
<style>
/* Bahan Pendukung page specific - BLACK text for kode and nama bahan */
.table tbody td:nth-child(1) {
    color: #333 !important; /* Kolom Kode */
    text-align: center !important;
    padding: 8px !important;
}
.table tbody td:nth-child(2) {
    color: #333 !important; /* Kolom Nama Bahan */
}
.table tbody td:nth-child(2) strong {
    color: #333 !important; /* Nama bahan yang bold */
}

/* Special styling for code column (1st column) - Purple rounded pill like bahan baku */
.table tbody td:nth-child(1) code {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    font-weight: bold !important;
    padding: 8px 20px !important;
    border-radius: 25px !important;
    display: inline-block !important;
    min-width: 120px !important;
    text-align: center !important;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
    font-size: 14px !important;
    letter-spacing: 0.5px !important;
    border: none !important;
}

.table tbody tr:hover td:nth-child(1) code {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
    transform: scale(1.05) !important;
    transition: all 0.3s ease !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-flask me-2"></i>Bahan Pendukung
        </h2>
        <a href="{{ route('master-data.bahan-pendukung.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Bahan Pendukung
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Bahan Pendukung
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Nama Bahan</th>
                            <th>Satuan</th>
                            <th>Harga Satuan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahanPendukungs as $key => $bahan)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-flask text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $bahan->nama_bahan }}</div>
                                            <small class="text-muted">ID: {{ $bahan->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ is_string($bahan->satuan) ? $bahan->satuan : (optional($bahan->satuan)->nama ?? '-') }}</td>
                                <td class="fw-semibold">Rp {{ number_format($bahan->harga_satuan, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-data.bahan-pendukung.edit', $bahan->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.bahan-pendukung.destroy', $bahan->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger" onclick="return confirm('Hapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data bahan pendukung</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
