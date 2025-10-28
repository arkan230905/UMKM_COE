@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Tambah Produksi</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('transaksi.produksi.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Produk</label>
                <select name="produk_id" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($produks as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->nama_produk }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" value="{{ now()->toDateString() }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Qty Produksi</label>
                <input type="number" name="qty_produksi" step="0.0001" min="0.0001" class="form-control" required>
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection
