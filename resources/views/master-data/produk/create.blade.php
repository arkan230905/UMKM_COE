@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tambah Produk</h1>
        <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('master-data.produk.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                    <input type="text" name="nama_produk" id="nama_produk" 
                           class="form-control" value="{{ old('nama_produk') }}" required>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" 
                              class="form-control">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="margin_percent" class="form-label">Presentase Keuntungan (%)</label>
                    <input type="number" step="0.01" name="margin_percent" 
                           class="form-control" value="{{ old('margin_percent', 30) }}">
                    <small class="text-muted">Harga jual dihitung otomatis dari Harga BOM × (1 + Margin%).</small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('produkForm');
    
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-arrow-repeat fa-spin me-1"></i> Menyimpan...';
            }
        });
    }
    
    const marginInput = document.querySelector('input[name="margin_percent"]');
    
    // Contoh fungsi untuk menghitung harga jual
    function hitungHargaJual() {
        // Logika perhitungan harga jual bisa ditambahkan di sini
        // Misalnya: harga_jual = harga_bom * (1 + (margin_percent / 100))
    }
    
    // Panggil fungsi saat nilai margin berubah
    if (marginInput) {
        marginInput.addEventListener('change', hitungHargaJual);
        marginInput.addEventListener('keyup', hitungHargaJual);
    }
});
</script>
@endpush

<style>
    .form-control, .form-select, .form-control:focus, .form-select:focus {
        background-color: #1e1e2f !important;
        border-color: #2d2d3a !important;
        color: #ffffff !important;
    }
    
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(108, 99, 255, 0.25) !important;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    option {
        background-color: #1e1e2f;
        color: #ffffff;
    }
    
    .card {
        background-color: #222232;
        border: 1px solid #2d2d3a;
    }
    
    .text-muted {
        color: #8a8a9a !important;
    }
</style>
@endsection
