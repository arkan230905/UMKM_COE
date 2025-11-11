@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit BOM: {{ $produk->nama_produk }}</h3>

    <form action="{{ route('master-data.bom.update', $bom->id) }}" method="POST" id="bomForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="produk_id" value="{{ $bom->produk_id }}">

        <div class="card mb-4">
            <div class="card-header" style="background-color: #2c3e50 !important; border-bottom: 1px solid rgba(0,0,0,.125) !important;">
                <h5 class="mb-0" style="color: #ffffff !important; margin: 0 !important;">Bahan Baku</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="bomTable">
                        <thead class="table-light">
                            <tr>
                                <th>Bahan Baku</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Harga Utama</th>
                                <th>Harga 1</th>
                                <th>Harga 2</th>
                                <th>Harga 3</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bomDetails as $detail)
                            @php
                                $bahanBaku = $detail->bahanBaku;
                                $satuanNama = $bahanBaku->satuan->nama ?? 'KG';
                                $hargaKg = $bahanBaku->harga_satuan;
                                $hargaHg = $hargaKg * 0.1;
                                $hargaDag = $hargaKg * 0.01;
                                $hargaGr = $hargaKg / 1000;
                            @endphp
                            <tr>
                                <td>
                                    <select name="bahan_baku_id[]" class="form-select bahanSelect" required>
                                        <option value="">-- Pilih Bahan --</option>
                                        @foreach($bahanBakus as $bahan)
                                            @php
                                                $bahanSatuanNama = $bahan->satuan->nama ?? 'KG';
                                                $namaBahan = $bahan->nama ?? $bahan->nama_bahan ?? 'Bahan Tanpa Nama';
                                                $namaBahan .= ' (' . $bahanSatuanNama . ')';
                                            @endphp
                                            <option value="{{ $bahan->id }}" 
                                                data-satuan="{{ $bahanSatuanNama }}"
                                                data-harga-kg="{{ $bahan->harga_satuan }}"
                                                data-harga-hg="{{ $bahan->harga_satuan * 0.1 }}"
                                                data-harga-dag="{{ $bahan->harga_satuan * 0.01 }}"
                                                data-harga-gr="{{ $bahan->harga_satuan / 1000 }}"
                                                data-satuan-utama="{{ $bahanSatuanNama }}"
                                                @if($bahan->id == $detail->bahan_baku_id) selected @endif>
                                                {{ $namaBahan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="jumlah[]" class="form-control jumlahInput" 
                                           value="{{ $detail->jumlah }}" min="0.01" step="0.01" required>
                                </td>
                                <td>
                                    <select name="satuan[]" class="form-select form-select-sm satuanSelect">
                                        <option value="KG" {{ $detail->satuan == 'KG' ? 'selected' : '' }}>KG</option>
                                        <option value="HG" {{ $detail->satuan == 'HG' ? 'selected' : '' }}>HG</option>
                                        <option value="DAG" {{ $detail->satuan == 'DAG' ? 'selected' : '' }}>DAG</option>
                                        <option value="GR" {{ $detail->satuan == 'GR' ? 'selected' : '' }}>GR</option>
                                    </select>
                                </td>
                                <td class="text-center harga-utama">
                                    {{ number_format($hargaKg, 0, ',', '.') }}<br>
                                    <small class="text-muted">/KG</small>
                                </td>
                                <td class="text-center harga-1">
                                    {{ number_format($hargaHg, 0, ',', '.') }}<br>
                                    <small class="text-muted">/HG</small>
                                </td>
                                <td class="text-center harga-2">
                                    {{ number_format($hargaDag, 0, ',', '.') }}<br>
                                    <small class="text-muted">/DAG</small>
                                </td>
                                <td class="text-center harga-3">
                                    {{ number_format($hargaGr, 0, ',', '.') }}<br>
                                    <small class="text-muted">/GR</small>
                                </td>
                                <td>
                                    <select name="kategori[]" class="form-select form-select-sm">
                                        <option value="BOP" {{ $detail->kategori == 'BOP' ? 'selected' : '' }}>BOP</option>
                                        <option value="BTKL" {{ $detail->kategori == 'BTKL' ? 'selected' : '' }}>BTKL</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger removeRow">Hapus</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-2" id="addRow">Tambah Baris</button>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Update BOM</button>
            <a href="{{ route('master-data.bom.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

@include('master-data.bom.js')
@endsection
