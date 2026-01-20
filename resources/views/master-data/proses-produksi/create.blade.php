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
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('master-data.btkl.store') }}" method="POST" id="createBtklForm">
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kapasitas per Jam <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="kapasitas_per_jam" class="form-control @error('kapasitas_per_jam') is-invalid @enderror" 
                                       value="{{ old('kapasitas_per_jam', 50) }}" min="1" step="1" placeholder="50" required>
                                <span class="input-group-text">unit/jam</span>
                            </div>
                            @error('kapasitas_per_jam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Jumlah unit yang dapat diproduksi per jam</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2" placeholder="Deskripsi proses produksi">{{ old('deskripsi') }}</textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.btkl.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createBtklForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        console.log('Form is being submitted...');
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        
        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
});
</script>
@endsection
