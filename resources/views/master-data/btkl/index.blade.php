@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-user-clock me-2"></i>Daftar Proses Produksi (BTKL)
        </h2>
        <a href="{{ route('master-data.btkl.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Tambah Proses
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-wide">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 8%">Kode</th>
                            <th style="width: 15%">Nama Proses</th>
                            <th style="width: 15%">Jabatan BTKL</th>
                            <th style="width: 10%">Jumlah Pegawai</th>
                            <th style="width: 12%">Tarif BTKL</th>
                            <th style="width: 8%">Satuan</th>
                            <th style="width: 12%">Kapasitas/Jam</th>
                            <th style="width: 12%">Biaya Per Produk</th>
                            <th style="width: 15%">Deskripsi</th>
                            <th style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($btkls as $btkl)
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $btkl->kode_proses }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-gear-fill me-2 text-primary"></i>
                                    <div>
                                        <div class="fw-bold">{{ $btkl->nama_btkl ?? '-' }}</div>
                                        <small class="text-muted">Nama proses produksi</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-workspace me-2 text-info"></i>
                                    <div>
                                        <div class="fw-bold">{{ $btkl->jabatan->nama ?? '-' }}</div>
                                        <small class="text-muted">{{ $btkl->jabatan->kategori ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-people-fill me-2 text-primary"></i>
                                    <div>
                                        <div class="fw-bold text-primary">{{ $btkl->jabatan->pegawais->count() ?? 0 }} orang</div>
                                        <small class="text-muted">Jabatan: {{ $btkl->jabatan->nama ?? '-' }}</small>
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
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cash-stack me-2 text-warning"></i>
                                    <div>
                                        <div class="fw-bold text-warning">{{ $btkl->biaya_per_produk_formatted }}</div>
                                        <small class="text-muted">Rp {{ number_format($btkl->tarif_per_jam / $btkl->kapasitas_per_jam, 2, ",", ".") }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>{{ $btkl->deskripsi_proses ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('master-data.btkl.edit', $btkl->id) }}" 
                                       class="btn btn-sm btn-warning text-white rounded-pill px-3"
                                       data-bs-toggle="tooltip" 
                                       title="Edit BTKL">
                                        <i class="bi bi-pencil-square me-1"></i>
                                        <span class="d-none d-md-inline">Edit</span>
                                    </a>
                                    <button type="button" 
                                           class="btn btn-sm btn-danger text-white rounded-pill px-3"
                                           data-bs-toggle="modal" 
                                           data-bs-target="#deleteModal{{ $btkl->id }}"
                                           data-bs-toggle="tooltip" 
                                           title="Hapus BTKL">
                                        <i class="bi bi-trash3 me-1"></i>
                                        <span class="d-none d-md-inline">Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
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

<!-- Delete Modals -->
@forelse($btkls as $btkl)
<div class="modal fade" id="deleteModal{{ $btkl->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $btkl->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel{{ $btkl->id }}">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-trash3 text-danger" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-danger fw-bold">Apakah Anda yakin?</h6>
                    <p class="text-muted mb-0">Data BTKL untuk proses <strong>"{{ $btkl->jabatan->nama ?? 'Tidak Diketahui' }}"</strong> akan dihapus secara permanen.</p>
                </div>
                
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-warning me-2"></i>
                        <div>
                            <strong>Informasi:</strong>
                            <ul class="mb-0 mt-1 small">
                                <li>Kode Proses: <code>{{ $btkl->kode_proses }}</code></li>
                                <li>Tarif BTKL: {{ $btkl->tarif_per_jam_formatted }}</li>
                                <li>Kapasitas: {{ number_format($btkl->kapasitas_per_jam) }} pcs/jam</li>
                                <li>Biaya/Produk: {{ $btkl->biaya_per_produk_formatted }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Batal
                </button>
                <form action="{{ route('master-data.btkl.destroy', $btkl->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-trash3 me-1"></i>Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@empty
@endforelse

@push('scripts')
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
@endpush
@endsection

