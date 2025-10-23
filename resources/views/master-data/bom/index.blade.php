@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Bill of Materials (BOM)</h3>

    <div class="row mb-3">
        <!-- Pilih Produk -->
        <div class="col-md-6">
            <label for="produk_id" class="form-label">Pilih Produk</label>
            <select id="produk_id" class="form-control" onchange="location = this.value;">
                <option value="">-- Pilih Produk --</option>
                @foreach($produks as $produk)
                    <option value="{{ route('master-data.bom.index', ['produk_id' => $produk->id]) }}"
                        @if(isset($selectedProdukId) && $selectedProdukId == $produk->id) selected @endif>
                        {{ $produk->nama_produk }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Tombol Tambah BOM -->
        <div class="col-md-6 d-flex align-items-end">
            <a href="{{ route('master-data.bom.create') }}" class="btn btn-primary w-100">
                <i class="bi bi-plus-circle"></i> Tambah BOM
            </a>
        </div>
    </div>

    <!-- Tabel BOM -->
    @if(isset($selectedProdukId) && isset($bomItems) && count($bomItems) > 0)
        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Bahan Baku</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Harga Utama</th>
                    <th colspan="3">Detail Harga (Satuan Lebih Kecil)</th>
                    <th>Total Harga</th>
                </tr>
                <tr>
                    <th colspan="4"></th>
                    <th>Sub 1</th>
                    <th>Sub 2</th>
                    <th>Sub 3</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($bomItems as $item)
                    <tr>
                        <td>{{ $item->nama_bahan }}</td>
                        <td>{{ $item->jumlah }}</td>
                        <td>{{ $item->satuan }}</td>
                        <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }} / {{ $item->satuan }}</td>

                        {{-- Detail harga untuk satuan lebih kecil --}}
                        @php
                            // Misal konversi satuan lebih kecil secara manual
                            $detailHarga = [];
                            if (strtolower($item->satuan) == 'kg') {
                                $detailHarga = [
                                    'hg' => $item->harga_satuan / 10,
                                    'g'  => $item->harga_satuan / 1000,
                                    'mg' => $item->harga_satuan / 1000000,
                                ];
                            } elseif (strtolower($item->satuan) == 'liter') {
                                $detailHarga = [
                                    'dL' => $item->harga_satuan / 10,
                                    'cL' => $item->harga_satuan / 100,
                                    'mL' => $item->harga_satuan / 1000,
                                ];
                            }
                        @endphp

                        @foreach($detailHarga as $satuanKecil => $hargaKecil)
                            <td>Rp {{ number_format($hargaKecil, 2, ',', '.') }} / {{ $satuanKecil }}</td>
                        @endforeach

                        {{-- Jika kurang dari 3 satuan kecil --}}
                        @for($i = count($detailHarga); $i < 3; $i++)
                            <td>-</td>
                        @endfor

                        <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif(isset($selectedProdukId))
        <p class="text-center text-muted">Belum ada BOM untuk produk ini.</p>
    @endif
</div>
@endsection
