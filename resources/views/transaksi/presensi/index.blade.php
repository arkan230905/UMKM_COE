@extends('layouts.app')

@section('title', 'Data Presensi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calendar-check me-2"></i>Data Presensi
        </h2>
        <a href="{{ route('transaksi.presensi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Presensi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Presensi
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('transaksi.presensi.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select">
                            <option value="">-- Semua Bulan --</option>
                            @foreach($bulanList as $key => $bulan)
                                <option value="{{ $key }}" {{ $filters['bulan'] == $key ? 'selected' : '' }}>
                                    {{ $bulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select">
                            <option value="">-- Semua Tahun --</option>
                            @foreach($tahunList as $tahun)
                                <option value="{{ $tahun }}" {{ $filters['tahun'] == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="Hadir" {{ $filters['status'] == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="Alpha" {{ $filters['status'] == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                            <option value="Masuk Saja" {{ $filters['status'] == 'Masuk Saja' ? 'selected' : '' }}>Masuk Saja</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cari Pegawai</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Cari nama pegawai..." value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.presensi.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                            @if(auth()->user()->role === 'owner' || auth()->user()->role === 'admin')
                            <a href="{{ route('transaksi.presensi.cetak', ['bulan' => $filters['bulan'] ?? '', 'tahun' => $filters['tahun'] ?? '', 'search' => $search ?? '']) }}"
                               target="_blank" class="btn btn-success ms-auto">
                                <i class="fas fa-print me-2"></i>Cetak Laporan
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Presensi
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">No</th>
                            <th>Pegawai</th>
                            <th class="text-center" style="width: 100px">Tanggal</th>
                            <th class="text-center" style="width: 80px">Jam Masuk</th>
                            <th class="text-center" style="width: 80px">Jam Keluar</th>
                            <th class="text-center" style="width: 80px">Jumlah Jam</th>
                            <th class="text-center" style="width: 100px">Status</th>
                            <th class="text-center" style="width: 100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presensis as $presensi)
                        <tr>
                            <td class="text-center">{{ ($presensis->currentPage()-1)*$presensis->perPage() + $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($presensi->pegawai && $presensi->pegawai->foto)
                                        <img src="{{ Storage::url($presensi->pegawai->foto) }}"
                                             alt="Foto" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e9ecef;">
                                    @else
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #6c757d; display: flex; align-items: center; justify-content: center; border: 2px solid #e9ecef; flex-shrink: 0;">
                                            <i class="fas fa-user text-white" style="font-size: 0.9rem;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $presensi->pegawai->nama ?? 'Pegawai Tidak Ditemukan' }}</div>
                                        <small class="text-muted">{{ $presensi->pegawai->kualifikasiRelasi->nama_kualifikasi ?? $presensi->pegawai->kualifikasi ?? $presensi->pegawai->jabatan ?? 'Tidak ada kualifikasi' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                {{ $presensi->tgl_presensi ? $presensi->tgl_presensi->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-center">
                                {{ $presensi->jam_masuk ? date('H:i', strtotime($presensi->jam_masuk)) : '-' }}
                            </td>
                            <td class="text-center">
                                {{ $presensi->jam_keluar ? date('H:i', strtotime($presensi->jam_keluar)) : '-' }}
                            </td>
                            <td class="text-center">
                                {{ $presensi->jumlah_jam !== null ? $presensi->jumlah_jam . ' jam' : '-' }}
                            </td>
                            <td class="text-center">
                                @php $statusLower = strtolower($presensi->status); @endphp
                                @if($statusLower === 'hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($statusLower === 'terlambat')
                                    <span class="badge bg-secondary text-dark">Terlambat</span>
                                @elseif($statusLower === 'izin')
                                    <span class="badge bg-info text-dark">Izin</span>
                                @elseif($statusLower === 'sakit')
                                    <span class="badge bg-warning text-dark">Sakit</span>
                                @else
                                    <span class="badge bg-danger">{{ ucfirst($presensi->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center" style="overflow: hidden; text-overflow: ellipsis;">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('transaksi.presensi.edit', $presensi->id) }}"
                                       class="btn btn-outline-primary btn-sm" style="padding: 0.25rem 0.5rem;" title="Edit">
                                        <i class="fas fa-edit fa-xs"></i>
                                    </a>
                                    <form action="{{ route('transaksi.presensi.destroy', $presensi->id) }}"
                                          method="POST" class="m-0 d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="bulan" value="{{ $filters['bulan'] ?? '' }}">
                                        <input type="hidden" name="tahun" value="{{ $filters['tahun'] ?? '' }}">
                                        <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
                                        <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" style="padding: 0.25rem 0.5rem;" title="Hapus">
                                            <i class="fas fa-trash fa-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data presensi</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($presensis->hasPages())
        <div class="card-footer">
            {{ $presensis->links('pagination::bootstrap-5') }}
        </div>
        @endif
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
