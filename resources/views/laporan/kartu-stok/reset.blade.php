@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Reset Stok Produk
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('reset-produk-stok') }}" class="p-4">
                        @csrf
                        <div class="mb-3">
                            <label for="produk_id" class="form-label">Pilih Produk</label>
                            <select name="produk_id" id="produk_id" class="form-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->nama_produk }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="konfirmasi" id="konfirmasi" required>
                                <label class="form-check-label" for="konfirmasi">
                                    Saya setuju untuk mereset stok produk ini menjadi 0
                                </label>
                            </div>
                            <small class="text-danger">* Tindakan ini tidak dapat dibatalkan</small>
                        </div>
                        
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Perhatian:</strong> Stok produk akan direset ke 0 dan semua data pergerakan stok akan dihapus. 
                            Pastikan Anda sudah yakin sebelum melakukan tindakan ini.
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('kartu-stok') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i> Reset Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const produkSelect = document.getElementById('produk_id');
    const konfirmasiCheckbox = document.getElementById('konfirmasi');
    
    produkSelect.addEventListener('change', function() {
        const selectedOption = produkSelect.options[produkSelect.selectedIndex];
        if (selectedOption) {
            const productName = selectedOption.text.trim();
            const confirmation = `Apakah Anda yakin ingin mereset stok produk "${productName}" menjadi 0?`;
            konfirmasiCheckbox.parentElement.querySelector('.form-check-label').textContent = confirmation;
        }
    });
});
</script>
@endsection
