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
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Nama Pegawai</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Gaji Pokok / Tarif</th>
                            <th>Jam Kerja</th>
                            <th>Tunjangan</th>
                            <th>Bonus</th>
                            <th>Potongan</th>
                            <th>Total Terbayar</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penggajians as $index => $gaji)
                            @php
                                $jenis = strtoupper($gaji->pegawai->jenis_pegawai ?? 'BTKTL');
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $gaji->pegawai->nama ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $jenis === 'BTKL' ? 'bg-info' : 'bg-secondary' }}">
                                        {{ $jenis }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($gaji->tanggal_penggajian)->format('d-m-Y') }}</td>
                                <td>
                                    @if($jenis === 'BTKL')
                                        Rp {{ number_format($gaji->tarif_per_jam ?? 0, 0, ',', '.') }}/jam
                                    @else
                                        Rp {{ number_format($gaji->gaji_pokok ?? 0, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td>
                                    @if($jenis === 'BTKL')
                                        {{ number_format($gaji->total_jam_kerja ?? 0, 2) }} jam
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>Rp {{ number_format($gaji->tunjangan ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($gaji->bonus ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($gaji->potongan ?? 0, 0, ',', '.') }}</td>
                                <td><strong>Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</strong></td>
                                <td>
                                    <span class="badge 
                                        @if(($gaji->status_pembayaran ?? 'belum_lunas') === 'lunas') bg-success
                                        @elseif(($gaji->status_pembayaran ?? 'belum_lunas') === 'dibatalkan') bg-danger
                                        @else bg-warning @endif">
                                        {{ ucfirst($gaji->status_pembayaran ?? 'Belum Lunas') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('transaksi.penggajian.slip', $gaji->id) }}" class="btn btn-outline-info" title="Lihat Slip">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                        <a href="{{ route('transaksi.penggajian.slip-pdf', $gaji->id) }}" class="btn btn-outline-success" title="Download PDF">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-success" onclick="confirmPayment({{ $gaji->id }})" title="Bayar">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="confirmCancel({{ $gaji->id }})" title="Batalkan">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <a href="{{ route('transaksi.penggajian.edit', $gaji->id) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">
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

<!-- Modal Konfirmasi Pembayaran -->
<form id="paymentForm" method="POST">
    @csrf
    <input type="hidden" name="action" value="pay">
    <input type="hidden" name="metode_pembayaran" value="transfer">
</form>

<!-- Modal Konfirmasi Pembatalan -->
<form id="cancelForm" method="POST">
    @csrf
    <input type="hidden" name="action" value="cancel">
</form>

<script>
function confirmPayment(id) {
    if (confirm('Tandai transaksi ini sebagai dibayar?')) {
        const form = document.getElementById('paymentForm');
        form.action = '/transaksi/penggajian/' + id + '/update-status';
        form.submit();
    }
}

function confirmCancel(id) {
    if (confirm('Batalkan transaksi ini?')) {
        const form = document.getElementById('cancelForm');
        form.action = '/transaksi/penggajian/' + id + '/update-status';
        form.submit();
    }
}
</script>
@endsection
