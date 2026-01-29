@extends('layouts.pegawai-pembelian')

@section('title', 'Detail Pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">
            <i class="bi bi-eye"></i> Detail Pembelian
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('pegawai-pembelian.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pegawai-pembelian.pembelian.index') }}">Pembelian</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('pegawai-pembelian.pembelian.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Pembelian</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label text-muted">Nomor Pembelian</label>
                <p class="form-control-plaintext fw-bold">{{ $pembelian->nomor_pembelian ?? 'AUTO-' . $pembelian->id }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted">Tanggal</label>
                <p class="form-control-plaintext">{{ $pembelian->tanggal->format('d-m-Y') }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted">Vendor</label>
                <p class="form-control-plaintext">{{ $pembelian->vendor->nama_vendor }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted">Status</label>
                <p class="form-control-plaintext">
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
                </p>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-3">
                <label class="form-label text-muted">Metode Pembayaran</label>
                <p class="form-control-plaintext">
                    @php
                        // Gunakan payment_method yang sesuai dengan data database
                        $paymentMethod = $pembelian->payment_method ?? 'cash';
                        if ($paymentMethod === 'cash') {
                            $paymentText = '💵 Tunai';
                        } elseif ($paymentMethod === 'transfer') {
                            $paymentText = '🏦 Transfer Bank';
                        } else {
                            $paymentText = '💳 Kredit (Hutang)';
                        }
                    @endphp
                    {{ $paymentText }}
                </p>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted">Total Harga</label>
                <p class="form-control-plaintext fw-bold text-primary">
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
                    Rp {{ number_format($totalHarga, 0, ',', '.') }}
                </p>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted">Terbayar</label>
                <p class="form-control-plaintext">Rp {{ number_format($pembelian->terbayar, 0, ',', '.') }}</p>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted">Sisa Pembayaran</label>
                <p class="form-control-plaintext">Rp {{ number_format($pembelian->sisa_pembayaran, 0, ',', '.') }}</p>
            </div>
        </div>
        @if($pembelian->keterangan)
        <div class="row g-3 mt-2">
            <div class="col-md-12">
                <label class="form-label text-muted">Keterangan</label>
                <p class="form-control-plaintext">{{ $pembelian->keterangan }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0"><i class="bi bi-box me-2"></i>Detail Bahan Baku</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Bahan Baku</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Harga/Satuan</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalBahanBaku = 0; @endphp
                    @foreach($pembelian->details as $index => $detail)
                        @php 
                            // Logic berdasarkan posisi input saat create
                            $namaItem = 'Unknown';
                            $satuanItem = 'unit';
                            $shouldShowInBahanBaku = false;
                            
                            // Jika item diinput sebagai bahan baku (berdasarkan relation yang ada)
                            if ($detail->bahan_baku_id && $detail->bahanBaku) {
                                $namaItem = $detail->bahanBaku->nama_bahan;
                                // Prioritas: detail->satuan, lalu relation->satuanRelation->nama
                                $satuanItem = $detail->satuan ?: ($detail->bahanBaku->satuanRelation->nama ?? 'unit');
                                $shouldShowInBahanBaku = true;
                            }
                            // Jika item diinput sebagai bahan pendukung (berdasarkan relation yang ada)
                            elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                                $namaItem = $detail->bahanPendukung->nama_bahan;
                                // Prioritas: detail->satuan, lalu relation->satuanRelation->nama
                                $satuanItem = $detail->satuan ?: ($detail->bahanPendukung->satuanRelation->nama ?? 'unit');
                                $shouldShowInBahanBaku = false; // Tampilkan di bahan pendukung
                            }
                            // Fallback jika relation tidak ada
                            elseif ($detail->bahan_baku_id) {
                                $namaItem = 'Bahan Baku (ID: ' . $detail->bahan_baku_id . ')';
                                $satuanItem = $detail->satuan ?: 'unit';
                                $shouldShowInBahanBaku = true;
                            }
                            elseif ($detail->bahan_pendukung_id) {
                                $namaItem = 'Bahan Pendukung (ID: ' . $detail->bahan_pendukung_id . ')';
                                $satuanItem = $detail->satuan ?: 'unit';
                                $shouldShowInBahanBaku = false; // Tampilkan di bahan pendukung
                            }
                            
                            $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                            if ($shouldShowInBahanBaku) {
                                $totalBahanBaku += $subtotal;
                            }
                        @endphp
                        @if($shouldShowInBahanBaku)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $namaItem }}</td>
                            <td>{{ number_format($detail->jumlah, 2, ',', '.') }}</td>
                            <td>{{ $satuanItem }}</td>
                            <td>Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                            <td class="fw-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
                @if($totalBahanBaku > 0)
                <tfoot>
                    <tr class="table-success">
                        <th colspan="5" class="text-end">Total Bahan Baku:</th>
                        <th class="fw-bold">Rp {{ number_format($totalBahanBaku, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h6 class="mb-0"><i class="bi bi-tools me-2"></i>Detail Bahan Pendukung</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Bahan Pendukung</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Harga/Satuan</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalBahanPendukung = 0; @endphp
                    @foreach($pembelian->details as $index => $detail)
                        @php 
                            // Logic berdasarkan posisi input saat create
                            $namaItem = 'Unknown';
                            $satuanItem = 'unit';
                            $shouldShowInBahanPendukung = false;
                            
                            // Jika item diinput sebagai bahan baku (berdasarkan relation yang ada)
                            if ($detail->bahan_baku_id && $detail->bahanBaku) {
                                $namaItem = $detail->bahanBaku->nama_bahan;
                                $satuanItem = $detail->satuan ?: ($detail->bahanBaku->satuanRelation->nama ?? 'unit');
                                $shouldShowInBahanPendukung = false; // Tampilkan di bahan baku
                            }
                            // Jika item diinput sebagai bahan pendukung (berdasarkan relation yang ada)
                            elseif ($detail->bahan_pendukung_id && $detail->bahanPendukung) {
                                $namaItem = $detail->bahanPendukung->nama_bahan;
                                $satuanItem = $detail->satuan ?: ($detail->bahanPendukung->satuanRelation->nama ?? 'unit');
                                $shouldShowInBahanPendukung = true; // Tampilkan di bahan pendukung
                            }
                            // Fallback jika relation tidak ada
                            elseif ($detail->bahan_baku_id) {
                                $namaItem = 'Bahan Baku (ID: ' . $detail->bahan_baku_id . ')';
                                $satuanItem = $detail->satuan ?: 'unit';
                                $shouldShowInBahanPendukung = false; // Tampilkan di bahan baku
                            }
                            elseif ($detail->bahan_pendukung_id) {
                                $namaItem = 'Bahan Pendukung (ID: ' . $detail->bahan_pendukung_id . ')';
                                $satuanItem = $detail->satuan ?: 'unit';
                                $shouldShowInBahanPendukung = true; // Tampilkan di bahan pendukung
                            }
                            
                            $subtotal = ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                            if ($shouldShowInBahanPendukung) {
                                $totalBahanPendukung += $subtotal;
                            }
                        @endphp
                        @if($shouldShowInBahanPendukung)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $namaItem }}</td>
                            <td>{{ number_format($detail->jumlah, 2, ',', '.') }}</td>
                            <td>{{ $satuanItem }}</td>
                            <td>Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                            <td class="fw-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
                @if($totalBahanPendukung > 0)
                <tfoot>
                    <tr class="table-info">
                        <th colspan="5" class="text-end">Total Bahan Pendukung:</th>
                        <th class="fw-bold">Rp {{ number_format($totalBahanPendukung, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-warning text-dark">
        <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Ringkasan Pembelian</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="h5">Total Pembelian:</span>
                    <span class="h3 fw-bold text-success">
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
                        Rp {{ number_format($totalHarga, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('pegawai-pembelian.pembelian.index') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
