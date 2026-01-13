@extends('layouts.app')

@push('styles')
<style>
/* BTKL page - White text for code badges */
.badge.bg-secondary {
    color: white !important;
    background-color: #6c757d !important;
}

/* Ensure all table text is black except badges */
.table tbody td {
    color: black !important;
}

/* Keep badge text white */
.table tbody td .badge {
    color: white !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark"><i class="bi bi-user-clock me-2"></i>Daftar Proses Produksi (BTKL)</h2>
        <a href="{{ route('master-data.btkl.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Tambah Proses
        </a>
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

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover custom-table" style="color: black !important;">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-3 py-3" width="10%">Kode</th>
                            <th width="20%">Nama BTKL</th>
                            <th width="15%">Tarif BTKL</th>
                            <th width="10%">Satuan</th>
                            <th width="15%">Kapasitas/Jam</th>
                            <th width="20%">Deskripsi</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($btkls as $btkl)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ $btkl->kode_proses }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-gear-fill me-2 text-primary"></i>
                                    <div>
                                        <div class="fw-bold">{{ $btkl->jabatan->nama ?? '-' }}</div>
                                        <small class="text-muted">{{ $btkl->jabatan->kategori ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-success">{{ $btkl->tarif_per_jam_formatted }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $btkl->satuan }}</span>
                            </td>
                            <td>
                                <span class="fw-bold">{{ number_format($btkl->kapasitas_per_jam) }} pcs</span>
                            </td>
                            <td>
                                <small>{{ $btkl->deskripsi_proses ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('master-data.btkl.edit', $btkl->id) }}" 
                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('master-data.btkl.destroy', $btkl->id) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus proses {{ $btkl->jabatan->nama ?? '' }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    <p>Belum ada data proses produksi</p>
                                    <a href="{{ route('master-data.btkl.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus"></i> Tambah Proses Pertama
                                    </a>
                                </div>
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