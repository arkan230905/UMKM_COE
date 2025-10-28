@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-diagram-3"></i> Bill of Materials (BOM)</h3>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label for="produkSelect" class="form-label">Pilih Produk</label>
                    <select id="produkSelect" class="form-select">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($produks as $p)
                            <option value="{{ $p->id }}" {{ (isset($selectedProductId) && (int)$selectedProductId === (int)$p->id) ? 'selected' : '' }}>{{ $p->nama_produk }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 text-end">
                    <a href="{{ route('master-data.bom.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah BOM
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body" id="bomTable">
            <p class="text-muted mb-0">Pilih produk untuk melihat BOM.</p>
        </div>
    </div>
</div>

<script>
const produkSelect = document.getElementById('produkSelect');
const bomTable = document.getElementById('bomTable');

produkSelect.addEventListener('change', function() {
    const produkId = this.value;
    if (produkId) {
        bomTable.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        fetch(`/master-data/bom/by-produk/${produkId}`)
            .then(res => res.text())
            .then(html => bomTable.innerHTML = html)
            .catch(() => bomTable.innerHTML = '<p class="text-danger">Gagal memuat data BOM.</p>');
    } else {
        bomTable.innerHTML = '<p class="text-muted mb-0">Pilih produk untuk melihat BOM.</p>';
    }
});

// Auto-load if preselected
window.addEventListener('DOMContentLoaded', () => {
    if (produkSelect.value) {
        const event = new Event('change');
        produkSelect.dispatchEvent(event);
    }
});
</script>
@endsection
