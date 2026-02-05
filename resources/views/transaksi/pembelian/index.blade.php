@extends('layouts.app')

@section('title', 'Daftar Pembelian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Daftar Pembelian
        </h2>
        <a href="{{ route('transaksi.pembelian.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Pembelian
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
            <form method="GET" action="{{ route('transaksi.pembelian.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nomor Transaksi</label>
                        <input type="text" name="nomor_transaksi" class="form-control" 
                               value="{{ request('nomor_transaksi') }}" placeholder="Cari nomor transaksi...">
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
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Semua Metode</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                            <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Kredit</option>
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
                            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-outline-secondary">
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
                <i class="fas fa-list me-2"></i>Riwayat Pembelian
                @if(request()->hasAny(['nomor_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'vendor_id', 'payment_method', 'status']))
                    <small class="text-muted">(Filter Aktif)</small>
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">No</th>
                            <th>Nomor Transaksi</th>
                            <th>Nomor Faktur</th>
                            <th>Tanggal</th>
                            <th>Vendor</th>
                            <th>Item Dibeli</th>
                            <th>Pembayaran</th>
                            <th>Total Harga</th>
                            <th>Status Retur</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pembelians as $key => $pembelian)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td style="color: #000; font-weight: bold;">{{ $pembelian->nomor_pembelian ?? 'KOSONG' }}</td>
                                <td>
                                    @if($pembelian->nomor_faktur)
                                        <span class="badge bg-info">{{ $pembelian->nomor_faktur }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $pembelian->tanggal->format('d-m-Y') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-store text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
                                            <small class="text-muted">ID: {{ $pembelian->vendor->id ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($pembelian->details && $pembelian->details->count() > 0)
                                        <small>
                                        @foreach($pembelian->details as $detail)
                                            <div>
                                                @if($detail->bahan_baku_id && $detail->bahanBaku)
                                                    • <span class="badge bg-primary">BB</span> {{ $detail->bahanBaku->nama_bahan }} 
                                                    ({{ number_format($detail->jumlah, 0, '.', '') }} {{ $detail->bahanBaku->satuan->nama ?? 'unit' }})
                                                @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                                    • <span class="badge bg-info">BP</span> {{ $detail->bahanPendukung->nama_bahan }} 
                                                    ({{ number_format($detail->jumlah, 0, '.', '') }} {{ $detail->bahanPendukung->satuanRelation->nama ?? 'unit' }})
                                                @else
                                                    • -
                                                @endif
                                                @ Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                                = <strong>Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.') }}</strong>
                                            </div>
                                        @endforeach
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $paymentMethod = $pembelian->payment_method ?? 'cash';
                                        if ($paymentMethod === 'credit') {
                                            $badgeClass = 'bg-warning';
                                            $paymentText = 'Kredit';
                                        } elseif ($paymentMethod === 'transfer') {
                                            $badgeClass = 'bg-info';
                                            $paymentText = 'Transfer';
                                        } else {
                                            $badgeClass = 'bg-success';
                                            $paymentText = 'Tunai';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $paymentText }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        // Hitung total dari details untuk konsistensi
                                        $totalPembelian = 0;
                                        if ($pembelian->details && $pembelian->details->count() > 0) {
                                            $totalPembelian = $pembelian->details->sum(function($detail) {
                                                return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                            });
                                        }
                                        
                                        // Jika ada total_harga di database, gunakan yang lebih besar
                                        if ($pembelian->total_harga > $totalPembelian) {
                                            $totalPembelian = $pembelian->total_harga;
                                        }
                                    @endphp
                                    <span class="fw-semibold">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    @php
                                        // Cek apakah ada retur untuk pembelian ini
                                        $hasRetur = \App\Models\PurchaseReturn::where('pembelian_id', $pembelian->id)->exists();
                                    @endphp
                                    @if($hasRetur)
                                        <span class="badge bg-danger">Ada Retur</span>
                                    @else
                                        <span class="badge bg-success">Tidak Ada Retur</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('transaksi.pembelian.edit', $pembelian->id) }}" class="btn btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('transaksi.pembelian.show', $pembelian->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('transaksi.retur-pembelian.create', ['pembelian_id' => $pembelian->id]) }}" class="btn btn-outline-info">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'purchase', 'ref_id' => $pembelian->id]) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <form action="{{ route('transaksi.pembelian.destroy', $pembelian->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
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
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data pembelian</p>
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
