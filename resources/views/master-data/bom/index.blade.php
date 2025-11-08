@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-diagram-3"></i> Bill of Materials (BOM)</h3>

    @if(session('success') || request()->has('highlight'))
        @php
            $bomId = request()->get('highlight') ?? session('bom_id');
        @endphp
        
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') ?? 'BOM berhasil disimpan' }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Scroll ke BOM yang baru ditambahkan
                const bomRow = document.getElementById('bom-{{ $bomId }}');
                if (bomRow) {
                    // Tambahkan class highlight
                    bomRow.classList.add('table-success');
                    // Scroll ke elemen
                    setTimeout(() => {
                        bomRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 100);
                    
                    // Hapus highlight setelah 5 detik
                    setTimeout(() => {
                        bomRow.classList.remove('table-success');
                    }, 5000);
                }
            });
        </script>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('master-data.bom.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label for="produkSelect" class="form-label text-white">Pilih Produk</label>
                        <select name="produk_id" id="produkSelect" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Produk --</option>
                            @foreach($produks as $p)
                                <option value="{{ $p->id }}" {{ (isset($selectedProductId) && (int)$selectedProductId === (int)$p->id) ? 'selected' : '' }}>{{ $p->nama_produk }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 text-end">
                        @if(isset($selectedProductId))
                            <a href="{{ route('master-data.bom.create', ['produk_id' => $selectedProductId]) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Tambah BOM
                            </a>
                        @else
                            <a href="#" class="btn btn-primary disabled" title="Pilih produk terlebih dahulu">
                                <i class="bi bi-plus-circle"></i> Tambah BOM
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($boms->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode BOM</th>
                                <th>Produk</th>
                                <th class="text-end">Total Biaya Produksi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($boms as $bom)
                                <tr id="bom-{{ $bom->id }}" class="{{ session('bom_id') == $bom->id ? 'table-success' : '' }}">
                                    <td>{{ $bom->kode_bom ?? 'BOM-' . str_pad($bom->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $bom->produk->nama_produk }}</td>
                                    <td class="text-end">Rp {{ number_format($bom->total_biaya_produksi, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('master-data.bom.show', $bom->id) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('master-data.bom.edit', $bom->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('master-data.bom.destroy', $bom->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus BOM ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $boms->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <p class="mb-0 text-white">Tidak ada data BOM yang ditemukan.</p>
                    @if(isset($selectedProductId))
                        <a href="{{ route('master-data.bom.create', ['produk_id' => $selectedProductId]) }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle"></i> Buat BOM Baru
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-submit filter form when product is selected
    document.addEventListener('DOMContentLoaded', function() {
        const produkSelect = document.getElementById('produkSelect');
        
        if (produkSelect.value) {
            const event = new Event('change');
            produkSelect.dispatchEvent(event);
        }
        
        produkSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
</script>
@endpush

@endsection
