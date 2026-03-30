@extends('layouts.app')

@section('title', 'Retur Penjualan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-undo me-2"></i>Data Retur Penjualan
        </h2>
        <div>
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Penjualan
            </a>
            <a href="{{ route('transaksi.retur-penjualan.create') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus me-2"></i>Tambah Retur
            </a>
            <a href="{{ route('transaksi.retur-penjualan.laporan') }}" class="btn btn-info">
                <i class="fas fa-print me-2"></i>Laporan
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('transaksi.retur-penjualan.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Retur</label>
                        <select name="jenis_retur" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="tukar_barang" {{ request('jenis_retur') == 'tukar_barang' ? 'selected' : '' }}>Tukar Barang</option>
                            <option value="refund" {{ request('jenis_retur') == 'refund' ? 'selected' : '' }}>Refund</option>
                            <option value="kredit" {{ request('jenis_retur') == 'kredit' ? 'selected' : '' }}>Kredit</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="belum_dibayar" {{ request('status') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.retur-penjualan.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($returPenjualans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px">#</th>
                                <th>Nomor Retur</th>
                                <th>Tanggal</th>
                                <th>Nomor Transaksi</th>
                                <th>Pelanggan</th>
                                <th>Jenis Retur</th>
                                <th>Total Retur</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 150px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($returPenjualans as $index => $retur)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td><strong>{{ $retur->nomor_retur }}</strong></td>
                                    <td>{{ $retur->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $retur->penjualan->nomor_penjualan ?? '-' }}</td>
                                    <td>{{ $retur->pelanggan->name ?? '-' }}</td>
                                    <td>
                                        @switch($retur->jenis_retur)
                                            @case('tukar_barang')
                                                <span class="badge bg-warning">Tukar Barang</span>
                                                @break
                                            @case('refund')
                                                <span class="badge bg-info">Refund</span>
                                                @break
                                            @case('kredit')
                                                <span class="badge bg-secondary">Kredit</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($retur->total_retur, 2) }}</td>
                                    <td>
                                        @switch($retur->status)
                                            @case('belum_dibayar')
                                                <span class="badge bg-danger">Belum Dibayar</span>
                                                @break
                                            @case('lunas')
                                                <span class="badge bg-success">Lunas</span>
                                                @break
                                            @case('selesai')
                                                <span class="badge bg-primary">Selesai</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('transaksi.retur-penjualan.show', $retur) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($retur->status === 'belum_dibayar')
                                                <a href="{{ route('transaksi.retur-penjualan.edit', $retur) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('transaksi.retur-penjualan.destroy', $retur) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus retur ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($retur->jenis_retur === 'kredit' && $retur->status === 'belum_dibayar')
                                                <a href="{{ route('transaksi.retur-penjualan.bayar-kredit', $retur) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Bayar" onclick="return confirm('Apakah Anda yakin ingin melunasi retur kredit ini?')">
                                                    <i class="fas fa-money-bill"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrapper">
                    {{ $returPenjualans->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-undo fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada data retur penjualan</h5>
                    <p class="text-muted">Belum ada transaksi retur penjualan yang tercatat.</p>
                    <a href="{{ route('transaksi.retur-penjualan.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat Retur Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
</style>
@endpush
