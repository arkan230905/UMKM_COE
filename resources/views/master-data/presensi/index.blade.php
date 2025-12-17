@extends('layouts.app')

@section('content')
<!-- CACHE BUSTER: {{ uniqid() }} - {{ now()->timestamp }} -->
<div class="container-fluid py-4" style="background-color: #1b1b28; min-height: 100vh;">
    <script>
        // Force reload jika ada cache
        if (performance.navigation.type === 2) {
            location.reload(true);
        }
    </script>
    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
        <h2 class="text-white fw-bold mb-0">
            <i class="bi bi-calendar-check me-2"></i> Data Presensi
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('master-data.presensi.create') }}" class="btn btn-primary fw-semibold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah Presensi
            </a>
        </div>
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
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-list-ul me-2"></i>Daftar Presensi
                    </h5>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('master-data.presensi.index') }}" method="GET" class="d-flex w-100">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control bg-white text-dark border-2 border-primary" 
                                   placeholder="Cari nama pegawai atau NIP..." value="{{ request('search') }}"
                                   style="border-top-left-radius: 0.375rem; border-bottom-left-radius: 0.375rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            @if(request('search') || request('bulan'))
                                <a href="{{ route('master-data.presensi.index') }}" class="btn btn-outline-light border-start-0 border-end-0">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="col-md-3">
                    <form action="{{ route('master-data.presensi.index') }}" method="GET" class="d-flex w-100">
                        <div class="input-group">
                            <input type="month" name="bulan" class="form-control bg-white text-dark border-2 border-primary" 
                                   value="{{ request('bulan') }}" style="border-top-left-radius: 0.375rem; border-bottom-left-radius: 0.375rem;">
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-calendar-month"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        <div class="card-body px-4 py-4">
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0 custom-table">
                    <thead>
                        @if($viewMode === 'ringkasan')
                        <tr>
                            <th class="ps-3 py-3">#</th>
                            <th>NAMA PEGAWAI</th>
                            <th class="text-center">TOTAL HADIR</th>
                            <th class="text-center">TOTAL JAM KERJA</th>
                            <th class="text-center">BULAN</th>
                        </tr>
                        @else
                        <tr>
                            <th class="ps-3 py-3">#</th>
                            <th>NAMA PEGAWAI</th>
                            <th>TANGGAL</th>
                            <th>JAM MASUK</th>
                            <th>JAM KELUAR</th>
                            <th>STATUS</th>
                            <th>JUMLAH JAM</th>
                            <th>KETERANGAN</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                        @endif
                    </thead>
                    <tbody>
                        @if($viewMode === 'ringkasan')
                            @forelse($presensis as $index => $presensi)
                            <tr class="data-row">
                                <td class="ps-3 fw-bold text-light">{{ $index + 1 }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="bi bi-person-circle" style="font-size: 24px; color: #6c63ff;"></i>
                                        <div>
                                            @if($presensi->pegawai)
                                                <div class="fw-semibold text-secondary" style="font-size: 15px;">
                                                    {{ $presensi->pegawai->nama_display ?? $presensi->pegawai->nama ?? $presensi->pegawai->kode_pegawai }}
                                                </div>
                                                <div style="color: #999; font-size: 11px; margin-top: 2px;">NIP: {{ $presensi->pegawai->kode_pegawai }}</div>
                                            @else
                                                <div class="fw-semibold text-danger" style="font-size: 15px;">
                                                    Pegawai tidak ditemukan (ID: {{ $presensi->pegawai_id }})
                                                </div>
                                                <div style="color: #999; font-size: 11px; margin-top: 2px;">Data pegawai hilang</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $presensi->total_hadir }}</span>
                                </td>
                                <td class="text-center fw-bold text-primary">
                                    {{ $presensi->total_jam_formatted }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ \Carbon\Carbon::parse($presensi->bulan . '-01')->isoFormat('MMMM YYYY') }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <h5 class="mb-0">Tidak ada data presensi untuk bulan ini</h5>
                                    <p class="mb-0">Pilih bulan lain atau tambahkan data presensi baru</p>
                                </td>
                            </tr>
                            @endforelse
                        @else
                            @forelse($presensis as $presensi)
                            <tr class="data-row">
                                <td class="ps-3 fw-bold text-light">{{ ($presensis->currentPage() - 1) * $presensis->perPage() + $loop->iteration }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="bi bi-person-circle" style="font-size: 24px; color: #6c63ff;"></i>
                                        <div>
                                            @if($presensi->pegawai)
                                                <div class="fw-semibold text-secondary" style="font-size: 15px;">
                                                    {{ $presensi->pegawai->nama_display ?? $presensi->pegawai->nama ?? $presensi->pegawai->kode_pegawai }}
                                                </div>
                                                <div style="color: #999; font-size: 11px; margin-top: 2px;">NIP: {{ $presensi->pegawai->kode_pegawai }}</div>
                                            @else
                                                <div class="fw-semibold text-danger" style="font-size: 15px;">
                                                    Pegawai tidak ditemukan (ID: {{ $presensi->pegawai_id }})
                                                </div>
                                                <div style="color: #999; font-size: 11px; margin-top: 2px;">Data pegawai hilang</div>
                                            @endif
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
                                        @php
                                            $jam = $presensi->jumlah_jam;
                                            // Format tanpa trailing zero
                                            if ($jam == floor($jam)) {
                                                echo number_format($jam, 0) . ' jam';
                                            } else {
                                                echo number_format($jam, 1) . ' jam';
                                            }
                                        @endphp
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-truncate" style="max-width: 200px;" title="{{ $presensi->keterangan ?? '-' }}">
                                    {{ $presensi->keterangan ?? '-' }}
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
                                            <button type="submit" class="btn btn-sm btn-danger text-white shadow-sm fw-semibold delete-btn">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <h5 class="mb-0">Belum ada data presensi</h5>
                                    <p class="mb-0">Klik tombol "Tambah Presensi" untuk menambahkan data baru</p>
                                </td>
                            </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
            
            @if($viewMode === 'detail' && $presensis->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 px-2 flex-wrap gap-3">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Menampilkan <strong>{{ $presensis->firstItem() }}</strong> sampai <strong>{{ $presensis->lastItem() }}</strong> dari <strong>{{ $presensis->total() }}</strong> data
                    </div>
                    <nav aria-label="Pagination">
                        {{ $presensis->withQueryString()->links('pagination::bootstrap-5') }}
                    </nav>
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
    
    /* Pagination Styling */
    .pagination {
        margin-bottom: 0;
        gap: 0.25rem;
    }
    
    .pagination .page-link {
        background-color: #2d2d3a;
        border-color: #3a3a4a;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #6c63ff;
        border-color: #6c63ff;
        color: #ffffff;
        font-weight: 600;
    }
    
    .pagination .page-link:hover {
        background-color: #5a52d3;
        border-color: #5a52d3;
        color: #ffffff;
        transform: translateY(-2px);
    }
    
    .pagination .page-item.disabled .page-link {
        background-color: #1e1e2f;
        border-color: #2a2a3a;
        color: #6c757d;
    }
    
    /* Ukuran panah pagination lebih kecil */
    .pagination .page-link svg {
        width: 0.875rem;
        height: 0.875rem;
    }
    
    /* Responsive pagination */
    @media (max-width: 576px) {
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
    
    .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Modal Ringkasan Styling */
    .modal-ringkasan .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-bottom: 2px solid #667eea;
    }

    .modal-ringkasan .modal-title {
        font-weight: 700;
        color: white;
    }

    .modal-ringkasan .form-select {
        border-color: #667eea;
    }

    .modal-ringkasan .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .modal-ringkasan .btn-filter {
        background-color: #667eea;
        border-color: #667eea;
    }

    .modal-ringkasan .btn-filter:hover {
        background-color: #5568d3;
        border-color: #5568d3;
    }

    .ringkasan-table {
        --bs-table-bg: transparent;
        --bs-table-striped-bg: rgba(102, 126, 234, 0.05);
        --bs-table-hover-bg: rgba(102, 126, 234, 0.1);
    }

    .ringkasan-table thead {
        background-color: #667eea;
        color: white;
    }

    .ringkasan-table th {
        border-color: #667eea;
        font-weight: 600;
        font-size: 13px;
    }

    .ringkasan-table td {
        border-color: #e0e0e0;
        font-size: 13px;
    }

    .ringkasan-table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.1);
    }

    .badge-hadir {
        background-color: #d4edda;
        color: #155724;
    }

    .no-data-ringkasan {
        text-align: center;
        padding: 40px 20px;
        color: #999;
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