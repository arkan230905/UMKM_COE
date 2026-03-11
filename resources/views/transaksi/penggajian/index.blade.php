@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-money-check-alt me-2"></i>Data Penggajian
        </h2>
        <a href="{{ route('transaksi.penggajian.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Penggajian
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Transaksi
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('transaksi.penggajian.index') }}">
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
                        <label class="form-label">Jenis Pegawai</label>
                        <select name="jenis_pegawai" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="btkl" {{ request('jenis_pegawai') == 'btkl' ? 'selected' : '' }}>BTKL</option>
                            <option value="btktl" {{ request('jenis_pegawai') == 'btktl' ? 'selected' : '' }}>BTKTL</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-outline-secondary">
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
                <i class="fas fa-list me-2"></i>Riwayat Penggajian
                @if(request()->hasAny(['nama_pegawai', 'tanggal_mulai', 'tanggal_selesai', 'jenis_pegawai', 'status_pembayaran']))
                    <small class="text-muted">(Filter Aktif)</small>
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 120px">Nomor Penggajian</th>
                            <th>Tanggal Penggajian</th>
                            <th>Bulan Penggajian</th>
                            <th>Karyawan</th>
                            <th>Metode Pembayaran</th>
                            <th class="text-end">Gaji Pokok</th>
                            <th class="text-end">Insentif</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penggajians as $index => $gaji)
                            @php
                                $jenis = strtoupper($gaji->pegawai->jenis_pegawai ?? 'BTKTL');
                                $tanggal = \Carbon\Carbon::parse($gaji->tanggal_penggajian);
                                $bulanPenggajian = $tanggal->locale('id')->translatedFormat('F Y');
                                $coa = \App\Models\Coa::where('kode_akun', $gaji->coa_kasbank)->first();
                                
                                // Hitung gaji pokok berdasarkan jenis
                                if($jenis === 'BTKL') {
                                    $gajiPokok = ($gaji->tarif_per_jam ?? 0) * ($gaji->total_jam_kerja ?? 0);
                                } else {
                                    $gajiPokok = $gaji->gaji_pokok ?? 0;
                                }
                                
                                // Insentif = Bonus
                                $insentif = $gaji->bonus ?? 0;
                            @endphp
                            <tr>
                                <td class="text-center">PGJ{{ str_pad($gaji->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $tanggal->format('d M Y') }}</td>
                                <td>{{ $bulanPenggajian }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $gaji->pegawai->nama ?? '-' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $jenis }}</small>
                                    </div>
                                </td>
                                <td>{{ $coa->nama_akun ?? $gaji->coa_kasbank }}</td>
                                <td class="text-end">Rp {{ number_format($gajiPokok, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($insentif, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('transaksi.penggajian.show', $gaji->id) }}" class="btn btn-outline-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-money-check-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data penggajian</p>
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

