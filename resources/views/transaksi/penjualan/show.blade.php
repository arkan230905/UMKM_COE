@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            <i class="fas fa-eye me-2"></i>Detail Transaksi Penjualan
        </h3>
        <div>
            <a href="{{ route('transaksi.penjualan.edit', $penjualan->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informasi Transaksi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nomor Transaksi:</strong><br>
                            <span class="text-primary">{{ $penjualan->nomor_penjualan ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Tanggal:</strong><br>
                            {{ optional($penjualan->tanggal)->format('d-m-Y') ?? $penjualan->tanggal }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Metode Pembayaran:</strong><br>
                            <span class="badge {{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'bg-warning' : 'bg-success' }}">
                                @switch($penjualan->payment_method ?? 'cash')
                                    @case('cash')
                                        Tunai
                                        @break
                                    @case('transfer')
                                        Transfer Bank
                                        @break
                                    @case('credit')
                                        Kredit
                                        @break
                                    @default
                                        Tunai
                                @endswitch
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Status Transaksi:</strong><br>
                            <span class="badge {{ ($penjualan->status ?? 'lunas') === 'lunas' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($penjualan->status ?? 'lunas') }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Qty Retur:</strong><br>
                            @php
                                $totalQtyRetur = $penjualan->total_qty_retur ?? 0;
                            @endphp
                            @if($totalQtyRetur > 0)
                                <span class="badge bg-danger">{{ number_format($totalQtyRetur, 2, ',', '.') }}</span>
                            @else
                                <span class="badge bg-success">0</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Catatan:</strong><br>
                            {{ $penjualan->catatan ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2"></i>Detail Produk
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">HPP</th>
                                    <th class="text-end">Profit</th>
                                    <th class="text-end">Diskon</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $detailCount = $penjualan->details->count();
                                    $totalSubtotal = 0;
                                    $totalHPP = 0;
                                    $totalProfit = 0;
                                    $totalDiskon = 0;
                                @endphp
                                
                                @if($detailCount > 0)
                                    @foreach($penjualan->details as $detail)
                                        @php 
                                            $actualHPP = $detail->produk->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                            $margin = ($detail->harga_satuan - $actualHPP) * $detail->jumlah;
                                            $subtotal = $detail->subtotal ?? ($detail->jumlah * $detail->harga_satuan - ($detail->diskon_nominal ?? 0));
                                            
                                            $totalSubtotal += $subtotal;
                                            $totalHPP += $actualHPP * $detail->jumlah;
                                            $totalProfit += $margin;
                                            $totalDiskon += $detail->diskon_nominal ?? 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                            <td class="text-end">{{ rtrim(rtrim(number_format($detail->jumlah,2,',','.'),'0'),',') }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                            <td class="text-end {{ $margin > 0 ? 'text-success' : 'text-danger' }}">
                                                Rp {{ number_format($margin, 0, ',', '.') }}
                                            </td>
                                            <td class="text-end">
                                                @if($detail->diskon_persen > 0)
                                                    {{ number_format($detail->diskon_persen, 2, ',', '.') }}%
                                                @endif
                                                @if($detail->diskon_nominal > 0)
                                                    (Rp {{ number_format($detail->diskon_nominal, 0, ',', '.') }})
                                                @endif
                                                @if($detail->diskon_persen == 0 && $detail->diskon_nominal == 0)
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- Single product (old format) --}}
                                    @php 
                                        $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                        $hdrHarga = $penjualan->harga_satuan;
                                        if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                            $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                        }
                                        $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0);
                                        $subtotal = ($penjualan->jumlah ?? 0) * $hdrHarga;
                                        
                                        $totalSubtotal = $subtotal;
                                        $totalHPP = $actualHPP * ($penjualan->jumlah ?? 0);
                                        $totalProfit = $margin;
                                        $totalDiskon = $penjualan->diskon_nominal ?? 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $penjualan->produk?->nama_produk ?? '-' }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format($penjualan->jumlah,2,',','.'),'0'),',') }}</td>
                                        <td class="text-end">Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                        <td class="text-end {{ $margin > 0 ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($margin, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">
                                            @if($penjualan->diskon_nominal > 0)
                                                Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Ringkasan Transaksi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal Produk:</span>
                            <strong class="text-primary">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total HPP:</span>
                            <strong class="text-info">Rp {{ number_format($totalHPP, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Profit:</span>
                            <strong class="{{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($totalProfit, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Diskon:</span>
                            <strong class="text-warning">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between">
                            <span><strong>Total Penjualan:</strong></span>
                            <strong class="text-dark fs-5">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Aksi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('transaksi.penjualan.edit', $penjualan->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit Transaksi
                        </a>
                        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'sale', 'ref_id' => $penjualan->id]) }}" class="btn btn-primary">
                            <i class="fas fa-book me-2"></i>Lihat Jurnal
                        </a>
                        <a href="{{ route('transaksi.retur-penjualan.create', ['penjualan_id' => $penjualan->id]) }}" class="btn btn-info">
                            <i class="fas fa-undo me-2"></i>Proses Retur
                        </a>
                        <form action="{{ route('transaksi.penjualan.destroy', $penjualan->id) }}" method="POST" onsubmit="return confirm('Yakin ingin hapus transaksi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>Hapus Transaksi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection