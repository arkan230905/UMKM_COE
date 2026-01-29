@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Detail Produksi</h3>

    <div class="card mb-3">
        <div class="card-body">
            <div><strong>Produk:</strong> {{ $produksi->produk->nama_produk }}</div>
            <div><strong>Tanggal:</strong> {{ $produksi->tanggal }}</div>
            <div><strong>Qty Produksi:</strong> {{ rtrim(rtrim(number_format($produksi->qty_produksi,4,',','.'),'0'),',') }}</div>
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
                    <td>{{ $d->bahanBaku->nama_bahan }}</td>
                    <td>{{ rtrim(rtrim(number_format($d->qty_resep,4,',','.'),'0'),',') }} {{ $d->satuan_resep }}</td>
                    <td>{{ rtrim(rtrim(number_format($d->qty_konversi,4,',','.'),'0'),',') }} 
                    @php
                        // Logic satuan yang sama dengan pegawai-pembelian
                        $satuanItem = 'unit';
                        
                        // Jika item diinput sebagai bahan baku (berdasarkan relation yang ada)
                        if ($d->bahan_baku_id && $d->bahanBaku) {
                            // Prioritas: detail->satuan, lalu relation->satuan
                            $satuanItem = $d->satuan ?: ($d->bahanBaku->satuan ?? 'unit');
                        }
                        // Fallback jika relation tidak ada
                        elseif ($d->bahan_baku_id) {
                            $satuanItem = $d->satuan ?: 'unit';
                        }
                    @endphp
                    {{ $satuanItem }}</td>
                    <td>Rp {{ number_format($d->harga_satuan,0,',','.') }} / 
                    @php
                        // Logic satuan yang sama untuk harga satuan
                        $satuanHarga = 'unit';
                        
                        if ($d->bahan_baku_id && $d->bahanBaku) {
                            $satuanHarga = $d->satuan ?: ($d->bahanBaku->satuan ?? 'unit');
                        }
                        elseif ($d->bahan_baku_id) {
                            $satuanHarga = $d->satuan ?: 'unit';
                        }
                    @endphp
                    {{ $satuanHarga }}</td>
                    <td>Rp {{ number_format($d->subtotal,0,',','.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-between">
        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'production_material', 'ref_id' => $produksi->id]) }}" class="btn btn-outline-primary btn-sm">Lihat Jurnal (Material→WIP)</a>
        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'production_labor_overhead', 'ref_id' => $produksi->id]) }}" class="btn btn-outline-primary btn-sm">Lihat Jurnal (BTKL/BOP→WIP)</a>
        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'production_finish', 'ref_id' => $produksi->id]) }}" class="btn btn-outline-primary btn-sm">Lihat Jurnal (WIP→Barang Jadi)</a>
        <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
