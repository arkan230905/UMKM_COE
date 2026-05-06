@extends('layouts.app')

@section('title', 'Presensi')

@section('content')
<style>
/* =====================================================
   PRESENSI PAGE - Scoped CSS (tidak merusak sidebar)
   Semua selector di-prefix dengan .presensi-page
   ===================================================== */

/* Variabel warna cokelat - hanya untuk halaman ini */
.presensi-page {
    --primary-gold: #8B5E3C;
    --secondary-gold: #A0714F;
    --light-gold: #C49A6C;
    --accent-gold: #B07D4F;
    --dark: #3E2723;
    --white: #ffffff;
    --shadow: rgba(139, 94, 60, 0.25);
}

/* Header banner cokelat */
.presensi-page .presensi-header {
    background: linear-gradient(135deg, #A0714F 0%, #8B5E3C 100%);
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(139, 94, 60, 0.25);
}

.presensi-page .presensi-header h1 {
    color: #ffffff !important;
    font-weight: 700;
    margin: 0;
    font-size: 2rem;
}

.presensi-page .presensi-header p {
    color: rgba(255,255,255,0.85);
}

/* Tombol Tambah Presensi */
.presensi-page .presensi-header .btn-tambah {
    background: linear-gradient(135deg, #C49A6C 0%, #B07D4F 100%);
    color: #3E2723 !important;
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 25px;
    padding: 0.6rem 1.5rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(139, 94, 60, 0.3);
}

.presensi-page .presensi-header .btn-tambah:hover {
    background: linear-gradient(135deg, #B07D4F 0%, #A0714F 100%);
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 94, 60, 0.4);
    text-decoration: none;
}

/* Card */
.presensi-page .card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.presensi-page .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    border-radius: 15px 15px 0 0 !important;
    border-bottom: 2px solid #dee2e6 !important;
    color: #495057 !important;
    font-weight: 600;
}

.presensi-page .card-header h6 {
    font-weight: 700;
    color: #495057 !important;
    margin: 0;
}

/* Tabel */
.presensi-page .table thead th {
    background: linear-gradient(135deg, #A0714F 0%, #8B5E3C 100%);
    color: white;
    font-weight: 600;
    border: none;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.presensi-page .table tbody tr {
    transition: background-color 0.2s ease;
}

.presensi-page .table tbody tr:hover {
    background-color: #fdf6f0;
}

.presensi-page .table tbody td {
    vertical-align: middle;
    padding: 0.85rem 0.75rem;
    color: #212529;
}

/* Avatar pegawai */
.presensi-page .employee-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
}

.presensi-page .employee-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e9ecef;
    flex-shrink: 0;
}

/* Tombol aksi di tabel */
.presensi-page .btn-edit {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    color: #212529;
    border: none;
    border-radius: 6px;
    padding: 0.4rem 0.7rem;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}

.presensi-page .btn-edit:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    color: #212529;
    text-decoration: none;
}

.presensi-page .btn-hapus {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 0.4rem 0.7rem;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}

.presensi-page .btn-hapus:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    color: #ffffff;
}

