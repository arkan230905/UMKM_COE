@extends('layouts.pegawai-pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">
            <i class="bi bi-box-seam"></i> Detail {{ $tipe == 'material' ? 'Bahan Baku' : 'Bahan Pendukung' }}
        </h2>
        <p class="text-muted">Informasi lengkap {{ $tipe == 'material' ? 'bahan baku' : 'bahan pendukung' }}</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('pegawai-pembelian.bahan-baku.index', ['tipe' => $tipe]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('pegawai-pembelian.bahan-baku.edit', $item->id) }}?tipe={{ $tipe }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="/pegawai-gudang/laporan-stok/detail?item_type={{ $tipe == 'material' ? 'material' : 'support' }}&item_id={{ $item->id }}" class="btn btn-info">
            <i class="bi bi-clipboard-data"></i> Kartu Stok
        </a>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Informasi Utama -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-info-circle"></i> Informasi {{ $tipe == 'material' ? 'Bahan Baku' : 'Bahan Pendukung' }}
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted">Kode {{ $tipe == 'material' ? 'Bahan Baku' : 'Bahan Pendukung' }}</label>
                    <p class="form-control-plaintext fw-bold">{{ $item->kode_bahan ?? 'N/A' }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Nama {{ $tipe == 'material' ? 'Bahan Baku' : 'Bahan Pendukung' }}</label>
                    <p class="form-control-plaintext fw-bold">{{ $item->nama_bahan }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Satuan</label>
                    <p class="form-control-plaintext">{{ optional($item->satuanRelation)->nama ?? 'N/A' }}</p>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted">Stok Saat Ini</label>
                    <p class="form-control-plaintext">
                        <span class="fw-bold">{{ number_format($item->stok, 2, ',', '.') }}</span>
                        {{ optional($item->satuanRelation)->nama ?? 'Unit' }}
                        @if($item->stok <= ($item->stok_minimum ?? 0))
                            <span class="badge bg-danger ms-2">Stok Rendah</span>
                        @else
                            <span class="badge bg-success ms-2">Stok Aman</span>
                        @endif
                    </p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Stok Minimum</label>
                    <p class="form-control-plaintext">{{ $item->stok_minimum ?? 0 }} {{ optional($item->satuanRelation)->nama ?? 'Unit' }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Harga Satuan</label>
                    <p class="form-control-plaintext">Rp {{ number_format($item->harga_satuan ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        
        @if($item->deskripsi)
        <div class="row">
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label text-muted">Deskripsi</label>
                    <p class="form-control-plaintext">{{ $item->deskripsi }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Riwayat Pembelian -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clock-history"></i> Riwayat Pembelian
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Pembelian</th>
                        <th>Supplier</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($item->pembelianDetails) && $item->pembelianDetails->count() > 0)
                        @foreach($item->pembelianDetails as $detail)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($detail->pembelian->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $detail->pembelian->nomor_pembelian }}</td>
                            <td>{{ optional($detail->pembelian->vendor)->nama_vendor ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Belum ada riwayat pembelian</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex gap-2">
            <a href="{{ route('pegawai-pembelian.bahan-baku.index', ['tipe' => $tipe]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
            <a href="{{ route('pegawai-pembelian.bahan-baku.edit', $item->id) }}?tipe={{ $tipe }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit {{ $tipe == 'material' ? 'Bahan Baku' : 'Bahan Pendukung' }}
            </a>
            <a href="/pegawai-gudang/laporan-stok/detail?item_type={{ $tipe == 'material' ? 'material' : 'support' }}&item_id={{ $item->id }}" class="btn btn-info">
                <i class="bi bi-clipboard-data"></i> Kartu Stok
            </a>
            <button class="btn btn-success" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak
            </button>
        </div>
    </div>
</div>

@endsection