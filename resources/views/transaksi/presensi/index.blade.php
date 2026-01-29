@extends('layouts.app')

@section('content')
<style>
/* CSS Reset untuk menghilangkan underline - HIGHEST PRIORITY */
* {
    text-decoration: none !important;
}

a {
    text-decoration: none !important;
    color: inherit !important;
}

a:hover {
    text-decoration: none !important;
    color: inherit !important;
}

a:focus {
    text-decoration: none !important;
    outline: none !important;
}

a:visited {
    text-decoration: none !important;
    color: inherit !important;
}

a:active {
    text-decoration: none !important;
    color: inherit !important;
}

/* Bootstrap override */
.btn {
    text-decoration: none !important;
}

.btn:hover {
    text-decoration: none !important;
}

.btn:focus {
    text-decoration: none !important;
    outline: none !important;
}

.btn:visited {
    text-decoration: none !important;
}

.btn:active {
    text-decoration: none !important;
}

/* FontAwesome override */
.fa, .fas, .far, .fab {
    text-decoration: none !important;
}

/* Custom Styles untuk Presensi Page */
.presensi-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.presensi-header h1 {
    color: white !important;
    font-weight: 700;
    margin: 0;
    font-size: 2rem;
}

.presensi-header .btn-group {
    gap: 0.5rem;
}

.presensi-header .btn {
    border-radius: 25px;
    padding: 0.6rem 1.5rem;
    font-weight: 600;
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(00,0,0,0.1);
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    outline: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.presensi-header .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.presensi-header .btn:focus {
    outline: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.presensi-header .btn:active {
    outline: none;
    transform: translateY(0);
}

.presensi-header .btn:visited {
    text-decoration: none !important;
    color: inherit !important;
}

.presensi-header .btn i {
    font-size: 0.9rem;
    margin: 0;
    text-decoration: none !important;
}

.presensi-header .btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white !important;
}

.presensi-header .btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    color: #212529 !important;
}

.presensi-header .btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white !important;
}

.alert-custom {
    border-radius: 10px;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    border-left: 4px solid #ffc107;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-left: 4px solid #17a2b8;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border-left: 4px solid #28a745;
}

.card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px 15px 0 0 !important;
    border-bottom: 2px solid #dee2e6;
}

.card-header h6 {
    font-weight: 700;
    color: #495057;
}

.table {
    border-radius: 10px;
    overflow: hidden;
}

.table thead th {
    background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    color: white;
    font-weight: 600;
    border: none;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
}

.table tbody td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
}

.employee-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e9ecef;
}

.employee-avatar-placeholder {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #e9ecef;
}

.badge {
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
}

.badge-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.badge-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    color: #212529;
}

.badge-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.badge-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

.btn-group .btn {
    border-radius: 8px;
    padding: 0.5rem 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    color: #212529;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.empty-state {
    padding: 3rem;
    text-align: center;
}

