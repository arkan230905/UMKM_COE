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
                    <div class="col-md-3">
                        <label class="form-label">Status Pembayaran</label>
                        <select name="status_pembayaran" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="belum_lunas" {{ request('status_pembayaran') == 'belum_lunas' ? 'selected' : '' }}>Belum Dibayar</option>
                            <option value="lunas" {{ request('status_pembayaran') == 'lunas' ? 'selected' : '' }}>Sudah Dibayar</option>
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
                            <th class="text-center">Status</th>
                            <th class="text-end">Gaji Pokok</th>
                            <th class="text-end">Tunjangan</th>
                            <th class="text-end">Asuransi</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Potongan</th>
                            <th class="text-end fw-bold">Total Gaji</th>
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
                                
                                // Hitung semua komponen gaji
                                if($jenis === 'BTKL') {
                                    $gajiPokok = ($gaji->tarif_per_jam ?? 0) * ($gaji->total_jam_kerja ?? 0);
                                } else {
                                    $gajiPokok = $gaji->gaji_pokok ?? 0;
                                }
                                
                                $tunjangan = $gaji->total_tunjangan ?? 0;
                                $asuransi = $gaji->asuransi ?? 0;
                                $bonus = $gaji->bonus ?? 0;
                                $potongan = $gaji->potongan ?? 0;
                                $totalGaji = $gaji->total_gaji ?? 0;
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
                                <td class="text-center">
                                    @if($gaji->status_pembayaran === 'lunas')
                                        <span class="badge bg-success">Sudah Dibayar</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Dibayar</span>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($gajiPokok, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($tunjangan, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($asuransi, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($bonus, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($potongan, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-primary">Rp {{ number_format($totalGaji, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('transaksi.penggajian.show', $gaji->id) }}" class="btn btn-outline-info btn-sm" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($gaji->status_pembayaran !== 'lunas')
                                                <form action="{{ route('transaksi.penggajian.destroy', $gaji->id) }}" method="POST" class="m-0 d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus data penggajian ini?');">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('transaksi.penggajian.markAsPaid', $gaji->id) }}" method="POST" class="m-0 d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm" title="Tandai sebagai Sudah Dibayar">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-outline-secondary btn-sm" disabled title="Tidak dapat dihapus karena sudah dibayar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary btn-sm" disabled title="Sudah dibayar">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center py-4">
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

