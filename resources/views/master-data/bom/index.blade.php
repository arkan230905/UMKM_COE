@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Bill of Materials (BOM)</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="produk_id" class="form-label">Pilih Produk</label>
            <select id="produk_id" class="form-control" onchange="location = this.value;">
                <option value="">-- Pilih Produk --</option>
                @foreach($produks as $produk)
                    <option value="{{ route('master-data.bom.show', $produk->id) }}">
                        {{ $produk->nama_produk }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6 d-flex align-items-end">
            <a href="{{ route('master-data.bom.create') }}" class="btn btn-primary w-100">
                <i class="bi bi-plus-circle"></i> Tambah BOM
            </a>
        </div>
    </div>
</div>
@endsection
