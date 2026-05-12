@extends('layouts.app')

@section('title', 'Detail Retur Pembelian')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-undo me-2"></i>
                        Detail Retur Pembelian #{{ $retur->return_number }}
                    </h4>
                    <div class="d-flex gap-2">
                        @include('transaksi.retur-pembelian.action-buttons-detail', ['retur' => $retur])
                        <a href="{{ route('transaksi.retur-pembelian.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    {{-- Alert Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Retur Information --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Informasi Retur
                                    </h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>No. Retur:</strong></td>
                                            <td>{{ $retur->return_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Retur:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($retur->return_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jenis Retur:</strong></td>
                                            <td>
                                                @if($retur->jenis_retur === 'refund')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-money-bill-wave me-1"></i>
                                                        Refund (Pengembalian Uang)
                                                    </span>
                                                @else
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-exchange-alt me-1"></i>
                                                        Tukar Barang
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>@include('transaksi.retur-pembelian.status-badge', ['status' => $retur->status])</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Nilai:</strong></td>
                                            <td class="fw-bold text-success">Rp {{ number_format($retur->total_return_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-shopping-cart me-2"></i>
                                        Informasi Pembelian
                                    </h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>No. Pembelian:</strong></td>
                                            <td>{{ $retur->pembelian->nomor_pembelian ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Vendor:</strong></td>
                                            <td>{{ $retur->pembelian->vendor->nama ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Pembelian:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($retur->pembelian->tanggal_pembelian)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Alasan Retur:</strong></td>
                                            <td>{{ $retur->reason }}</td>
                                        </tr>
                                        @if($retur->notes)
                                        <tr>
                                            <td><strong>Catatan:</strong></td>
                                            <td>{{ $retur->notes }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Item Retur ({{ $retur->items->count() }} item)
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No.</th>
                                            <th width="25%">Item</th>
                                            <th width="15%">Qty Retur</th>
                                            <th width="10%">Satuan</th>
                                            <th width="15%">Harga Satuan</th>
                                            <th width="15%">Subtotal</th>
                                            <th width="15%">Status Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($retur->items as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    @if($item->bahan_baku_id)
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-primary me-2">BB</span>
                                                            <span>{{ $item->bahanBaku->nama_bahan ?? 'Unknown' }}</span>
                                                        </div>
                                                    @elseif($item->bahan_pendukung_id)
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-info me-2">BP</span>
                                                            <span>{{ $item->bahanPendukung->nama_bahan ?? 'Unknown' }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">Unknown Item</span>
                                                    @endif
                                                </td>
                                                <td class="fw-bold">{{ $item->quantity == floor($item->quantity) ? number_format($item->quantity, 0) : number_format($item->quantity, 2) }}</td>
                                                <td>{{ $item->unit }}</td>
                                                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                <td class="fw-bold text-success">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                <td>
                                                    @if($retur->jenis_retur === 'refund')
                                                        @if($retur->status === 'pending')
                                                            <span class="badge bg-warning">Belum Dikurangi</span>
                                                        @else
                                                            <span class="badge bg-danger">Sudah Dikurangi</span>
                                                        @endif
                                                    @else
                                                        @if($retur->status === 'pending' || $retur->status === 'disetujui')
                                                            <span class="badge bg-secondary">Belum Berubah</span>
                                                        @elseif($retur->status === 'dikirim')
                                                            <span class="badge bg-warning">Dikurangi</span>
                                                        @elseif($retur->status === 'selesai')
                                                            <span class="badge bg-success">Ditambah (Pengganti)</span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="5" class="text-end">Total Retur:</th>
                                            <th class="text-success">Rp {{ number_format($retur->total_return_amount, 0, ',', '.') }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Status Flow Information --}}
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-route me-2"></i>
                                Alur Status Retur
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Untuk Jenis: {{ $retur->jenis_retur_label }}</h6>
                                    @if($retur->jenis_retur === 'refund')
                                        <ol class="list-group list-group-numbered">
                                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold">Pending</div>
                                                    Menunggu persetujuan vendor
                                                </div>
                                                @if($retur->status === 'pending')
                                                    <span class="badge bg-warning rounded-pill">Current</span>
                                                @endif
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold">Disetujui</div>
                                                    Vendor menyetujui refund, stok dikurangi
                                                </div>
                                                @if($retur->status === 'disetujui')
                                                    <span class="badge bg-info rounded-pill">Current</span>
                                                @endif
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold">Selesai</div>
                                                    Uang telah dikembalikan
                                                </div>
                                                @if($retur->status === 'selesai')
                                                    <span class="badge bg-success rounded-pill">Current</span>
                                                @endif
                                            </li>
                                        </ol>
                                    @else
                                        <ol class="list-group list-group-numbered">
                                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold">Pending</div>
                                                    Menunggu persetujuan vendor
                                                </div>
                                                @if($retur->status === 'pending')
                                                    <span class="badge bg-warning rounded-pill">Current</span>
                                                @endif
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold">Disetujui</div>
                                                    Vendor menyetujui tukar barang
                                                </div>
                                                @if($retur->status === 'disetujui')
                                                    <span class="badge bg-info rounded-pill">Current</span>
                                                @endif
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold">Dikirim</div>
                                                    Barang dikirim ke vendor, stok dikurangi
                                                </div>
                                                @if($retur->status === 'dikirim')
                                                    <span class="badge bg-primary rounded-pill">Current</span>
                                                @endif
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold">Selesai</div>
                                                    Barang pengganti diterima, stok ditambah
                                                </div>
                                                @if($retur->status === 'selesai')
                                                    <span class="badge bg-success rounded-pill">Current</span>
                                                @endif
                                            </li>
                                        </ol>
                                    @endif
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="text-primary">Dampak Terhadap Stok</h6>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        @if($retur->jenis_retur === 'refund')
                                            <strong>Refund:</strong> Stok langsung dikurangi saat retur disetujui karena barang dikembalikan ke vendor.
                                        @else
                                            <strong>Tukar Barang:</strong> Stok dikurangi saat barang dikirim, kemudian ditambah lagi saat barang pengganti diterima.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/retur-pembelian.css') }}">
@endpush

@endsection