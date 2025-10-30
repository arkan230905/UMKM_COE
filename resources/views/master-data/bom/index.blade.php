@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-diagram-3"></i> Bill of Materials (BOM)</h3>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('master-data.bom.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label for="produkSelect" class="form-label">Pilih Produk</label>
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
                                <th class="text-end">Total Biaya</th>
                                <th class="text-end">Keuntungan</th>
                                <th class="text-end">Harga Jual</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($boms as $bom)
                                <tr>
                                    <td>{{ $bom->kode_bom }}</td>
                                    <td>{{ $bom->produk->nama_produk }}</td>
                                    <td class="text-end">@money($bom->total_biaya)</td>
                                    <td class="text-end">{{ $bom->persentase_keuntungan }}%</td>
                                    <td class="text-end fw-bold">@money($bom->harga_jual)</td>
                                    <td class="text-center">
                                        <a href="{{ route('master-data.bom.show', $bom->id) }}" class="btn btn-sm btn-info text-white" title="Lihat">
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
                    <p class="text-muted mb-0">Tidak ada data BOM yang ditemukan.</p>
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
