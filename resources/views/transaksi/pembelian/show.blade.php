@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Pembelian</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Tanggal:</strong> {{ $pembelian->tanggal?->format('d-m-Y') }}</div>
                <div class="col-md-4"><strong>Vendor:</strong> {{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
                <div class="col-md-4"><strong>Total:</strong> Rp {{ number_format($pembelian->total_harga ?? 0,0,',','.') }}</div>
                <div class="col-md-4"><strong>Pembayaran:</strong> {{ ($pembelian->payment_method ?? 'cash')==='credit' ? 'Kredit' : 'Tunai' }}</div>
            </div>
        </div>
    </div>

    <!-- Detail Bahan Baku -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Detail Bahan Baku</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>Nama Bahan Baku</th>
                            <th class="text-end">Kuantitas</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga per Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalBahanBaku = 0; @endphp
                        @foreach(($pembelian->details ?? [])->where('bahan_baku_id', '!=', null) as $i => $d)
                        @php 
                            $subtotal = ($d->jumlah ?? 0) * ($d->harga_satuan ?? 0);
                            $totalBahanBaku += $subtotal;
                        @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $d->bahanBaku ? $d->bahanBaku->nama_bahan : 'Unknown' }}</td>
                            <td class="text-end">{{ number_format($d->jumlah, 0, '.', '') }}</td>
                            <td>
                                @php
                                    // Logic satuan yang sama dengan pegawai-pembelian
                                    $satuanItem = 'unit';
                                    
                                    // Jika item diinput sebagai bahan baku (berdasarkan relation yang ada)
                                    if ($d->bahan_baku_id && $d->bahanBaku) {
                                        // Prioritas: detail->satuan, lalu relation->satuanRelation->nama
                                        $satuanItem = $d->satuan ?: ($d->bahanBaku->satuan->nama ?? 'unit');
                                    }
                                    // Fallback jika relation tidak ada
                                    elseif ($d->bahan_baku_id) {
                                        $satuanItem = $d->satuan ?: 'unit';
                                    }
                                @endphp
                                {{ $satuanItem }}
                            </td>
                            <td class="text-end">Rp {{ number_format($d->harga_satuan,0,',','.') }}</td>
                            <td class="text-end">Rp {{ number_format($subtotal,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($totalBahanBaku > 0)
                    <tfoot>
                        <tr class="table-success">
                            <th colspan="5" class="text-end">Total Bahan Baku:</th>
                            <th class="text-end">Rp {{ number_format($totalBahanBaku,0,',','.') }}</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Detail Bahan Pendukung -->
    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Detail Bahan Pendukung</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>Nama Bahan Pendukung</th>
                            <th class="text-end">Kuantitas</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga per Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalBahanPendukung = 0; @endphp
                        @foreach(($pembelian->details ?? [])->where('bahan_pendukung_id', '!=', null) as $i => $d)
                        @php 
                            $subtotal = ($d->jumlah ?? 0) * ($d->harga_satuan ?? 0);
                            $totalBahanPendukung += $subtotal;
                        @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $d->bahanPendukung ? $d->bahanPendukung->nama_bahan : 'Unknown' }}</td>
                            <td class="text-end">{{ number_format($d->jumlah, 0, '.', '') }}</td>
                            <td>
                                @php
                                    // Logic satuan yang sama dengan pegawai-pembelian
                                    $satuanItem = 'unit';
                                    
                                    // Jika item diinput sebagai bahan pendukung (berdasarkan relation yang ada)
                                    if ($d->bahan_pendukung_id && $d->bahanPendukung) {
                                        // Prioritas: detail->satuan, lalu relation->satuanRelation->nama
                                        $satuanItem = $d->satuan ?: ($d->bahanPendukung->satuanRelation->nama ?? 'unit');
                                    }
                                    // Fallback jika relation tidak ada
                                    elseif ($d->bahan_pendukung_id) {
                                        $satuanItem = $d->satuan ?: 'unit';
                                    }
                                @endphp
                                {{ $satuanItem }}
                            </td>
                            <td class="text-end">Rp {{ number_format($d->harga_satuan,0,',','.') }}</td>
                            <td class="text-end">Rp {{ number_format($subtotal,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($totalBahanPendukung > 0)
                    <tfoot>
                        <tr class="table-info">
                            <th colspan="5" class="text-end">Total Bahan Pendukung:</th>
                            <th class="text-end">Rp {{ number_format($totalBahanPendukung,0,',','.') }}</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Ringkasan Pembelian</h6>
                    <div class="d-flex justify-content-between">
                        <span>Total Bahan Baku:</span>
                        <strong class="text-success">Rp {{ number_format($totalBahanBaku,0,',','.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total Bahan Pendukung:</span>
                        <strong class="text-info">Rp {{ number_format($totalBahanPendukung,0,',','.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total Pembelian:</span>
                        <strong class="text-primary">Rp {{ number_format($totalBahanBaku + $totalBahanPendukung,0,',','.') }}</strong>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex justify-content-between">
                        <span>Terbayar:</span>
                        <strong>Rp {{ number_format($pembelian->terbayar ?? 0,0,',','.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Sisa Pembayaran:</span>
                        <strong>Rp {{ number_format($pembelian->sisa_pembayaran ?? 0,0,',','.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Status:</span>
                        <strong>{{ $pembelian->status ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
