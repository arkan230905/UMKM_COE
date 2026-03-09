@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-industry me-2"></i>Transaksi Produksi
        </h2>
        <a href="{{ route('transaksi.produksi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Produksi
        </a>
    </div>

    @if(session('success'))
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
            <form method="GET" action="{{ route('transaksi.produksi.index') }}">
                <div class="row g-3">
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
                        <label class="form-label">Produk</label>
                        <select name="produk_id" class="form-select">
                            <option value="">Semua Produk</option>
                            @foreach($produks ?? [] as $produk)
                                <option value="{{ $produk->id }}" {{ request('produk_id') == $produk->id ? 'selected' : '' }}>
                                    {{ $produk->nama_produk }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="wip" {{ request('status') == 'wip' ? 'selected' : '' }}>Proses</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-outline-secondary">
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
                <i class="fas fa-list me-2"></i>Riwayat Produksi
                @if(request()->hasAny(['tanggal_mulai', 'tanggal_selesai', 'produk_id', 'status']))
                    <small class="text-muted">(Filter Aktif)</small>
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">NO</th>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th class="text-end">Qty Produksi</th>
                            <th class="text-end">Total Biaya</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produksis as $key => $p)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $p->produk->nama_produk }}</td>
                                <td class="text-end">{{ number_format($p->qty_produksi, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($p->total_biaya, 0, ',', '.') }}</td>
                                <td>
                                    @if($p->status === 'wip' || $p->status === 'pending' || !$p->status)
                                        <span class="badge bg-warning text-dark">Proses</span>
                                    @elseif($p->status === 'completed')
                                        <span class="badge bg-success">Selesai</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $p->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $p->id }}">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer">
                {{ $produksis->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Detail Modals -->
@foreach($produksis as $p)
<div class="modal fade" id="detailModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Produksi: {{ $p->produk->nama_produk }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Qty Produksi</label>
                        <p class="form-control-plaintext">{{ number_format($p->qty_produksi, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Total Biaya</label>
                        <p class="form-control-plaintext">Rp {{ number_format($p->total_biaya, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Status</label>
                        <p class="form-control-plaintext">
                            @if($p->status === 'wip' || $p->status === 'pending' || !$p->status)
                                <span class="badge bg-warning text-dark">Proses</span>
                            @elseif($p->status === 'completed')
                                <span class="badge bg-success">Selesai</span>
                            @else
                                <span class="badge bg-secondary">{{ $p->status }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Biaya Bahan -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">Biaya Bahan Per Produk</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Bahan Baku -->
                            <div class="col-md-6">
                                <h6 class="text-success mb-3">Bahan Baku</h6>
                                @if(isset($p->detail_breakdown['biaya_bahan']['bahan_baku']) && count($p->detail_breakdown['biaya_bahan']['bahan_baku']) > 0)
                                    @foreach($p->detail_breakdown['biaya_bahan']['bahan_baku'] as $index => $bahan)
                                        <div class="mb-2">
                                            <strong>{{ $index + 1 }}. {{ $bahan['nama'] }}:</strong> 
                                            Rp {{ number_format($bahan['total_per_produksi'], 0, ',', '.') }}
                                            <br><small class="text-muted">(Rp {{ number_format($bahan['harga_per_unit'], 0, ',', '.') }} per {{ $bahan['satuan'] }} X {{ number_format($p->qty_produksi, 0, ',', '.') }} quantity produksi)</small>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted">Tidak ada data bahan baku</p>
                                @endif
                            </div>
                            
                            <!-- Bahan Pendukung -->
                            <div class="col-md-6">
                                <h6 class="text-warning mb-3">Bahan Pendukung</h6>
                                @if(isset($p->detail_breakdown['biaya_bahan']['bahan_pendukung']) && count($p->detail_breakdown['biaya_bahan']['bahan_pendukung']) > 0)
                                    @foreach($p->detail_breakdown['biaya_bahan']['bahan_pendukung'] as $index => $bahan)
                                        <div class="mb-2">
                                            <strong>{{ $index + 1 }}. {{ $bahan['nama'] }}:</strong> 
                                            Rp {{ number_format($bahan['total_per_produksi'], 0, ',', '.') }}
                                            <br><small class="text-muted">(Rp {{ number_format($bahan['harga_per_unit'], 0, ',', '.') }} per {{ $bahan['satuan'] }} X {{ number_format($p->qty_produksi, 0, ',', '.') }} quantity produksi)</small>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted">Tidak ada data bahan pendukung</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Total</h5>
                                    <div>
                                        <h5 class="mb-0 text-success">Rp {{ number_format($p->detail_breakdown['biaya_bahan']['total'], 0, ',', '.') }}</h5>
                                        <button class="btn btn-sm btn-outline-success">Jurnal Biaya Bahan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biaya Tenaga Kerja Langsung (BTKL) -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Biaya Tenaga Kerja Langsung (BTKL)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Proses</th>
                                        <th>Nominal Biaya</th>
                                        <th>Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($p->detail_breakdown['btkl']) && count($p->detail_breakdown['btkl']) > 0)
                                        @foreach($p->detail_breakdown['btkl'] as $btkl)
                                            <tr>
                                                <td>{{ $btkl['nama'] }}</td>
                                                <td>
                                                    Rp {{ number_format($btkl['harga_per_unit'], 0, ',', '.') }}
                                                    <br><small class="text-muted">(Rp {{ number_format($btkl['harga_per_unit'], 0, ',', '.') }} per unit X {{ number_format($p->qty_produksi, 0, ',', '.') }} quantity produksi)</small>
                                                </td>
                                                <td class="fw-bold">Rp {{ number_format($btkl['total_per_produksi'], 0, ',', '.') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info">Jurnal BTKL</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-info">
                                            <td colspan="2" class="fw-bold">Total BTKL</td>
                                            <td class="fw-bold">Rp {{ number_format(array_sum(array_column($p->detail_breakdown['btkl'], 'total_per_produksi')), 0, ',', '.') }}</td>
                                            <td></td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada data BTKL</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Biaya Overhead Pabrik (BOP) -->
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">Biaya Overhead Pabrik (BOP)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Proses</th>
                                        <th>Nominal Biaya</th>
                                        <th>Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($p->detail_breakdown['bop']) && count($p->detail_breakdown['bop']) > 0)
                                        @foreach($p->detail_breakdown['bop'] as $bop)
                                            <tr>
                                                <td>{{ $bop['nama'] }}</td>
                                                <td>
                                                    Rp {{ number_format($bop['harga_per_unit'], 0, ',', '.') }}
                                                    <br><small class="text-muted">(Rp {{ number_format($bop['harga_per_unit'], 0, ',', '.') }} per unit X {{ number_format($p->qty_produksi, 0, ',', '.') }} quantity produksi)</small>
                                                </td>
                                                <td class="fw-bold">Rp {{ number_format($bop['total_per_produksi'], 0, ',', '.') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-warning">Jurnal BOP</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-warning">
                                            <td colspan="2" class="fw-bold">Total BOP</td>
                                            <td class="fw-bold">Rp {{ number_format(array_sum(array_column($p->detail_breakdown['bop'], 'total_per_produksi')), 0, ',', '.') }}</td>
                                            <td></td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada data BOP</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