.empty-state i {
    font-size: 4rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.empty-state h5 {
    color: #495057;
    font-weight: 600;
}

.empty-state p {
    color: #6c757d;
}

.pagination .page-link {
    border-radius: 8px;
    border: none;
    margin: 0 2px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.jam-masuk {
    color: #28a745;
    font-weight: 700;
}

.jam-keluar {
    color: #dc3545;
    font-weight: 700;
}

.jumlah-jam {
    color: #007bff;
    font-weight: 700;
}
</style>

<div class="container-fluid">
    <!-- Header dengan gradient -->
    <div class="presensi-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">
                    <i class="fas fa-calendar-check me-3"></i>Data Presensi
                </h1>
                <p class="text-white mb-0 mt-2 opacity-75">Kelola data kehadiran pegawai</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('transaksi.presensi.face-attendance') }}" class="btn btn-success">
                    <i class="fas fa-camera"></i> Absen Wajah
                </a>
                <a href="{{ route('transaksi.presensi.verifikasi-wajah.index') }}" class="btn btn-warning">
                    <i class="fas fa-user-check"></i> Verifikasi Wajah
                </a>
                <a href="{{ route('transaksi.presensi.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Presensi
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show alert-custom" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Alert untuk pegawai yang belum verifikasi wajah -->
    @if(isset($pegawaiTanpaVerifikasi) && count($pegawaiTanpaVerifikasi) > 0)
        <div class="alert alert-warning alert-dismissible fade show alert-custom" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Peringatan:</strong> {{ count($pegawaiTanpaVerifikasi) }} pegawai belum melakukan verifikasi wajah.
            <div class="mt-2">
                <small class="text-muted">Pegawai yang belum verifikasi:</small>
                <ul class="mb-0 mt-1">
                    @foreach($pegawaiTanpaVerifikasi as $pegawai)
                        <li>{{ $pegawai->nama_display ?? $pegawai->nama }} (NIP: {{ $pegawai->nomor_induk_pegawai }})</li>
                    @endforeach
                </ul>
                <div class="mt-2">
                    <a href="{{ route('transaksi.presensi.verifikasi-wajah.index') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-camera"></i> Kelola Verifikasi Wajah
                    </a>
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Alert untuk presensi hari ini yang belum verifikasi -->
    @if(isset($presensiTanpaVerifikasi) && count($presensiTanpaVerifikasi) > 0)
        <div class="alert alert-info alert-dismissible fade show alert-custom" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Informasi:</strong> {{ count($presensiTanpaVerifikasi) }} presensi hari ini belum diverifikasi wajah.
            <div class="mt-2">
                <small class="text-muted">Presensi yang belum verifikasi:</small>
                <ul class="mb-0 mt-1">
                    @foreach($presensiTanpaVerifikasi as $presensi)
                        <li>{{ optional($presensi->pegawai)->nama_display ?? optional($presensi->pegawai)->nama }} - {{ $presensi->tgl_presensi->format('d/m/Y') }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Card dengan desain modern -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="m-0">
                <i class="fas fa-list me-2"></i>Daftar Presensi
            </h6>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('transaksi.presensi.index') }}">
                        <div class="input-group">
                            <input type="date" name="tanggal" class="form-control" 
                                   value="{{ request('tanggal') ?? now()->format('Y-m-d') }}">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-8 text-end">
                    <span class="text-muted">
                        <i class="fas fa-calendar"></i> 
                        Menampilkan: <strong>{{ request('tanggal') ?? now()->format('d F Y') }}</strong>
                    </span>
                </div>
            </div>

            <!-- Table dengan desain menarik -->
            <div class="table-responsive">
                <table class="table table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">#</th>
                            <th>Pegawai</th>
                            <th class="text-center" width="10%">Tanggal</th>
                            <th class="text-center" width="8%">Jam Masuk</th>
                            <th class="text-center" width="8%">Jam Keluar</th>
                            <th class="text-center" width="8%">Jumlah Jam</th>
                            <th class="text-center" width="10%">Status</th>
                            <th class="text-center" width="12%">Verifikasi</th>
                            <th class="text-center" width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presensis as $presensi)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($presensi->pegawai && $presensi->pegawai->foto)
                                        <img src="{{ asset('storage/' . $presensi->pegawai->foto) }}" 
                                             alt="Foto" class="employee-avatar me-3">
                                    @else
                                        <div class="employee-avatar-placeholder me-3">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold">
                                            {{ optional($presensi->pegawai)->nama_display ?? optional($presensi->pegawai)->nama ?? 'Pegawai Tidak Ditemukan' }}
                                        </div>
                                        <small class="text-muted">NIP: {{ optional($presensi->pegawai)->nomor_induk_pegawai ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ $presensi->tgl_presensi ? $presensi->tgl_presensi->format('d/m/Y') : '-' }}</td>
                            <td class="text-center">
                                <span class="jam-masuk">{{ $presensi->jam_masuk ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="jam-keluar">{{ $presensi->jam_keluar ?? '-' }}</span>
                            </td>
                            <td class="text-center fw-bold">
                                @if($presensi->status === 'Hadir')
                                    <span class="jumlah-jam">{{ number_format($presensi->jumlah_jam, 1) }} jam</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @switch($presensi->status)
                                    @case('Hadir')
                                        <span class="badge badge-success">Hadir</span>
                                        @break
                                    @case('Terlambat')
                                        <span class="badge badge-warning">Terlambat</span>
                                        @break
                                    @case('Izin')
                                        <span class="badge badge-warning">Izin</span>
                                        @break
                                    @case('Sakit')
                                        <span class="badge badge-warning">Sakit</span>
                                        @break
                                    @case('Alpha')
                                        <span class="badge badge-danger">Alpha</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $presensi->status }}</span>
                                @endswitch
                            </td>
                            <td class="text-center">
                                @if($presensi->verifikasi_wajah)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Terverifikasi
                                    </span>
                                @else
                                    <span class="badge badge-danger" title="Pegawai harus verifikasi wajah dulu">
                                        <i class="fas fa-times-circle"></i> Belum Verifikasi
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('transaksi.presensi.edit', $presensi->id) }}" 
                                       class="btn btn-warning" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('transaksi.presensi.destroy', $presensi->id) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger delete-btn" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="empty-state">
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
                    <div class="text-muted">
                        Menampilkan <strong>{{ $presensis->firstItem() }}</strong> sampai <strong>{{ $presensis->lastItem() }}</strong> dari <strong>{{ $presensis->total() }}</strong> data
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
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Delete confirmation
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection
