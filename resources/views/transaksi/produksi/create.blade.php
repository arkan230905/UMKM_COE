@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">📦 Tambah Produksi</h4>
        </div>
        <div class="card-body">
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
                
                <!-- Form Input -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">🏷️ Produk</label>
                        <select name="produk_id" id="produk_id" class="form-select form-select-lg" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produks as $prod)
                                <option value="{{ $prod->id }}" data-harga="{{ $prod->harga_pokok ?? 0 }}">
                                    {{ $prod->nama_produk }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">📅 Tanggal</label>
                        <input type="date" name="tanggal" value="{{ now()->toDateString() }}" class="form-control form-control-lg" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">📊 Qty</label>
                        <input type="number" name="qty_produksi" id="qty_produksi" step="0.01" min="0.01" class="form-control form-control-lg" required>
                    </div>
                </div>

                <!-- Informasi BOM Produk -->
                <div class="card bg-light mb-4" id="bom-info" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📋 Informasi BOM Produk</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            <strong>Harga Pokok Produk:</strong> <span id="harga-pokok">Rp 0</span>
                            <br>
                            <small class="text-muted">Harga pokok akan dihitung berdasarkan BOM dan qty produksi</small>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('transaksi.produksi.index') }}" class="btn btn-secondary btn-lg">
                        ✖️ Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        💾 Simpan Produksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('produk_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const hargaPokok = selectedOption.getAttribute('data-harga');
    
    if (hargaPokok) {
        document.getElementById('bom-info').style.display = 'block';
        document.getElementById('harga-pokok').textContent = 'Rp ' + parseFloat(hargaPokok).toLocaleString('id-ID');
    } else {
        document.getElementById('bom-info').style.display = 'none';
    }
});
</script>
@endpush
@endsection
