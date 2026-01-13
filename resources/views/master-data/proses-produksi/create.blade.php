@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cogs me-2"></i>Tambah BTKL
        </h2>
        <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-plus me-2"></i>Form BTKL Baru
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.btkl.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Proses <span class="text-danger">*</span></label>
                            <input type="text" name="nama_proses" class="form-control @error('nama_proses') is-invalid @enderror" 
                                   value="{{ old('nama_proses') }}" placeholder="Contoh: Menggoreng, Membumbui, Mengemas" required>
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
                                <input type="number" name="tarif_btkl" class="form-control @error('tarif_btkl') is-invalid @enderror" 
                                       value="{{ old('tarif_btkl', 0) }}" min="0" step="100" required>
                            </div>
                            @error('tarif_btkl')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Biaya Tenaga Kerja Langsung per satuan waktu</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Satuan BTKL <span class="text-danger">*</span></label>
                            <select name="satuan_btkl" class="form-select @error('satuan_btkl') is-invalid @enderror" required>
                                <option value="jam" {{ old('satuan_btkl') == 'jam' ? 'selected' : '' }}>Jam</option>
                                <option value="menit" {{ old('satuan_btkl') == 'menit' ? 'selected' : '' }}>Menit</option>
                                <option value="unit" {{ old('satuan_btkl') == 'unit' ? 'selected' : '' }}>Unit</option>
                                <option value="batch" {{ old('satuan_btkl') == 'batch' ? 'selected' : '' }}>Batch</option>
                            </select>
                            @error('satuan_btkl')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2" placeholder="Deskripsi proses produksi">{{ old('deskripsi') }}</textarea>
                </div>

                <hr>
                <h5 class="mb-3"><i class="fas fa-cogs"></i> Komponen BOP Default</h5>
                <p class="text-muted small">Komponen Biaya Overhead Pabrik yang digunakan dalam proses ini (opsional)</p>

                <div id="bop-container">
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
                                <input type="number" name="kuantitas_default[]" class="form-control" placeholder="Kuantitas" min="0" step="0.01" value="0">
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
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btn-add-bop">
                    <i class="fas fa-plus"></i> Tambah Komponen BOP
                </button>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.proses-produksi.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
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
    
    // Template untuk row baru
    const rowTemplate = container.querySelector('.bop-row').cloneNode(true);
    
    // Add new row
    btnAdd.addEventListener('click', function() {
        const newRow = rowTemplate.cloneNode(true);
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
});
</script>
@endsection
