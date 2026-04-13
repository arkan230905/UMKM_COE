@extends('layouts.app')

@section('title', 'Dashboard Pegawai')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard Pegawai
                </h4>
                <div class="d-flex align-items-center">
                    <img src="{{ $pegawai->foto_wajah ? Storage::url($pegawai->foto_wajah) : '/images/default-avatar.png' }}" 
                         class="rounded-circle me-2" 
                         style="width: 40px; height: 40px; object-fit: cover;">
                    <div>
                        <h6 class="mb-0">{{ $pegawai->nama }}</h6>
                        <small class="text-primary">{{ $pegawai->jabatan ?? '-' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['total_hadir'] }}</h4>
                            <p class="mb-0">Hadir Bulan Ini</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-check fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['persentasi_kehadiran'] }}%</h4>
                            <p class="mb-0">Persentasi Kehadiran</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-graph-up fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['total_hari_kerja'] }}</h4>
                            <p class="mb-0">Total Hari Kerja</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar3 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            @if($stats['today_status'])
                                <h4 class="mb-0">
                                    {{ $stats['today_status']['sudah_lengkap'] ? 'Lengkap' : 'Masuk' }}
                                </h4>
                                <p class="mb-0">Status Hari Ini</p>
                            @else
                                <h4 class="mb-0">Belum</h4>
                                <p class="mb-0">Presensi Hari Ini</p>
                            @endif
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock-history fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Status & Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Status Presensi Hari Ini</h6>
                </div>
                <div class="card-body">
                    @if($stats['today_status'])
                        <div class="row">
                            <div class="col-6">
                                <small class="text-primary">Jam Masuk</small>
                                <div class="fw-bold text-success">
                                    <i class="bi bi-clock-fill me-1"></i> 
                                    {{ $stats['today_status']['jam_masuk'] }}
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-primary">Jam Keluar</small>
                                <div class="fw-bold text-{{ $stats['today_status']['jam_keluar'] ? 'danger' : 'primary' }}">
                                    <i class="bi bi-clock-fill me-1"></i> 
                                    {{ $stats['today_status']['jam_keluar'] ?: 'Belum' }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-{{ $stats['today_status']['sudah_lengkap'] ? 'success' : 'warning' }}">
                                {{ $stats['today_status']['sudah_lengkap'] ? '✅ Presensi Lengkap' : '⏰ Menunggu Absen Keluar' }}
                            </span>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-calendar-x fs-1 text-info"></i>
                            <p class="text-primary mt-2">Belum ada presensi hari ini</p>
                            <a href="{{ route('pegawai.presensi.absen-wajah') }}" class="btn btn-primary">
                                <i class="bi bi-camera-video me-1"></i> Absen Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('pegawai.presensi.absen-wajah') }}" class="btn btn-success">
                            <i class="bi bi-camera-video me-1"></i> Absen Wajah
                        </a>
                        <a href="{{ route('pegawai.riwayat-presensi') }}" class="btn btn-info">
                            <i class="bi bi-clock-history me-1"></i> Riwayat Presensi
                        </a>
                        <a href="{{ route('profile') }}" class="btn btn-secondary">
                            <i class="bi bi-person me-1"></i> Profil Saya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Presensi 7 Hari Terakhir</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($recentAttendance->count() > 0)
                                    @foreach($recentAttendance as $attendance)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($attendance->tgl_presensi)->format('d M Y') }}</td>
                                        <td class="text-success">{{ $attendance->jam_masuk }}</td>
                                        <td class="text-danger">{{ $attendance->jam_keluar ?: '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $attendance->jam_keluar ? 'success' : 'warning' }}">
                                                {{ $attendance->jam_keluar ? 'Lengkap' : 'Masuk Saja' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->jam_keluar)
                                                <span class="text-success">✅ Presensi Lengkap</span>
                                            @else
                                                <span class="text-warning">⏰ Belum Absen Keluar</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center text-primary">
                                            <i class="bi bi-calendar-x me-1"></i>
                                            Belum ada presensi dalam 7 hari terakhir
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