/* Tombol filter & search */
.presensi-page .btn-filter {
    background: linear-gradient(135deg, #A0714F 0%, #8B5E3C 100%);
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1.2rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.presensi-page .btn-filter:hover {
    background: linear-gradient(135deg, #8B5E3C 0%, #7a5234 100%);
    color: #ffffff;
    text-decoration: none;
}

/* Tombol cetak */
.presensi-page .btn-cetak {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1.2rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
}

.presensi-page .btn-cetak:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40,167,69,0.3);
    color: #ffffff;
    text-decoration: none;
}

/* Empty state */
.presensi-page .empty-state {
    padding: 3rem;
    text-align: center;
}

.presensi-page .empty-state i {
    font-size: 3.5rem;
    color: #adb5bd;
    display: block;
    margin-bottom: 1rem;
}

.presensi-page .empty-state h5 {
    color: #495057;
    font-weight: 600;
}

.presensi-page .empty-state p {
    color: #6c757d;
}

/* Alert sukses */
.presensi-page .alert-success {
    border-radius: 10px;
    border: none;
    border-left: 4px solid #28a745;
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    box-shadow: 0 3px 10px rgba(0,0,0,0.06);
}
</style>

<div class="container-fluid presensi-page">
    <!-- Header banner cokelat -->
    <div class="presensi-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">
                    <i class="fas fa-calendar-check me-3"></i>Data Presensi
                </h1>
                <p class="mb-0 mt-2">Kelola data kehadiran pegawai</p>
            </div>
            <div>
                <a href="{{ route('transaksi.presensi.create') }}" class="btn-tambah">
                    <i class="fas fa-plus"></i> Tambah Presensi
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Card utama -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="m-0">
                <i class="fas fa-list me-2"></i>Daftar Presensi
            </h6>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form method="GET" action="{{ route('transaksi.presensi.index') }}">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Filter Tanggal</label>
                                <input type="date" name="date_filter" class="form-control"
                                       value="{{ $dateFilter ?? '' }}">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                <button type="submit" class="btn-filter">
                                    <i class="fas fa-search me-1"></i> Filter
</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="GET" action="{{ route('transaksi.presensi.index') }}">
                        <label class="form-label small fw-semibold">Cari Pegawai</label>
                        <div class="input-group">
                            <input type="hidden" name="bulan" value="{{ $filters['bulan'] ?? '' }}">
                            <input type="hidden" name="tahun" value="{{ $filters['tahun'] ?? '' }}">
                            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Cari nama pegawai..." value="{{ $search ?? '' }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cetak Laporan (Owner/Admin Only) -->
            @if(auth()->user()->role === 'owner' || auth()->user()->role === 'admin')
            <div class="row mb-3">
                <div class="col-12 text-end">

                    <a href="{{ route('transaksi.presensi.cetak', ['date_filter' => $dateFilter ?? '', 'search' => $search ?? '']) }}"
                       target="_blank" class="btn-cetak">
<i class="fas fa-print"></i> Cetak Laporan
                    </a>
                </div>
            </div>
            @endif

            <!-- Tabel -->
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Pegawai</th>
                            <th class="text-center" width="10%">Tanggal</th>
                            <th class="text-center" width="8%">Jam Masuk</th>
                            <th class="text-center" width="8%">Jam Keluar</th>
                            <th class="text-center" width="8%">Jumlah Jam</th>
                            <th class="text-center" width="10%">Status</th>
                            <th class="text-center" width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presensis as $presensi)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($presensi->pegawai && $presensi->pegawai->foto)
                                        <img src="{{ storage_url($presensi->pegawai->foto) }}"
                                             alt="Foto" class="employee-avatar">
                                    @else
                                        <div class="employee-avatar-placeholder">
                                            <i class="fas fa-user text-white" style="font-size:0.9rem;"></i>
                                        </div>
                                    @endif
                                    <span class="fw-semibold">
                                        {{ $presensi->pegawai->nama ?? 'Pegawai Tidak Ditemukan' }} - {{ $presensi->pegawai->jabatan ?? 'Tidak ada jabatan' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center fw-semibold">
                                {{ $presensi->tgl_presensi ? $presensi->tgl_presensi->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-center fw-semibold">
                                {{ $presensi->jam_masuk ? date('H:i', strtotime($presensi->jam_masuk)) : '-' }}
                            </td>
                            <td class="text-center fw-semibold">
                                {{ $presensi->jam_keluar ? date('H:i', strtotime($presensi->jam_keluar)) : '-' }}
                            </td>
                            <td class="text-center fw-semibold">
                                {{ $presensi->jumlah_jam !== null ? $presensi->jumlah_jam . ' jam' : '-' }}
                            </td>
                            <td class="text-center">
                                <span class="badge
                                    @if($presensi->status === 'hadir') bg-success
                                    @elseif($presensi->status === 'terlambat') bg-warning text-dark
                                    @elseif($presensi->status === 'izin') bg-info text-dark
                                    @elseif($presensi->status === 'sakit') bg-secondary
                                    @else bg-danger
                                    @endif">
                                    {{ ucfirst($presensi->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('transaksi.presensi.edit', $presensi->id) }}"
                                       class="btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('transaksi.presensi.destroy', $presensi->id) }}"
                                          method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="bulan" value="{{ $filters['bulan'] ?? '' }}">
                                        <input type="hidden" name="tahun" value="{{ $filters['tahun'] ?? '' }}">
                                        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                                        <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                        <button type="submit" class="btn-hapus" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h5>Belum ada data presensi</h5>
                                <p class="mb-0">Klik tombol "Tambah Presensi" untuk menambahkan data baru</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($presensis->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Menampilkan <strong>{{ $presensis->firstItem() }}</strong>
                        sampai <strong>{{ $presensis->lastItem() }}</strong>
                        dari <strong>{{ $presensis->total() }}</strong> data
                    </div>
                    <div>
                        {{ $presensis->withQueryString()->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.delete-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Hapus data ini?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection
