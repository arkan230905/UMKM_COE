@extends('layouts.app')

@section('title', 'Rekap Harian Presensi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check me-2"></i>
                        Rekap Harian Presensi
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filter Tanggal -->
                    <form method="GET" action="{{ route('pegawai.rekap-harian') }}" class="mb-3">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" 
                                       value="{{ $tanggal }}" max="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i> Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Info Tanggal -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Menampilkan data presensi tanggal: <strong>{{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</strong>
                        <span class="ms-3">Total Hadir: <strong>{{ $attendances->count() }}</strong> pegawai</span>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-people-fill me-2"></i>
                        Daftar Kehadiran
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th class="text-center" style="width: 50px">No</th>
                                    <th>Nama Pegawai</th>
                                    <th>Kode Pegawai</th>
                                    <th class="text-center">Jam Masuk</th>
                                    <th class="text-center">Jam Keluar</th>
                                    <th class="text-center">Jumlah Jam</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $index => $attendance)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $attendance->pegawai->nama ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $attendance->pegawai->kode_pegawai ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            <i class="bi bi-clock-fill me-1"></i>
                                            {{ date('H.i', strtotime($attendance->jam_masuk)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($attendance->jam_keluar)
                                            <span class="badge bg-danger">
                                                <i class="bi bi-clock-fill me-1"></i>
                                                {{ date('H.i', strtotime($attendance->jam_keluar)) }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-dash me-1"></i>
                                                Belum
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($attendance->jumlah_jam !== null)
                                            <span class="badge bg-info text-white">
                                                {{ $attendance->jumlah_jam }} jam
                                            </span>
                                        @else
                                            <span class="text-primary">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $attendance->status === 'hadir' ? 'success' : 'warning' }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-calendar-x fs-1 text-info"></i>
                                        <p class="mt-2 text-primary">Tidak ada data presensi pada tanggal ini</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
