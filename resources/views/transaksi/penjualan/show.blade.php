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

    @php
        $detailCount = $penjualan->details->count();
        $totalSubtotal = 0; $totalHPP = 0; $totalProfit = 0; $totalDiskon = 0;
        if ($detailCount > 0) {
            foreach ($penjualan->details as $d) {
                $hpp = $d->produk->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                $sub = $d->subtotal ?? ($d->jumlah * $d->harga_satuan - ($d->diskon_nominal ?? 0));
                $totalSubtotal += $sub;
                $totalHPP += $hpp * $d->jumlah;
                $totalProfit += ($d->harga_satuan - $hpp) * $d->jumlah;
                $totalDiskon += $d->diskon_nominal ?? 0;
            }
        } else {
            $hpp = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
            $hdrHarga = $penjualan->harga_satuan;
            if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
            }
            $totalSubtotal = ($penjualan->jumlah ?? 0) * $hdrHarga;
            $totalHPP = $hpp * ($penjualan->jumlah ?? 0);
            $totalProfit = ($hdrHarga - $hpp) * ($penjualan->jumlah ?? 0);
            $totalDiskon = $penjualan->diskon_nominal ?? 0;
        }
    @endphp

    {{-- Row 1: Informasi Transaksi + Ringkasan --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Transaksi</h5>
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
                            <span class="badge {{ ($penjualan->payment_method ?? '') === 'credit' ? 'bg-warning' : 'bg-success' }}">
                                @switch($penjualan->payment_method ?? '')
                                    @case('cash') Tunai @break
                                    @case('transfer') Transfer Bank @break
                                    @case('credit') Kredit @break
                                    @default Tidak Diketahui
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
                            @php $totalQtyRetur = $penjualan->total_qty_retur ?? 0; @endphp
                            @if($totalQtyRetur > 0)
                                <span class="badge bg-danger">{{ (int)$totalQtyRetur }}</span>
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
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Ringkasan Transaksi</h5>
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
        </div>
    </div>

    {{-- Row 2: Detail Produk + Aksi --}}
    <div class="row mt-4">
        <div class="col-12">
            {{-- Tab Navigation --}}
            <ul class="nav nav-tabs" id="penjualanTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail-pane" type="button" role="tab" aria-controls="detail-pane" aria-selected="true">
                        <i class="fas fa-list me-2"></i>Detail Transaksi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="struk-tab" data-bs-toggle="tab" data-bs-target="#struk-pane" type="button" role="tab" aria-controls="struk-pane" aria-selected="false">
                        <i class="fas fa-receipt me-2"></i>Struk Penjualan
                    </button>
                </li>
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content" id="penjualanTabsContent">
                {{-- Detail Tab --}}
                <div class="tab-pane fade show active" id="detail-pane" role="tabpanel" aria-labelledby="detail-tab">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-box me-2"></i>Detail Produk</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Produk</th>
                                                    <th class="text-end">Qty</th>
                                                    <th class="text-end">Harga</th>
                                                    <th class="text-end">HPP</th>
                                                    <th class="text-end">Profit</th>
                                                    <th class="text-end">Diskon</th>
                                                    <th class="text-end">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($detailCount > 0)
                                                    @foreach($penjualan->details as $detail)
                                                        @php
                                                            $actualHPP = $detail->produk->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                                            $margin = ($detail->harga_satuan - $actualHPP) * $detail->jumlah;
                                                            $subtotal = $detail->subtotal ?? ($detail->jumlah * $detail->harga_satuan - ($detail->diskon_nominal ?? 0));
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                                            <td class="text-end">{{ rtrim(rtrim(number_format($detail->jumlah,2,',','.'),'0'),',') }}</td>
                                                            <td class="text-end">Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                                            <td class="text-end">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                                            <td class="text-end {{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</td>
                                                            <td class="text-end">
                                                                @if($detail->diskon_persen > 0) {{ number_format($detail->diskon_persen, 2, ',', '.') }}% @endif
                                                                @if($detail->diskon_nominal > 0) (Rp {{ number_format($detail->diskon_nominal, 0, ',', '.') }}) @endif
                                                                @if($detail->diskon_persen == 0 && $detail->diskon_nominal == 0) - @endif
                                                            </td>
                                                            <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    @php
                                                        $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0;
                                                        $hdrHarga = $penjualan->harga_satuan;
                                                        if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                                        $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                                        }
                                                        $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $penjualan->produk?->nama_produk ?? '-' }}</td>
                                                        <td class="text-end">{{ rtrim(rtrim(number_format($penjualan->jumlah,2,',','.'),'0'),',') }}</td>
                                                        <td class="text-end">Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}</td>
                                                        <td class="text-end">Rp {{ number_format($actualHPP, 0, ',', '.') }}</td>
                                                        <td class="text-end {{ $margin > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($margin, 0, ',', '.') }}</td>
                                                        <td class="text-end">
                                                            @if($penjualan->diskon_nominal > 0) Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }} @else - @endif
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
                                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Aksi</h5>
                                </div>
                                <div class="card-body">
                                    {{-- Baris pertama: Detail, Edit, Jurnal --}}
                                    <div class="mb-4">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('transaksi.penjualan.show', $penjualan->id) }}" class="btn btn-outline-success btn-sm flex-fill text-center">
                                                <i class="fas fa-eye d-block mb-1"></i><small>Detail</small>
                                            </a>
                                            <a href="{{ route('transaksi.penjualan.edit', $penjualan->id) }}" class="btn btn-outline-warning btn-sm flex-fill text-center">
                                                <i class="fas fa-edit d-block mb-1"></i><small>Edit</small>
                                            </a>
                                            <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'sale', 'ref_id' => $penjualan->id]) }}" class="btn btn-outline-primary btn-sm flex-fill text-center">
                                                <i class="fas fa-book d-block mb-1"></i><small>Jurnal</small>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    {{-- Baris kedua: Cetak, Retur, Hapus --}}
                                    <div>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="#" onclick="showStrukTab()" class="btn btn-outline-secondary btn-sm flex-fill text-center">
                                                <i class="fas fa-print d-block mb-1"></i><small>Cetak</small>
                                            </a>
                                            <a href="{{ route('transaksi.retur-penjualan.detail-retur', $penjualan->id) }}" class="btn btn-outline-info btn-sm flex-fill text-center">
                                                <i class="fas fa-undo d-block mb-1"></i><small>Retur</small>
                                            </a>
                                            <div class="flex-fill">
                                                <form action="{{ route('transaksi.penjualan.destroy', $penjualan->id) }}" method="POST" onsubmit="return confirm('Yakin ingin hapus transaksi ini?')" class="h-100">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 h-100 text-center">
                                                        <i class="fas fa-trash d-block mb-1"></i><small>Hapus</small>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Struk Tab --}}
                <div class="tab-pane fade" id="struk-pane" role="tabpanel" aria-labelledby="struk-tab">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-header text-center">
                                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Struk Penjualan</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="printStruk()">
                                        <i class="fas fa-print me-1"></i>Print (Ctrl+P)
                                    </button>
                                    <small class="text-muted d-block mt-1">Tab struk berhasil dimuat</small>
                                </div>
                                <div class="card-body d-flex justify-content-center p-2">
                                    <div id="strukContent" class="struk-container">
                                        {{-- Header Perusahaan --}}
                                        <div class="struk-header">
                                            @php
                                                // Get company data with fallback
                                                $dataPerusahaan = (object)[
                                                    'nama' => 'TOKO ANDA',
                                                    'alamat' => 'Alamat Toko',
                                                    'telepon' => '021-12345678'
                                                ];
                                                
                                                try {
                                                    $company = \App\Models\Perusahaan::select('nama', 'alamat', 'telepon')->first();
                                                    if ($company) {
                                                        $dataPerusahaan = $company;
                                                    }
                                                } catch (Exception $e) {
                                                    // Use fallback data
                                                }
                                            @endphp
                                            <div class="company-name">{{ $dataPerusahaan->nama ?? 'TOKO ANDA' }}</div>
                                            <div class="company-info">
                                                {{ $dataPerusahaan->alamat ?? 'Alamat Toko' }}<br>
                                                Telp: {{ $dataPerusahaan->telepon ?? '021-12345678' }}
                                            </div>
                                        </div>
                                        
                                        {{-- Info Transaksi --}}
                                        <div class="transaction-info">
                                            <div class="info-row">
                                                <span>No. Transaksi:</span>
                                                <span>{{ $penjualan->nomor_penjualan ?? 'PJ-' . str_pad($penjualan->id, 4, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span>Tanggal:</span>
                                                <span>{{ optional($penjualan->tanggal)->format('d/m/Y H:i') ?? date('d/m/Y H:i') }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span>Kasir:</span>
                                                <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="divider">================================</div>
                                        
                                        {{-- Items --}}
                                        <div class="items-section">
                                            @if($detailCount > 0)
                                                @foreach($penjualan->details as $detail)
                                                    @php
                                                        $subtotal = $detail->subtotal ?? ($detail->jumlah * $detail->harga_satuan - ($detail->diskon_nominal ?? 0));
                                                    @endphp
                                                    <div class="item">
                                                        <div class="item-name">{{ $detail->produk->nama_produk ?? '-' }}</div>
                                                        <div class="item-detail">
                                                            <span>{{ number_format($detail->jumlah, 0) }} x Rp {{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</span>
                                                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                                        </div>
                                                        @if(($detail->diskon_nominal ?? 0) > 0)
                                                            <div class="item-discount">
                                                                <span>Diskon:</span>
                                                                <span>-Rp {{ number_format($detail->diskon_nominal, 0, ',', '.') }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                @php
                                                    $hdrHarga = $penjualan->harga_satuan;
                                                    if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                                        $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                                                    }
                                                @endphp
                                                <div class="item">
                                                    <div class="item-name">{{ $penjualan->produk?->nama_produk ?? '-' }}</div>
                                                    <div class="item-detail">
                                                        <span>{{ number_format($penjualan->jumlah, 0) }} x Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}</span>
                                                        <span>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
                                                    </div>
                                                    @if(($penjualan->diskon_nominal ?? 0) > 0)
                                                        <div class="item-discount">
                                                            <span>Diskon:</span>
                                                            <span>-Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Summary --}}
                                        <div class="summary-section">
                                            <div class="summary-row">
                                                <span>Subtotal:</span>
                                                <span>Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</span>
                                            </div>
                                            @if($totalDiskon > 0)
                                                <div class="summary-row">
                                                    <span>Total Diskon:</span>
                                                    <span>-Rp {{ number_format($totalDiskon, 0, ',', '.') }}</span>
                                                </div>
                                            @endif
                                            <div class="total-row">
                                                <span>TOTAL:</span>
                                                <span>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        
                                        {{-- Payment Info --}}
                                        <div class="payment-info">
                                            <div class="info-row">
                                                <span>Pembayaran:</span>
                                                <span>
                                                    @switch($penjualan->payment_method ?? 'cash')
                                                        @case('cash') Tunai @break
                                                        @case('transfer') Transfer Bank @break
                                                        @case('credit') Kredit @break
                                                        @default Tunai
                                                    @endswitch
                                                </span>
                                            </div>
                                        </div>
                                        
                                        {{-- Footer --}}
                                        <div class="footer">
                                            Terima kasih atas kunjungan Anda<br>
                                            <br>
                                            {{ date('d/m/Y H:i:s') }}
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

    {{-- Row 3: Riwayat Retur --}}
    @if($penjualan->returPenjualans->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-undo me-2"></i>Riwayat Retur</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nomor Retur</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Produk</th>
                                    <th class="text-end">Total Retur</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penjualan->returPenjualans as $retur)
                                <tr>
                                    <td><strong>{{ $retur->nomor_retur }}</strong></td>
                                    <td>{{ $retur->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $retur->jenis_retur === 'tukar_barang' ? 'Tukar Barang' : 'Refund' }}</td>
                                    <td>
                                        @foreach($retur->detailReturPenjualans as $d)
                                            <div>{{ $d->produk?->nama_produk }} ({{ (int)$d->qty_retur }} pcs)</div>
                                        @endforeach
                                    </td>
                                    <td class="text-end">Rp {{ number_format($retur->total_retur, 0, ',', '.') }}</td>
                                    <td>
                                    span class="badge {{ $retur->status === 'selesai' ? 'bg-success' : ($retur->status === 'lunas' ? 'bg-info' : 'bg-warning') }}">
                                            {{ ucfirst($retur->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

        </div>
    </div>
    @endif
</div>

<style>
.struk-container {
    width: 280px;
    background: white;
    padding: 15px;
    font-family: 'Courier New', monospace;
    font-size: 11px;
    line-height: 1.4;
    border: 1px solid #ddd;
    margin: 0 auto;
}

.struk-header {
    text-align: center;
    margin-bottom: 10px;
    border-bottom: 1px dashed #333;
    padding-bottom: 8px;
}

.company-name {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 3px;
    text-transform: uppercase;
}

.company-info {
    font-size: 9px;
    line-height: 1.2;
    color: #555;
}

.divider {
    text-align: center;
    margin: 8px 0;
    font-size: 10px;
    color: #666;
}

.transaction-info {
    margin-bottom: 8px;
    font-size: 10px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2px;
}

.items-section {
    margin-bottom: 8px;
}

.item {
    margin-bottom: 6px;
}

.item-name {
    font-weight: bold;
    font-size: 10px;
    margin-bottom: 1px;
}

.item-detail {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
}

.item-discount {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
    color: #666;
    font-style: italic;
}

.summary-section {
    border-top: 1px dashed #333;
    padding-top: 6px;
    margin-bottom: 8px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2px;
    font-size: 10px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 12px;
    border-top: 1px solid #333;
    padding-top: 4px;
    margin-top: 4px;
}

.payment-info {
    margin-bottom: 8px;
    font-size: 10px;
}

.footer {
    text-align: center;
    border-top: 1px dashed #333;
    padding-top: 8px;
    font-size: 8px;
    color: #666;
    line-height: 1.3;
}

/* Print preparation styles */
body.printing {
    overflow: hidden;
}

body.printing * {
    -webkit-print-color-adjust: exact !important;
    color-adjust: exact !important;
}

/* Ensure struk is ready for print */
.tab-pane#struk-pane.active .struk-container {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
}
</style>

<script>
function showStrukTab() {
    try {
        // Activate struk tab
        const strukTab = new bootstrap.Tab(document.getElementById('struk-tab'));
        strukTab.show();
    } catch (error) {
        console.error('Error showing struk tab:', error);
        alert('Terjadi kesalahan saat membuka tab struk. Silakan refresh halaman.');
    }
}

function printStruk() {
    try {
        // Get the struk content
        const strukContent = document.getElementById('strukContent');
        if (!strukContent) {
            alert('Konten struk tidak ditemukan');
            return;
        }

        // Create a new window for printing
        const printWindow = window.open('', '_blank', 'width=400,height=600,scrollbars=yes');
        
        // Write the print content
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Struk Penjualan</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    
                    body {
                        font-family: 'Courier New', monospace;
                        padding: 10px;
                        background: white;
                    }
                    
                    .struk-container {
                        width: 280px;
                        margin: 0 auto;
                        font-size: 11px;
                        line-height: 1.4;
                    }
                    
                    .struk-header {
                        text-align: center;
                        margin-bottom: 10px;
                        border-bottom: 1px dashed #333;
                        padding-bottom: 8px;
                    }
                    
                    .company-name {
                        font-size: 14px;
                        font-weight: bold;
                        margin-bottom: 3px;
                        text-transform: uppercase;
                    }
                    
                    .company-info {
                        font-size: 9px;
                        line-height: 1.2;
                        color: #555;
                    }
                    
                    .divider {
                        text-align: center;
                        margin: 8px 0;
                        font-size: 10px;
                        color: #666;
                    }
                    
                    .transaction-info {
                        margin-bottom: 8px;
                        font-size: 10px;
                    }
                    
                    .info-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 2px;
                    }
                    
                    .items-section {
                        margin-bottom: 8px;
                    }
                    
                    .item {
                        margin-bottom: 6px;
                    }
                    
                    .item-name {
                        font-weight: bold;
                        font-size: 10px;
                        margin-bottom: 1px;
                    }
                    
                    .item-detail {
                        display: flex;
                        justify-content: space-between;
                        font-size: 9px;
                    }
                    
                    .item-discount {
                        display: flex;
                        justify-content: space-between;
                        font-size: 9px;
                        color: #666;
                        font-style: italic;
                    }
                    
                    .summary-section {
                        border-top: 1px dashed #333;
                        padding-top: 6px;
                        margin-bottom: 8px;
                    }
                    
                    .summary-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 2px;
                        font-size: 10px;
                    }
                    
                    .total-row {
                        display: flex;
                        justify-content: space-between;
                        font-weight: bold;
                        font-size: 12px;
                        border-top: 1px solid #333;
                        padding-top: 4px;
                        margin-top: 4px;
                    }
                    
                    .payment-info {
                        margin-bottom: 8px;
                        font-size: 10px;
                    }
                    
                    .footer {
                        text-align: center;
                        border-top: 1px dashed #333;
                        padding-top: 8px;
                        font-size: 8px;
                        color: #666;
                        line-height: 1.3;
                    }
                    
                    @media print {
                        @page {
                            size: 80mm auto;
                            margin: 5mm;
                        }
                        
                        body {
                            margin: 0;
                            padding: 0;
                        }
                        
                        .struk-container {
                            width: 100%;
                            margin: 0;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="struk-container">
                    ${strukContent.innerHTML}
                </div>
            </body>
            </html>
        `);
        
        printWindow.document.close();
        
        // Wait for content to load, then print and close
        printWindow.onload = function() {
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        };
        
    } catch (error) {
        console.error('Error printing struk:', error);
        alert('Terjadi kesalahan saat mencetak. Silakan coba lagi.');
    }
}

// Add keyboard shortcut for print when on struk tab
document.addEventListener('keydown', function(e) {
    try {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            const strukTab = document.getElementById('struk-pane');
            if (strukTab && strukTab.classList.contains('active')) {
                e.preventDefault();
                printStruk();
            }
        }
    } catch (error) {
        console.error('Error handling keyboard shortcut:', error);
    }
});

// Prevent any potential 404 requests
window.addEventListener('error', function(e) {
    if (e.target && e.target.src && e.target.src.includes('404')) {
        console.warn('Blocked 404 request:', e.target.src);
        e.preventDefault();
    }
});
</script>

@endsection
            </div>