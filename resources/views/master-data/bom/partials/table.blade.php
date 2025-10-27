<h5 class="mt-3">BOM Produk: {{ $produk->nama_produk }}</h5>

<table class="table table-bordered table-hover mt-2">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Nama Bahan</th>
            <th>Jumlah</th>
            <th>Satuan</th>
            <th>Harga Satuan</th>
            <th>Total Biaya</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->nama_bahan }}</td>
            <td>{{ $item->jumlah }}</td>
            <td>{{ $item->satuan }}</td>
            <td>{{ number_format($item->harga_satuan,0) }}</td>
            <td>{{ number_format($item->total_biaya,0) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5">Total Bahan</th>
            <th>{{ number_format($total_bahan,0) }}</th>
        </tr>
        <tr>
            <th colspan="5">BTKL</th>
            <th>{{ number_format($btkl_sum,0) }}</th>
        </tr>
        <tr>
            <th colspan="5">BOP</th>
            <th>{{ number_format($bop_sum,0) }}</th>
        </tr>
        <tr>
            <th colspan="5">Grand Total</th>
            <th>{{ number_format($grand_total,0) }}</th>
        </tr>
        <tr>
            <th colspan="5">Harga Jual (60% Keuntungan)</th>
            <th>{{ number_format($harga_jual,0) }}</th>
        </tr>
    </tfoot>
</table>
