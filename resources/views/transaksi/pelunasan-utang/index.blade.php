@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-hand-holding-usd me-2"></i>Pelunasan Utang
        </h2>
        <a href="{{ route('transaksi.pelunasan-utang.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Pelunasan
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
            <form method="GET" action="{{ route('transaksi.pelunasan-utang.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kode Transaksi</label>
                        <input type="text" name="kode_transaksi" class="form-control" 
                               value="{{ request('kode_transaksi') }}" placeholder="Cari kode transaksi...">
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
                        <label class="form-label">Vendor</label>
                        <select name="vendor_id" class="form-select">
                            <option value="">Semua Vendor</option>
                            @foreach($vendors ?? [] as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->nama_vendor }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.pelunasan-utang.index') }}" class="btn btn-outline-secondary">
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
                <i class="fas fa-list me-2"></i>Riwayat Pelunasan Utang
                @if(request()->hasAny(['kode_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'vendor_id', 'status']))
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
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pembelian</th>
                            <th>Vendor</th>
                            <th class="text-end">Jumlah</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelunasanUtang as $key => $item)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-receipt text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->kode_transaksi }}</div>
                                            <small class="text-muted">ID: {{ $item->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-shopping-cart text-success"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->pembelian->kode_pembelian ?? '-' }}</div>
                                            <small class="text-muted">Pembelian</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-truck text-info"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->pembelian->vendor->nama ?? '-' }}</div>
                                            <small class="text-muted">Vendor</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                <td>{!! $item->status_badge !!}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('transaksi.pelunasan-utang.show', $item->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('transaksi.pelunasan-utang.print', $item->id) }}" class="btn btn-outline-warning" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <form action="{{ route('transaksi.pelunasan-utang.destroy', $item->id) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data pelunasan utang</p>
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
