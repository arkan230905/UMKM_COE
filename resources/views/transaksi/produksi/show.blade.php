@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Detail Produksi</h3>

    <div class="card mb-3">
        <div class="card-body">
            <div><strong>Produk:</strong> {{ $produksi->produk->nama_produk }}</div>
            <div><strong>Tanggal:</strong> {{ $produksi->tanggal }}</div>
            <div><strong>Qty Produksi:</strong> {{ number_format($produksi->qty_produksi, 0, ',', '.') }} unit</div>
            <div><strong>Total Bahan:</strong> Rp {{ number_format($produksi->total_bahan,0,',','.') }}</div>
            <div><strong>BTKL:</strong> Rp {{ number_format($produksi->total_btkl,0,',','.') }}</div>
            <div><strong>BOP:</strong> Rp {{ number_format($produksi->total_bop,0,',','.') }}</div>
            <div><strong>Total Biaya:</strong> Rp {{ number_format($produksi->total_biaya,0,',','.') }}</div>
        </div>
    </div>

    <h5>Bahan Terpakai</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nama Bahan</th>
                <th>Resep (Total)</th>
                <th>Konversi ke Satuan Bahan</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produksi->details as $d)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @if($d->bahan_baku_id)
                            {{ $d->bahanBaku->nama_bahan }}
                            <small class="text-muted">(Bahan Baku)</small>
                        @elseif($d->bahan_pendukung_id)
                            {{ $d->bahanPendukung->nama_bahan }}
                            <small class="text-muted">(Bahan Pendukung)</small>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ rtrim(rtrim(number_format($d->qty_resep,4,',','.'),'0'),',') }} {{ $d->satuan_resep }}</td>
                    <td>{{ rtrim(rtrim(number_format($d->qty_konversi,4,',','.'),'0'),',') }} 
                    @php
                        // Logic satuan yang sama dengan pegawai-pembelian
                        $satuanItem = 'unit';
                        
                        // Jika item diinput sebagai bahan baku (berdasarkan relation yang ada)
                        if ($d->bahan_baku_id && $d->bahanBaku) {
                            // Prioritas: detail->satuan, lalu relation->satuan->nama
                            $satuanItem = $d->satuan ?: ($d->bahanBaku->satuan->nama ?? $d->bahanBaku->satuan ?? 'unit');
                        }
                        // Jika item diinput sebagai bahan pendukung
                        elseif ($d->bahan_pendukung_id && $d->bahanPendukung) {
                            $satuanItem = $d->satuan ?: ($d->bahanPendukung->satuan->nama ?? $d->bahanPendukung->satuan ?? 'unit');
                        }
                        // Fallback jika relation tidak ada
                        else {
                            $satuanItem = $d->satuan ?: 'unit';
                        }
                    @endphp
                    {{ $satuanItem }}</td>
                    <td>Rp {{ number_format($d->harga_satuan,0,',','.') }} / 
                    @php
                        // Logic satuan yang sama untuk harga satuan
                        $satuanHarga = 'unit';
                        
                        if ($d->bahan_baku_id && $d->bahanBaku) {
                            $satuanHarga = $d->satuan ?: ($d->bahanBaku->satuan->nama ?? $d->bahanBaku->satuan ?? 'unit');
                        }
                        elseif ($d->bahan_pendukung_id && $d->bahanPendukung) {
                            $satuanHarga = $d->satuan ?: ($d->bahanPendukung->satuan->nama ?? $d->bahanPendukung->satuan ?? 'unit');
                        }
                        else {
                            $satuanHarga = $d->satuan ?: 'unit';
                        }
                    @endphp
                    {{ $satuanHarga }}</td>
                    <td>Rp {{ number_format($d->subtotal,0,',','.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Detail BTKL -->
    <h5 class="mt-4">Total BTKL yang Bekerja</h5>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="text-primary">Rp {{ number_format($produksi->total_btkl, 0, ',', '.') }}</h6>
                    <small class="text-muted">Total biaya tenaga kerja langsung untuk {{ number_format($produksi->qty_produksi, 0, ',', '.') }} unit produksi</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail BOP -->
    <h5 class="mt-4">Total BOP yang Dijalankan</h5>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="text-warning">Rp {{ number_format($produksi->total_bop, 0, ',', '.') }}</h6>
                    <small class="text-muted">Total biaya overhead pabrik untuk {{ number_format($produksi->qty_produksi, 0, ',', '.') }} unit produksi</small>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'production_material', 'ref_id' => $produksi->id]) }}" class="btn btn-outline-primary btn-sm">Lihat Jurnal (Material→WIP)</a>
        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'production_labor_overhead', 'ref_id' => $produksi->id]) }}" class="btn btn-outline-primary btn-sm">Lihat Jurnal (BTKL/BOP→WIP)</a>
        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'production_finish', 'ref_id' => $produksi->id]) }}" class="btn btn-outline-primary btn-sm">Lihat Jurnal (WIP→Barang Jadi)</a>
        <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
