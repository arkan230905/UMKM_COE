@extends('layouts.pegawai-pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">
            <i class="bi bi-file-earmark-text"></i> Laporan Pembelian
        </h2>
        <p class="text-muted">Lihat riwayat pembelian yang telah dilakukan</p>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group" role="group">
            <a href="{{ route('pegawai-pembelian.laporan.pembelian') }}" 
               class="btn btn-primary">
                <i class="bi bi-cart-plus"></i> Pembelian
            </a>
            <a href="{{ route('pegawai-pembelian.laporan.retur') }}" 
               class="btn btn-outline-primary">
                <i class="bi bi-arrow-return-left"></i> Retur
            </a>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('pegawai-pembelian.laporan.pembelian') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" 
                           value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" 
                           value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select">
                        <option value="">Semua Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" 
                                    {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->nama_vendor }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('pegawai-pembelian.laporan.pembelian') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Transaksi</h5>
                <h3>{{ $totalTransaksi }}</h3>
                <p class="card-text">Pembelian</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Pembelian</h5>
                <h3>Rp {{ number_format($totalPembelian, 0, ',', '.') }}</h3>
                <p class="card-text">Semua Pembelian</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Pembelian Tunai</h5>
                <h3>Rp {{ number_format($totalPembelianTunai, 0, ',', '.') }}</h3>
                <p class="card-text">Cash</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Belum Lunas</h5>
                <h3>Rp {{ number_format($totalPembelianBelumLunas, 0, ',', '.') }}</h3>
                <p class="card-text">Credit</p>
            </div>
        </div>
    </div>
</div>

<!-- Data Card -->
<div class="card">
    <div class="card-body">
        @if($pembelians->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Pembelian</th>
                        <th>Vendor</th>
                        <th>Item Dibeli</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pembelians as $pembelian)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($pembelian->tanggal)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-info">
                                {{ $pembelian->nomor_pembelian ?? '#' . str_pad($pembelian->id, 6, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td>{{ $pembelian->vendor->nama_vendor ?? '-' }}</td>
                        <td>
                            @if($pembelian->details && $pembelian->details->count() > 0)
                                <div class="small">
                                    @foreach($pembelian->details as $detail)
                                        <div class="mb-1">
                                            • 
                                            @if($detail->tipe_item === 'bahan_baku' && $detail->bahanBaku)
                                                {{ $detail->bahanBaku->nama_bahan }}
                                            @elseif($detail->tipe_item === 'bahan_pendukung' && $detail->bahanPendukung)
                                                {{ $detail->bahanPendukung->nama_bahan }}
                                            @elseif($detail->tipe_item === 'bahan_baku' && $detail->bahan_baku_id && !$detail->bahanBaku)
                                                Bahan Baku (ID: {{ $detail->bahan_baku_id }})
                                            @elseif($detail->tipe_item === 'bahan_pendukung' && $detail->bahan_pendukung_id && !$detail->bahanPendukung)
                                                Bahan Pendukung (ID: {{ $detail->bahan_pendukung_id }})
                                            @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                                {{ $detail->bahanPendukung->nama_bahan }}
                                            @elseif($detail->bahan_baku_id && $detail->bahanBaku)
                                                {{ $detail->bahanBaku->nama_bahan }}
                                            @elseif($detail->bahan_pendukung_id)
                                                Bahan Pendukung (ID: {{ $detail->bahan_pendukung_id }})
                                            @elseif($detail->bahan_baku_id)
                                                Bahan Baku (ID: {{ $detail->bahan_baku_id }})
                                            @else
                                                Item
                                            @endif
                                            <span class="text-muted">
                                                ({{ number_format($detail->jumlah ?? 0, 0, ',', '.') }} 
                                                @if($detail->tipe_item === 'bahan_baku' && $detail->bahanBaku)
                                                    {{ $detail->bahanBaku->satuan->nama ?? 'unit' }}
                                                @elseif($detail->tipe_item === 'bahan_pendukung' && $detail->bahanPendukung)
                                                    {{ $detail->bahanPendukung->satuanRelation->nama ?? 'unit' }}
                                                @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                                    {{ $detail->bahanPendukung->satuanRelation->nama ?? 'unit' }}
                                                @elseif($detail->bahan_baku_id && $detail->bahanBaku)
                                                    {{ $detail->bahanBaku->satuan->nama ?? 'unit' }}
                                                @else
                                                    {{ $detail->satuan ?? 'unit' }}
                                                @endif
                                                })
                                            </span>
                                            - Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                                            = <strong>Rp {{ number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.') }}</strong>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Detail tidak tersedia
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            @php
                                // Gunakan total yang sama dengan laporan/pembelian
                                $totalHarga = 0;
                                if ($pembelian->details && $pembelian->details->count() > 0) {
                                    $totalHarga = $pembelian->details->sum(function($detail) {
                                        return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                    });
                                }
                                
                                // Jika ada total_harga di database, gunakan yang lebih besar (sama seperti admin)
                                if ($pembelian->total_harga > $totalHarga) {
                                    $totalHarga = $pembelian->total_harga;
                                }
                            @endphp
                            <strong>Rp {{ number_format($totalHarga, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            @php
                                // Logic status sama dengan laporan/pembelian - cek apakah ada retur
                                $hasRetur = \App\Models\PurchaseReturn::where('pembelian_id', $pembelian->id)->exists();
                                
                                if ($hasRetur) {
                                    $statusText = 'Ada Retur';
                                    $statusBadgeClass = 'bg-warning';
                                } else {
                                    $statusText = 'Tidak Ada Retur';
                                    $statusBadgeClass = 'bg-success';
                                }
                            @endphp
                            <span class="badge {{ $statusBadgeClass }}">
                                {{ $statusText }}
                            </span>
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
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('pegawai-pembelian.pembelian.show', $pembelian->id) }}" 
                                   class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('pegawai-pembelian.laporan.invoice', $pembelian->id) }}" 
                                   target="_blank" class="btn btn-primary" title="Cetak Invoice">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $pembelians->links() }}
        </div>
        
        @else
        <div class="text-center py-5">
            <i class="bi bi-file-earmark-text" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Belum ada data pembelian</p>
            <a href="{{ route('pegawai-pembelian.pembelian.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Buat Pembelian
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
