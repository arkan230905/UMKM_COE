@extends('layouts.app')

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
                        <label class="form-label">Nama Pegawai</label>
                        <input type="text" name="nama_pegawai" class="form-control" 
                               value="{{ request('nama_pegawai') }}" placeholder="Cari nama pegawai...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" 
                               value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" 
                               value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="Sakit" {{ request('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="Izin" {{ request('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                            <option value="Cuti" {{ request('status') == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.presensi.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Riwayat Presensi
                @if(request()->hasAny(['nama_pegawai', 'tanggal_mulai', 'tanggal_selesai', 'status']))
                    <small class="text-muted">(Filter Aktif)</small>
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Pegawai</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                            <th class="text-center">Total Jam</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presensi as $key => $presensi)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $presensi->pegawai->nama_display ?? $presensi->pegawai->nama ?? 'Tidak Diketahui' }}</div>
                                            <small class="text-muted">NIP: {{ $presensi->pegawai->kode_pegawai ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($presensi->tgl_presensi)->isoFormat('dddd, D MMMM YYYY') }}</td>
                                <td>
                                    @if($presensi->status === 'Hadir')
                                        {{ \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($presensi->jam_keluar)
                                        {{ \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @switch($presensi->status)
                                        @case('Hadir')
                                            <span class="badge bg-success">Hadir</span>
                                            @break
                                        @case('Sakit')
                                            <span class="badge bg-warning">Sakit</span>
                                            @break
                                        @case('Izin')
                                            <span class="badge bg-info">Izin</span>
                                            @break
                                        @case('Cuti')
                                            <span class="badge bg-primary">Cuti</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $presensi->status }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center fw-semibold">{{ $presensi->jumlah_jam ?? 0 }} jam</td>
                                <td>{{ $presensi->keterangan ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('transaksi.presensi.edit', $presensi->id) }}" class="btn btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('transaksi.presensi.destroy', $presensi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data presensi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data presensi</p>
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
