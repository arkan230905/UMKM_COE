@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #1b1b28; min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
        <h2 class="text-white fw-bold mb-0">
            <i class="bi bi-calendar-check me-2"></i> Data Presensi
        </h2>
        <a href="{{ route('master-data.presensi.create') }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Presensi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-lg border-0 mx-3" style="background-color: #222232; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.5);">
        <div class="card-header bg-transparent border-0 py-3 px-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-list-ul me-2"></i>Daftar Presensi
                    </h5>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('master-data.presensi.index') }}" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control bg-dark text-white border-dark" 
                               placeholder="Cari nama pegawai atau NIP..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary ms-2">
                            <i class="bi bi-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('master-data.presensi.index') }}" class="btn btn-outline-light ms-2">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body px-4 py-4">
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0 custom-table">
                    <thead>
                        <tr>
                            <th class="ps-3 py-3">#</th>
                            <th>Pegawai</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                            <th>Jumlah Jam</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presensis as $presensi)
                        <tr class="data-row">
                            <td class="ps-3 fw-bold text-light">{{ ($presensis->currentPage() - 1) * $presensis->perPage() + $loop->iteration }}</td>
                            <td class="fw-bold text-white">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <i class="bi bi-person-circle fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $presensi->pegawai->nama }}</div>
                                        <div class="text-muted small">{{ $presensi->pegawai->nomor_induk_pegawai }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-semibold text-secondary">
                                {{ \Carbon\Carbon::parse($presensi->tgl_presensi)->isoFormat('dddd, D MMMM YYYY') }}
                            </td>
                            <td class="fw-semibold text-secondary">
                                @if($presensi->status === 'Hadir')
                                    {{ \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-secondary">
                                @if($presensi->status === 'Hadir')
                                    {{ \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($presensi->status == 'Hadir')
                                    <span class="badge bg-success">{{ $presensi->status }}</span>
                                @elseif(in_array($presensi->status, ['Izin', 'Sakit']))
                                    <span class="badge bg-warning text-dark">{{ $presensi->status }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $presensi->status }}</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-secondary">
                                @if($presensi->status === 'Hadir')
                                    {{ number_format($presensi->jumlah_jam, 1) }} jam
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('master-data.presensi.edit', $presensi->id) }}" 
                                       class="btn btn-sm btn-warning text-dark shadow-sm fw-semibold"
                                       data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('master-data.presensi.destroy', $presensi->id) }}" 
                                          method="POST" class="d-inline delete-form"
                                          data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger text-white shadow-sm fw-semibold delete-btn">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <h5 class="mb-0">Belum ada data presensi</h5>
                                <p class="mb-0">Klik tombol "Tambah Presensi" untuk menambahkan data baru</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($presensis->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 px-2">
                    <div class="text-muted">
                        Menampilkan {{ $presensis->firstItem() }} sampai {{ $presensis->lastItem() }} dari {{ $presensis->total() }} data
                    </div>
                    <div>
                        {{ $presensis->withQueryString()->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .custom-table {
        --bs-table-bg: transparent;
        --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
        --bs-table-hover-bg: rgba(108, 99, 255, 0.1);
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.5rem;
        color: var(--bs-table-color-state, var(--bs-table-color-type, var(--bs-table-color)));
    }
    
    .pagination .page-link {
        background-color: #2d2d3a;
        border-color: #3a3a4a;
        color: #ffffff;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #6c63ff;
        border-color: #6c63ff;
    }
    
    .pagination .page-link:hover {
        background-color: #5a52d3;
        border-color: #5a52d3;
        color: #ffffff;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi tooltip
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Konfirmasi hapus data
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        const deleteBtn = form.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6c63ff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });
    
    // Tambahkan animasi pada baris tabel
    const dataRows = document.querySelectorAll('.data-row');
    dataRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        row.style.transition = `opacity 0.3s ease-out ${index * 0.05}s, transform 0.3s ease-out ${index * 0.05}s`;
        
        // Trigger reflow
        void row.offsetWidth;
        
        // Tambahkan kelas untuk animasi
        row.style.opacity = '1';
        row.style.transform = 'translateY(0)';
    });
});
</script>
@endpush