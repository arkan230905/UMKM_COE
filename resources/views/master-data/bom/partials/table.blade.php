<h5 class="mt-3">Harga Pokok Produksi Produk: {{ $produk->nama_produk }}</h5>

<form method="POST" action="{{ route('master-data.bom.updateByProduk', $produk->id) }}">
    @csrf

    <table class="table table-bordered table-hover mt-2">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nama Bahan</th>
                <th>Resep</th>
                <th>Konversi</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($breakdown ?? []) as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['nama_bahan'] }}</td>
                <td class="text-nowrap">
                    <div class="d-flex gap-2">
                        <input type="number" name="rows[{{ $row['bom_id'] }}][jumlah]" step="0.0001" min="0" value="{{ $row['qty_resep'] }}" class="form-control form-control-sm" style="max-width: 120px;">
                        <select name="rows[{{ $row['bom_id'] }}][satuan_resep]" class="form-select form-select-sm" style="max-width: 160px;">
                            <option value="{{ $row['satuan_resep'] }}" selected>{{ $row['satuan_resep'] }} (saat ini)</option>
                            <option value="">(ikuti satuan bahan)</option>
                            <option value="g">gram (g)</option>
                            <option value="kg">kilogram (kg)</option>
                            <option value="mg">miligram (mg)</option>
                            <option value="ml">mililiter (ml)</option>
                            <option value="sdt">sendok teh (sdt)</option>
                            <option value="sdm">sendok makan (sdm)</option>
                            <option value="cup">cup</option>
                            <option value="pcs">pcs</option>
                            <option value="buah">buah</option>
                            <option value="butir">butir</option>
                        </select>
                    </div>
                </td>
                <td>
                    {{ $row['konversi_ket'] }}:
                    {{ rtrim(rtrim(number_format($row['qty_konversi'], 4, ',', '.'), '0'), ',') }} {{ $row['satuan_bahan'] ?? '-' }}
                </td>
                <td>Rp {{ number_format($row['harga_satuan'], 0, ',', '.') }} / {{ $row['satuan_bahan'] ?? '-' }}</td>
                <td>Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">Total Bahan</th>
                <th>Rp {{ number_format($total_bahan, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="5">BTKL</th>
                <th>
                    <div class="input-group input-group-sm" style="max-width: 240px;">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="btkl" step="0.01" min="0" value="{{ $btkl_sum }}" class="form-control">
                    </div>
                </th>
            </tr>
            <tr>
                <th colspan="5">BOP</th>
                <th>
                    <div class="input-group input-group-sm" style="max-width: 240px;">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="bop" step="0.01" min="0" value="{{ $bop_sum }}" class="form-control">
                    </div>
                </th>
            </tr>
            <tr>
                <th colspan="5">Grand Total</th>
                <th>Rp {{ number_format($grand_total, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="6" class="text-end">
                    <button type="submit" class="btn btn-success btn-sm">Simpan Perubahan</button>
                </th>
            </tr>
        </tfoot>
    </table>
</form>
