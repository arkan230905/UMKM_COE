@extends('layouts.app')

@section('title', 'Riwayat Presensi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>
                    <i class="bi bi-clock-history me-2"></i>
                    Riwayat Presensi
                </h4>
                <div class="d-flex align-items-center">
                    <img src="{{ $pegawai->foto_wajah ? Storage::url($pegawai->foto_wajah) : '/images/default-avatar.png' }}" 
                         class="rounded-circle me-2" 
                         style="width: 40px; height: 40px; object-fit: cover;">
                    <div>
                        <h6 class="mb-0">{{ $pegawai->nama }}</h6>
                        <small class="text-muted">{{ $pegawai->jabatan ?? '-' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="month" class="form-label">Bulan</label>
                            <select name="month" id="month" class="form-select">
                                <option value="">Semua Bulan</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ (request('month') == $i) ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="year" class="form-label">Tahun</label>
                            <select name="year" id="year" class="form-select">
                                <option value="">Semua Tahun</option>
                                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ (request('year') == $i) ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            <a href="{{ route('pegawai.riwayat-presensi') }}" class="btn btn-secondary ms-2">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Data Presensi</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($attendances->count() > 0)
                                    @foreach($attendances as $index => $attendance)
                                    <tr>
                                        <td>{{ ($attendances->currentPage() - 1) * $attendances->perPage() + $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($attendance->tgl_presensi)->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="bi bi-clock-fill me-1"></i>
                                                {{ $attendance->jam_masuk }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->jam_keluar)
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-clock-fill me-1"></i>
                                                    {{ $attendance->jam_keluar }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-dash me-1"></i>
                                                    Belum
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $attendance->status === 'hadir' ? 'success' : 'warning' }}">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->jam_keluar)
                                                <span class="text-success">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    Presensi Lengkap
                                                </span>
                                            @else
                                                <span class="text-warning">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Belum Absen Keluar
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-calendar-x fs-1"></i>
                                            <p class="mt-2">Belum ada data presensi</p>
                                            <a href="{{ route('pegawai.presensi.absen-wajah') }}" class="btn btn-primary">
                                                <i class="bi bi-camera-video me-1"></i> Absen Sekarang
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($attendances->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $attendances->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
