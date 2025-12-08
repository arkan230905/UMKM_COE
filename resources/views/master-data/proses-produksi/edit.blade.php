@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Proses Produksi</h1>
        <a href="{{ route('master-data.proses-produksi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('master-data.proses-produksi.update', $prosesProduksi) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">Kode Proses</label>
                            <input type="text" class="form-control" value="{{ $prosesProduksi->kode_proses }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Nama Proses <span class="text-danger">*</span></label>
                            <input type="text" name="nama_proses" class="form-control @error('nama_proses') is-invalid @enderror" 
                                   value="{{ old('nama_proses', $prosesProduksi->nama_proses) }}" required>
                            @error('nama_proses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Tarif BTKL <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="tarif_btkl" class="form-control" 
                                       value="{{ old('tarif_btkl', $prosesProduksi->tarif_btkl) }}" min="0" step="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Satuan BTKL <span class="text-danger">*</span></label>
                            <select name="satuan_btkl" class="form-select" required>
                                <option value="jam" {{ $prosesProduksi->satuan_btkl == 'jam' ? 'selected' : '' }}>Jam</option>
                                <option value="menit" {{ $prosesProduksi->satuan_btkl == 'menit' ? 'selected' : '' }}>Menit</option>
                                <option value="unit" {{ $prosesProduksi->satuan_btkl == 'unit' ? 'selected' : '' }}>Unit</option>
                                <option value="batch" {{ $prosesProduksi->satuan_btkl == 'batch' ? 'selected' : '' }}>Batch</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9">
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi', $prosesProduksi->deskripsi) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                       {{ $prosesProduksi->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3"><i class="fas fa-cogs"></i> Komponen BOP Default</h5>

                <div id="bop-container">
                    @forelse($prosesProduksi->prosesBops as $pb)
                    <div class="row bop-row mb-2">
                        <div class="col-md-5">
                            <select name="komponen_bop_id[]" class="form-select">
                                <option value="">-- Pilih Komponen BOP --</option>
                                @foreach($komponenBops as $komponen)
                                    <option value="{{ $komponen->id }}" 
                                            data-satuan="{{ $komponen->satuan }}" 
                                            data-tarif="{{ $komponen->tarif_per_satuan }}"
                                            {{ $pb->komponen_bop_id == $komponen->id ? 'selected' : '' }}>
                                        {{ $komponen->nama_komponen }} ({{ $komponen->satuan }} @ Rp {{ number_format($komponen->tarif_per_satuan, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="number" name="kuantitas_default[]" class="form-control" 
                                       value="{{ $pb->kuantitas_default }}" min="0" step="0.01">
                                <span class="input-group-text satuan-label">{{ $pb->komponenBop->satuan ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control biaya-preview" readonly 
                                   value="Rp {{ number_format($pb->kuantitas_default * ($pb->komponenBop->tarif_per_satuan ?? 0), 0, ',', '.') }}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-remove-bop">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="row bop-row mb-2">
                        <div class="col-md-5">
                            <select name="komponen_bop_id[]" class="form-select">
                                <option value="">-- Pilih Komponen BOP --</option>
                                @foreach($komponenBops as $komponen)
                                    <option value="{{ $komponen->id }}" data-satuan="{{ $komponen->satuan }}" data-tarif="{{ $komponen->tarif_per_satuan }}">
                                        {{ $komponen->nama_komponen }} ({{ $komponen->satuan }} @ Rp {{ number_format($komponen->tarif_per_satuan, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="number" name="kuantitas_default[]" class="form-control" value="0" min="0" step="0.01">
                                <span class="input-group-text satuan-label">-</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control biaya-preview" readonly placeholder="Biaya">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-remove-bop" disabled>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @endforelse
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btn-add-bop">
                    <i class="fas fa-plus"></i> Tambah Komponen BOP
                </button>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.proses-produksi.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('bop-container');
    const btnAdd = document.getElementById('btn-add-bop');
    
    // Add new row
    btnAdd.addEventListener('click', function() {
        const firstRow = container.querySelector('.bop-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('select').value = '';
        newRow.querySelector('input[type="number"]').value = '0';
        newRow.querySelector('.biaya-preview').value = '';
        newRow.querySelector('.satuan-label').textContent = '-';
        newRow.querySelector('.btn-remove-bop').disabled = false;
        container.appendChild(newRow);
        updateRemoveButtons();
    });
    
    // Remove row
    container.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-bop')) {
            const rows = container.querySelectorAll('.bop-row');
            if (rows.length > 1) {
                e.target.closest('.bop-row').remove();
            }
            updateRemoveButtons();
        }
    });
    
    // Update satuan dan biaya preview
    container.addEventListener('change', function(e) {
        if (e.target.tagName === 'SELECT') {
            const row = e.target.closest('.bop-row');
            const option = e.target.selectedOptions[0];
            const satuan = option.dataset.satuan || '-';
            const tarif = parseFloat(option.dataset.tarif) || 0;
            row.querySelector('.satuan-label').textContent = satuan;
            updateBiayaPreview(row, tarif);
        }
    });
    
    container.addEventListener('input', function(e) {
        if (e.target.name === 'kuantitas_default[]') {
            const row = e.target.closest('.bop-row');
            const select = row.querySelector('select');
            const option = select.selectedOptions[0];
            const tarif = parseFloat(option?.dataset?.tarif) || 0;
            updateBiayaPreview(row, tarif);
        }
    });
    
    function updateBiayaPreview(row, tarif) {
        const qty = parseFloat(row.querySelector('input[type="number"]').value) || 0;
        const biaya = qty * tarif;
        row.querySelector('.biaya-preview').value = 'Rp ' + biaya.toLocaleString('id-ID');
    }
    
    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.bop-row');
        rows.forEach((row, index) => {
            row.querySelector('.btn-remove-bop').disabled = rows.length === 1;
        });
    }
    
    updateRemoveButtons();
});
</script>
@endsection
